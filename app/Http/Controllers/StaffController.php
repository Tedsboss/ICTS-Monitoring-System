<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Models\Staff;
use App\Traits\TracksHistoryTrait;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StaffController extends Controller
{
    use TracksHistoryTrait;

    public function index()
    {
        $this->authorize('viewAny', Staff::class);

        return view('staffs.index');
    }

    public function create()
    {
        // Staff creation is not part of this screen.
    }

    public function store(StaffRequest $request)
    {
        // Staff creation is not part of this screen.
    }

    public function show(Staff $staff)
    {
        // Staff information is displayed in the directory.
    }

    public function edit(Staff $staff)
    {
        $this->authorize('update', [Staff::class, $staff]);

        return redirect()->route('staffs.index');
    }

    public function update(StaffRequest $request, Staff $staff)
    {
        $this->authorize('update', [Staff::class, $staff]);
        $this->storeAllOldRelationshipValues($staff);

        DB::transaction(function () use ($request, &$staff) {
            $staff = Staff::whereKey($staff->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $staff->name = $request->input('name');
            $staff->abbreviation = $request->input('abbreviation');
            $staff->head_name = $request->input('head_name');
            $staff->head_position = $request->input('head_position');
            $staff->head_email = $request->input('head_email');
            $staff->updated_at = now();
            $staff->save();
        });

        return redirect()->route('staffs.index')
            ->with('succes', 'Staff successfully updated');
    }

    public function destroy(Staff $staff)
    {
        // Staff deletion is not part of this screen.
    }

    public function getstaffs()
    {
        $this->authorize('viewAny', Staff::class);

        $staffs = Staff::query()
            ->select([
                'id', 'name', 'abbreviation', 'head_name',
                'head_position', 'head_email', 'can_check', 'office_id',
            ])
            ->where(function ($query) {
                $query->where('is_DCO', 'Y')
                    ->orWhere(function ($query) {
                        $query->where('is_DCO', 'N')
                            ->whereNotNull('region_code');
                    });
            });

        return DataTables::of($staffs)
            ->addColumn('actions', function (Staff $staff) {
                if (! auth()->user()->can('update', [Staff::class, $staff])) {
                    return '<span aria-label="No actions available">—</span>';
                }

                $flags = JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
                    | JSON_HEX_TAG | JSON_THROW_ON_ERROR;

                // Limit the edit payload to fields used by the modal.
                $payload = json_encode([
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'abbreviation' => $staff->abbreviation,
                    'head_name' => $staff->head_name,
                    'head_position' => $staff->head_position,
                    'head_email' => $staff->head_email,
                ], $flags);

                $updateUrl = json_encode(route('staffs.update', $staff->id), $flags);
                $editHandler = 'showStaff(' . $payload . ', ' . $updateUrl . ')';
                $enabled = (int) $staff->can_check === 1;
                $statusLabel = $enabled ? 'Disable checking' : 'Enable checking';
                $confirmation = $enabled
                    ? 'Disable checking for this staff? Review eligibility for all its divisions will also be disabled.'
                    : 'Enable checking for this staff? Division review eligibility will remain unchanged.';
                $statusHandler = 'return confirm(' . json_encode($confirmation, $flags) . ');';
                $statusIcon = $enabled ? 'fa-unlock' : 'fa-lock';

                return '<div class="btn-group" role="group" aria-label="Staff actions">'
                    . '<button type="button" class="staff-action-edit" title="Edit staff" aria-label="'
                    . e('Edit ' . $staff->name) . '" onclick="' . e($editHandler) . '">'
                    . '<i class="fa fa-pencil" aria-hidden="true"></i></button>'
                    . '<a class="staff-action-status" href="' . e(route('staffs.switchstatus', $staff->id))
                    . '" title="' . e($statusLabel) . '" aria-label="' . e($statusLabel . ' for ' . $staff->name)
                    . '" onclick="' . e($statusHandler) . '"><i class="fa ' . $statusIcon
                    . '" aria-hidden="true"></i></a></div>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function switchstatus(Staff $staff)
    {
        $this->authorize('update', [Staff::class, $staff]);

        $enabled = DB::transaction(function () use ($staff) {
            $lockedStaff = Staff::whereKey($staff->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedStaff->can_check = (int) $lockedStaff->can_check === 1 ? 0 : 1;
            $lockedStaff->save();

            if ((int) $lockedStaff->can_check === 0) {
                foreach ($lockedStaff->divisions as $division) {
                    $division->can_review = 'N';
                    $division->save();
                }
            }

            return (int) $lockedStaff->can_check === 1;
        });

        return redirect()->route('staffs.index')->with(
            'succes',
            $enabled
                ? 'Staff checking enabled. Division review eligibility remains unchanged.'
                : 'Staff checking and division review eligibility disabled.'
        );
    }
}

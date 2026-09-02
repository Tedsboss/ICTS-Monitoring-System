<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgencyRequest;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class AgencyController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Agency::class);

        return view('agencies.index');
    }

    public function update(AgencyRequest $request, Agency $agency)
    {
        $this->authorize('update', [Agency::class, $agency]);

        DB::transaction(function () use ($request, &$agency) {
            // Lock the agency row while updating
            $agency = Agency::whereKey($agency->id)
                ->lockForUpdate()
                ->firstOrFail();

            $agency->UACS_AGY_DSC = $request->UACS_AGY_DSC;
            $agency->active = $request->active;
            $agency->head_lname = $request->head_lname;
            $agency->head_mname = $request->head_mname;
            $agency->head_fname = $request->head_fname;
            $agency->head_designation = $request->head_designation;
            $agency->head_telnumber = $request->head_telnumber;
            $agency->head_email = $request->head_email;
            $agency->save();
        });

        // Keep the existing flash key used by the application
        return redirect()
            ->route('agencies.index')
            ->with('succes', 'Agency succesfully updated');
    }

    public function getagencies(Request $request)
    {
        $this->authorize('viewAny', Agency::class);

        // No join is used here, so GROUP BY and SQL_MODE override are unnecessary
        $agencies = Agency::query()->select([
            'agencies.id',
            'agencies.UACS_AGY_ID',
            'agencies.UACS_AGY_DSC',
            'agencies.Abbreviation',
            'agencies.active',
            'agencies.head_lname',
            'agencies.head_mname',
            'agencies.head_fname',
            'agencies.head_designation',
            'agencies.head_telnumber',
            'agencies.head_email',
            'agencies.updated_at',
        ]);

        return DataTables::of($agencies)
            ->editColumn('active', function (Agency $agency) {
                if ($agency->active === null) {
                    return [
                        'text' => 'Unknown',
                        'html' => '<span class="badge bg-secondary">Unknown</span>',
                    ];
                }

                return $agency->active == 1
                    ? [
                        'text' => 'Active',
                        'html' => '<span class="badge bg-success">Active</span>',
                    ]
                    : [
                        'text' => 'Inactive',
                        'html' => '<span class="badge bg-secondary">Inactive</span>',
                    ];
            })
            ->addColumn('head_name', function (Agency $agency) {
                return $this->formatHeadName($agency);
            })
            ->addColumn('actions', function (Agency $agency) {
                return $this->agencyActions($agency);
            })
            ->rawColumns(['active.html', 'actions'])
            ->toJson();
    }

    public function switchstatus(Agency $agency)
    {
        $this->authorize('update', [Agency::class, $agency]);

        DB::transaction(function () use (&$agency) {
            // Lock the row to avoid conflicting status changes
            $agency = Agency::whereKey($agency->id)
                ->lockForUpdate()
                ->firstOrFail();

            $agency->active = $agency->active == 1 ? 0 : 1;
            $agency->save();
        });

        // Keep the existing flash key used by the application
        return redirect()
            ->route('agencies.index')
            ->with('succes', 'Agency status has been updated');
    }

    // Format agency head name
    private function formatHeadName(Agency $agency): string
    {
        $firstName = trim((string) $agency->head_fname);
        $middleName = trim((string) $agency->head_mname);
        $lastName = trim((string) $agency->head_lname);

        if ($middleName !== '') {
            $middleName = mb_substr($middleName, 0, 1) . '.';
        }

        return collect([
            $firstName,
            $middleName,
            $lastName,
        ])->filter()->implode(' ');
    }

    // Build action buttons
    private function agencyActions(Agency $agency): string
    {
        if (! auth()->user()->can('update', [Agency::class, $agency])) {
            return '';
        }

        $agencyJson = json_encode(
            $agency,
            JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG
        );

        $editUrl = route('agencies.update', $agency->id);
        $statusUrl = route('agencies.switchstatus', $agency->id);

        $edit = '
            <button
                type="button"
                class="border-0 bg-transparent px-1"
                data-bs-toggle="tooltip"
                data-bs-original-title="Edit Agency"
                onclick=\'showAgency(' . $agencyJson . ', "' . $editUrl . '")\'
            >
                <i class="fa fa-pencil text-info"></i>
            </button>
        ';

        $statusTitle = $agency->active == 1
            ? 'Deactivate Agency'
            : 'Activate Agency';

        $statusIcon = $agency->active == 1
            ? 'fa-unlock text-success'
            : 'fa-lock text-secondary';

        $status = '
            <a
                href="' . $statusUrl . '"
                class="px-1"
                data-bs-toggle="tooltip"
                data-bs-original-title="' . $statusTitle . '"
                onclick="return confirmAgencyStatusChange(this, \'' . addslashes($statusTitle) . '\')"
            >
                <i class="fa ' . $statusIcon . '"></i>
            </a>
        ';

        return '<div class="btn-group" role="group">' . $edit . $status . '</div>';
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffPersonnel;
use App\Traits\GenerateLogs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffPersonnelController extends Controller
{
    use GenerateLogs;

    // Only DIREK administrators can maintain the personnel master list
    private function authorizeAdministrator(): void
    {
        abort_unless(
            auth()->check()
            && in_array((int) auth()->user()->role_id, [1, 29], true),
            403
        );
    }

    // Personnel list
    public function index(Request $request): View
    {
        $this->authorizeAdministrator();

        $staffId = $request->filled('staff_id')
            ? (int) $request->input('staff_id')
            : null;

        $personnelQuery = StaffPersonnel::query()
            ->with('staff')
            ->orderBy('name');

        if ($staffId !== null) {
            $personnelQuery->where('staff_id', $staffId);
        }

        $personnel = $personnelQuery->get();

        // Load all Staff/Office records for filtering and forms
        $staffs = Staff::query()
            ->orderBy('name')
            ->get();

        return view('staff-personnel.index', [
            'personnel' => $personnel,
            'staffs'    => $staffs,
            'staffId'   => $staffId,
        ]);
    }

    // Add personnel
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdministrator();

        $validated = $request->validate([
            'staff_id'  => ['required', 'integer', 'exists:staffs,id'],
            'name'      => ['required', 'string', 'max:150'],
            'position'  => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $name = trim($validated['name']);

        // Prevent duplicate personnel names inside the same Staff/Office
        $exists = StaffPersonnel::query()
            ->where('staff_id', $validated['staff_id'])
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'name' => 'This personnel already exists under the selected Staff/Office.',
                ]);
        }

        $personnel = StaffPersonnel::create([
            'staff_id'  => $validated['staff_id'],
            'name'      => $name,
            'position'  => filled($validated['position'] ?? null)
                ? trim($validated['position'])
                : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->addSystemLogs(
            "Staff Personnel: Added {$personnel->name}",
            auth()->id(),
            auth()->user()->name ?? auth()->user()->email ?? null,
            $request->getClientIp(true),
            'staff_personnel',
            (int) $personnel->id
        );

        return redirect()
            ->route('staff-personnel.index', [
                'staff_id' => $personnel->staff_id,
            ])
            ->with('success', 'Staff personnel added successfully.');
    }

    // Update personnel
    public function update(
        Request $request,
        StaffPersonnel $staff_personnel
    ): RedirectResponse {
        $this->authorizeAdministrator();

        $validated = $request->validate([
            'staff_id'  => ['required', 'integer', 'exists:staffs,id'],
            'name'      => ['required', 'string', 'max:150'],
            'position'  => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $name = trim($validated['name']);

        // Prevent another personnel record from using the same name
        // under the same Staff/Office
        $exists = StaffPersonnel::query()
            ->where('staff_id', $validated['staff_id'])
            ->where('id', '!=', $staff_personnel->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'name' => 'This personnel already exists under the selected Staff/Office.',
                ]);
        }

        $oldName = $staff_personnel->name;

        $staff_personnel->update([
            'staff_id'  => $validated['staff_id'],
            'name'      => $name,
            'position'  => filled($validated['position'] ?? null)
                ? trim($validated['position'])
                : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->addSystemLogs(
            "Staff Personnel: Updated {$oldName} to {$staff_personnel->name}",
            auth()->id(),
            auth()->user()->name ?? auth()->user()->email ?? null,
            $request->getClientIp(true),
            'staff_personnel',
            (int) $staff_personnel->id
        );

        return redirect()
            ->route('staff-personnel.index', [
                'staff_id' => $staff_personnel->staff_id,
            ])
            ->with('success', 'Staff personnel updated successfully.');
    }

    // Activate or deactivate personnel
    public function switchstatus(
        Request $request,
        StaffPersonnel $staff_personnel
    ): RedirectResponse {
        $this->authorizeAdministrator();

        $oldStatus = $staff_personnel->is_active;

        $staff_personnel->update([
            'is_active' => ! $oldStatus,
        ]);

        $status = $staff_personnel->is_active
            ? 'activated'
            : 'deactivated';

        $this->addSystemLogs(
            "Staff Personnel: {$staff_personnel->name} {$status}",
            auth()->id(),
            auth()->user()->name ?? auth()->user()->email ?? null,
            $request->getClientIp(true),
            'staff_personnel',
            (int) $staff_personnel->id
        );

        return redirect()
            ->back()
            ->with(
                'success',
                "Staff personnel {$status} successfully."
            );
    }

    // Delete personnel
    public function destroy(
        Request $request,
        StaffPersonnel $staff_personnel
    ): RedirectResponse {
        $this->authorizeAdministrator();

        $name = $staff_personnel->name;
        $staffId = $staff_personnel->staff_id;
        $personnelId = $staff_personnel->id;

        /*
         * Personnel should normally be deactivated instead of deleted.
         * Delete is allowed only for cleanup of records added by mistake.
         *
         * Financial Plans currently preserve assigned personnel as text,
         * so deleting this master record does not delete historical
         * Financial Plan assignments.
         */
        $staff_personnel->delete();

        $this->addSystemLogs(
            "Staff Personnel: Deleted {$name}",
            auth()->id(),
            auth()->user()->name ?? auth()->user()->email ?? null,
            $request->getClientIp(true),
            'staff_personnel',
            (int) $personnelId
        );

        return redirect()
            ->route('staff-personnel.index', [
                'staff_id' => $staffId,
            ])
            ->with('success', 'Staff personnel deleted successfully.');
    }
}

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

    private function authorizeAdministrator(): void
    {
        abort_unless(
            auth()->check()
                && in_array((int) auth()->user()->role_id, [1, 29], true),
            403
        );
    }

    public function index(Request $request): View
    {
        $this->authorizeAdministrator();

        $staffId = $request->filled('staff_id')
            ? (int) $request->input('staff_id')
            : null;

        $query = StaffPersonnel::query()->with('staff')->orderBy('name');

        if ($staffId !== null) {
            $query->where('staff_id', $staffId);
        }

        return view('staff-personnel.index', [
            'personnel' => $query->get(),
            'staffs' => Staff::query()->orderBy('name')->get(),
            'staffId' => $staffId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdministrator();
        $request->merge(['_editing_personnel_id' => null]);
        $validated = $this->validatePersonnel($request);

        if ($this->duplicateExists((int) $validated['staff_id'], $validated['name'])) {
            return back()->withInput()->withErrors([
                'name' => 'This personnel already exists under the selected Staff/Office.',
            ]);
        }

        $personnel = StaffPersonnel::create([
            'staff_id' => $validated['staff_id'],
            'name' => $validated['name'],
            'position' => $validated['position'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->logPersonnel(
            $request,
            "Staff Personnel: Added {$personnel->name}",
            (int) $personnel->id
        );

        return redirect()->route('staff-personnel.index', [
            'staff_id' => $personnel->staff_id,
        ])->with('succes', 'Staff personnel added successfully.');
    }

    public function update(Request $request, StaffPersonnel $staff_personnel): RedirectResponse
    {
        $this->authorizeAdministrator();

        // Validation recovery uses the bound record, never a posted record ID.
        $request->merge(['_editing_personnel_id' => $staff_personnel->getKey()]);
        $validated = $this->validatePersonnel($request);

        if ($this->duplicateExists(
            (int) $validated['staff_id'],
            $validated['name'],
            (int) $staff_personnel->id
        )) {
            return back()->withInput()->withErrors([
                'name' => 'This personnel already exists under the selected Staff/Office.',
            ]);
        }

        $oldName = $staff_personnel->name;
        $updates = [
            'staff_id' => $validated['staff_id'],
            'name' => $validated['name'],
            'position' => $validated['position'],
        ];

        // Editing details must not deactivate a person when no status was posted.
        if (array_key_exists('is_active', $validated)) {
            $updates['is_active'] = $request->boolean('is_active');
        }

        $staff_personnel->update($updates);

        $this->logPersonnel(
            $request,
            "Staff Personnel: Updated {$oldName} to {$staff_personnel->name}",
            (int) $staff_personnel->id
        );

        return redirect()->route('staff-personnel.index', [
            'staff_id' => $staff_personnel->staff_id,
        ])->with('succes', 'Staff personnel updated successfully.');
    }

    public function switchstatus(Request $request, StaffPersonnel $staff_personnel): RedirectResponse
    {
        $this->authorizeAdministrator();

        $staff_personnel->update([
            'is_active' => ! $staff_personnel->is_active,
        ]);

        $status = $staff_personnel->is_active ? 'activated' : 'deactivated';

        $this->logPersonnel(
            $request,
            "Staff Personnel: {$staff_personnel->name} {$status}",
            (int) $staff_personnel->id
        );

        return back()->with('succes', "Staff personnel {$status} successfully.");
    }

    public function destroy(Request $request, StaffPersonnel $staff_personnel): RedirectResponse
    {
        $this->authorizeAdministrator();

        $name = $staff_personnel->name;
        $staffId = $staff_personnel->staff_id;
        $personnelId = (int) $staff_personnel->id;

        // Existing Financial Plan assignments remain stored as text.
        $staff_personnel->delete();

        $this->logPersonnel($request, "Staff Personnel: Deleted {$name}", $personnelId);

        return redirect()->route('staff-personnel.index', [
            'staff_id' => $staffId,
        ])->with('succes', 'Staff personnel deleted successfully.');
    }

    private function validatePersonnel(Request $request): array
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:staffs,id'],
            'name' => ['required', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['position'] = filled($validated['position'] ?? null)
            ? trim($validated['position'])
            : null;

        return $validated;
    }

    private function duplicateExists(int $staffId, string $name, ?int $ignoreId = null): bool
    {
        $query = StaffPersonnel::query()
            ->where('staff_id', $staffId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function logPersonnel(Request $request, string $message, int $personnelId): void
    {
        $this->addSystemLogs(
            $message,
            auth()->id(),
            auth()->user()->name ?? auth()->user()->email ?? null,
            $request->getClientIp(true),
            'staff_personnel',
            $personnelId
        );
    }
}

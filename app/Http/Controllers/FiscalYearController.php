<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FiscalYearController extends Controller
{
    private function hasAllocationPermission(string $permission): bool
    {
        $user = auth()->user();

        if ($user->isAdministrator()) {
            return true;
        }

        return $user->role
            && $user->role->permissions->contains(function ($permissionModel) use ($permission) {
                return strcasecmp(
                    (string) optional($permissionModel->module)->name,
                    'Allocation Management'
                ) === 0
                && strcasecmp(
                    (string) $permissionModel->name,
                    $permission
                ) === 0;
            });
    }

    private function scopedFiscalYears()
    {
        $query = FiscalYear::query();

        if (! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;

            if ($staffId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('staff_id', (int) $staffId);
            }
        }

        return $query;
    }

    private function authorizeFiscalYear(
        FiscalYear $fiscalYear,
        string $permission
    ): void {
        abort_unless(
            $this->hasAllocationPermission($permission),
            403
        );

        if (auth()->user()->isAdministrator()) {
            return;
        }

        $staffId = auth()->user()->staff_id;

        abort_unless(
            $staffId !== null
            && (int) $fiscalYear->staff_id === (int) $staffId,
            403
        );
    }

    public function index(): View
    {
        abort_unless(
            $this->hasAllocationPermission('view'),
            403
        );

        $fiscalYears = $this->scopedFiscalYears()
            ->withCount('allocations')
            ->orderByDesc('year')
            ->get();

        return view(
            'fiscal-years.index',
            compact('fiscalYears')
        );
    }

    public function create(): View
    {
        abort_unless(
            $this->hasAllocationPermission('add'),
            403
        );

        $staffOptions = auth()->user()->isAdministrator()
            ? Staff::query()
                ->orderBy('name')
                ->get(['id', 'name', 'abbreviation'])
            : Staff::query()
                ->where('id', auth()->user()->staff_id)
                ->get(['id', 'name', 'abbreviation']);

        return view(
            'fiscal-years.create',
            compact('staffOptions')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(
            $this->hasAllocationPermission('add'),
            403
        );

        $validated = $request->validate([
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],
        ]);

        $staffId = auth()->user()->isAdministrator()
            ? $request->input('staff_id')
            : auth()->user()->staff_id;

        if (! auth()->user()->isAdministrator()) {
            abort_unless(
                $staffId !== null,
                403
            );
        }

        if (auth()->user()->isAdministrator() && $staffId === null) {
            return back()
                ->withInput()
                ->withErrors([
                    'staff_id' => 'Please select a Staff / Office.',
                ]);
        }

        $staffId = (int) $staffId;

        $duplicateExists = FiscalYear::query()
            ->where('year', $validated['year'])
            ->where('staff_id', $staffId)
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'year' => 'This fiscal year already exists for the selected Staff / Office.',
                ]);
        }

        FiscalYear::create([
            'year' => $validated['year'],
            'staff_id' => $staffId,
        ]);

        return redirect()
            ->route('fiscal-years.index')
            ->with('success', 'Fiscal year created successfully.');
    }

    public function edit(FiscalYear $fiscalYear): View
    {
        $this->authorizeFiscalYear($fiscalYear, 'edit');

        $staffOptions = auth()->user()->isAdministrator()
            ? Staff::query()
                ->orderBy('name')
                ->get(['id', 'name', 'abbreviation'])
            : Staff::query()
                ->where('id', auth()->user()->staff_id)
                ->get(['id', 'name', 'abbreviation']);

        return view(
            'fiscal-years.edit',
            compact('fiscalYear', 'staffOptions')
        );
    }

    public function update(
        Request $request,
        FiscalYear $fiscalYear
    ): RedirectResponse {
        $this->authorizeFiscalYear($fiscalYear, 'edit');

        $validated = $request->validate([
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],
        ]);

        $staffId = auth()->user()->isAdministrator()
            ? $request->input('staff_id', $fiscalYear->staff_id)
            : auth()->user()->staff_id;

        if (! auth()->user()->isAdministrator()) {
            abort_unless(
                $staffId !== null
                && (int) $fiscalYear->staff_id === (int) $staffId,
                403
            );
        }

        if ($staffId === null) {
            return back()
                ->withInput()
                ->withErrors([
                    'staff_id' => 'Please select a Staff / Office.',
                ]);
        }

        $staffId = (int) $staffId;

        $duplicateExists = FiscalYear::query()
            ->where('year', $validated['year'])
            ->where('staff_id', $staffId)
            ->where('id', '!=', $fiscalYear->id)
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'year' => 'This fiscal year already exists for the selected Staff / Office.',
                ]);
        }

        $fiscalYear->update([
            'year' => $validated['year'],
            'staff_id' => $staffId,
        ]);

        return redirect()
            ->route('fiscal-years.index')
            ->with('success', 'Fiscal year updated successfully.');
    }

    public function destroy(FiscalYear $fiscalYear): RedirectResponse
    {
        $this->authorizeFiscalYear($fiscalYear, 'delete');

        if ($fiscalYear->allocations()->exists()) {
            return redirect()
                ->route('fiscal-years.index')
                ->with(
                    'error',
                    'This fiscal year cannot be deleted because it is already used by an allocation.'
                );
        }

        $fiscalYear->delete();

        return redirect()
            ->route('fiscal-years.index')
            ->with('success', 'Fiscal year deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\ExpenseType;
use App\Models\FiscalYear;
use App\Models\Level;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AllocationController extends Controller
{
    private function isAdmin(): bool
    {
        return in_array((int) auth()->user()->role_id, [1, 29], true);
    }

    private function hasAllocationPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $user = auth()->user();

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

    private function scopedAllocations()
    {
        $query = Allocation::query();

        if (! $this->isAdmin()) {
            $staffId = auth()->user()->staff_id;

            if ($staffId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('staff_id', (int) $staffId);
            }
        }

        return $query;
    }

    private function authorizeAllocation(
        Allocation $allocation,
        string $permission
    ): void {
        abort_unless(
            $this->hasAllocationPermission($permission),
            403
        );

        if ($this->isAdmin()) {
            return;
        }

        $staffId = auth()->user()->staff_id;

        abort_unless(
            $staffId !== null &&
            (int) $allocation->staff_id === (int) $staffId,
            403
        );
    }

    public function index(Request $request): View
    {
        abort_unless(
            $this->hasAllocationPermission('view'),
            403
        );

        $allocations = $this->scopedAllocations()
            ->with([
                'fiscalYear',
                'level',
                'staff',
                'expenses.expenseType',
            ])
            ->when(
                $request->filled('year_id'),
                fn ($query) => $query->where(
                    'year_id',
                    $request->integer('year_id')
                )
            )
            ->when(
                $request->filled('level_id'),
                fn ($query) => $query->where(
                    'level_id',
                    $request->integer('level_id')
                )
            )
            ->orderByDesc(
                FiscalYear::select('year')
                    ->whereColumn(
                        'fiscal_years.id',
                        'allocations.year_id'
                    )
            )
            ->get();

        $fiscalYears = FiscalYear::orderByDesc('year')->get();
        $levels = Level::orderBy('level_code')->get();

        return view(
            'allocations.index',
            compact('allocations', 'fiscalYears', 'levels')
        );
    }

    public function create(): View
    {
        abort_unless(
            $this->hasAllocationPermission('add'),
            403
        );

        $fiscalYears = FiscalYear::orderByDesc('year')->get();

        $levels = Level::orderBy('level_code')->get();

        $expenseTypes = ExpenseType::orderBy('type')
            ->orderBy('expense_description')
            ->get();

        $staffOptions = $this->isAdmin()
            ? Staff::query()
                ->orderBy('name')
                ->get(['id', 'name', 'abbreviation'])
            : Staff::query()
                ->where('id', auth()->user()->staff_id)
                ->get(['id', 'name', 'abbreviation']);

        return view(
            'allocations.create',
            compact(
                'fiscalYears',
                'levels',
                'expenseTypes',
                'staffOptions'
            )
        );
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(
            $this->hasAllocationPermission('add'),
            403
        );

        $validated = $request->validate([
            'year_id' => [
                'required',
                'integer',
                'exists:fiscal_years,id',
            ],
            'level_id' => [
                'required',
                'integer',
                'exists:levels,id',
            ],
            'staff_id' => [
                'required',
                'integer',
                'exists:staffs,id',
            ],
            'expenses' => [
                'required',
                'array',
                'min:1',
            ],
            'expenses.*.expense_id' => [
                'required',
                'integer',
                'exists:expense_types,id',
                'distinct',
            ],
            'expenses.*.cost' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);

        if (! $this->isAdmin()) {
            abort_unless(
                auth()->user()->staff_id !== null &&
                (int) $validated['staff_id'] === (int) auth()->user()->staff_id,
                403
            );
        }

        $duplicateExists = Allocation::query()
            ->where('year_id', $validated['year_id'])
            ->where('level_id', $validated['level_id'])
            ->where('staff_id', $validated['staff_id'])
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'level_id' => 'An allocation already exists for this Staff/Office, Fiscal Year, and Level.',
                ]);
        }

        DB::transaction(function () use ($validated) {
            $allocation = Allocation::create([
                'year_id' => $validated['year_id'],
                'level_id' => $validated['level_id'],
                'staff_id' => $validated['staff_id'],
            ]);

            foreach ($validated['expenses'] as $expense) {
                $allocation->expenses()->create([
                    'expense_id' => $expense['expense_id'],
                    'cost' => $expense['cost'],
                ]);
            }
        });

        return redirect()
            ->route('allocations.index')
            ->with('success', 'Allocation created successfully.');
    }

    public function edit(Allocation $allocation): View
    {
        $this->authorizeAllocation($allocation, 'edit');

        $allocation->load([
            'fiscalYear',
            'level',
            'staff',
            'expenses.expenseType',
        ]);

        $fiscalYears = FiscalYear::orderByDesc('year')->get();

        $levels = Level::orderBy('level_code')->get();

        $expenseTypes = ExpenseType::orderBy('type')
            ->orderBy('expense_description')
            ->get();

        $staffOptions = $this->isAdmin()
            ? Staff::query()
                ->orderBy('name')
                ->get(['id', 'name', 'abbreviation'])
            : Staff::query()
                ->where('id', auth()->user()->staff_id)
                ->get(['id', 'name', 'abbreviation']);

        return view(
            'allocations.edit',
            compact(
                'allocation',
                'fiscalYears',
                'levels',
                'expenseTypes',
                'staffOptions'
            )
        );
    }

    public function update(
        Request $request,
        Allocation $allocation
    ): RedirectResponse {
        $this->authorizeAllocation($allocation, 'edit');

        $validated = $request->validate([
            'year_id' => [
                'required',
                'integer',
                'exists:fiscal_years,id',
            ],
            'level_id' => [
                'required',
                'integer',
                'exists:levels,id',
            ],
            'staff_id' => [
                'required',
                'integer',
                'exists:staffs,id',
            ],
            'expenses' => [
                'required',
                'array',
                'min:1',
            ],
            'expenses.*.expense_id' => [
                'required',
                'integer',
                'exists:expense_types,id',
                'distinct',
            ],
            'expenses.*.cost' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);

        if (! $this->isAdmin()) {
            abort_unless(
                auth()->user()->staff_id !== null &&
                (int) $validated['staff_id'] === (int) auth()->user()->staff_id,
                403
            );
        }

        $duplicateExists = Allocation::query()
            ->where('year_id', $validated['year_id'])
            ->where('level_id', $validated['level_id'])
            ->where('staff_id', $validated['staff_id'])
            ->where('id', '!=', $allocation->id)
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'level_id' => 'An allocation already exists for this Staff/Office, Fiscal Year, and Level.',
                ]);
        }

        DB::transaction(function () use ($validated, $allocation) {
            $allocation->update([
                'year_id' => $validated['year_id'],
                'level_id' => $validated['level_id'],
                'staff_id' => $validated['staff_id'],
            ]);

            $allocation->expenses()->delete();

            foreach ($validated['expenses'] as $expense) {
                $allocation->expenses()->create([
                    'expense_id' => $expense['expense_id'],
                    'cost' => $expense['cost'],
                ]);
            }
        });

        return redirect()
            ->route('allocations.index')
            ->with('success', 'Allocation updated successfully.');
    }

    public function destroy(Allocation $allocation): RedirectResponse
    {
        $this->authorizeAllocation($allocation, 'delete');

        $allocation->delete();

        return redirect()
            ->route('allocations.index')
            ->with('success', 'Allocation deleted successfully.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseTypeController extends Controller
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

    private function scopedExpenseTypes()
    {
        $query = ExpenseType::query();

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

    private function authorizeExpenseType(
        ExpenseType $expenseType,
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
            (int) $expenseType->staff_id === (int) $staffId,
            403
        );
    }

    private function staffOptions()
    {
        return $this->isAdmin()
            ? Staff::query()
                ->orderBy('name')
                ->get(['id', 'name', 'abbreviation'])
            : Staff::query()
                ->where('id', auth()->user()->staff_id)
                ->get(['id', 'name', 'abbreviation']);
    }

    public function index(): View
    {
        abort_unless(
            $this->hasAllocationPermission('view'),
            403
        );

        $expenseTypes = $this->scopedExpenseTypes()
            ->with(['staff'])
            ->withCount('allocationExpenses')
            ->orderBy('type')
            ->orderBy('expense_description')
            ->get();

        return view(
            'expense-types.index',
            compact('expenseTypes')
        );
    }

    public function create(): View
    {
        abort_unless(
            $this->hasAllocationPermission('add'),
            403
        );

        $staffOptions = $this->staffOptions();

        return view(
            'expense-types.create',
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
            'type' => [
                'required',
                'string',
                'max:50',
            ],
            'expense_description' => [
                'required',
                'string',
                'max:150',
            ],
            'staff_id' => [
                'nullable',
                'integer',
                'exists:staffs,id',
            ],
        ]);

        $staffId = $this->isAdmin()
            ? $validated['staff_id'] ?? null
            : auth()->user()->staff_id;

        if ($staffId === null) {
            return back()
                ->withInput()
                ->withErrors([
                    'staff_id' => 'Please select a Staff / Office.',
                ]);
        }

        $staffId = (int) $staffId;

        if (! $this->isAdmin()) {
            abort_unless(
                $staffId === (int) auth()->user()->staff_id,
                403
            );
        }

        $duplicateExists = ExpenseType::query()
            ->where('type', $validated['type'])
            ->where('expense_description', $validated['expense_description'])
            ->where('staff_id', $staffId)
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'expense_description' => 'This Expense Type and Description already exists for the selected Staff / Office.',
                ]);
        }

        ExpenseType::create([
            'type' => $validated['type'],
            'expense_description' => $validated['expense_description'],
            'staff_id' => $staffId,
        ]);

        return redirect()
            ->route('expense-types.index')
            ->with('success', 'Expense type created successfully.');
    }

    public function edit(ExpenseType $expenseType): View
    {
        $this->authorizeExpenseType($expenseType, 'edit');

        $staffOptions = $this->staffOptions();

        return view(
            'expense-types.edit',
            compact('expenseType', 'staffOptions')
        );
    }

    public function update(
        Request $request,
        ExpenseType $expenseType
    ): RedirectResponse {
        $this->authorizeExpenseType($expenseType, 'edit');

        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                'max:50',
            ],
            'expense_description' => [
                'required',
                'string',
                'max:150',
            ],
            'staff_id' => [
                'nullable',
                'integer',
                'exists:staffs,id',
            ],
        ]);

        $staffId = $this->isAdmin()
            ? $validated['staff_id'] ?? $expenseType->staff_id
            : auth()->user()->staff_id;

        if ($staffId === null) {
            return back()
                ->withInput()
                ->withErrors([
                    'staff_id' => 'Please select a Staff / Office.',
                ]);
        }

        $staffId = (int) $staffId;

        if (! $this->isAdmin()) {
            abort_unless(
                $staffId === (int) auth()->user()->staff_id &&
                (int) $expenseType->staff_id === (int) auth()->user()->staff_id,
                403
            );
        }

        $duplicateExists = ExpenseType::query()
            ->where('type', $validated['type'])
            ->where('expense_description', $validated['expense_description'])
            ->where('staff_id', $staffId)
            ->where('id', '!=', $expenseType->id)
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'expense_description' => 'This Expense Type and Description already exists for the selected Staff / Office.',
                ]);
        }

        $expenseType->update([
            'type' => $validated['type'],
            'expense_description' => $validated['expense_description'],
            'staff_id' => $staffId,
        ]);

        return redirect()
            ->route('expense-types.index')
            ->with('success', 'Expense type updated successfully.');
    }

    public function destroy(ExpenseType $expenseType): RedirectResponse
    {
        $this->authorizeExpenseType($expenseType, 'delete');

        if ($expenseType->allocationExpenses()->exists()) {
            return redirect()
                ->route('expense-types.index')
                ->with(
                    'error',
                    'This expense type cannot be deleted because it is already used by an allocation.'
                );
        }

        $expenseType->delete();

        return redirect()
            ->route('expense-types.index')
            ->with('success', 'Expense type deleted successfully.');
    }
}
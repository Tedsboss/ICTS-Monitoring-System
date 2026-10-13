<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExpenseTypeController extends Controller
{
    private function hasAllocationPermission(string $permission): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isAdministrator()) {
            return true;
        }

        return $user->role
            && $user->role->permissions->contains(function ($permissionModel) use ($permission) {
                return strcasecmp((string) optional($permissionModel->module)->name, 'Allocation Management') === 0
                    && strcasecmp((string) $permissionModel->name, $permission) === 0;
            });
    }

    private function scopedExpenseTypes(): Builder
    {
        $query = ExpenseType::query();

        if (! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;
            $staffId === null
                ? $query->whereRaw('1 = 0')
                : $query->where('staff_id', (int) $staffId);
        }

        return $query;
    }

    private function authorizeExpenseType(ExpenseType $expenseType, string $permission): void
    {
        abort_unless($this->hasAllocationPermission($permission), 403);

        if (! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;
            abort_unless($staffId !== null && (int) $expenseType->staff_id === (int) $staffId, 403);
        }
    }

    private function staffOptions()
    {
        return auth()->user()->isAdministrator()
            ? Staff::query()->orderBy('name')->get(['id', 'name', 'abbreviation'])
            : Staff::query()->where('id', auth()->user()->staff_id)->get(['id', 'name', 'abbreviation']);
    }

    public function index(): View
    {
        abort_unless($this->hasAllocationPermission('view'), 403);

        return view('expense-types.index', [
            'expenseTypes' => $this->scopedExpenseTypes()
                ->with('staff')->withCount('allocationExpenses')
                ->orderBy('type')->orderBy('expense_description')->get(),
            'canAddExpenseTypes' => $this->hasAllocationPermission('add'),
            'canEditExpenseTypes' => $this->hasAllocationPermission('edit'),
            'canDeleteExpenseTypes' => $this->hasAllocationPermission('delete'),
        ]);
    }

    public function create(): View
    {
        abort_unless($this->hasAllocationPermission('add'), 403);

        return view('expense-types.create', ['staffOptions' => $this->staffOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->hasAllocationPermission('add'), 403);
        $data = $this->validateExpenseType($request);

        DB::transaction(function () use ($data) {
            $this->lockStaffs([(int) $data['staff_id']]);
            $this->rejectDuplicate($data);
            ExpenseType::create($data);
        });

        return redirect()->route('expense-types.index')
            ->with('success', 'Expense type created successfully.');
    }

    public function edit(ExpenseType $expenseType): View
    {
        $this->authorizeExpenseType($expenseType, 'edit');

        return view('expense-types.edit', [
            'expenseType' => $expenseType,
            'staffOptions' => $this->staffOptions(),
        ]);
    }

    public function update(Request $request, ExpenseType $expenseType): RedirectResponse
    {
        $this->authorizeExpenseType($expenseType, 'edit');
        $data = $this->validateExpenseType($request);

        DB::transaction(function () use ($data, $expenseType) {
            $this->lockStaffs([(int) $expenseType->staff_id, (int) $data['staff_id']]);
            $locked = ExpenseType::whereKey($expenseType->getKey())->lockForUpdate()->firstOrFail();
            $this->authorizeExpenseType($locked, 'edit');

            if ($locked->allocationExpenses()->exists()) {
                $errors = [];

                if (strtoupper(trim((string) $locked->type)) !== $data['type']) {
                    $errors['type'] = 'The classification cannot be changed because this expense type is used by an allocation.';
                }

                if ((int) $locked->staff_id !== (int) $data['staff_id']) {
                    $errors['staff_id'] = 'The Staff / Office cannot be changed because this expense type is used by an allocation.';
                }

                if ($errors) {
                    throw ValidationException::withMessages($errors);
                }
            }

            $this->rejectDuplicate($data, (int) $locked->id);
            $locked->update($data);
        });

        return redirect()->route('expense-types.index')
            ->with('success', 'Expense type updated successfully.');
    }

    public function destroy(ExpenseType $expenseType): RedirectResponse
    {
        $this->authorizeExpenseType($expenseType, 'delete');

        $deleted = DB::transaction(function () use ($expenseType) {
            $locked = ExpenseType::whereKey($expenseType->getKey())->lockForUpdate()->firstOrFail();
            $this->authorizeExpenseType($locked, 'delete');

            if ($locked->allocationExpenses()->exists()) {
                return false;
            }

            $locked->delete();
            return true;
        });

        return redirect()->route('expense-types.index')->with(
            $deleted ? 'success' : 'error',
            $deleted
                ? 'Expense type deleted successfully.'
                : 'This expense type cannot be deleted because it is already used by an allocation.'
        );
    }

    private function validateExpenseType(Request $request): array
    {
        foreach (['type', 'expense_description'] as $field) {
            if (is_string($request->input($field))) {
                $value = trim($request->input($field));
                $request->merge([$field => $field === 'type' ? strtoupper($value) : $value]);
            }
        }

        if (! auth()->user()->isAdministrator()) {
            $request->merge(['staff_id' => auth()->user()->staff_id]);
        }

        return $request->validate([
            'type' => ['required', 'string', Rule::in(['MOOE', 'CO'])],
            'expense_description' => ['required', 'string', 'max:150'],
            'staff_id' => ['required', 'integer', 'exists:staffs,id'],
        ], [
            'type.in' => 'Please select MOOE or Capital Outlay (CO).',
            'staff_id.required' => 'Please select a Staff / Office.',
        ]);
    }

    private function lockStaffs(array $staffIds): void
    {
        // Consistent lock order serializes duplicate checks within each office.
        Staff::whereIn('id', array_unique($staffIds))
            ->orderBy('id')->lockForUpdate()->get(['id']);
    }

    private function rejectDuplicate(array $data, ?int $ignoreId = null): void
    {
        $query = ExpenseType::query()
            ->where('staff_id', (int) $data['staff_id'])
            ->whereRaw('LOWER(TRIM(type)) = ?', [mb_strtolower($data['type'])])
            ->whereRaw('LOWER(TRIM(expense_description)) = ?', [mb_strtolower($data['expense_description'])]);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'expense_description' => 'This Expense Type and Description already exists for the selected Staff / Office.',
            ]);
        }
    }
}

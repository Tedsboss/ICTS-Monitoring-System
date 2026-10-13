<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\ExpenseType;
use App\Models\FiscalYear;
use App\Models\Level;
use App\Models\Program;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AllocationController extends Controller
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
        return $user->role && $user->role->permissions->contains(function ($item) use ($permission) {
            return strcasecmp((string) optional($item->module)->name, 'Allocation Management') === 0
                && strcasecmp((string) $item->name, $permission) === 0;
        });
    }

    private function scopedQuery(string $model): Builder
    {
        $query = $model::query();
        if (! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;
            $staffId === null ? $query->whereRaw('1 = 0') : $query->where('staff_id', (int) $staffId);
        }
        return $query;
    }

    private function authorizeAllocation(Allocation $allocation, string $permission): void
    {
        abort_unless($this->hasAllocationPermission($permission), 403);
        if (! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;
            abort_unless($staffId !== null && (int) $allocation->staff_id === (int) $staffId, 403);
        }
    }

    private function options(): array
    {
        return [
            'fiscalYears' => $this->scopedQuery(FiscalYear::class)->orderByDesc('year')->get(),
            'levels' => $this->scopedQuery(Level::class)->orderBy('level_code')->get(),
            'programs' => Program::orderBy('program')->get(),
            'staffOptions' => Staff::query()
                ->when(! auth()->user()->isAdministrator(), fn ($query) => $query->where('id', auth()->user()->staff_id))
                ->orderBy('name')->get(['id', 'name', 'abbreviation']),
        ];
    }

    private function formOptions(): array
    {
        $options = $this->options();
        $options['expenseTypes'] = $this->scopedQuery(ExpenseType::class)
            ->orderBy('type')->orderBy('expense_description')->get();
        return $options;
    }

    public function index(Request $request): View
    {
        abort_unless($this->hasAllocationPermission('view'), 403);
        $query = $this->scopedQuery(Allocation::class)->with([
            'fiscalYear', 'level', 'program', 'staff', 'expenses.expenseType',
            'financialPlans:id,allocation_id,mooe,capital_outlay,contract_amount',
        ])->withCount('financialPlans');
        foreach (['year_id', 'level_id', 'program_id', 'staff_id'] as $field) {
            if ($request->filled($field) && ($field !== 'staff_id' || auth()->user()->isAdministrator())) {
                $query->where($field, $request->integer($field));
            }
        }
        $allocations = $query->orderByDesc(FiscalYear::select('year')
            ->whereColumn('fiscal_years.id', 'allocations.year_id'))->orderByDesc('id')->get();
        return view('allocations.index', array_merge($this->options(), [
            'allocations' => $allocations,
            'canAddAllocations' => $this->hasAllocationPermission('add'),
            'canEditAllocations' => $this->hasAllocationPermission('edit'),
            'canDeleteAllocations' => $this->hasAllocationPermission('delete'),
        ]));
    }

    public function create(): View
    {
        abort_unless($this->hasAllocationPermission('add'), 403);
        return view('allocations.create', $this->formOptions());
    }

    public function edit(Allocation $allocation): View
    {
        $this->authorizeAllocation($allocation, 'edit');
        $allocation->load(['fiscalYear', 'level', 'program', 'staff', 'expenses.expenseType'])
            ->loadCount('financialPlans');
        return view('allocations.edit', array_merge($this->formOptions(), ['allocation' => $allocation]));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->hasAllocationPermission('add'), 403);
        $data = $this->validateData($request);
        DB::transaction(function () use ($data) {
            Staff::whereKey($data['staff_id'])->lockForUpdate()->firstOrFail();
            $this->validateRelatedRecordsAndBudgets($data);
            $this->rejectDuplicate($data);
            $allocation = Allocation::create($this->allocationFields($data));
            foreach ($data['expenses'] as $expense) {
                $allocation->expenses()->create($expense);
            }
        });
        return redirect()->route('allocations.index')->with('success', 'Allocation created successfully.');
    }

    public function update(Request $request, Allocation $allocation): RedirectResponse
    {
        $this->authorizeAllocation($allocation, 'edit');
        $data = $this->validateData($request);
        DB::transaction(function () use ($data, $allocation) {
            Staff::whereIn('id', array_unique([(int) $allocation->staff_id, (int) $data['staff_id']]))
                ->orderBy('id')->lockForUpdate()->get(['id']);
            $locked = Allocation::whereKey($allocation->getKey())->lockForUpdate()->firstOrFail();
            $this->authorizeAllocation($locked, 'edit');
            $this->validateRelatedRecordsAndBudgets($data);
            $this->rejectDuplicate($data, (int) $locked->id);
            $plans = $locked->financialPlans()->get();
            if ($plans->isNotEmpty()) {
                $this->validateExistingExpenseUsage($data, $plans);
                foreach (['staff_id', 'year_id', 'level_id', 'program_id'] as $field) {
                    if ((int) $locked->{$field} !== (int) $data[$field]) {
                        throw ValidationException::withMessages([
                            $field => 'The allocation context cannot be changed while Financial Plan rows use it.',
                        ]);
                    }
                }
                [$usedMooe, $usedCo] = $this->programmedCents($plans);
                foreach (['mooe_budget' => $usedMooe, 'co_budget' => $usedCo] as $field => $used) {
                    if ($this->cents($data[$field]) < $used) {
                        throw ValidationException::withMessages([
                            $field => 'This budget cannot be lower than the effective Financial Plan usage of ₱'
                                . number_format($used / 100, 2) . '.',
                        ]);
                    }
                }
            }
            $locked->update($this->allocationFields($data));
            // Preserve existing expense row IDs rather than deleting/recreating every row.
            $ids = array_column($data['expenses'], 'expense_id');
            $locked->expenses()->whereNotIn('expense_id', $ids)->delete();
            foreach ($data['expenses'] as $expense) {
                $locked->expenses()->updateOrCreate(['expense_id' => $expense['expense_id']], ['cost' => $expense['cost']]);
            }
        });
        return redirect()->route('allocations.index')->with('success', 'Allocation updated successfully.');
    }

    public function destroy(Allocation $allocation): RedirectResponse
    {
        $this->authorizeAllocation($allocation, 'delete');
        $deleted = DB::transaction(function () use ($allocation) {
            $locked = Allocation::whereKey($allocation->getKey())->lockForUpdate()->firstOrFail();
            $this->authorizeAllocation($locked, 'delete');
            if ($locked->financialPlans()->exists()) {
                return false;
            }
            $locked->expenses()->delete();
            $locked->delete();
            return true;
        });
        return redirect()->route('allocations.index')->with($deleted ? 'success' : 'error', $deleted
            ? 'Allocation deleted successfully.'
            : 'This allocation cannot be deleted because it is already used by a Financial Plan.');
    }

    private function validateData(Request $request): array
    {
        $money = ['required', 'numeric', 'min:0', 'regex:/^\d{1,12}(?:\.\d{1,2})?$/'];
        $data = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:staffs,id'],
            'year_id' => ['required', 'integer', 'exists:fiscal_years,id'],
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'mooe_budget' => $money, 'co_budget' => $money,
            'expenses' => ['required', 'array', 'min:1'],
            'expenses.*.expense_id' => ['required', 'integer', 'exists:expense_types,id', 'distinct'],
            'expenses.*.cost' => $money,
        ], [
            'mooe_budget.regex' => 'Enter a MOOE budget with up to 12 whole digits and two decimal places.',
            'co_budget.regex' => 'Enter a CO budget with up to 12 whole digits and two decimal places.',
            'expenses.*.cost.regex' => 'Each expense amount must have up to 12 whole digits and two decimal places.',
        ]);
        if (! auth()->user()->isAdministrator()) {
            abort_unless(auth()->user()->staff_id !== null
                && (int) $data['staff_id'] === (int) auth()->user()->staff_id, 403);
        }
        // Exclude unexpected nested properties from relationship writes.
        $data['expenses'] = array_map(fn ($item) => [
            'expense_id' => (int) $item['expense_id'], 'cost' => $item['cost'],
        ], $data['expenses']);
        return $data;
    }

    private function validateRelatedRecordsAndBudgets(array $data): void
    {
        $staffId = (int) $data['staff_id'];
        $errors = [];
        foreach (['year_id' => FiscalYear::class, 'level_id' => Level::class] as $field => $model) {
            if (! $model::whereKey($data[$field])->where('staff_id', $staffId)->exists()) {
                $errors[$field] = 'Select a record belonging to the selected Staff / Office.';
            }
        }
        $types = ExpenseType::whereIn('id', array_column($data['expenses'], 'expense_id'))
            ->lockForUpdate()->get()->keyBy('id');
        $totals = ['MOOE' => 0, 'CO' => 0];
        foreach ($data['expenses'] as $index => $expense) {
            $type = $types->get($expense['expense_id']);
            $classification = strtoupper(trim((string) $type?->type));
            if (! $type || (int) $type->staff_id !== $staffId) {
                $errors["expenses.$index.expense_id"] = 'Select an expense type belonging to the selected Staff / Office.';
            } elseif (! array_key_exists($classification, $totals)) {
                $errors["expenses.$index.expense_id"] = 'This expense type must be classified as MOOE or CO.';
            } else {
                $totals[$classification] += $this->cents($expense['cost']);
            }
        }
        foreach (['MOOE' => 'mooe_budget', 'CO' => 'co_budget'] as $type => $field) {
            $budget = $this->cents($data[$field]);
            if ($totals[$type] > $budget) {
                $errors[$field] = $type . ' expenses exceed the budget by ₱' . number_format(($totals[$type] - $budget) / 100, 2) . '.';
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function rejectDuplicate(array $data, ?int $ignoreId = null): void
    {
        $query = Allocation::query();
        foreach (['staff_id', 'year_id', 'level_id', 'program_id'] as $field) {
            $query->where($field, $data[$field]);
        }
        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages([
                'program_id' => 'An allocation already exists for this Staff/Office, Fiscal Year, Level, and Program/Project.',
            ]);
        }
    }

    private function validateExistingExpenseUsage(array $data, $plans): void
    {
        $types = ExpenseType::whereIn('id', array_column($data['expenses'], 'expense_id'))
            ->get()->keyBy('id');
        $ceilings = [];
        foreach ($data['expenses'] as $expense) {
            $type = $types->get($expense['expense_id']);
            $name = trim((string) $type?->expense_description);
            $category = strtoupper(trim((string) $type?->type)) === 'MOOE' ? 'mooe' : 'capital_outlay';
            $ceilings[$name][$category] = ($ceilings[$name][$category] ?? 0) + $this->cents($expense['cost']);
        }
        $usage = [];
        foreach ($plans as $plan) {
            if ($plan->row_type !== 'item') {
                continue;
            }
            $name = trim((string) $plan->expense_item);
            // Incomplete draft rows may not yet have an Expense Item.
            if ($name === '') {
                continue;
            }
            foreach (['mooe', 'capital_outlay'] as $category) {
                $usage[$name][$category] = ($usage[$name][$category] ?? 0)
                    + (int) round((float) $plan->{$category} * 100);
            }
        }
        $errors = [];
        foreach ($usage as $name => $categories) {
            if (! array_key_exists($name, $ceilings)) {
                $errors[] = $name . ' cannot be removed because existing Financial Plan rows reference it.';
                continue;
            }
            foreach ($categories as $category => $used) {
                $ceiling = $ceilings[$name][$category] ?? 0;
                if ($used > $ceiling) {
                    $label = $category === 'mooe' ? 'MOOE' : 'Capital Outlay';
                    $errors[] = $name . ' (' . $label . ') needs at least ₱' . number_format($used / 100, 2)
                        . ' to cover existing Financial Plan amounts. The proposed allocation is ₱'
                        . number_format($ceiling / 100, 2) . '.';
                }
            }
        }
        if ($errors) {
            throw ValidationException::withMessages(['expenses' => $errors]);
        }
    }

    private function allocationFields(array $data): array
    {
        return array_intersect_key($data, array_flip(['staff_id', 'year_id', 'level_id', 'program_id', 'mooe_budget', 'co_budget']));
    }

    private function cents($value): int
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $value, 2), 2, '');
        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function programmedCents($plans): array
    {
        $mooe = 0.0;
        $co = 0.0;
        foreach ($plans as $plan) {
            $m = (float) $plan->mooe;
            $c = (float) $plan->capital_outlay;
            $total = $m + $c;
            if ($plan->contract_amount !== null && $plan->contract_amount !== '') {
                $contract = (float) $plan->contract_amount;
                $m = $total > 0 ? $contract * ($m / $total) : 0;
                $c = $total > 0 ? $contract * ($c / $total) : 0;
            }
            $mooe += $m;
            $co += $c;
        }
        return [(int) round($mooe * 100), (int) round($co * 100)];
    }
}

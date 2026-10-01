<?php
namespace App\Http\Controllers;
use App\Models\Allocation;
use App\Models\ExpenseType;
use App\Models\FiscalYear;
use App\Models\Level;
use App\Models\Program;
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
    private function scopedFiscalYears()
    {
        $query = FiscalYear::query();
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
    private function scopedLevels()
    {
        $query = Level::query();
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
    private function authorizeRelatedRecords(
        int $yearId,
        int $levelId,
        array $expenseIds
    ): void {
        if ($this->isAdmin()) {
            return;
        }
        $staffId = auth()->user()->staff_id;
        abort_unless($staffId !== null, 403);
        $yearBelongsToStaff = FiscalYear::query()
            ->where('id', $yearId)
            ->where('staff_id', $staffId)
            ->exists();
        abort_unless($yearBelongsToStaff, 403);
        $levelBelongsToStaff = Level::query()
            ->where('id', $levelId)
            ->where('staff_id', $staffId)
            ->exists();
        abort_unless($levelBelongsToStaff, 403);
        $expenseIds = collect($expenseIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $ownedExpenseCount = ExpenseType::query()
            ->where('staff_id', $staffId)
            ->whereIn('id', $expenseIds)
            ->count();
        abort_unless(
            $ownedExpenseCount === $expenseIds->count(),
            403
        );
    }
    private function validateExpenseBudgets(
        array $expenses,
        float $mooeBudget,
        float $coBudget
    ): array {
        $mooeTotal = 0;
        $coTotal = 0;
        $expenseIds = collect($expenses)
            ->pluck('expense_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $expenseTypes = ExpenseType::query()
            ->whereIn('id', $expenseIds)
            ->get()
            ->keyBy('id');
        foreach ($expenses as $expense) {
            $expenseId = (int) $expense['expense_id'];
            $cost = (float) $expense['cost'];
            $expenseType = $expenseTypes->get($expenseId);
            if (! $expenseType) {
                continue;
            }
            $type = strtoupper(trim((string) $expenseType->type));
            if ($type === 'MOOE') {
                $mooeTotal += $cost;
            } elseif ($type === 'CO') {
                $coTotal += $cost;
            }
        }
        $errors = [];
        if ($mooeTotal > $mooeBudget) {
            $errors['expenses'] =
                'The total MOOE expenses of ₱' .
                number_format($mooeTotal, 2) .
                ' exceed the MOOE budget of ₱' .
                number_format($mooeBudget, 2) .
                ' by ₱' .
                number_format($mooeTotal - $mooeBudget, 2) .
                '.';
        }
        if ($coTotal > $coBudget) {
            $errors['expenses'] =
                ($errors['expenses'] ?? '') .
                ($errors['expenses'] ?? '' ? ' ' : '') .
                'The total CO expenses of ₱' .
                number_format($coTotal, 2) .
                ' exceed the CO budget of ₱' .
                number_format($coBudget, 2) .
                ' by ₱' .
                number_format($coTotal - $coBudget, 2) .
                '.';
        }
        return $errors;
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
                'program',
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
            ->when(
                $request->filled('program_id'),
                fn ($query) => $query->where(
                    'program_id',
                    $request->integer('program_id')
                )
            )
            ->when(
                $request->filled('staff_id') && $this->isAdmin(),
                fn ($query) => $query->where(
                    'staff_id',
                    $request->integer('staff_id')
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
        $fiscalYears = $this->scopedFiscalYears()
            ->orderByDesc('year')
            ->get();
        $levels = $this->scopedLevels()
            ->orderBy('level_code')
            ->get();
        $programs = Program::orderBy('program')->get();
        $staffOptions = $this->staffOptions();
        return view(
            'allocations.index',
            compact(
                'allocations',
                'fiscalYears',
                'levels',
                'programs',
                'staffOptions'
            )
        );
    }
    public function create(): View
    {
        abort_unless(
            $this->hasAllocationPermission('add'),
            403
        );
        $fiscalYears = $this->scopedFiscalYears()
            ->orderByDesc('year')
            ->get();
        $levels = $this->scopedLevels()
            ->orderBy('level_code')
            ->get();
        $programs = Program::orderBy('program')->get();
        $expenseTypes = $this->scopedExpenseTypes()
            ->orderBy('type')
            ->orderBy('expense_description')
            ->get();
        $staffOptions = $this->staffOptions();
        return view(
            'allocations.create',
            compact(
                'fiscalYears',
                'levels',
                'programs',
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
            'program_id' => [
                'required',
                'integer',
                'exists:programs,id',
            ],
            'mooe_budget' => [
                'required',
                'numeric',
                'min:0',
            ],
            'co_budget' => [
                'required',
                'numeric',
                'min:0',
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
            'expenses.\*.expense_id' => [
                'required',
                'integer',
                'exists:expense_types,id',
                'distinct',
            ],
            'expenses.\*.cost' => [
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
        $this->authorizeRelatedRecords(
            (int) $validated['year_id'],
            (int) $validated['level_id'],
            collect($validated['expenses'])
                ->pluck('expense_id')
                ->all()
        );
        $budgetErrors = $this->validateExpenseBudgets(
            $validated['expenses'],
            (float) $validated['mooe_budget'],
            (float) $validated['co_budget']
        );
        if (! empty($budgetErrors)) {
            return back()
                ->withInput()
                ->withErrors($budgetErrors);
        }
        $duplicateExists = Allocation::query()
            ->where('year_id', $validated['year_id'])
            ->where('level_id', $validated['level_id'])
            ->where('staff_id', $validated['staff_id'])
            ->where('program_id', $validated['program_id'])
            ->exists();
        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'program_id' => 'An allocation already exists for this Staff/Office, Fiscal Year, Level, and Program/Project.',
                ]);
        }
        DB::transaction(function () use ($validated) {
            $allocation = Allocation::create([
                'year_id' => $validated['year_id'],
                'level_id' => $validated['level_id'],
                'program_id' => $validated['program_id'],
                'mooe_budget' => $validated['mooe_budget'],
                'co_budget' => $validated['co_budget'],
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
            'program',
            'staff',
            'expenses.expenseType',
        ]);
        $fiscalYears = $this->scopedFiscalYears()
            ->orderByDesc('year')
            ->get();
        $levels = $this->scopedLevels()
            ->orderBy('level_code')
            ->get();
        $programs = Program::orderBy('program')->get();
        $expenseTypes = $this->scopedExpenseTypes()
            ->orderBy('type')
            ->orderBy('expense_description')
            ->get();
        $staffOptions = $this->staffOptions();
        return view(
            'allocations.edit',
            compact(
                'allocation',
                'fiscalYears',
                'levels',
                'programs',
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
            'program_id' => [
                'required',
                'integer',
                'exists:programs,id',
            ],
            'mooe_budget' => [
                'required',
                'numeric',
                'min:0',
            ],
            'co_budget' => [
                'required',
                'numeric',
                'min:0',
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
            'expenses.\*.expense_id' => [
                'required',
                'integer',
                'exists:expense_types,id',
                'distinct',
            ],
            'expenses.\*.cost' => [
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
        $this->authorizeRelatedRecords(
            (int) $validated['year_id'],
            (int) $validated['level_id'],
            collect($validated['expenses'])
                ->pluck('expense_id')
                ->all()
        );
        $budgetErrors = $this->validateExpenseBudgets(
            $validated['expenses'],
            (float) $validated['mooe_budget'],
            (float) $validated['co_budget']
        );
        if (! empty($budgetErrors)) {
            return back()
                ->withInput()
                ->withErrors($budgetErrors);
        }
        $duplicateExists = Allocation::query()
            ->where('year_id', $validated['year_id'])
            ->where('level_id', $validated['level_id'])
            ->where('staff_id', $validated['staff_id'])
            ->where('program_id', $validated['program_id'])
            ->where('id', '!=', $allocation->id)
            ->exists();
        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'program_id' => 'An allocation already exists for this Staff/Office, Fiscal Year, Level, and Program/Project.',
                ]);
        }
        DB::transaction(function () use ($validated, $allocation) {
            $allocation->update([
                'year_id' => $validated['year_id'],
                'level_id' => $validated['level_id'],
                'program_id' => $validated['program_id'],
                'mooe_budget' => $validated['mooe_budget'],
                'co_budget' => $validated['co_budget'],
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

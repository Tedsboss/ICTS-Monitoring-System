<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\ExpenseType;
use App\Models\FiscalYear;
use App\Models\Level;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AllocationController extends Controller
{
    public function index(Request $request): View
    {
        $allocations = Allocation::with(['fiscalYear', 'level', 'expenses.expenseType'])
            ->when($request->filled('year_id'), fn ($query) => $query->where('year_id', $request->year_id))
            ->when($request->filled('level_id'), fn ($query) => $query->where('level_id', $request->level_id))
            ->orderByDesc(FiscalYear::select('year')->whereColumn('fiscal_years.id', 'allocations.year_id'))
            ->get();

        $fiscalYears = FiscalYear::orderByDesc('year')->get();
        $levels = Level::orderBy('level_code')->get();

        return view('allocations.index', compact('allocations', 'fiscalYears', 'levels'));
    }

    public function create(): View
    {
        $fiscalYears = FiscalYear::orderByDesc('year')->get();
        $levels = Level::orderBy('level_code')->get();
        $expenseTypes = ExpenseType::orderBy('type')->orderBy('expense_description')->get();

        return view('allocations.create', compact('fiscalYears', 'levels', 'expenseTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year_id' => ['required', 'integer', 'exists:fiscal_years,id'],
            'level_id' => [
                'required',
                'integer',
                'exists:levels,id',
                Rule::unique('allocations', 'level_id')
                    ->where(fn ($query) => $query->where('year_id', $request->year_id)),
            ],
            'expenses' => ['required', 'array', 'min:1'],
            'expenses.*.expense_id' => ['required', 'integer', 'exists:expense_types,id', 'distinct'],
            'expenses.*.cost' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $allocation = Allocation::create([
                'year_id' => $validated['year_id'],
                'level_id' => $validated['level_id'],
            ]);

            foreach ($validated['expenses'] as $expense) {
                $allocation->expenses()->create([
                    'expense_id' => $expense['expense_id'],
                    'cost' => $expense['cost'],
                ]);
            }
        });

        return redirect()->route('allocations.index')
            ->with('success', 'Allocation created successfully.');
    }

    public function edit(Allocation $allocation): View
    {
        $allocation->load(['fiscalYear', 'level', 'expenses.expenseType']);

        $fiscalYears = FiscalYear::orderByDesc('year')->get();
        $levels = Level::orderBy('level_code')->get();
        $expenseTypes = ExpenseType::orderBy('type')->orderBy('expense_description')->get();

        return view('allocations.edit', compact('allocation', 'fiscalYears', 'levels', 'expenseTypes'));
    }

    public function update(Request $request, Allocation $allocation): RedirectResponse
    {
        $validated = $request->validate([
            'year_id' => ['required', 'integer', 'exists:fiscal_years,id'],
            'level_id' => [
                'required',
                'integer',
                'exists:levels,id',
                Rule::unique('allocations', 'level_id')
                    ->where(fn ($query) => $query
                        ->where('year_id', $request->year_id)
                        ->where('id', '!=', $allocation->id)),
            ],
            'expenses' => ['required', 'array', 'min:1'],
            'expenses.*.expense_id' => ['required', 'integer', 'exists:expense_types,id', 'distinct'],
            'expenses.*.cost' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $allocation) {
            $allocation->update([
                'year_id' => $validated['year_id'],
                'level_id' => $validated['level_id'],
            ]);

            $allocation->expenses()->delete();

            foreach ($validated['expenses'] as $expense) {
                $allocation->expenses()->create([
                    'expense_id' => $expense['expense_id'],
                    'cost' => $expense['cost'],
                ]);
            }
        });

        return redirect()->route('allocations.index')
            ->with('success', 'Allocation updated successfully.');
    }

    public function destroy(Allocation $allocation): RedirectResponse
    {
        $allocation->delete();

        return redirect()->route('allocations.index')
            ->with('success', 'Allocation deleted successfully.');
    }
}
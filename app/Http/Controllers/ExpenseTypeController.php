<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseTypeController extends Controller
{
    public function index(): View
    {
        $expenseTypes = ExpenseType::withCount('allocationExpenses')
            ->orderBy('type')
            ->orderBy('expense_description')
            ->get();

        return view('expense-types.index', compact('expenseTypes'));
    }

    public function create(): View
    {
        return view('expense-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'expense_description' => [
                'required',
                'string',
                'max:150',
                Rule::unique('expense_types', 'expense_description')
                    ->where(fn ($query) => $query->where('type', $request->type)),
            ],
        ]);

        ExpenseType::create($validated);

        return redirect()->route('expense-types.index')
            ->with('success', 'Expense type created successfully.');
    }

    public function edit(ExpenseType $expenseType): View
    {
        return view('expense-types.edit', compact('expenseType'));
    }

    public function update(Request $request, ExpenseType $expenseType): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'expense_description' => [
                'required',
                'string',
                'max:150',
                Rule::unique('expense_types', 'expense_description')
                    ->where(fn ($query) => $query->where('type', $request->type))
                    ->ignore($expenseType->id),
            ],
        ]);

        $expenseType->update($validated);

        return redirect()->route('expense-types.index')
            ->with('success', 'Expense type updated successfully.');
    }

    public function destroy(ExpenseType $expenseType): RedirectResponse
    {
        if ($expenseType->allocationExpenses()->exists()) {
            return redirect()->route('expense-types.index')
                ->with('error', 'This expense type cannot be deleted because it is already used by an allocation.');
        }

        $expenseType->delete();

        return redirect()->route('expense-types.index')
            ->with('success', 'Expense type deleted successfully.');
    }
}
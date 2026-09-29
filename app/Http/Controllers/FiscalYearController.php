<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FiscalYearController extends Controller
{
    public function index(): View
    {
        $fiscalYears = FiscalYear::orderByDesc('year')->get();

        return view('fiscal-years.index', compact('fiscalYears'));
    }

    public function create(): View
    {
        return view('fiscal-years.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100', 'unique:fiscal_years,year'],
        ]);

        FiscalYear::create($validated);

        return redirect()
            ->route('fiscal-years.index')
            ->with('success', 'Fiscal year created successfully.');
    }

    public function edit(FiscalYear $fiscalYear): View
    {
        return view('fiscal-years.edit', compact('fiscalYear'));
    }

    public function update(Request $request, FiscalYear $fiscalYear): RedirectResponse
    {
        $validated = $request->validate([
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('fiscal_years', 'year')->ignore($fiscalYear->id),
            ],
        ]);

        $fiscalYear->update($validated);

        return redirect()
            ->route('fiscal-years.index')
            ->with('success', 'Fiscal year updated successfully.');
    }

    public function destroy(FiscalYear $fiscalYear): RedirectResponse
    {
        if ($fiscalYear->allocations()->exists()) {
            return redirect()
                ->route('fiscal-years.index')
                ->with('error', 'This fiscal year cannot be deleted because it is already used by an allocation.');
        }

        $fiscalYear->delete();

        return redirect()
            ->route('fiscal-years.index')
            ->with('success', 'Fiscal year deleted successfully.');
    }
}
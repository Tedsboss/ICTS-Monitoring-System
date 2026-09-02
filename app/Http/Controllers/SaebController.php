<?php

namespace App\Http\Controllers;

use App\Models\FinancialPlan;
use App\Models\Saeb;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SaebController extends Controller
{
    public function __construct()
    {
        // Apply SaebPolicy automatically to the standard resource routes
        $this->authorizeResource(Saeb::class, 'saeb');
    }

    public function index(Request $request): View
    {
        $query = Saeb::query();

        if ($request->filled('funding_source')) {
            $query->where('funding_source', $request->input('funding_source'));
        }

        if ($request->filled('allotment_class')) {
            $query->where('allotment_class', $request->input('allotment_class'));
        }

        $saebs = $query
            ->orderBy('funding_source')
            ->orderBy('expense_class')
            ->paginate(20)
            ->withQueryString();

        $fundingSources = Saeb::query()
            ->whereNotNull('funding_source')
            ->where('funding_source', '<>', '')
            ->distinct()
            ->orderBy('funding_source')
            ->pluck('funding_source');

        $allotmentClasses = Saeb::query()
            ->whereNotNull('allotment_class')
            ->where('allotment_class', '<>', '')
            ->distinct()
            ->orderBy('allotment_class')
            ->pluck('allotment_class');

        return view('saebs.index', compact(
            'saebs',
            'fundingSources',
            'allotmentClasses'
        ));
    }

    public function create(): View
    {
        return view('saebs.create', [
            'saeb' => new Saeb(),
            'fundingSourceOptions' => $this->fundingSourceOptions(),
            'financialPlanItems' => $this->financialPlanItems(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Saeb::create($this->validateData($request));

        return redirect()
            ->route('saebs.index')
            ->with('success', 'SAEB entry created successfully.');
    }

    public function show(Saeb $saeb): View
    {
        return view('saebs.show', compact('saeb'));
    }

    public function edit(Saeb $saeb): View
    {
        return view('saebs.edit', [
            'saeb' => $saeb,
            'fundingSourceOptions' => $this->fundingSourceOptions(),
            'financialPlanItems' => $this->financialPlanItems(),
        ]);
    }

    public function update(Request $request, Saeb $saeb): RedirectResponse
    {
        $saeb->update($this->validateData($request));

        return redirect()
            ->route('saebs.index')
            ->with('success', 'SAEB entry updated successfully.');
    }

    public function destroy(Saeb $saeb): RedirectResponse
    {
        $saeb->delete();

        return redirect()
            ->route('saebs.index')
            ->with('success', 'SAEB entry deleted successfully.');
    }

    public function data(Request $request)
    {
        // Custom resource route, so authorize it explicitly
        $this->authorize('viewAny', Saeb::class);

        $query = Saeb::query();

        if ($request->filled('funding_source')) {
            $query->where('funding_source', $request->input('funding_source'));
        }

        if ($request->filled('allotment_class')) {
            $query->where('allotment_class', $request->input('allotment_class'));
        }

        $rows = $query
            ->orderBy('funding_source')
            ->orderBy('expense_class')
            ->get();

        return response()->json(
            $rows->map(function (Saeb $saeb) {
                return [
                    'id' => $saeb->id,
                    'funding_source' => $saeb->funding_source,
                    'allotment_class' => $saeb->allotment_class,
                    'expense_class' => $saeb->expense_class,
                    'allotment' => (float) $saeb->allotment,
                    'obligated' => (float) $saeb->obligated,
                    'aa' => (float) $saeb->aa,
                    'balances' => (float) $saeb->balances,
                    'percent_obligated' => (float) $saeb->percent_obligated,
                ];
            })->values()
        );
    }

    private function fundingSourceOptions(): array
    {
        return config('lookups.procurement_funding_sources', []);
    }

    private function financialPlanItems()
    {
        $query = FinancialPlan::query()
            ->where('row_type', 'item');

        // Administrators can access WFP items from all divisions
        if (!$this->isAdministrator()) {
            $divisionId = auth()->user()->division_id;

            // Normal users must have a division
            if ($divisionId === null) {
                return collect();
            }

            $query->where('division_id', $divisionId);
        }

        return $query
            ->orderBy('program_classification')
            ->orderBy('specific_activity')
            ->get();
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'as_of_date' => ['required', 'date'],
            'funding_source' => ['required', 'string', 'max:100'],
            'allotment_class' => ['required', Rule::in(['MOOE', 'CO'])],
            'expense_class' => ['required', 'string', 'max:150'],
            'allotment' => ['required', 'numeric', 'min:0'],
            'obligated' => ['required', 'numeric', 'min:0'],
            'aa' => ['required', 'numeric', 'min:0'],
            'balances' => ['required', 'numeric', 'min:0'],
            'financial_plan_item_id' => [
                'nullable',
                'integer',
                'exists:financial_plans,id',
            ],
        ]);
    }

    private function isAdministrator(): bool
    {
        return in_array((int) auth()->user()->role_id, [1, 29], true);
    }
}

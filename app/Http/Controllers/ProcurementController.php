<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\FinancialPlan;
use App\Models\Procurement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProcurementController extends Controller
{
    public function __construct()
    {
        // Apply ProcurementPolicy automatically to the standard resource routes
        $this->authorizeResource(Procurement::class, 'procurement');
    }

    public function index(Request $request): View
    {
        $query = Procurement::query();

        if ($request->filled('funding_source')) {
            $query->where('funding_source', $request->input('funding_source'));
        }

        if ($request->filled('expense_class')) {
            $query->where('expense_class', $request->input('expense_class'));
        }

        $procurements = $query
            ->orderBy('funding_source')
            ->orderBy('procurement_title')
            ->paginate(20)
            ->withQueryString();

        $fundingSources = Procurement::query()
            ->whereNotNull('funding_source')
            ->where('funding_source', '<>', '')
            ->distinct()
            ->orderBy('funding_source')
            ->pluck('funding_source');

        $expenseClasses = Procurement::query()
            ->whereNotNull('expense_class')
            ->where('expense_class', '<>', '')
            ->distinct()
            ->orderBy('expense_class')
            ->pluck('expense_class');

        return view('procurements.index', compact(
            'procurements',
            'fundingSources',
            'expenseClasses'
        ));
    }

    public function create(): View
    {
        return view('procurements.create', [
            'procurement' => new Procurement(),
            'fundingSourceOptions' => $this->fundingSourceOptions(),
            'divisions' => $this->divisions(),
            'financialPlanItems' => $this->financialPlanItems(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Procurement::create($this->validateData($request));

        return redirect()
            ->route('procurements.index')
            ->with('success', 'Procurement entry created successfully.');
    }

    public function show(Procurement $procurement): View
    {
        return view('procurements.show', compact('procurement'));
    }

    public function edit(Procurement $procurement): View
    {
        return view('procurements.edit', [
            'procurement' => $procurement,
            'fundingSourceOptions' => $this->fundingSourceOptions(),
            'divisions' => $this->divisions(),
            'financialPlanItems' => $this->financialPlanItems(),
        ]);
    }

    public function update(Request $request, Procurement $procurement): RedirectResponse
    {
        $procurement->update($this->validateData($request));

        return redirect()
            ->route('procurements.index')
            ->with('success', 'Procurement entry updated successfully.');
    }

    public function destroy(Procurement $procurement): RedirectResponse
    {
        $procurement->delete();

        return redirect()
            ->route('procurements.index')
            ->with('success', 'Procurement entry deleted successfully.');
    }

    public function data(Request $request)
    {
        // Custom resource route, so authorize it explicitly
        $this->authorize('viewAny', Procurement::class);

        $query = Procurement::query();

        if ($request->filled('funding_source')) {
            $query->where('funding_source', $request->input('funding_source'));
        }

        if ($request->filled('expense_class')) {
            $query->where('expense_class', $request->input('expense_class'));
        }

        $rows = $query
            ->orderBy('funding_source')
            ->orderBy('procurement_title')
            ->get();

        return response()->json(
            $rows->map(function (Procurement $procurement) {
                return [
                    'id' => $procurement->id,
                    'funding_source' => $procurement->funding_source,
                    'procurement_title' => $procurement->procurement_title,
                    'expense_class' => $procurement->expense_class,
                    'division_assigned' => $procurement->division_assigned,
                    'amount' => (float) $procurement->amount,
                    'quarter' => $procurement->quarter,
                    'procurement_status' => $procurement->procurement_status,
                    'payment_status' => $procurement->payment_status,
                    'retention_status' => $procurement->retention_status,
                ];
            })->values()
        );
    }

    private function fundingSourceOptions(): array
    {
        return config('lookups.procurement_funding_sources', []);
    }

    private function divisions()
    {
        return Division::query()
            ->orderBy('name')
            ->get();
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
            'funding_source' => ['required', 'string', 'max:100'],
            'expense_class' => ['required', Rule::in(['MOOE', 'CO'])],
            'division_assigned' => ['required', 'string', 'max:100'],
            'procurement_title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'quarter' => ['nullable', 'string', 'max:20'],
            'procurement_status' => ['nullable', Rule::in(['OK'])],
            'payment_status' => ['nullable', Rule::in(['OK'])],
            'retention_status' => ['nullable', Rule::in(['OK'])],
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

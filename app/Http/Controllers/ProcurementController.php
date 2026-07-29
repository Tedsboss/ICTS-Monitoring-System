<?php
// app/Http/Controllers/ProcurementController.php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\FinancialPlan;
use App\Models\Procurement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Procurement::class, 'procurement');
    }

    public function index(Request $request): View
    {
        $query = Procurement::query();

        if ($request->filled('funding_source')) {
            $query->where('funding_source', $request->string('funding_source'));
        }

        if ($request->filled('expense_class')) {
            $query->where('expense_class', $request->string('expense_class'));
        }

        if ($request->filled('search')) {
            $query->where('procurement_title', 'like', '%' . $request->string('search') . '%');
        }

        $procurements = $query->orderByDesc('id')->paginate(20)->withQueryString();

        $fundingSources = Procurement::query()->distinct()->orderBy('funding_source')->pluck('funding_source');
        $expenseClasses = Procurement::query()->distinct()->orderBy('expense_class')->pluck('expense_class');

        return view('procurements.index', compact('procurements', 'fundingSources', 'expenseClasses'));
    }

    public function create(): View
    {
        $procurement = new Procurement();

        return view('procurements.create', [
            'procurement'          => $procurement,
            'fundingSourceOptions' => $this->fundingSourceOptions(),
            'divisions'            => Division::orderBy('name')->get(),
            'financialPlanItems'   => FinancialPlan::where('row_type', 'item')
                ->orderBy('program_classification')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Procurement::create($this->validateData($request));

        return redirect()->route('procurements.index')
            ->with('success', 'Procurement entry created successfully.');
    }

    public function show(Procurement $procurement): View
    {
        return view('procurements.show', compact('procurement'));
    }

    public function edit(Procurement $procurement): View
    {
        return view('procurements.edit', [
            'procurement'          => $procurement,
            'fundingSourceOptions' => $this->fundingSourceOptions(),
            'divisions'            => Division::orderBy('name')->get(),
            'financialPlanItems'   => FinancialPlan::where('row_type', 'item')
                ->orderBy('program_classification')
                ->get(),
        ]);
    }

    public function update(Request $request, Procurement $procurement): RedirectResponse
    {
        $procurement->update($this->validateData($request));

        return redirect()->route('procurements.index')
            ->with('success', 'Procurement entry updated successfully.');
    }

    public function destroy(Procurement $procurement): RedirectResponse
    {
        $procurement->delete();

        return redirect()->route('procurements.index')
            ->with('success', 'Procurement entry deleted successfully.');
    }

    private function fundingSourceOptions(): array
    {
        return config('lookups.procurement_funding_sources', []);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'funding_source'          => ['required', 'string', 'max:50'],
            'procurement_title'       => ['required', 'string', 'max:500'],
            'expense_class'           => ['required', 'string', 'max:20'],
            'division_assigned'       => ['required', 'string', 'max:20'],
            'amount'                  => ['required', 'numeric', 'min:0'],
            'quarter'                 => ['nullable', 'string', 'max:10'],
            'procurement_status'      => ['nullable', 'string', 'max:20'],
            'payment_status'          => ['nullable', 'string', 'max:20'],
            'retention_status'        => ['nullable', 'string', 'max:20'],
            'financial_plan_item_id'  => ['nullable', 'integer', 'exists:financial_plans,id'],
        ]);
    }

    public function data(Request $request)
    {
        $query = Procurement::query();

        if ($request->filled('funding_source')) {
            $query->where('funding_source', $request->string('funding_source'));
        }

        if ($request->filled('expense_class')) {
            $query->where('expense_class', $request->string('expense_class'));
        }

        $rows = $query->orderByDesc('id')->get();

        return response()->json($rows->map(function (Procurement $p) {
            return [
                'id'                 => $p->id,
                'funding_source'     => $p->funding_source,
                'procurement_title'  => $p->procurement_title,
                'expense_class'      => $p->expense_class,
                'division_assigned'  => $p->division_assigned,
                'amount'             => (float) $p->amount,
                'quarter'            => $p->quarter,
                'procurement_status' => $p->procurement_status,
                'payment_status'     => $p->payment_status,
                'retention_status'   => $p->retention_status,
            ];
        }));
    }
}
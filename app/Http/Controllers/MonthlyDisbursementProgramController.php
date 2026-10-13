<?php

namespace App\Http\Controllers;

use App\Models\FinancialPlan;
use App\Models\WorkPlan;
use App\Services\MdpCalculator;
use App\Services\MdpReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MonthlyDisbursementProgramController extends Controller
{
    public const MONTHS = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', WorkPlan::class);
        $this->authorize('viewAny', FinancialPlan::class);

        $validated = $request->validate([
            'fiscal_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $workPlanTable = (new WorkPlan())->getTable();
        $financialPlanTable = (new FinancialPlan())->getTable();

        $scope = WorkPlan::query()
            ->whereNotNull('staff_id')
            ->whereNotNull('office_name')
            ->where('office_name', '!=', '')
            ->whereExists(function (Builder $query) use ($workPlanTable, $financialPlanTable) {
                $query->selectRaw('1')
                    ->from($financialPlanTable . ' as mdp_financial_plans')
                    ->whereColumn('mdp_financial_plans.fiscal_year', $workPlanTable . '.fiscal_year')
                    ->whereColumn('mdp_financial_plans.staff_id', $workPlanTable . '.staff_id')
                    ->whereColumn('mdp_financial_plans.office_name', $workPlanTable . '.office_name');
            });

        if (!auth()->user()->isAdministrator()) {
            $scope->where('staff_id', auth()->user()->staff_id ?? 0);
        }

        $fiscalYears = (clone $scope)
            ->distinct()
            ->orderByDesc('fiscal_year')
            ->pluck('fiscal_year');

        $fiscalYear = isset($validated['fiscal_year'])
            ? (int) $validated['fiscal_year']
            : (int) ($fiscalYears->first() ?? now()->year);

        $plans = $scope
            ->with('staff')
            ->withCount('items')
            ->where('fiscal_year', $fiscalYear)
            ->orderBy('office_name')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('mdp.index', compact('plans', 'fiscalYears', 'fiscalYear'));
    }

    public function show(WorkPlan $workPlan, MdpReportBuilder $builder, MdpCalculator $calculator)
    {
        $this->authorizeScope($workPlan);
        $report = $builder->build($workPlan);

        return view('mdp.show', array_merge($report, [
            'months' => self::MONTHS,
            'money' => fn (int $cents, bool $dash = false) => $calculator->formatCents($cents, $dash),
        ]));
    }

    public function pdf(WorkPlan $workPlan, MdpReportBuilder $builder, MdpCalculator $calculator)
    {
        $this->authorizeScope($workPlan);
        $report = $builder->build($workPlan);

        if (!$report['complete']) {
            throw ValidationException::withMessages([
                'mdp' => 'Resolve the MDP source issues before downloading a complete report.',
            ]);
        }

        return Pdf::loadView('mdp.pdf', array_merge($report, [
            'months' => self::MONTHS,
            'money' => fn (int $cents, bool $dash = false) => $calculator->formatCents($cents, $dash),
        ]))
            ->setPaper('a4', 'landscape')
            ->download('MDP-FY' . $workPlan->fiscal_year . '-WP' . $workPlan->id . '.pdf');
    }

    private function authorizeScope(WorkPlan $workPlan): void
    {
        $this->authorize('viewAny', WorkPlan::class);
        $this->authorize('viewAny', FinancialPlan::class);
        $this->authorize('view', $workPlan);

        abort_unless(
            auth()->user()->isAdministrator()
            || (
                auth()->user()->staff_id !== null
                && (int) auth()->user()->staff_id === (int) $workPlan->staff_id
            ),
            403
        );
    }
}

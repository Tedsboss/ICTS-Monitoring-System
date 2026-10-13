<?php
namespace App\Services;
use App\Models\FinancialPlan;
use App\Models\FiscalYear;
use App\Models\WorkPlan;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use OverflowException;
class MdpReportBuilder
{
    public function __construct(private MdpCalculator $calculator)
    {
    }
    public function build(WorkPlan $workPlan): array
    {
        Gate::authorize('viewAny', FinancialPlan::class);
        Gate::authorize('view', $workPlan);
        $workPlan->loadMissing(['staff', 'signatory', 'items']);
        $errors = [];
        $warnings = [];
        $rows = [];
        if (!FiscalYear::query()->where('year', $workPlan->fiscal_year)->where('staff_id', $workPlan->staff_id)->exists()) {
            $errors[] = 'The Work Plan fiscal year is not registered for this Staff/Office.';
        }
        $items = $workPlan->items->where('row_type', 'item');
        if ($items->isEmpty()) {
            $errors[] = 'The Work Plan has no activity rows to use as MDP sources.';
        }
        $legacyItems = $items->filter(fn ($item) => empty($item->financial_plan_id));
        if ($legacyItems->isNotEmpty()) {
            $errors[] = $legacyItems->count() . ' Work Plan activity row(s) have no Financial Plan source. Link them before generating a complete MDP.';
        }
        $ids = $items->pluck('financial_plan_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
        $linkedCount = $items->filter(fn ($item) => !empty($item->financial_plan_id))->count();
        if ($linkedCount > $ids->count()) {
            $warnings[] = 'Repeated Financial Plan links are counted once to prevent duplicate cash requirements.';
        }
        $sources = FinancialPlan::query()
            ->with(['targets', 'allocation.level', 'allocation.fiscalYear'])
            ->whereIn('id', $ids->all())
            ->where('fiscal_year', $workPlan->fiscal_year)
            ->where('staff_id', $workPlan->staff_id)
            ->where('office_name', $workPlan->office_name)
            ->where('row_type', 'item')
            ->orderBy('sort_order')->orderBy('id')->get()->keyBy('id');
        $summary = $this->calculator->emptySummary();
        $groups = [];
        foreach ($ids as $id) {
            $source = $sources->get($id);
            if (!$source) {
                $errors[] = "Financial Plan {$id} is missing or does not match this Work Plan's year, staff, and office.";
                continue;
            }
            Gate::authorize('view', $source);
            $allocation = $source->allocation;
            if ($allocation && ((int) $allocation->staff_id !== (int) $workPlan->staff_id || (int) optional($allocation->fiscalYear)->year !== (int) $workPlan->fiscal_year)) {
                $errors[] = "Financial Plan {$id} is linked to an Allocation outside this Work Plan's scope.";
                continue;
            }
            $level = $allocation?->level;
            $groupKey = $level ? 'level-' . $level->getKey() : 'unassigned';
            $levelName = $level?->level_description ?: 'Unassigned allocation level';
            if (!$level) {
                $warnings[] = "Financial Plan {$id} has no allocation level label.";
            }
            try {
                $row = $this->calculator->calculateSource([
                    'id' => (int) $source->id,
                    'activity' => (string) ($items->firstWhere('financial_plan_id', $id)?->specific_activity ?: $source->specific_activity),
                    'prexc_code' => (string) $source->prexc_code,
                    'mooe' => $source->getRawOriginal('mooe') ?? '0',
                    'capital_outlay' => $source->getRawOriginal('capital_outlay') ?? '0',
                    'contract_amount' => $source->getRawOriginal('contract_amount'),
                    'targets' => $source->targets->map(fn ($target) => [
                        'month' => $target->month,
                        'amount' => $target->getRawOriginal('amount') ?? '0',
                    ])->all(),
                ]);
                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = ['name' => $levelName, 'rows' => [], 'summary' => $this->calculator->emptySummary()];
                }
                $groups[$groupKey]['rows'][] = $row;
                $rows[] = $row;
                $this->calculator->addRow($groups[$groupKey]['summary'], $row);
                $this->calculator->addRow($summary, $row);
                if ($row['unscheduled'] > 0) {
                    $warnings[] = "Financial Plan {$id}: PHP " . $this->calculator->formatCents($row['unscheduled']) . ' of its effective budget is not scheduled in the monthly cash amounts.';
                }
            } catch (InvalidArgumentException | OverflowException $exception) {
                $errors[] = $exception->getMessage();
            }
        }
        return [
            'workPlan' => $workPlan,
            'groups' => array_values($groups),
            'rows' => $rows,
            'summary' => $summary,
            'sourceErrors' => array_values(array_unique($errors)),
            'sourceWarnings' => array_values(array_unique($warnings)),
            'complete' => empty($errors),
            'sourceCount' => $ids->count(),
            'calculatedCount' => count($rows),
        ];
    }
}

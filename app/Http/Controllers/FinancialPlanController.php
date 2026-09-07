<?php
namespace App\Http\Controllers;
use App\Models\FinancialPlan;
use App\Models\FinancialPlanAllocation;
use App\Models\FinancialPlanAllocationItem;
use App\Models\FinancialPlanAllocationType;
use App\Models\FinancialPlanSignatory;
use App\Models\FinancialPlanSubmission;
use App\Models\FinancialPlanTarget;
use App\Models\PrexcClassification;
use App\Models\StaffPersonnel;
use App\Traits\GenerateLogs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
class FinancialPlanController extends Controller
{
    use GenerateLogs;
    // Config
    private const MONTHS = [
        1 => 'J', 2 => 'F', 3 => 'M', 4 => 'A', 5 => 'M', 6 => 'J',
        7 => 'J', 8 => 'A', 9 => 'S', 10 => 'O', 11 => 'N', 12 => 'D',
    ];
    private const TRACKED_FIELDS = [
        'program_classification',
        'prexc_code',
        'allocation_type',
        'staff_unit_project',
        'specific_activity',
        'procurement_status',
        'expense_item',
        'assigned_personnel',
        'mooe',
        'capital_outlay',
        'contract_amount',
    ];
    // Helpers
    private function effectiveAmounts(float $mooe, float $capitalOutlay, $contractAmount): array
    {
        if ($contractAmount === null || $contractAmount === '') {
            return [$mooe, $capitalOutlay];
        }
        $contractAmount = (float) $contractAmount;
        $total = $mooe + $capitalOutlay;
        if ($total <= 0) {
            return [0.0, 0.0];
        }
        return [
            $contractAmount * ($mooe / $total),
            $contractAmount * ($capitalOutlay / $total),
        ];
    }
    private function normalizeRows(array $rows): array
    {
        return collect($rows)->map(function ($row) {
            foreach (['mooe', 'capital_outlay', 'contract_amount'] as $field) {
                if (array_key_exists($field, $row) && $row[$field] === '') {
                    $row[$field] = null;
                }
            }
            if (isset($row['months']) && is_array($row['months'])) {
                foreach ($row['months'] as $m => $val) {
                    if ($val === '') {
                        $row['months'][$m] = null;
                    }
                }
            }
            return $row;
        })->all();
    }
    // Validate Program Classification and PREXC pairs for budget lines.
    // Existing legacy pairs are allowed while unchanged so old plans are not broken.
    private function validatePrexcRows(array $rows, int $fiscalYear, string $officeName): void
    {
        $rowIds = collect($rows)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $existingRows = collect();
        if ($rowIds->isNotEmpty()) {
            $existingQuery = FinancialPlan::query()
                ->where('fiscal_year', $fiscalYear)
                ->where('office_name', $officeName)
                ->whereIn('id', $rowIds);
            $this->applyStaffScope($existingQuery);
            $existingRows = $existingQuery
                ->get(['id', 'program_classification', 'prexc_code'])
                ->keyBy('id');
        }
        $activeClassifications = PrexcClassification::query()
            ->active()
            ->get(['classification_name', 'prexc_code']);
        $errors = [];
        foreach ($rows as $index => $row) {
            if (($row['row_type'] ?? null) !== 'item') {
                continue;
            }
            $classification = trim((string) ($row['program_classification'] ?? ''));
            $prexcCode = trim((string) ($row['prexc_code'] ?? ''));
            $existing = ! empty($row['id'])
                ? $existingRows->get((int) $row['id'])
                : null;
            // Preserve an existing legacy classification/PREXC pair when the user
            // has not changed it. The next intentional classification change must
            // use an active master-list pair.
            if ($existing
                && $classification === trim((string) ($existing->program_classification ?? ''))
                && $prexcCode === trim((string) ($existing->prexc_code ?? ''))) {
                continue;
            }
            // Draft rows may remain unclassified. Strict completeness belongs to
            // the Submit validation, not normal draft saving.
            if ($classification === '' && $prexcCode === '') {
                continue;
            }
            if ($classification === '' || $prexcCode === '') {
                $errors["rows.{$index}.prexc_code"] =
                    'Program Classification and PREXC Code must be selected together.';
                continue;
            }
            $matchingPair = $activeClassifications->first(function ($master) use ($classification, $prexcCode) {
                return trim((string) $master->classification_name) === $classification
                    && trim((string) $master->prexc_code) === $prexcCode;
            });
            if ($matchingPair) {
                continue;
            }
            $classificationExists = $activeClassifications->contains(function ($master) use ($classification) {
                return trim((string) $master->classification_name) === $classification;
            });
            if ($classificationExists) {
                $errors["rows.{$index}.prexc_code"] =
                    'The PREXC Code does not match the selected Program Classification.';
            } else {
                $errors["rows.{$index}.program_classification"] =
                    'The selected Program Classification is not available in the active PREXC master list.';
            }
        }
        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
    // Validate Allocation Type against the Staff/Office master data while saving a draft.
    // Allocation Type may remain blank in draft.
    private function validateAllocationRowsForSave(
        array $rows,
        int $fiscalYear,
        string $officeName,
        ?int $staffId
    ): void {
        $rowIds = collect($rows)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $existingRows = collect();
        if ($rowIds->isNotEmpty()) {
            $existingQuery = FinancialPlan::query()
                ->where('fiscal_year', $fiscalYear)
                ->where('office_name', $officeName)
                ->whereIn('id', $rowIds);
            $this->applyStaffScope($existingQuery);
            $existingRows = $existingQuery
                ->get(['id', 'staff_id', 'allocation_type'])
                ->keyBy('id');
        }
        $resolvedStaffId = $staffId;
        if ($resolvedStaffId === null) {
            $resolvedStaffId = $existingRows
                ->pluck('staff_id')
                ->filter()
                ->first();
        }
        $allocationTypes = collect();
        if ($resolvedStaffId !== null) {
            $allocationTypes = FinancialPlanAllocationType::query()
                ->forStaff((int) $resolvedStaffId)
                ->active()
                ->get([
                    'code',
                    'name',
                    'allows_mooe',
                    'allows_capital_outlay',
                ])
                ->keyBy(fn ($type) => strtolower(trim((string) $type->code)));
        }
        $errors = [];
        foreach ($rows as $index => $row) {
            if (($row['row_type'] ?? null) !== 'item') {
                continue;
            }
            $existing = ! empty($row['id'])
                ? $existingRows->get((int) $row['id'])
                : null;
            $allocationType = array_key_exists('allocation_type', $row)
                ? $row['allocation_type']
                : $existing?->allocation_type;
            $allocationType = strtolower(trim((string) ($allocationType ?? '')));
            // Draft rows may remain unclassified.
            if ($allocationType === '') {
                continue;
            }
            if ($resolvedStaffId === null) {
                $errors["rows.{$index}.allocation_type"] =
                    'The Staff/Office could not be determined for this Allocation Type.';
                continue;
            }
            $allocationMaster = $allocationTypes->get($allocationType);
            if (! $allocationMaster) {
                $errors["rows.{$index}.allocation_type"] =
                    'The selected Allocation Type is not available for this Staff/Office.';
                continue;
            }
            $mooe = (float) ($row['mooe'] ?? 0);
            $capitalOutlay = (float) ($row['capital_outlay'] ?? 0);
            if ($mooe > 0 && ! $allocationMaster->allows_mooe) {
                $errors["rows.{$index}.mooe"] =
                    "{$allocationMaster->name} does not allow MOOE.";
            }
            if ($capitalOutlay > 0 && ! $allocationMaster->allows_capital_outlay) {
                $errors["rows.{$index}.capital_outlay"] =
                    "{$allocationMaster->name} does not allow Capital Outlay.";
            }
        }
        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
    // Validate the complete Financial Plan before submission.
    // Draft saving remains permissive; these rules apply only when submitting.
    private function validateFinancialPlanForSubmit(
        int $fiscalYear,
        string $officeName,
        ?int $staffId
    ): void {
        $itemsQuery = FinancialPlan::query()
            ->with(['targets:id,financial_plan_id,month,amount'])
            ->where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName)
            ->where('row_type', 'item');
        if ($staffId !== null) {
            $itemsQuery->where('staff_id', $staffId);
        } else {
            $this->applyStaffScope($itemsQuery);
        }
        $items = $itemsQuery
            ->orderBy('sort_order')
            ->get([
                'id',
                'program_classification',
                'prexc_code',
                'allocation_type',
                'staff_unit_project',
                'specific_activity',
                'expense_item',
                'assigned_personnel',
                'mooe',
                'capital_outlay',
                'contract_amount',
                'sort_order',
            ]);
        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'financial_plan' => 'At least one Budget Line is required before submission.',
            ]);
        }
        if ($staffId === null) {
            throw ValidationException::withMessages([
                'financial_plan_allocation_type' =>
                    'The Staff/Office could not be determined for Allocation Type validation.',
            ]);
        }
        $allocationTypes = FinancialPlanAllocationType::query()
            ->forStaff($staffId)
            ->active()
            ->ordered()
            ->get([
                'id',
                'code',
                'name',
                'allows_mooe',
                'allows_capital_outlay',
            ]);
        if ($allocationTypes->isEmpty()) {
            throw ValidationException::withMessages([
                'financial_plan_allocation_type' =>
                    'No active Allocation Types are configured for this Staff/Office.',
            ]);
        }
        $allocationTypesByCode = $allocationTypes->keyBy(
            fn ($type) => strtolower(trim((string) $type->code))
        );
        $activePrexcPairs = PrexcClassification::query()
            ->active()
            ->get(['classification_name', 'prexc_code'])
            ->mapWithKeys(function ($master) {
                $classification = trim((string) $master->classification_name);
                $prexcCode = trim((string) $master->prexc_code);
                return [$classification . '|' . $prexcCode => true];
            });
        $errors = [];
        $programmed = [];
        foreach ($items as $item) {
            $rowLabel = trim((string) $item->specific_activity);
            if ($rowLabel === '') {
                $rowLabel = trim((string) $item->program_classification);
            }
            if ($rowLabel === '') {
                $rowLabel = "Budget line #{$item->id}";
            }
            $prefix = $rowLabel;
            $classification = trim((string) $item->program_classification);
            $prexcCode = trim((string) $item->prexc_code);
            $allocationType = strtolower(trim((string) ($item->allocation_type ?? '')));
            $staffUnitProject = trim((string) $item->staff_unit_project);
            $specificActivity = trim((string) $item->specific_activity);
            $expenseItem = trim((string) $item->expense_item);
            $assignedPersonnel = trim((string) $item->assigned_personnel);
            $mooe = (float) $item->mooe;
            $capitalOutlay = (float) $item->capital_outlay;
            $originalBudget = $mooe + $capitalOutlay;
            $contractAmount = $item->contract_amount !== null
                ? (float) $item->contract_amount
                : null;
            $hasBudget = $originalBudget > 0
                || ($contractAmount !== null && $contractAmount > 0);
            if ($classification === '') {
                $errors["financial_plan_row_{$item->id}_program_classification"] =
                    "{$prefix}: Program Classification is required.";
            }
            if ($prexcCode === '') {
                $errors["financial_plan_row_{$item->id}_prexc_code"] =
                    "{$prefix}: PREXC Code is required.";
            }
            // Existing stored legacy PREXC pairs remain valid for submission.
            $isActivePrexcPair = $classification !== ''
                && $prexcCode !== ''
                && $activePrexcPairs->has($classification . '|' . $prexcCode);
            if (! $isActivePrexcPair && $classification !== '' && $prexcCode !== '') {
                // Presence is sufficient for an unchanged legacy pair.
            }
            if ($staffUnitProject === '') {
                $errors["financial_plan_row_{$item->id}_staff_unit_project"] =
                    "{$prefix}: Staff/Unit is required.";
            }
            if ($specificActivity === '') {
                $errors["financial_plan_row_{$item->id}_specific_activity"] =
                    "{$prefix}: Specific Activity is required.";
            }
            if ($expenseItem === '') {
                $errors["financial_plan_row_{$item->id}_expense_item"] =
                    "{$prefix}: Expense Item is required.";
            }
            if ($assignedPersonnel === '') {
                $errors["financial_plan_row_{$item->id}_assigned_personnel"] =
                    "{$prefix}: Assigned Personnel is required.";
            }
            if ($originalBudget <= 0) {
                $errors["financial_plan_row_{$item->id}_budget"] =
                    "{$prefix}: Enter an MOOE or Capital Outlay amount greater than zero.";
            }
            if ($contractAmount !== null && $contractAmount > $originalBudget) {
                $errors["financial_plan_row_{$item->id}_contract_amount"] =
                    "{$prefix}: Contract Amount cannot be greater than the original MOOE + Capital Outlay budget.";
            }
            $allocationMaster = null;
            if ($hasBudget && $allocationType === '') {
                $errors["financial_plan_row_{$item->id}_allocation_type"] =
                    "{$prefix}: Allocation Type is required before submission.";
            } elseif ($allocationType !== '') {
                $allocationMaster = $allocationTypesByCode->get($allocationType);
                if (! $allocationMaster) {
                    $errors["financial_plan_row_{$item->id}_allocation_type"] =
                        "{$prefix}: The selected Allocation Type is not available for this Staff/Office.";
                }
            }
            if ($allocationMaster) {
                if ($mooe > 0 && ! $allocationMaster->allows_mooe) {
                    $errors["financial_plan_row_{$item->id}_mooe"] =
                        "{$prefix}: {$allocationMaster->name} does not allow MOOE.";
                }
                if ($capitalOutlay > 0 && ! $allocationMaster->allows_capital_outlay) {
                    $errors["financial_plan_row_{$item->id}_capital_outlay"] =
                        "{$prefix}: {$allocationMaster->name} does not allow Capital Outlay.";
                }
            }
            [$effectiveMooe, $effectiveCo] = $this->effectiveAmounts(
                $mooe,
                $capitalOutlay,
                $contractAmount
            );
            $effectiveBudget = $effectiveMooe + $effectiveCo;
            $targetTotal = (float) $item->targets->sum('amount');
            if (abs($targetTotal - $effectiveBudget) > 0.01) {
                $errors["financial_plan_row_{$item->id}_financial_target"] =
                    "{$prefix}: Monthly Financial Target total must equal the Effective Budget of "
                    . number_format($effectiveBudget, 2) . '. Current target total is '
                    . number_format($targetTotal, 2) . '.';
            }
            if ($allocationMaster) {
                if ($allocationMaster->allows_mooe && $effectiveMooe > 0) {
                    $programmed[$allocationMaster->id]['mooe'] =
                        ($programmed[$allocationMaster->id]['mooe'] ?? 0) + $effectiveMooe;
                }
                if ($allocationMaster->allows_capital_outlay && $effectiveCo > 0) {
                    $programmed[$allocationMaster->id]['capital_outlay'] =
                        ($programmed[$allocationMaster->id]['capital_outlay'] ?? 0) + $effectiveCo;
                }
            }
        }
        $allocation = $this->findAllocationHeader(
            $fiscalYear,
            $officeName,
            $staffId
        );
        $storedItems = $allocation
            ? FinancialPlanAllocationItem::query()
                ->where('financial_plan_allocation_id', $allocation->id)
                ->get()
                ->keyBy(
                    fn ($item) =>
                        $item->allocation_type_id . '|' . $item->expense_category
                )
            : collect();
        foreach ($allocationTypes as $type) {
            foreach (['mooe', 'capital_outlay'] as $category) {
                $allowed = $category === 'mooe'
                    ? (bool) $type->allows_mooe
                    : (bool) $type->allows_capital_outlay;
                if (! $allowed) {
                    continue;
                }
                $programmedAmount = (float) (
                    $programmed[$type->id][$category] ?? 0
                );
                if ($programmedAmount <= 0) {
                    continue;
                }
                $allocationAmount = (float) (
                    $storedItems->get(
                        $type->id . '|' . $category
                    )->amount ?? 0
                );
                $balance = $allocationAmount - $programmedAmount;
                if ($balance < -0.01) {
                    $categoryName = $category === 'mooe'
                        ? 'MOOE'
                        : 'Capital Outlay';
                    $errorKey = 'financial_plan_allocation_'
                        . $type->id . '_' . $category . '_balance';
                    $errors[$errorKey] =
                        "{$type->name} {$categoryName} programmed amount exceeds the available allocation by "
                        . number_format(abs($balance), 2) . '.';
                }
            }
        }
        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
    // Get submission record
    private function getSubmission(int $fiscalYear, string $officeName, ?int $staffId = null): ?FinancialPlanSubmission
    {
        $query = FinancialPlanSubmission::where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName);
        if ($staffId !== null) {
            $query->where('staff_id', $staffId);
        } else {
            $this->applyStaffScope($query);
        }
        return $query->first();
    }
    // Check if plan is finalized
    private function planIsLocked(int $fiscalYear, string $officeName): bool
    {
        return $this->getSubmission($fiscalYear, $officeName)?->isLocked() ?? false;
    }
    // Return locked response
    private function lockedResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'This plan is finalized. Reopen it first if you need to make changes.',
        ], 403);
    }
    // Get the first row used to authorize a whole plan
    private function findPlanForAccess(int $fiscalYear, string $officeName): ?FinancialPlan
    {
        $query = FinancialPlan::where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName);
        // Non-administrators must resolve the plan from their own staff.
        // This prevents a row from another staff-level office from being selected.
        $this->applyStaffScope($query);
        return $query->first();
    }
    // Authorize an existing plan, or creation when no plan exists yet
    private function authorizePlanWrite(int $fiscalYear, string $officeName): ?FinancialPlan
    {
        $plan = $this->findPlanForAccess($fiscalYear, $officeName);
        if ($plan) {
            $this->authorize('update', $plan);
            return $plan;
        }
        $this->authorize('create', FinancialPlan::class);
        return null;
    }
    // Authorize reading an existing plan
    private function authorizePlanRead(int $fiscalYear, string $officeName): ?FinancialPlan
    {
        $plan = $this->findPlanForAccess($fiscalYear, $officeName);
        if ($plan) {
            $this->authorize('view', $plan);
        }
        return $plan;
    }
    // Restrict queries to the current user's staff unless administrator
    private function applyStaffScope($query)
    {
        $user = auth()->user();
        if (! in_array((int) $user->role_id, [1, 29], true)) {
            $query->where('staff_id', $user->staff_id);
        }
        return $query;
    }
    // Audit log
    private function auditLog($planId, string $field, $old, $new, string $activity): void
    {
        $oldDisplay = $old === null || $old === '' ? '—' : $old;
        $newDisplay = $new === null || $new === '' ? '—' : $new;
        $this->addSystemLogs(
            "Financial Plan: {$activity} (from \"{$oldDisplay}\" to \"{$newDisplay}\")",
            auth()->id(),
            auth()->user()->name ?? auth()->user()->email ?? null,
            request()->getClientIp(true),
            'financial_plans',
            (int) $planId
        );
    }
    private function auditFieldChanges(FinancialPlan $plan, array $incoming): void
    {
        foreach (self::TRACKED_FIELDS as $field) {
            if (! array_key_exists($field, $incoming)) {
                continue;
            }
            $old = $plan->getOriginal($field);
            $new = $incoming[$field];
            if ((string) $old !== (string) $new) {
                $this->auditLog(
                    $plan->id,
                    $field,
                    $old,
                    $new,
                    'Updated ' . str_replace('_', ' ', $field)
                );
            }
        }
    }
    // Plans list
    public function plans(Request $request): View
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $rowsQuery = $this->applyStaffScope(FinancialPlan::query());
        $rows = $rowsQuery
            ->where('row_type', 'item')
            ->get(['fiscal_year', 'office_name', 'mooe', 'capital_outlay', 'contract_amount']);
        $submissionsQuery = FinancialPlanSubmission::query();
        if (! in_array((int) auth()->user()->role_id, [1, 29], true)) {
            $submissionsQuery->where('staff_id', auth()->user()->staff_id);
        }
        $submissions = $submissionsQuery
            ->get(['fiscal_year', 'office_name', 'status', 'finalized'])
            ->keyBy(fn ($s) => $s->fiscal_year . '|' . $s->office_name);
        $plans = $rows
            ->groupBy(fn ($r) => $r->fiscal_year . '|' . $r->office_name)
            ->map(function ($group, $key) use ($submissions) {
                $budgetSum = $group->sum(function ($r) {
                    [$effMooe, $effCo] = $this->effectiveAmounts(
                        (float) $r->mooe,
                        (float) $r->capital_outlay,
                        $r->contract_amount
                    );
                    return $effMooe + $effCo;
                });
                return (object) [
                    'fiscal_year' => $group->first()->fiscal_year,
                    'office_name' => $group->first()->office_name,
                    'row_count'   => $group->count(),
                    'budget_sum'  => $budgetSum,
                    'status'      => $submissions->get($key)->status ?? 'draft',
                    'finalized'   => $submissions->get($key)->finalized ?? 'no',
                ];
            })
            ->values()
            ->sortBy('office_name')
            ->sortByDesc('fiscal_year')
            ->values();
        // Build filter options from available plans
        $offices     = $plans->pluck('office_name')->unique()->sort()->values();
        $fiscalYears = $plans->pluck('fiscal_year')->unique()->sortDesc()->values();
        return view('financial-plans.plans', [
            'plans'       => $plans,
            'offices'     => $offices,
            'fiscalYears' => $fiscalYears,
        ]);
    }
    // Index and builder pages
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();
        $officeQuery = $this->applyStaffScope(FinancialPlan::query());
        $offices = $officeQuery->distinct()->orderBy('office_name')->pluck('office_name');
        if (! $officeName && $offices->isNotEmpty()) {
            $officeName = $offices->first();
        }
        return view('financial-plans.index', [
            'fiscalYear' => $fiscalYear,
            'officeName' => $officeName,
            'offices'    => $offices,
            'months'     => self::MONTHS,
        ]);
    }
    public function builder(Request $request): View
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();
        $accessPlan = null;
        if ($officeName !== '') {
            $accessPlan = $this->findPlanForAccess($fiscalYear, $officeName);
            if ($accessPlan) {
                $this->authorize('view', $accessPlan);
            } else {
                $this->authorize('create', FinancialPlan::class);
            }
        } else {
            $this->authorize('create', FinancialPlan::class);
        }
        // Resolve the Staff/Office owner from the Financial Plan itself first.
        // This is important for administrators because their user account may
        // not have a staff_id even when they open an existing staff-owned plan.
        $personnelStaffId = $accessPlan?->staff_id;
        if ($personnelStaffId === null && $officeName !== '') {
            $personnelStaffId = FinancialPlan::query()
                ->where('fiscal_year', $fiscalYear)
                ->where('office_name', $officeName)
                ->whereNotNull('staff_id')
                ->value('staff_id');
        }
        // For a brand-new plan with no existing owner rows yet, use the
        // logged-in user's Staff/Office when available.
        if ($personnelStaffId === null) {
            $personnelStaffId = auth()->user()->staff_id;
        }
        $personnelOptions = collect();
        if ($personnelStaffId !== null) {
            $personnelOptions = StaffPersonnel::query()
                ->active()
                ->where('staff_id', $personnelStaffId)
                ->orderBy('name')
                ->get(['id', 'name', 'position']);
        }
        // Program Classification and PREXC come from the controlled master list.
        $prexcClassifications = PrexcClassification::query()
            ->active()
            ->ordered()
            ->get([
                'id',
                'classification_group',
                'program_name',
                'classification_name',
                'prexc_code',
            ]);
        // Allocation Types are configured per Staff/Office.
        // Do not assume every office uses ICTS allocation types such as MITHI/NINP.
        $allocationTypeOptions = collect();
        if ($personnelStaffId !== null) {
            $allocationTypeOptions = FinancialPlanAllocationType::query()
                ->forStaff((int) $personnelStaffId)
                ->active()
                ->ordered()
                ->get([
                    'id',
                    'code',
                    'name',
                    'allows_mooe',
                    'allows_capital_outlay',
                ]);
        }
        return view('financial-plans.builder', [
            'fiscalYear'            => $fiscalYear,
            'officeName'            => $officeName,
            'months'                => self::MONTHS,
            'personnelOptions'      => $personnelOptions,
            'prexcClassifications'  => $prexcClassifications,
            'allocationTypeOptions' => $allocationTypeOptions,
        ]);
    }
    // Data
    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();
        $query = FinancialPlan::query()
            ->with('targets')
            ->where('fiscal_year', $fiscalYear);
        if ($officeName) {
            $this->authorizePlanRead($fiscalYear, $officeName);
            $query->where('office_name', $officeName);
        }
        // Always scope the returned rows for non-administrators.
        // Authorization checks the plan; this also prevents mixed-division rows
        // with the same fiscal year and office name from being returned.
        $this->applyStaffScope($query);
        $rows = $query
            ->orderBy('sort_order')
            ->get();
        return response()->json($rows->map(function (FinancialPlan $p) {
            [$effMooe, $effCapitalOutlay] = $this->effectiveAmounts(
                (float) $p->mooe,
                (float) $p->capital_outlay,
                $p->contract_amount
            );
            return [
                'id'                      => $p->id,
                'row_type'                => $p->row_type,
                'program_classification'  => $p->program_classification,
                'prexc_code'              => $p->prexc_code,
                'allocation_type'          => $p->allocation_type,
                'staff_unit_project'      => $p->staff_unit_project,
                'specific_activity'       => $p->specific_activity,
                'procurement_status'      => $p->is_procured ? 'OK' : $p->procurement_status,
                'expense_item'            => $p->expense_item,
                'assigned_personnel'      => $p->assigned_personnel,
                // Original amounts
                'mooe'                     => (float) $p->mooe,
                'capital_outlay'           => (float) $p->capital_outlay,
                'contract_amount'          => $p->contract_amount !== null ? (float) $p->contract_amount : null,
                // Effective amounts after contract adjustment
                'effective_mooe'           => $effMooe,
                'effective_capital_outlay' => $effCapitalOutlay,
                'months'                  => $p->monthly_amounts,
                'total'                   => $p->total_target,
                'saeb_balance'            => $p->saeb_balance,
            ];
        }));
    }
    public function signatories(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();
        $accessPlan = $this->authorizePlanRead($fiscalYear, $officeName);
        $signatoryQuery = FinancialPlanSignatory::where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName);
        if ($accessPlan?->staff_id !== null) {
            $signatoryQuery->where('staff_id', $accessPlan->staff_id);
        } else {
            $this->applyStaffScope($signatoryQuery);
        }
        $signatory = $signatoryQuery->first();
        return response()->json([
            'prepared_by'              => $signatory->prepared_by ?? '',
            'prepared_by_position'     => $signatory->prepared_by_position ?? '',
            'reviewed_by'              => $signatory->reviewed_by ?? '',
            'reviewed_by_position'     => $signatory->reviewed_by_position ?? '',
            'recommended_by'           => $signatory->recommended_by ?? '',
            'recommended_by_position'  => $signatory->recommended_by_position ?? '',
            'approved_by'              => $signatory->approved_by ?? '',
            'approved_by_position'     => $signatory->approved_by_position ?? '',
        ]);
    }
    public function saveSignatories(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
            'prepared_by' => ['nullable', 'string', 'max:150'],
            'prepared_by_position' => ['nullable', 'string', 'max:150'],
            'reviewed_by' => ['nullable', 'string', 'max:150'],
            'reviewed_by_position' => ['nullable', 'string', 'max:150'],
            'recommended_by' => ['nullable', 'string', 'max:150'],
            'recommended_by_position' => ['nullable', 'string', 'max:150'],
            'approved_by' => ['nullable', 'string', 'max:150'],
            'approved_by_position' => ['nullable', 'string', 'max:150'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $accessPlan = $this->authorizePlanWrite($year, $office);
        // Prevent changes after finalization
        if ($this->planIsLocked($year, $office)) {
            return $this->lockedResponse();
        }
        // The database has one signatory record per fiscal year and office.
        // Reuse an existing legacy record and synchronize its Staff/Office ownership.
        $signatory = FinancialPlanSignatory::firstOrNew([
            'fiscal_year' => $year,
            'office_name' => $office,
        ]);
        $signatory->staff_id = $accessPlan?->staff_id
            ?? auth()->user()->staff_id
            ?? $signatory->staff_id;
        $signatory->division_id = $accessPlan?->division_id
            ?? auth()->user()->division_id
            ?? $signatory->division_id;
        $signatory->prepared_by = $validated['prepared_by'] ?? null;
        $signatory->prepared_by_position = $validated['prepared_by_position'] ?? null;
        $signatory->reviewed_by = $validated['reviewed_by'] ?? null;
        $signatory->reviewed_by_position = $validated['reviewed_by_position'] ?? null;
        $signatory->recommended_by = $validated['recommended_by'] ?? null;
        $signatory->recommended_by_position = $validated['recommended_by_position'] ?? null;
        $signatory->approved_by = $validated['approved_by'] ?? null;
        $signatory->approved_by_position = $validated['approved_by_position'] ?? null;
        $signatory->save();
        return response()->json([
            'success' => true,
            'message' => 'Signatories saved.',
            'data' => $signatory,
        ]);
    }
    public function save(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $request->merge([
            'rows' => $this->normalizeRows($request->input('rows', [])),
        ]);
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
            'rows'                          => ['present', 'array'],
            'rows.*.id'                     => ['nullable', 'integer', 'exists:financial_plans,id'],
            'rows.*.row_type'               => ['required', 'string', 'in:header,subheader,item'],
            'rows.*.program_classification' => ['nullable', 'string', 'max:500'],
            'rows.*.prexc_code'             => ['nullable', 'string', 'max:50'],
            'rows.*.allocation_type'        => ['nullable', 'string', 'max:100'],
            'rows.*.staff_unit_project'     => ['nullable', 'string', 'max:150'],
            'rows.*.specific_activity'      => ['nullable', 'string'],
            'rows.*.procurement_status'     => ['nullable', 'string', 'max:150'],
            'rows.*.expense_item'           => ['nullable', 'string', 'max:150'],
            'rows.*.assigned_personnel'     => ['nullable', 'string', 'max:150'],
            'rows.*.mooe'                   => ['nullable', 'numeric', 'min:0'],
            'rows.*.capital_outlay'         => ['nullable', 'numeric', 'min:0'],
            'rows.*.contract_amount'        => ['nullable', 'numeric', 'min:0'],
            'rows.*.months'                 => ['nullable', 'array'],
            'rows.*.months.*'               => ['nullable', 'numeric', 'min:0'],
        ]);
        $year   = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $rows   = $validated['rows'] ?? [];
        $accessPlan = $this->authorizePlanWrite($year, $office);
        // Enforce the controlled Program Classification/PREXC pairing on the server.
        $this->validatePrexcRows($rows, $year, $office);
        // Validate any selected Allocation Type against this Staff/Office master data.
        // Blank Allocation Type remains allowed while saving a draft.
        $allocationStaffId = $accessPlan?->staff_id ?? auth()->user()->staff_id;
        $this->validateAllocationRowsForSave(
            $rows,
            $year,
            $office,
            $allocationStaffId
        );
        // Prevent changes after finalization
        if ($this->planIsLocked($year, $office)) {
            return $this->lockedResponse();
        }
        DB::beginTransaction();
        try {
            $existingQuery = FinancialPlan::where('fiscal_year', $year)
                ->where('office_name', $office);
            $this->applyStaffScope($existingQuery);
            $existingIds = $existingQuery->pluck('id')->toArray();
            $savedIds  = [];
            $sortOrder = 10;
            foreach ($rows as $row) {
                $plan = null;
                if (! empty($row['id'])) {
                    $planQuery = FinancialPlan::where('id', $row['id'])
                        ->where('fiscal_year', $year)
                        ->where('office_name', $office);
                    $this->applyStaffScope($planQuery);
                    $plan = $planQuery->first();
                }
                $isNew = ! $plan;
                $plan  ??= new FinancialPlan();
                $incoming = [
                    'fiscal_year' => $year,
                    'office_name' => $office,
                    'staff_id' => $plan->staff_id ?? $accessPlan?->staff_id ?? auth()->user()->staff_id,
                    'division_id' => $plan->division_id
                        ?? $accessPlan?->division_id
                        ?? auth()->user()->division_id,
                    'row_type'               => $row['row_type'],
                    'program_classification' => $row['program_classification'] ?? null,
                    'prexc_code'             => $row['prexc_code'] ?? null,
                    'allocation_type'        => $row['row_type'] === 'item'
                        ? (
                            array_key_exists('allocation_type', $row)
                                ? $row['allocation_type']
                                : ($plan->allocation_type ?? null)
                        )
                        : null,
                    'staff_unit_project'     => $row['staff_unit_project'] ?? null,
                    'specific_activity'      => $row['specific_activity'] ?? null,
                    'procurement_status'     => array_key_exists('procurement_status', $row)
                        ? $row['procurement_status']
                        : ($plan->procurement_status ?? null),
                    'expense_item'           => $row['expense_item'] ?? null,
                    'assigned_personnel'     => $row['assigned_personnel'] ?? null,
                    'mooe'                   => (float) ($row['mooe'] ?? 0),
                    'capital_outlay'         => (float) ($row['capital_outlay'] ?? 0),
                    'contract_amount'        => array_key_exists('contract_amount', $row) && $row['contract_amount'] !== null
                        ? (float) $row['contract_amount']
                        : null,
                    'sort_order'             => $sortOrder,
                ];
                if (! $isNew) {
                    $this->auditFieldChanges($plan, $incoming);
                }
                $plan->fill($incoming);
                $plan->save();
                if ($isNew) {
                    $this->auditLog($plan->id, 'row', null, $row['row_type'], 'Created a new row');
                }
                $savedIds[] = $plan->id;
                if ($row['row_type'] === 'item') {
                    for ($m = 1; $m <= 12; $m++) {
                        FinancialPlanTarget::updateOrCreate(
                            [
                                'financial_plan_id' => $plan->id,
                                'month'              => $m,
                            ],
                            [
                                'amount' => (float) ($row['months'][$m] ?? 0),
                            ]
                        );
                    }
                } else {
                    FinancialPlanTarget::where('financial_plan_id', $plan->id)->delete();
                }
                $sortOrder += 10;
            }
            $deleteIds = array_diff($existingIds, $savedIds);
            if (! empty($deleteIds)) {
                foreach ($deleteIds as $id) {
                    $this->auditLog($id, 'row', 'present', 'deleted', 'Row removed from plan');
                }
                FinancialPlan::whereIn('id', $deleteIds)->delete();
            }
            DB::commit();
            return response()->json([
                'success'  => true,
                'message'  => 'Successfully saved data.',
                'redirect' => route('financial-plans.index', [
                    'fiscal_year' => $year,
                    'office_name' => $office,
                ]),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Financial Plan Save Error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    // Delete
    public function destroy(FinancialPlan $financial_plan): RedirectResponse
    {
        $this->authorize('delete', $financial_plan);
        // Prevent deletion from finalized plan
        if ($this->planIsLocked((int) $financial_plan->fiscal_year, $financial_plan->office_name)) {
            return redirect()->back()->with(
                'error',
                'This plan is finalized. Reopen it first.'
            );
        }
        DB::transaction(function () use ($financial_plan) {
            $this->auditLog(
                $financial_plan->id,
                'row',
                $financial_plan->row_type,
                'deleted',
                'Row deleted'
            );
            FinancialPlanTarget::where('financial_plan_id', $financial_plan->id)->delete();
            $financial_plan->delete();
        });
        return redirect()->back()->with('success', 'Row deleted.');
    }
    // Delete (entire filed plan for one fiscal year + office)
    public function destroyPlan(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        // Prevent deleting a finalized plan
        if ($this->planIsLocked($year, $office)) {
            return $this->lockedResponse();
        }
        $plansQuery = FinancialPlan::where('fiscal_year', $year)
            ->where('office_name', $office);
        $this->applyStaffScope($plansQuery);
        $plans = $plansQuery->get();
        if ($plans->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Nothing to delete. That financial plan no longer exists.',
            ]);
        }
        $this->authorize('delete', $plans->first());
        try {
            $rowCount = $plans->count();
            $planIds = $plans->pluck('id');
            $staffId = $plans->first()->staff_id;
            DB::transaction(function () use ($plans, $planIds, $year, $office, $staffId) {
                foreach ($plans as $plan) {
                    $this->auditLog(
                        $plan->id,
                        'row',
                        $plan->row_type,
                        'deleted',
                        "Row deleted (bulk plan delete: FY {$year}, {$office})"
                    );
                }
                // Delete monthly targets first
                FinancialPlanTarget::whereIn('financial_plan_id', $planIds)->delete();
                // Delete WFP rows
                FinancialPlan::whereIn('id', $planIds)->delete();
                // Delete plan-level records
                FinancialPlanSignatory::where('fiscal_year', $year)
                    ->where('office_name', $office)
                    ->where('staff_id', $staffId)
                    ->delete();
                FinancialPlanAllocation::where('fiscal_year', $year)
                    ->where('office_name', $office)
                    ->where('staff_id', $staffId)
                    ->delete();
                FinancialPlanSubmission::where('fiscal_year', $year)
                    ->where('office_name', $office)
                    ->where('staff_id', $staffId)
                    ->delete();
            });
            return response()->json([
                'success' => true,
                'message' => "Deleted FY {$year} financial plan for {$office} ({$rowCount} row(s)).",
            ]);
        } catch (\Throwable $e) {
            Log::error('Financial Plan Bulk Delete Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'fiscal_year' => $year,
                'office_name' => $office,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting the financial plan.',
            ], 500);
        }
    }
    public function exportPdf(Request $request)
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();
        $query = FinancialPlan::query()
            ->with(['targets', 'saebEntries', 'procurements'])
            ->where('fiscal_year', $fiscalYear);
        if ($officeName) {
            $this->authorizePlanRead($fiscalYear, $officeName);
            $query->where('office_name', $officeName);
        }
        $this->applyStaffScope($query);
        $rows = $query
            ->with(['saebEntries', 'procurements'])
            ->orderBy('sort_order')
            ->get()
            ->map(function (FinancialPlan $p) {
                [$effMooe, $effCapitalOutlay] = $this->effectiveAmounts(
                    (float) $p->mooe,
                    (float) $p->capital_outlay,
                    $p->contract_amount
                );
                return [
                    'row_type'               => $p->row_type,
                    'program_classification' => $p->program_classification,
                    'prexc_code'             => $p->prexc_code,
                    'allocation_type'        => $p->allocation_type,
                    'staff_unit_project'     => $p->staff_unit_project,
                    'specific_activity'      => $p->specific_activity,
                    'procurement_status'     => $p->is_procured ? 'OK' : $p->procurement_status,
                    'expense_item'           => $p->expense_item,
                    'assigned_personnel'     => $p->assigned_personnel,
                    // Original amounts
                    'mooe'                    => (float) $p->mooe,
                    'capital_outlay'          => (float) $p->capital_outlay,
                    'contract_amount'         => $p->contract_amount !== null ? (float) $p->contract_amount : null,
                    // Effective amounts after contract adjustment
                    'effective_mooe'           => $effMooe,
                    'effective_capital_outlay' => $effCapitalOutlay,
                    'months'                  => $p->monthly_amounts,
                    'total'                   => $p->total_target,
                    'saeb_balance'            => $p->saeb_balance,
                ];
            });
        $blocks      = $this->buildPdfBlocks($rows);
        $grandTotals = $this->buildPdfGrandTotals($rows);
        $signatoryQuery = FinancialPlanSignatory::where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName);
        $this->applyStaffScope($signatoryQuery);
        $signatory = $signatoryQuery->first();
        $pdf = Pdf::loadView('financial-plans.pdf', [
            'fiscalYear'  => $fiscalYear,
            'officeName'  => $officeName,
            'months'      => self::MONTHS,
            'blocks'      => $blocks,
            'grandTotals' => $grandTotals,
            'signatory'   => $signatory,
            'generatedAt' => now(),
        ])->setPaper('folio', 'landscape');
        $filename = "FY{$fiscalYear}_Financial_Plan_" . \Illuminate\Support\Str::slug($officeName ?: 'All') . '.pdf';
        return $pdf->download($filename)->withHeaders([
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    private function buildPdfBlocks($rows): array
    {
        $blocks = [];
        $run = null;
        foreach ($rows as $r) {
            if ($r['row_type'] === 'header') {
                if ($run) {
                    $blocks[] = $run;
                    $run = null;
                }
                $blocks[] = ['type' => 'header', 'row' => $r];
                continue;
            }
            $key = trim($r['program_classification'] ?? '') . '::' . trim($r['prexc_code'] ?? '');
            if (! $run || $run['key'] !== $key) {
                if ($run) {
                    $blocks[] = $run;
                }
                $run = ['type' => 'group', 'key' => $key, 'rows' => []];
            }
            $run['rows'][] = $r;
        }
        if ($run) {
            $blocks[] = $run;
        }
        foreach ($blocks as &$block) {
            if ($block['type'] !== 'group') {
                continue;
            }
            $totals = [
                'mooe'           => 0,
                'capital_outlay' => 0,
                'total'          => 0,
                'months'         => array_fill(1, 12, 0),
            ];
            foreach ($block['rows'] as $r) {
                [$effMooe, $effCo] = $this->effectiveAmounts(
                    (float) $r['mooe'],
                    (float) $r['capital_outlay'],
                    $r['contract_amount'] ?? null
                );
                $totals['mooe'] += $effMooe;
                $totals['capital_outlay'] += $effCo;
                $totals['total'] += $r['total'];
                for ($m = 1; $m <= 12; $m++) {
                    $totals['months'][$m] += (float) ($r['months'][$m] ?? 0);
                }
            }
            $block['totals'] = $totals;
        }
        unset($block);
        return $blocks;
    }
    private function buildPdfGrandTotals($rows): array
    {
        $grand = [
            'mooe'           => 0,
            'capital_outlay' => 0,
            'total'          => 0,
            'months'         => array_fill(1, 12, 0),
        ];
        foreach ($rows as $r) {
            if ($r['row_type'] !== 'item') {
                continue;
            }
            [$effMooe, $effCo] = $this->effectiveAmounts(
                (float) $r['mooe'],
                (float) $r['capital_outlay'],
                $r['contract_amount'] ?? null
            );
            $grand['mooe'] += $effMooe;
            $grand['capital_outlay'] += $effCo;
            $grand['total'] += $r['total'];
            for ($m = 1; $m <= 12; $m++) {
                $grand['months'][$m] += (float) ($r['months'][$m] ?? 0);
            }
        }
        return $grand;
    }
    // Allocation and balance
    public function allocation(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();
        $accessPlan = $this->authorizePlanRead($fiscalYear, $officeName);
        $staffId = $this->resolvePlanStaffId($fiscalYear, $officeName, $accessPlan);
        $allocation = $this->findAllocationHeader($fiscalYear, $officeName, $staffId);
        $allocationTypes = $this->allocationTypesForStaff($staffId);
        $items = $allocation
            ? FinancialPlanAllocationItem::query()
                ->where('financial_plan_allocation_id', $allocation->id)
                ->get()
                ->keyBy(fn ($item) => $item->allocation_type_id . '|' . $item->expense_category)
            : collect();
        $allocationItems = $allocationTypes->map(function ($type) use ($items) {
            $categories = [];
            if ($type->allows_mooe) {
                $categories['mooe'] = (float) ($items->get($type->id . '|mooe')->amount ?? 0);
            }
            if ($type->allows_capital_outlay) {
                $categories['capital_outlay'] = (float) (
                    $items->get($type->id . '|capital_outlay')->amount ?? 0
                );
            }
            return [
                'allocation_type_id' => (int) $type->id,
                'code' => $type->code,
                'name' => $type->name,
                'allows_mooe' => (bool) $type->allows_mooe,
                'allows_capital_outlay' => (bool) $type->allows_capital_outlay,
                'categories' => $categories,
            ];
        })->values();
        return response()->json([
            'allocation_items' => $allocationItems,
            // Temporary compatibility for the current ICTS Builder/Index.
            'mooe_allocation' => $this->allocationItemAmount(
                $allocation,
                $allocationTypes,
                'mithi',
                'mooe'
            ),
            'capital_outlay_allocation' => $this->allocationItemAmount(
                $allocation,
                $allocationTypes,
                'mithi',
                'capital_outlay'
            ),
            'ninp_allocation' => $this->allocationItemAmount(
                $allocation,
                $allocationTypes,
                'ninp',
                'mooe'
            ),
        ]);
    }
    public function saveAllocation(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
            'allocation_items' => ['nullable', 'array'],
            'allocation_items.*.allocation_type_id' => [
                'required_with:allocation_items',
                'integer',
                'exists:financial_plan_allocation_types,id',
            ],
            'allocation_items.*.expense_category' => [
                'required_with:allocation_items',
                'string',
                'in:mooe,capital_outlay',
            ],
            'allocation_items.*.amount' => ['nullable', 'numeric', 'min:0'],
            // Temporary compatibility for the current ICTS Builder.
            'mooe_allocation' => ['nullable', 'numeric', 'min:0'],
            'capital_outlay_allocation' => ['nullable', 'numeric', 'min:0'],
            'ninp_allocation' => ['nullable', 'numeric', 'min:0'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $accessPlan = $this->authorizePlanWrite($year, $office);
        if ($this->planIsLocked($year, $office)) {
            return $this->lockedResponse();
        }
        $staffId = $this->resolvePlanStaffId($year, $office, $accessPlan);
        if ($staffId === null) {
            throw ValidationException::withMessages([
                'office_name' => 'The Staff/Office owner could not be determined.',
            ]);
        }
        $allocationTypes = $this->allocationTypesForStaff($staffId);
        if ($allocationTypes->isEmpty()) {
            throw ValidationException::withMessages([
                'allocation_items' => 'No active Allocation Types are configured for this Staff/Office.',
            ]);
        }
        $incomingItems = collect($validated['allocation_items'] ?? []);
        // Convert the current ICTS fields until the Builder sends generic items.
        if ($incomingItems->isEmpty()) {
            $legacyItems = [
                ['code' => 'mithi', 'expense_category' => 'mooe', 'amount' => $validated['mooe_allocation'] ?? 0],
                ['code' => 'mithi', 'expense_category' => 'capital_outlay', 'amount' => $validated['capital_outlay_allocation'] ?? 0],
                ['code' => 'ninp', 'expense_category' => 'mooe', 'amount' => $validated['ninp_allocation'] ?? 0],
            ];
            $incomingItems = collect($legacyItems)
                ->map(function ($item) use ($allocationTypes) {
                    $type = $allocationTypes->first(
                        fn ($type) => strtolower(trim((string) $type->code)) === $item['code']
                    );
                    if (! $type) {
                        return null;
                    }
                    return [
                        'allocation_type_id' => (int) $type->id,
                        'expense_category' => $item['expense_category'],
                        'amount' => (float) $item['amount'],
                    ];
                })
                ->filter()
                ->values();
        }
        $errors = [];
        $seen = [];
        foreach ($incomingItems as $index => $item) {
            $type = $allocationTypes->firstWhere('id', (int) $item['allocation_type_id']);
            if (! $type) {
                $errors["allocation_items.{$index}.allocation_type_id"] =
                    'The selected Allocation Type is not available for this Staff/Office.';
                continue;
            }
            $category = $item['expense_category'];
            if ($category === 'mooe' && ! $type->allows_mooe) {
                $errors["allocation_items.{$index}.expense_category"] =
                    "{$type->name} does not allow MOOE.";
            }
            if ($category === 'capital_outlay' && ! $type->allows_capital_outlay) {
                $errors["allocation_items.{$index}.expense_category"] =
                    "{$type->name} does not allow Capital Outlay.";
            }
            $key = $type->id . '|' . $category;
            if (isset($seen[$key])) {
                $errors["allocation_items.{$index}.expense_category"] =
                    'Duplicate Allocation Type and expense category.';
            }
            $seen[$key] = true;
        }
        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
        $allocation = DB::transaction(function () use (
            $year,
            $office,
            $staffId,
            $accessPlan,
            $allocationTypes,
            $incomingItems
        ) {
            // The database has one allocation header per fiscal year and office.
            // Reuse an existing legacy header even when staff_id was previously null
            // or different, then synchronize its ownership.
            $allocation = FinancialPlanAllocation::firstOrNew([
                'fiscal_year' => $year,
                'office_name' => $office,
            ]);
            $allocation->staff_id = $staffId;
            $allocation->division_id = $accessPlan?->division_id
                ?? $allocation->division_id
                ?? auth()->user()->division_id;
            $allocation->save();
            $keepIds = [];
            foreach ($incomingItems as $item) {
                $allocationItem = FinancialPlanAllocationItem::updateOrCreate(
                    [
                        'financial_plan_allocation_id' => $allocation->id,
                        'allocation_type_id' => (int) $item['allocation_type_id'],
                        'expense_category' => $item['expense_category'],
                    ],
                    [
                        'amount' => (float) ($item['amount'] ?? 0),
                    ]
                );
                $keepIds[] = $allocationItem->id;
            }
            $deleteQuery = FinancialPlanAllocationItem::where(
                'financial_plan_allocation_id',
                $allocation->id
            );
            if (! empty($keepIds)) {
                $deleteQuery->whereNotIn('id', $keepIds);
            }
            $deleteQuery->delete();
            // Keep the old ICTS columns synchronized during the transition.
            $allocation->update([
                'mooe_allocation' => $this->allocationItemAmount(
                    $allocation,
                    $allocationTypes,
                    'mithi',
                    'mooe'
                ),
                'capital_outlay_allocation' => $this->allocationItemAmount(
                    $allocation,
                    $allocationTypes,
                    'mithi',
                    'capital_outlay'
                ),
                'ninp_allocation' => $this->allocationItemAmount(
                    $allocation,
                    $allocationTypes,
                    'ninp',
                    'mooe'
                ),
            ]);
            return $allocation;
        });
        return response()->json([
            'success' => true,
            'message' => 'Allocation saved.',
            'data' => $allocation,
        ]);
    }
    // Calculate programmed amounts and remaining allocation
    public function totals(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();
        $accessPlan = $this->authorizePlanRead($fiscalYear, $officeName);
        $staffId = $this->resolvePlanStaffId($fiscalYear, $officeName, $accessPlan);
        $allocationTypes = $this->allocationTypesForStaff($staffId);
        $allocationTypesByCode = $allocationTypes->keyBy(
            fn ($type) => strtolower(trim((string) $type->code))
        );
        $itemsQuery = FinancialPlan::where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName)
            ->where('row_type', 'item');
        if ($staffId !== null) {
            $itemsQuery->where('staff_id', $staffId);
        } else {
            $this->applyStaffScope($itemsQuery);
        }
        $planItems = $itemsQuery->get([
            'mooe',
            'capital_outlay',
            'contract_amount',
            'allocation_type',
        ]);
        $programmed = [];
        foreach ($planItems as $row) {
            $code = strtolower(trim((string) ($row->allocation_type ?? '')));
            $type = $allocationTypesByCode->get($code);
            if (! $type) {
                continue;
            }
            [$effectiveMooe, $effectiveCo] = $this->effectiveAmounts(
                (float) $row->mooe,
                (float) $row->capital_outlay,
                $row->contract_amount
            );
            if ($type->allows_mooe) {
                $programmed[$type->id]['mooe'] =
                    ($programmed[$type->id]['mooe'] ?? 0) + $effectiveMooe;
            }
            if ($type->allows_capital_outlay) {
                $programmed[$type->id]['capital_outlay'] =
                    ($programmed[$type->id]['capital_outlay'] ?? 0) + $effectiveCo;
            }
        }
        $allocation = $this->findAllocationHeader($fiscalYear, $officeName, $staffId);
        $storedItems = $allocation
            ? FinancialPlanAllocationItem::where(
                'financial_plan_allocation_id',
                $allocation->id
            )
                ->get()
                ->keyBy(fn ($item) => $item->allocation_type_id . '|' . $item->expense_category)
            : collect();
        $totals = collect();
        foreach ($allocationTypes as $type) {
            foreach (['mooe', 'capital_outlay'] as $category) {
                $allowed = $category === 'mooe'
                    ? $type->allows_mooe
                    : $type->allows_capital_outlay;
                if (! $allowed) {
                    continue;
                }
                $allocationAmount = (float) (
                    $storedItems->get($type->id . '|' . $category)->amount ?? 0
                );
                $programmedAmount = (float) ($programmed[$type->id][$category] ?? 0);
                $totals->push([
                    'allocation_type_id' => (int) $type->id,
                    'code' => $type->code,
                    'name' => $type->name,
                    'expense_category' => $category,
                    'allocation' => $allocationAmount,
                    'programmed' => $programmedAmount,
                    'balance' => $allocationAmount - $programmedAmount,
                ]);
            }
        }
        // Temporary compatibility values for the current ICTS Index/Builder.
        $mithiMooe = $totals->first(
            fn ($row) => strtolower($row['code']) === 'mithi'
                && $row['expense_category'] === 'mooe'
        );
        $mithiCo = $totals->first(
            fn ($row) => strtolower($row['code']) === 'mithi'
                && $row['expense_category'] === 'capital_outlay'
        );
        $ninpMooe = $totals->first(
            fn ($row) => strtolower($row['code']) === 'ninp'
                && $row['expense_category'] === 'mooe'
        );
        return response()->json([
            'allocation_totals' => $totals->values(),
            'mooe_allocation' => (float) ($mithiMooe['allocation'] ?? 0),
            'capital_outlay_allocation' => (float) ($mithiCo['allocation'] ?? 0),
            'ninp_allocation' => (float) ($ninpMooe['allocation'] ?? 0),
            'mooe_sum' => (float) ($mithiMooe['programmed'] ?? 0),
            'capital_outlay_sum' => (float) ($mithiCo['programmed'] ?? 0),
            'ninp_sum' => (float) ($ninpMooe['programmed'] ?? 0),
            'mooe_balance' => (float) ($mithiMooe['balance'] ?? 0),
            'capital_outlay_balance' => (float) ($mithiCo['balance'] ?? 0),
            'ninp_balance' => (float) ($ninpMooe['balance'] ?? 0),
        ]);
    }
    // Resolve Staff/Office ownership
    private function resolvePlanStaffId(
        int $fiscalYear,
        string $officeName,
        ?FinancialPlan $accessPlan = null
    ): ?int {
        $staffId = $accessPlan?->staff_id;
        if ($staffId === null && $officeName !== '') {
            $staffId = FinancialPlan::query()
                ->where('fiscal_year', $fiscalYear)
                ->where('office_name', $officeName)
                ->whereNotNull('staff_id')
                ->value('staff_id');
        }
        if ($staffId === null) {
            $staffId = auth()->user()->staff_id;
        }
        return $staffId !== null ? (int) $staffId : null;
    }
    // Load Allocation Types for one Staff/Office
    private function allocationTypesForStaff(?int $staffId)
    {
        if ($staffId === null) {
            return collect();
        }
        return FinancialPlanAllocationType::query()
            ->forStaff($staffId)
            ->active()
            ->ordered()
            ->get([
                'id',
                'code',
                'name',
                'allows_mooe',
                'allows_capital_outlay',
                'sort_order',
            ]);
    }
    // Load the allocation header
    private function findAllocationHeader(
        int $fiscalYear,
        string $officeName,
        ?int $staffId
    ): ?FinancialPlanAllocation {
        $query = FinancialPlanAllocation::query()
            ->where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName);
        // The database business key is fiscal year + office name.
        // Staff ownership is synchronized when allocation is saved.
        if ($staffId === null) {
            $this->applyStaffScope($query);
        }
        return $query->first();
    }
    // Read one generic allocation amount
    private function allocationItemAmount(
        ?FinancialPlanAllocation $allocation,
        $allocationTypes,
        string $code,
        string $expenseCategory
    ): float {
        if (! $allocation) {
            return 0.0;
        }
        $type = $allocationTypes->first(
            fn ($type) => strtolower(trim((string) $type->code)) === strtolower($code)
        );
        if (! $type) {
            return 0.0;
        }
        return (float) FinancialPlanAllocationItem::query()
            ->where('financial_plan_allocation_id', $allocation->id)
            ->where('allocation_type_id', $type->id)
            ->where('expense_category', $expenseCategory)
            ->value('amount');
    }
    // Workflow
    public function submitForApproval(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        // Resolve the Financial Plan inside the current user's Staff/Office scope.
        $plan = $this->findPlanForAccess($year, $office);
        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'No financial plan was found.',
            ], 404);
        }
        $this->authorize('submit', $plan);
        if ($this->planIsLocked($year, $office)) {
            return $this->lockedResponse();
        }
        $oldStatus = $this->getSubmission(
            $year,
            $office,
            $plan->staff_id
        )?->status ?? 'draft';
        // Only draft or returned plans can be submitted.
        if (! in_array($oldStatus, ['draft', 'returned'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft or returned plans can be submitted for approval.',
            ], 422);
        }
        // Apply complete Financial Plan validation before submission.
        $this->validateFinancialPlanForSubmit(
            $year,
            $office,
            $plan->staff_id
        );
        $submission = FinancialPlanSubmission::updateOrCreate(
            [
                'fiscal_year' => $year,
                'office_name' => $office,
                'staff_id' => $plan->staff_id,
            ],
            [
                'staff_id' => $plan->staff_id,
                'division_id' => $plan->division_id,
                'status' => 'submitted',
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
                'approved_by' => null,
                'approved_at' => null,
                'return_remarks' => null,
            ]
        );
        $this->auditLog(
            $plan->id,
            'status',
            $oldStatus,
            'submitted',
            "Plan submitted for approval: FY {$year}, {$office}"
        );
        return response()->json([
            'success' => true,
            'message' => 'Plan submitted for approval.',
            'data' => $submission,
        ]);
    }
    public function approve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        // Resolve the plan first so policy authorization can enforce staff_id.
        $plan = $this->findPlanForAccess($year, $office);
        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'No financial plan was found.',
            ], 404);
        }
        $this->authorize('approve', $plan);
        $submission = $this->getSubmission(
            $year,
            $office,
            $plan->staff_id
        );
        if (! $submission) {
            return response()->json([
                'success' => false,
                'message' => 'No submitted plan was found.',
            ], 404);
        }
        if ($submission->finalized === 'yes') {
            return response()->json([
                'success' => false,
                'message' => 'A finalized plan cannot be approved again.',
            ], 422);
        }
        if ($submission->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Only submitted plans can be approved.',
            ], 422);
        }
        $submission->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'return_remarks' => null,
        ]);
        $this->auditLog(
            $plan->id,
            'status',
            'submitted',
            'approved',
            "Plan approved: FY {$year}, {$office}"
        );
        return response()->json([
            'success' => true,
            'message' => 'Plan approved.',
            'data' => $submission,
        ]);
    }
    public function returnForRevision(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
            'return_remarks' => ['required', 'string', 'max:2000'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        // Resolve the plan first so policy authorization can enforce staff_id.
        $plan = $this->findPlanForAccess($year, $office);
        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'No financial plan was found.',
            ], 404);
        }
        $this->authorize('return', $plan);
        $submission = $this->getSubmission(
            $year,
            $office,
            $plan->staff_id
        );
        if (! $submission) {
            return response()->json([
                'success' => false,
                'message' => 'No submitted plan was found.',
            ], 404);
        }
        if ($submission->finalized === 'yes') {
            return response()->json([
                'success' => false,
                'message' => 'A finalized plan cannot be returned.',
            ], 422);
        }
        if ($submission->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Only submitted plans can be returned for revision.',
            ], 422);
        }
        $oldStatus = $submission->status;
        $submission->update([
            'status' => 'returned',
            'return_remarks' => $validated['return_remarks'],
            'approved_by' => null,
            'approved_at' => null,
        ]);
        $this->auditLog(
            $plan->id,
            'status',
            $oldStatus,
            'returned',
            "Plan returned for revision: FY {$year}, {$office}"
        );
        return response()->json([
            'success' => true,
            'message' => 'Plan returned for revision.',
            'data' => $submission,
        ]);
    }
    // Finalize and reopen
    public function finalize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        // Resolve the plan first so policy authorization can enforce staff_id.
        $plan = $this->findPlanForAccess($year, $office);
        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'No financial plan was found.',
            ], 404);
        }
        $this->authorize('finalize', $plan);
        $existingSubmission = $this->getSubmission(
            $year,
            $office,
            $plan->staff_id
        );
        $oldStatus = $existingSubmission?->status ?? 'draft';
        // Finalization is the last step after approval.
        if (! $existingSubmission || $oldStatus !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved plans can be finalized.',
            ], 422);
        }
        if ($existingSubmission->finalized === 'yes') {
            return response()->json([
                'success' => false,
                'message' => 'This plan is already finalized.',
            ], 422);
        }
        $existingSubmission->update([
            'status' => 'finalized',
            'finalized' => 'yes',
            'finalized_by' => auth()->id(),
            'finalized_at' => now(),
        ]);
        $this->auditLog(
            $plan->id,
            'status',
            $oldStatus,
            'finalized',
            "Plan finalized: FY {$year}, {$office}"
        );
        return response()->json([
            'success' => true,
            'message' => 'Plan finalized and locked.',
            'data' => $existingSubmission,
        ]);
    }
    public function reopen(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        // Resolve the plan first so policy authorization can enforce staff_id.
        $plan = $this->findPlanForAccess($year, $office);
        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'No financial plan was found.',
            ], 404);
        }
        $this->authorize('reopen', $plan);
        $submission = $this->getSubmission(
            $year,
            $office,
            $plan->staff_id
        );
        if (! $submission || $submission->finalized !== 'yes') {
            return response()->json([
                'success' => false,
                'message' => 'No finalized plan was found.',
            ], 404);
        }
        $submission->update([
            'status' => 'draft',
            'finalized' => 'no',
            'finalized_by' => null,
            'finalized_at' => null,
        ]);
        $this->auditLog(
            $plan->id,
            'status',
            'finalized',
            'draft',
            "Plan reopened for editing: FY {$year}, {$office}"
        );
        return response()->json([
            'success' => true,
            'message' => 'Plan reopened for editing.',
            'data' => $submission,
        ]);
    }
    // Status
    public function status(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $plan = $this->authorizePlanRead(
            $year,
            $office
        );
        $submissionQuery = FinancialPlanSubmission::where(
            'fiscal_year',
            $year
        )
            ->where('office_name', $office);
        if ($plan?->staff_id !== null) {
            $submissionQuery->where(
                'staff_id',
                $plan->staff_id
            );
        } else {
            $this->applyStaffScope($submissionQuery);
        }
        $submission = $submissionQuery
            ->with([
                'submittedBy:id,firstname,middlename,lastname,email',
                'approvedBy:id,firstname,middlename,lastname,email',
                'finalizedBy:id,firstname,middlename,lastname,email',
            ])
            ->first();
        // Build display name from the actual users table columns.
        $displayName = function ($user): ?string {
            if (! $user) {
                return null;
            }
            $name = collect([
                $user->firstname,
                $user->middlename,
                $user->lastname,
            ])
                ->filter(fn ($part) => filled($part))
                ->implode(' ');
            return $name !== ''
                ? $name
                : $user->email;
        };
        $status = $submission->status ?? 'draft';
        $finalized = $submission->finalized ?? 'no';
        // Workflow abilities are checked against the actual Financial Plan.
        // This allows the policy to enforce Staff/Office ownership.
        // Workflow abilities
        $canEdit = $plan
            ? auth()->user()->can('update', $plan)
            : auth()->user()->can('create', FinancialPlan::class);
        $canSubmit = $plan
            ? auth()->user()->can('submit', $plan)
            : false;
        $canApprove = $plan
            ? auth()->user()->can('approve', $plan)
            : false;
        $canReturn = $plan
            ? auth()->user()->can('return', $plan)
            : false;
        $canFinalize = $plan
            ? auth()->user()->can('finalize', $plan)
            : false;
        $canReopen = $plan
            ? auth()->user()->can('reopen', $plan)
            : false;
        return response()->json([
            'status' => $status,
            'finalized' => $finalized,
            'can_edit' => $finalized !== 'yes'
                && $canEdit,
            'can_submit' => $finalized !== 'yes'
                && in_array($status, ['draft', 'returned'], true)
                && $canSubmit,
            'can_approve' => $finalized !== 'yes'
                && $status === 'submitted'
                && $canApprove,
            'can_return' => $finalized !== 'yes'
                && $status === 'submitted'
                && $canReturn,
            'can_finalize' => $finalized !== 'yes'
                && $status === 'approved'
                && $canFinalize,
            'can_reopen' => $finalized === 'yes'
                && $canReopen,
            'submitted_by' => $displayName(
                $submission?->submittedBy
            ),
            'submitted_at' => $submission?->submitted_at,
            'approved_by' => $displayName(
                $submission?->approvedBy
            ),
            'approved_at' => $submission?->approved_at,
            'finalized_by' => $displayName(
                $submission?->finalizedBy
            ),
            'finalized_at' => $submission?->finalized_at,
            'return_remarks' => $submission?->return_remarks,
        ]);
    }
}

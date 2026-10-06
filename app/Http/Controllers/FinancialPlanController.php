<?php
namespace App\Http\Controllers;
use App\Models\Allocation;
use App\Models\FinancialPlan;
use App\Models\FinancialPlanSignatory;
use App\Models\FinancialPlanSubmission;
use App\Models\FinancialPlanTarget;
use App\Models\PrexcClassification;
use App\Models\StaffPersonnel;
use App\Traits\GenerateLogs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ExpenseItem;
use App\Models\ProgramHeader;
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
    private function validateAllocationExpenseCeiling(
        array $rows,
        Allocation $allocation
    ): void {
        $allocation->loadMissing([
            'fiscalYear',
            'level',
            'program',
        ]);
        $allocationMooe = (float) $allocation->mooe_budget;
        $allocationCapitalOutlay = (float) $allocation->co_budget;
        $programmedMooe = 0.0;
        $programmedCapitalOutlay = 0.0;
        $contractTotal = 0.0;
        foreach ($rows as $row) {
            if (($row['row_type'] ?? null) !== 'item') {
                continue;
            }
            $mooe = (float) ($row['mooe'] ?? 0);
            $capitalOutlay = (float) ($row['capital_outlay'] ?? 0);
            $contractAmount = array_key_exists('contract_amount', $row)
                && $row['contract_amount'] !== null
                ? (float) $row['contract_amount']
                : null;
            [$effectiveMooe, $effectiveCapitalOutlay] = $this->effectiveAmounts(
                $mooe,
                $capitalOutlay,
                $contractAmount
            );
            $programmedMooe += $effectiveMooe;
            $programmedCapitalOutlay += $effectiveCapitalOutlay;
            if ($contractAmount !== null) {
                $contractTotal += $contractAmount;
            }
        }
        $errors = [];
        if ($programmedMooe > $allocationMooe + 0.01) {
            $errors['financial_plan_allocation_mooe'] =
                'Total programmed MOOE of ₱' .
                number_format($programmedMooe, 2) .
                ' exceeds the selected Allocation MOOE Budget of ₱' .
                number_format($allocationMooe, 2) .
                ' by ₱' .
                number_format($programmedMooe - $allocationMooe, 2) .
                '.';
        }
        if ($programmedCapitalOutlay > $allocationCapitalOutlay + 0.01) {
            $errors['financial_plan_allocation_capital_outlay'] =
                'Total programmed CO of ₱' .
                number_format($programmedCapitalOutlay, 2) .
                ' exceeds the selected Allocation CO Budget of ₱' .
                number_format($allocationCapitalOutlay, 2) .
                ' by ₱' .
                number_format($programmedCapitalOutlay - $allocationCapitalOutlay, 2) .
                '.';
        }
        if ($contractTotal > ($allocationMooe + $allocationCapitalOutlay) + 0.01) {
            $errors['financial_plan_allocation_contract'] =
                'Total Contract Amount of ₱' .
                number_format($contractTotal, 2) .
                ' exceeds the selected Allocation Overall Budget of ₱' .
                number_format($allocationMooe + $allocationCapitalOutlay, 2) .
                ' by ₱' .
                number_format(
                    $contractTotal - ($allocationMooe + $allocationCapitalOutlay),
                    2
                ) .
                '.';
        }
        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
    private function validateAllocationAvailableBudget(
        array $rows,
        Allocation $allocation,
        int $fiscalYear,
        string $officeName
    ): void {
        $allocationMooe = (float) $allocation->mooe_budget;
        $allocationCapitalOutlay = (float) $allocation->co_budget;
        $otherMooe = 0.0;
        $otherCapitalOutlay = 0.0;
        $otherContractTotal = 0.0;
        $otherPlans = FinancialPlan::query()
            ->where('allocation_id', (int) $allocation->id)
            ->where(function ($query) use ($fiscalYear, $officeName) {
                $query->where('fiscal_year', '!=', $fiscalYear)
                    ->orWhere('office_name', '!=', $officeName);
            })
            ->get(['mooe', 'capital_outlay', 'contract_amount']);
        foreach ($otherPlans as $plan) {
            [$effectiveMooe, $effectiveCapitalOutlay] = $this->effectiveAmounts(
                (float) $plan->mooe,
                (float) $plan->capital_outlay,
                $plan->contract_amount
            );
            $otherMooe += $effectiveMooe;
            $otherCapitalOutlay += $effectiveCapitalOutlay;
            if ($plan->contract_amount !== null) {
                $otherContractTotal += (float) $plan->contract_amount;
            }
        }
        $currentMooe = 0.0;
        $currentCapitalOutlay = 0.0;
        $currentContractTotal = 0.0;
        foreach ($rows as $row) {
            if (($row['row_type'] ?? null) !== 'item') {
                continue;
            }
            [$effectiveMooe, $effectiveCapitalOutlay] = $this->effectiveAmounts(
                (float) ($row['mooe'] ?? 0),
                (float) ($row['capital_outlay'] ?? 0),
                array_key_exists('contract_amount', $row) && $row['contract_amount'] !== null
                    ? (float) $row['contract_amount']
                    : null
            );
            $currentMooe += $effectiveMooe;
            $currentCapitalOutlay += $effectiveCapitalOutlay;
            if (array_key_exists('contract_amount', $row) && $row['contract_amount'] !== null) {
                $currentContractTotal += (float) $row['contract_amount'];
            }
        }
        $availableMooe = max(0, $allocationMooe - $otherMooe);
        $availableCapitalOutlay = max(0, $allocationCapitalOutlay - $otherCapitalOutlay);
        $availableOverall = max(0, ($allocationMooe + $allocationCapitalOutlay) - ($otherMooe + $otherCapitalOutlay));
        $errors = [];
        if ($currentMooe > $availableMooe + 0.01) {
            $errors['financial_plan_allocation_mooe_available'] =
                'The current Financial Plan uses ₱' . number_format($currentMooe, 2) .
                ' MOOE, but only ₱' . number_format($availableMooe, 2) .
                ' remains available from the selected Allocation after other Financial Plans are considered.';
        }
        if ($currentCapitalOutlay > $availableCapitalOutlay + 0.01) {
            $errors['financial_plan_allocation_capital_outlay_available'] =
                'The current Financial Plan uses ₱' . number_format($currentCapitalOutlay, 2) .
                ' Capital Outlay, but only ₱' . number_format($availableCapitalOutlay, 2) .
                ' remains available from the selected Allocation after other Financial Plans are considered.';
        }
        if (($currentMooe + $currentCapitalOutlay) > $availableOverall + 0.01) {
            $errors['financial_plan_allocation_available'] =
                'The current Financial Plan uses ₱' . number_format($currentMooe + $currentCapitalOutlay, 2) .
                ', but only ₱' . number_format($availableOverall, 2) .
                ' remains available from the selected Allocation after other Financial Plans are considered.';
        }
        if (($otherContractTotal + $currentContractTotal) > ($allocationMooe + $allocationCapitalOutlay) + 0.01) {
            $errors['financial_plan_allocation_contract_available'] =
                'Combined Contract Amount of ₱' . number_format($otherContractTotal + $currentContractTotal, 2) .
                ' across Financial Plans exceeds the selected Allocation Overall Budget of ₱' .
                number_format($allocationMooe + $allocationCapitalOutlay, 2) . '.';
        }
        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
    private function buildProgramAllocationContext(int $fiscalYear, int $levelId, ?int $staffId, string $officeName = ''): array
    {
        $allocationQuery = Allocation::query()
            ->with(['fiscalYear', 'level', 'program', 'expenses.expenseType'])
            ->where('level_id', $levelId)
            ->whereHas('fiscalYear', function ($query) use ($fiscalYear) {
                $query->where('year', $fiscalYear);
            });
        if ($staffId !== null) {
            $allocationQuery->where('staff_id', $staffId);
        }

        $allocations = $allocationQuery->get()->keyBy('program_id');
        $allocationIds = $allocations
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $otherPlansByAllocation = collect();
        if ($allocationIds->isNotEmpty()) {
            $otherPlansByAllocation = FinancialPlan::query()
                ->whereIn('allocation_id', $allocationIds->all())
                ->where(function ($query) use ($fiscalYear, $officeName) {
                    $query->where('fiscal_year', '!=', $fiscalYear)
                        ->orWhere('office_name', '!=', $officeName);
                })
                ->get([
                    'allocation_id',
                    'mooe',
                    'capital_outlay',
                    'contract_amount',
                ])
                ->groupBy('allocation_id');
        }

        $context = [];
        foreach ($allocations as $programId => $allocation) {
            $otherMooe = 0.0;
            $otherCo = 0.0;
            $otherContract = 0.0;

            foreach ($otherPlansByAllocation->get((int) $allocation->id, collect()) as $plan) {
                [$effectiveMooe, $effectiveCo] = $this->effectiveAmounts(
                    (float) $plan->mooe,
                    (float) $plan->capital_outlay,
                    $plan->contract_amount
                );
                $otherMooe += $effectiveMooe;
                $otherCo += $effectiveCo;
                if ($plan->contract_amount !== null) {
                    $otherContract += (float) $plan->contract_amount;
                }
            }

            $context[(int) $programId] = [
                'allocation_id' => (int) $allocation->id,
                'program_id' => (int) $programId,
                'program_name' => (string) ($allocation->program?->program ?? ''),
                'level_id' => (int) $allocation->level_id,
                'level_code' => (string) ($allocation->level?->level_code ?? ''),
                'level_description' => (string) ($allocation->level?->level_description ?? ''),
                'mooe_budget' => (float) $allocation->mooe_budget,
                'co_budget' => (float) $allocation->co_budget,
                'total_budget' => (float) $allocation->mooe_budget + (float) $allocation->co_budget,
                'other_mooe' => $otherMooe,
                'other_co' => $otherCo,
                'other_contract' => $otherContract,
                'available_mooe' => max(0, (float) $allocation->mooe_budget - $otherMooe),
                'available_co' => max(0, (float) $allocation->co_budget - $otherCo),
                'available_total' => max(0, (float) $allocation->mooe_budget + (float) $allocation->co_budget - $otherMooe - $otherCo),
                'expenses' => collect($allocation->expenses ?? [])->map(fn ($expense) => [
                    'allocation_expense_id' => (int) $expense->id,
                    'expense_id' => (int) $expense->expense_id,
                    'type' => (string) ($expense->expenseType?->type ?? ''),
                    'name' => (string) ($expense->expenseType?->expense_description ?? ''),
                ])->filter(fn ($expense) => trim($expense['name']) !== '')->values()->all(),
            ];
        }

        return $context;
    }
    private function cachedProgramClassificationTree()
    {
        return Cache::remember(
            'financial-plans:program-classification-tree:v1',
            now()->addMinutes(10),
            function () {
                return ProgramHeader::query()
                    ->with([
                        'subHeaders' => fn ($query) => $query->orderBy('id'),
                        'subHeaders.programs' => fn ($query) => $query->orderBy('id'),
                        'subHeaders.programs.expenditures' => fn ($query) => $query->orderBy('id'),
                    ])
                    ->orderBy('id')
                    ->get()
                    ->map(function ($header) {
                        return [
                            'id' => (int) $header->id,
                            'header' => $header->header,
                            'sub_headers' => $header->subHeaders->map(function ($subHeader) {
                                return [
                                    'id' => (int) $subHeader->id,
                                    'header_id' => (int) $subHeader->header_id,
                                    'sub_header' => $subHeader->sub_header,
                                    'sub_code' => $subHeader->sub_code,
                                    'programs' => $subHeader->programs->map(function ($program) {
                                        return [
                                            'id' => (int) $program->id,
                                            'sub_header_id' => (int) $program->sub_header_id,
                                            'program' => $program->program,
                                            'expenditures' => $program->expenditures->map(function ($expenditure) {
                                                return [
                                                    'id' => (int) $expenditure->id,
                                                    'program_id' => (int) $expenditure->program_id,
                                                    'expenditure' => $expenditure->expenditure,
                                                    'prexc' => $expenditure->prexc,
                                                    'is_ops' => (bool) $expenditure->is_ops,
                                                ];
                                            })->values(),
                                        ];
                                    })->values(),
                                ];
                            })->values(),
                        ];
                    })
                    ->values();
            }
        );
    }

    private function buildPrexcProgramMap(): array
    {
        return $this->cachedProgramClassificationTree()
            ->flatMap(function ($header) {
                return collect($header['sub_headers'] ?? [])->flatMap(function ($subHeader) {
                    return collect($subHeader['programs'] ?? [])->flatMap(function ($program) {
                        return collect($program['expenditures'] ?? [])->mapWithKeys(function ($expenditure) use ($program) {
                            $prexc = trim((string) ($expenditure['prexc'] ?? ''));

                            return [$prexc => [
                                'program_id' => (int) ($program['id'] ?? 0),
                                'program_name' => (string) ($program['program'] ?? ''),
                            ]];
                        });
                    });
                });
            })
            ->filter(fn ($value, $key) => $key !== '')
            ->all();
    }
    private function validateProgramAllocationRows(
        array $rows,
        array $programAllocations,
        bool $requireComplete = false
    ): array {
        $grouped = [];
        $errors = [];
        foreach ($rows as $index => $row) {
            if (($row['row_type'] ?? null) !== 'item') {
                continue;
            }
            $prexc = trim((string) ($row['prexc_code'] ?? ''));
            if ($prexc === '') {
                if ($requireComplete) {
                    $errors["rows.{$index}.prexc_code"] = 'A PREXC Code is required so the system can identify the Program Allocation.';
                }
                continue;
            }
            $programId = (int) ($row['_program_id'] ?? 0);
            if ($programId <= 0) {
                if ($requireComplete) {
                    $errors["rows.{$index}.program_classification"] = 'The selected Program Classification is not linked to a Program Allocation for this Fiscal Year and Level.';
                }
                continue;
            }
            $allocation = $programAllocations[$programId] ?? null;
            if (! $allocation) {
                $errors["rows.{$index}.program_classification"] = 'No Allocation Management budget exists for this Program under the selected Fiscal Year and Level.';
                continue;
            }
            $expenseItem = trim((string) ($row['expense_item'] ?? ''));
            if ($expenseItem !== '') {
                $configuredExpense = collect($allocation['expenses'] ?? [])->first(function ($expense) use ($expenseItem) {
                    return trim((string) ($expense['name'] ?? '')) === $expenseItem;
                });
                if (! $configuredExpense) {
                    $errors["rows.{$index}.expense_item"] = 'The selected Expense Item is not configured for the Allocation of this Program.';
                } else {
                    $expenseType = strtoupper(trim((string) ($configuredExpense['type'] ?? '')));
                    $mooe = (float) ($row['mooe'] ?? 0);
                    $capitalOutlay = (float) ($row['capital_outlay'] ?? 0);
                    if (in_array($expenseType, ['CO', 'CAPITAL OUTLAY', 'CAPITAL_OUTLAY', 'CAPITAL-OUTLAY'], true) && $mooe > 0.01) {
                        $errors["rows.{$index}.mooe"] = 'CO Expense Items must be budgeted in Capital Outlay, not MOOE.';
                    }
                    if ($expenseType === 'MOOE' && $capitalOutlay > 0.01) {
                        $errors["rows.{$index}.capital_outlay"] = 'MOOE Expense Items must be budgeted in MOOE, not Capital Outlay.';
                    }
                }
            } elseif ($requireComplete) {
                $errors["rows.{$index}.expense_item"] = 'Expense Item is required.';
            }
            $grouped[$programId][] = $row;
        }
        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
        return $grouped;
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
    private function validatePrexcRows(
        array $rows,
        int $fiscalYear,
        string $officeName,
        int $staffId
    ): void
    {
        $rowIds = collect($rows)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $existingRows = collect();
        if ($rowIds->isNotEmpty()) {
            $existingQuery = $this->applyPlanIdentityScope(
                FinancialPlan::query(),
                $fiscalYear,
                $officeName,
                $staffId
            )->whereIn('id', $rowIds);
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
        $items = $itemsQuery->orderBy('sort_order')->get([
            'id', 'allocation_id', 'program_classification', 'prexc_code', 'staff_unit_project',
            'specific_activity', 'expense_item', 'assigned_personnel', 'mooe', 'capital_outlay',
            'contract_amount', 'sort_order',
        ]);
        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['financial_plan' => 'At least one Budget Line is required before submission.']);
        }
        $firstAllocation = $items->firstWhere('allocation_id', '!=', null)?->allocation;
        $levelId = $firstAllocation?->level_id;
        if ($levelId === null) {
            throw ValidationException::withMessages(['financial_plan_level' => 'Select a Financial Plan Level before submission.']);
        }
        $prexcProgramMap = $this->buildPrexcProgramMap();
        $programAllocations = $this->buildProgramAllocationContext($fiscalYear, (int) $levelId, $staffId, $officeName);
        $errors = [];
        $grouped = [];
        foreach ($items as $item) {
            $label = trim((string) $item->specific_activity) ?: trim((string) $item->program_classification) ?: "Budget line #{$item->id}";
            $classification = trim((string) $item->program_classification);
            $prexcCode = trim((string) $item->prexc_code);
            $staffUnitProject = trim((string) $item->staff_unit_project);
            $specificActivity = trim((string) $item->specific_activity);
            $expenseItem = trim((string) $item->expense_item);
            $assignedPersonnel = trim((string) $item->assigned_personnel);
            $mooe = (float) $item->mooe;
            $capitalOutlay = (float) $item->capital_outlay;
            $originalBudget = $mooe + $capitalOutlay;
            $contractAmount = $item->contract_amount !== null ? (float) $item->contract_amount : null;
            if ($classification === '') $errors["financial_plan_row_{$item->id}_program_classification"] = "{$label}: Program Classification is required.";
            if ($prexcCode === '') $errors["financial_plan_row_{$item->id}_prexc_code"] = "{$label}: PREXC Code is required.";
            if ($staffUnitProject === '') $errors["financial_plan_row_{$item->id}_staff_unit_project"] = "{$label}: Staff/Unit is required.";
            if ($specificActivity === '') $errors["financial_plan_row_{$item->id}_specific_activity"] = "{$label}: Specific Activity is required.";
            if ($expenseItem === '') $errors["financial_plan_row_{$item->id}_expense_item"] = "{$label}: Expense Item is required.";
            if ($assignedPersonnel === '') $errors["financial_plan_row_{$item->id}_assigned_personnel"] = "{$label}: Assigned Personnel is required.";
            if ($originalBudget <= 0) $errors["financial_plan_row_{$item->id}_budget"] = "{$label}: Enter an MOOE or Capital Outlay amount greater than zero.";
            if ($contractAmount !== null && $contractAmount > $originalBudget) $errors["financial_plan_row_{$item->id}_contract_amount"] = "{$label}: Contract Amount cannot be greater than the original MOOE + Capital Outlay budget.";
            $programId = (int) ($prexcProgramMap[$prexcCode]['program_id'] ?? 0);
            $allocation = $programId > 0 ? ($programAllocations[$programId] ?? null) : null;
            if (! $allocation) {
                $errors["financial_plan_row_{$item->id}_allocation"] = "{$label}: No Allocation Management budget exists for this Program under the selected Fiscal Year and Level.";
            } else {
                if ((int) $item->allocation_id !== (int) $allocation['allocation_id']) {
                    $errors["financial_plan_row_{$item->id}_allocation"] = "{$label}: The saved Program Allocation no longer matches the current Fiscal Year/Level mapping.";
                }
                $configuredExpense = collect($allocation['expenses'])->first(function ($expense) use ($expenseItem) {
                    return trim((string) ($expense['name'] ?? '')) === $expenseItem;
                });
                if (! $configuredExpense) {
                    $errors["financial_plan_row_{$item->id}_expense_item"] = "{$label}: Expense Item is not configured for this Program's Allocation.";
                } else {
                    $expenseType = strtoupper(trim((string) ($configuredExpense['type'] ?? '')));
                    if (in_array($expenseType, ['CO', 'CAPITAL OUTLAY', 'CAPITAL_OUTLAY', 'CAPITAL-OUTLAY'], true) && $mooe > 0.01) {
                        $errors["financial_plan_row_{$item->id}_mooe"] = "{$label}: CO Expense Items must be budgeted in Capital Outlay, not MOOE.";
                    }
                    if ($expenseType === 'MOOE' && $capitalOutlay > 0.01) {
                        $errors["financial_plan_row_{$item->id}_capital_outlay"] = "{$label}: MOOE Expense Items must be budgeted in MOOE, not Capital Outlay.";
                    }
                }
                $grouped[$programId][] = [
                    'row_type' => 'item',
                    'mooe' => $mooe,
                    'capital_outlay' => $capitalOutlay,
                    'contract_amount' => $contractAmount,
                ];
            }
            [$effectiveMooe, $effectiveCo] = $this->effectiveAmounts($mooe, $capitalOutlay, $contractAmount);
            $targetTotal = (float) $item->targets->sum('amount');
            if (abs($targetTotal - ($effectiveMooe + $effectiveCo)) > 0.01) {
                $errors["financial_plan_row_{$item->id}_financial_target"] = "{$label}: Monthly Financial Target total must equal the Effective Budget of " . number_format($effectiveMooe + $effectiveCo, 2) . '.';
            }
        }
        $validationAllocationIds = collect($grouped)
            ->keys()
            ->map(function ($programId) use ($programAllocations) {
                return (int) (
                    $programAllocations[(int) $programId]['allocation_id']
                    ?? 0
                );
            })
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $validationAllocations = collect();
        if ($validationAllocationIds->isNotEmpty()) {
            $validationAllocations = Allocation::query()
                ->with('fiscalYear')
                ->whereIn('id', $validationAllocationIds->all())
                ->get()
                ->keyBy('id');
        }

        foreach ($grouped as $programId => $programRows) {
            $allocation = $programAllocations[(int) $programId] ?? null;

            if (! $allocation) {
                continue;
            }

            $validationAllocation = $validationAllocations->get(
                (int) $allocation['allocation_id']
            );

            if (! $validationAllocation) {
                continue;
            }

            $this->validateAllocationAvailableBudget(
                $programRows,
                $validationAllocation,
                $fiscalYear,
                $officeName
            );
        }
        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
    private function normalizePlanOfficeName(string $officeName): string
    {
        return trim((string) preg_replace('/\\s+/u', ' ', $officeName));
    }

    private function resolvePlanStaffId(
        int $fiscalYear,
        string $officeName,
        ?int $requestedStaffId = null,
        bool $required = false
    ): ?int {
        $user = auth()->user();
        $officeName = $this->normalizePlanOfficeName($officeName);

        if (! $user->isAdministrator()) {
            abort_unless($user->staff_id !== null, 403);

            if ($requestedStaffId !== null) {
                abort_unless(
                    (int) $requestedStaffId === (int) $user->staff_id,
                    403
                );
            }

            return (int) $user->staff_id;
        }

        if ($requestedStaffId !== null) {
            return (int) $requestedStaffId;
        }

        if ($officeName === '') {
            if ($required) {
                throw ValidationException::withMessages([
                    'staff_id' => 'Please select a Staff/Office.',
                ]);
            }

            return null;
        }

        $staffIds = FinancialPlan::query()
            ->where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName)
            ->whereNotNull('staff_id')
            ->distinct()
            ->pluck('staff_id')
            ->merge(
                FinancialPlanSubmission::query()
                    ->where('fiscal_year', $fiscalYear)
                    ->where('office_name', $officeName)
                    ->whereNotNull('staff_id')
                    ->distinct()
                    ->pluck('staff_id')
            )
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($staffIds->count() === 1) {
            return (int) $staffIds->first();
        }

        if ($staffIds->count() > 1) {
            throw ValidationException::withMessages([
                'staff_id' => 'This Office Name exists under more than one Staff/Office. Please select the exact Staff/Office.',
            ]);
        }

        if ($required) {
            throw ValidationException::withMessages([
                'staff_id' => 'The Staff/Office could not be determined. Please select the exact Staff/Office.',
            ]);
        }

        return null;
    }

    private function applyPlanIdentityScope(
        $query,
        int $fiscalYear,
        string $officeName,
        int $staffId
    ) {
        return $query
            ->where('fiscal_year', $fiscalYear)
            ->where('staff_id', $staffId)
            ->where(
                'office_name',
                $this->normalizePlanOfficeName($officeName)
            );
    }

    // Get submission record
    private function getSubmission(int $fiscalYear, string $officeName, ?int $staffId = null): ?FinancialPlanSubmission
    {
        $officeName = $this->normalizePlanOfficeName($officeName);
        $staffId = $this->resolvePlanStaffId(
            $fiscalYear,
            $officeName,
            $staffId,
            false
        );

        if ($staffId === null) {
            return null;
        }

        return $this->applyPlanIdentityScope(
            FinancialPlanSubmission::query(),
            $fiscalYear,
            $officeName,
            $staffId
        )->first();
    }

    private function planIsLocked(
        int $fiscalYear,
        string $officeName,
        ?int $staffId = null
    ): bool {
        return $this->getSubmission(
            $fiscalYear,
            $officeName,
            $staffId
        )?->isLocked() ?? false;
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
    private function findPlanForAccess(
        int $fiscalYear,
        string $officeName,
        ?int $staffId = null
    ): ?FinancialPlan {
        $officeName = $this->normalizePlanOfficeName($officeName);
        $staffId = $this->resolvePlanStaffId(
            $fiscalYear,
            $officeName,
            $staffId,
            false
        );

        if ($staffId === null || $officeName === '') {
            return null;
        }

        return $this->applyPlanIdentityScope(
            FinancialPlan::query(),
            $fiscalYear,
            $officeName,
            $staffId
        )->first();
    }

    private function authorizePlanWrite(
        int $fiscalYear,
        string $officeName,
        ?int $staffId = null
    ): ?FinancialPlan {
        $plan = $this->findPlanForAccess(
            $fiscalYear,
            $officeName,
            $staffId
        );
        if ($plan) {
            $this->authorize('update', $plan);
            return $plan;
        }

        $this->resolvePlanStaffId(
            $fiscalYear,
            $officeName,
            $staffId,
            true
        );
        $this->authorize('create', FinancialPlan::class);

        return null;
    }
    // Authorize reading an existing plan
    private function authorizePlanRead(
        int $fiscalYear,
        string $officeName,
        ?int $staffId = null
    ): ?FinancialPlan {
        $plan = $this->findPlanForAccess(
            $fiscalYear,
            $officeName,
            $staffId
        );
        if ($plan) {
            $this->authorize('view', $plan);
        }
        return $plan;
    }
    // Restrict queries to the current user's staff unless administrator
    private function applyStaffScope($query)
    {
        $user = auth()->user();

        if ($user->isAdministrator()) {
            return $query;
        }

        if ($user->staff_id === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(
            'staff_id',
            (int) $user->staff_id
        );
    }
    private function authorizeAllocationAccess(Allocation $allocation): void
    {
        if (auth()->user()->isAdministrator()) {
            return;
        }
        $staffId = auth()->user()->staff_id;
        abort_unless(
            $staffId !== null &&
            (int) $allocation->staff_id === (int) $staffId,
            403
        );
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

        $user = auth()->user();
        $fiscalYearFilter = $request->filled('fiscal_year')
            ? (int) $request->input('fiscal_year')
            : null;
        $officeFilter = $this->normalizePlanOfficeName(
            $request->string('office_name')->toString()
        );
        $statusFilter = $request->string('status')->toString();
        $allowedStatuses = ['draft', 'submitted', 'returned', 'approved', 'finalized'];

        if (! in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = '';
        }

        $plansQuery = FinancialPlan::query()
            ->from('financial_plans as fp')
            ->leftJoin('financial_plan_submissions as fps', function ($join) {
                $join->on('fps.fiscal_year', '=', 'fp.fiscal_year')
                    ->on('fps.office_name', '=', 'fp.office_name')
                    ->whereRaw('fps.staff_id <=> fp.staff_id');
            })
            ->where('fp.row_type', 'item');

        if (! $user->isAdministrator()) {
            if ($user->staff_id === null) {
                $plansQuery->whereRaw('1 = 0');
            } else {
                $plansQuery->where('fp.staff_id', (int) $user->staff_id);
            }
        }

        if ($fiscalYearFilter !== null) {
            $plansQuery->where('fp.fiscal_year', $fiscalYearFilter);
        }

        if ($officeFilter !== '') {
            $plansQuery->where('fp.office_name', $officeFilter);
        }

        if ($statusFilter === 'finalized') {
            $plansQuery->where('fps.finalized', 'yes');
        } elseif ($statusFilter !== '') {
            $plansQuery
                ->whereRaw("COALESCE(fps.finalized, 'no') <> 'yes'")
                ->whereRaw("COALESCE(fps.status, 'draft') = ?", [$statusFilter]);
        }

        $plans = $plansQuery
            ->select([
                'fp.fiscal_year',
                'fp.staff_id',
                'fp.office_name',
            ])
            ->selectRaw('COUNT(*) AS row_count')
            ->selectRaw("SUM(CASE
                WHEN fp.contract_amount IS NULL
                    THEN COALESCE(fp.mooe, 0) + COALESCE(fp.capital_outlay, 0)
                WHEN (COALESCE(fp.mooe, 0) + COALESCE(fp.capital_outlay, 0)) > 0
                    THEN fp.contract_amount
                ELSE 0
            END) AS budget_sum")
            ->selectRaw("COALESCE(MAX(fps.status), 'draft') AS status")
            ->selectRaw("COALESCE(MAX(fps.finalized), 'no') AS finalized")
            ->groupBy('fp.fiscal_year', 'fp.staff_id', 'fp.office_name')
            ->orderByDesc('fp.fiscal_year')
            ->orderBy('fp.office_name')
            ->paginate(20)
            ->withQueryString();

        $optionsQuery = $this->applyStaffScope(
            FinancialPlan::query()->where('row_type', 'item')
        );

        $offices = (clone $optionsQuery)
            ->whereNotNull('office_name')
            ->where('office_name', '!=', '')
            ->distinct()
            ->orderBy('office_name')
            ->pluck('office_name');

        $fiscalYears = (clone $optionsQuery)
            ->select('fiscal_year')
            ->distinct()
            ->orderByDesc('fiscal_year')
            ->pluck('fiscal_year');

        return view('financial-plans.plans', [
            'plans' => $plans,
            'offices' => $offices,
            'fiscalYears' => $fiscalYears,
            'selectedFiscalYear' => $fiscalYearFilter,
            'selectedOffice' => $officeFilter,
            'selectedStatus' => $statusFilter,
        ]);
    }
    // Index and builder pages
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $fiscalYear = (int) $request->input(
            'fiscal_year',
            now()->year
        );
        $requestedStaffId = $request->filled('staff_id')
            ? (int) $request->input('staff_id')
            : null;
        $officeName = $this->normalizePlanOfficeName(
            $request->string('office_name')->toString()
        );

        $officeQuery = $this->applyStaffScope(
            FinancialPlan::query()
        );

        if ($requestedStaffId !== null) {
            if (! auth()->user()->isAdministrator()) {
                abort_unless(
                    auth()->user()->staff_id !== null
                    && (int) auth()->user()->staff_id === $requestedStaffId,
                    403
                );
            }

            $officeQuery->where(
                'staff_id',
                $requestedStaffId
            );
        }

        $offices = $officeQuery
            ->where('fiscal_year', $fiscalYear)
            ->whereNotNull('office_name')
            ->where('office_name', '!=', '')
            ->distinct()
            ->orderBy('office_name')
            ->pluck('office_name');

        if ($officeName === '' && $offices->isNotEmpty()) {
            $officeName = $this->normalizePlanOfficeName(
                (string) $offices->first()
            );
        }

        $staffId = $this->resolvePlanStaffId(
            $fiscalYear,
            $officeName,
            $requestedStaffId,
            false
        );

        $allocationSummary = [];

        if ($staffId !== null) {
            $allocations = Allocation::query()
                ->with([
                    'fiscalYear',
                    'level',
                    'program',
                ])
                ->where('staff_id', (int) $staffId)
                ->whereHas(
                    'fiscalYear',
                    function ($query) use ($fiscalYear) {
                        $query->where(
                            'year',
                            $fiscalYear
                        );
                    }
                )
                ->orderBy('program_id')
                ->orderBy('id')
                ->get();

            $allocationIds = $allocations
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();

            $allTotals = [];
            $currentOfficeTotals = [];

            if ($allocationIds->isNotEmpty()) {
                $allocationPlans = FinancialPlan::query()
                    ->whereIn(
                        'allocation_id',
                        $allocationIds->all()
                    )
                    ->get([
                        'allocation_id',
                        'fiscal_year',
                        'staff_id',
                        'office_name',
                        'mooe',
                        'capital_outlay',
                        'contract_amount',
                    ]);

                foreach ($allocationPlans as $plan) {
                    $allocationId = (int) $plan->allocation_id;

                    [$effectiveMooe, $effectiveCo] =
                        $this->effectiveAmounts(
                            (float) $plan->mooe,
                            (float) $plan->capital_outlay,
                            $plan->contract_amount
                        );

                    if (! isset($allTotals[$allocationId])) {
                        $allTotals[$allocationId] = [
                            'mooe' => 0.0,
                            'capital_outlay' => 0.0,
                        ];
                    }

                    $allTotals[$allocationId]['mooe']
                        += $effectiveMooe;
                    $allTotals[$allocationId]['capital_outlay']
                        += $effectiveCo;

                    $isCurrentPlan =
                        (int) $plan->fiscal_year === $fiscalYear
                        && (int) $plan->staff_id === (int) $staffId
                        && $this->normalizePlanOfficeName(
                            (string) $plan->office_name
                        ) === $officeName;

                    if (! $isCurrentPlan) {
                        continue;
                    }

                    if (! isset($currentOfficeTotals[$allocationId])) {
                        $currentOfficeTotals[$allocationId] = [
                            'mooe' => 0.0,
                            'capital_outlay' => 0.0,
                        ];
                    }

                    $currentOfficeTotals[$allocationId]['mooe']
                        += $effectiveMooe;
                    $currentOfficeTotals[$allocationId]['capital_outlay']
                        += $effectiveCo;
                }
            }

            foreach ($allocations as $allocation) {
                $allocationId = (int) $allocation->id;

                $totalMooe = (float) (
                    $allTotals[$allocationId]['mooe']
                    ?? 0
                );
                $totalCo = (float) (
                    $allTotals[$allocationId]['capital_outlay']
                    ?? 0
                );
                $thisMooe = (float) (
                    $currentOfficeTotals[$allocationId]['mooe']
                    ?? 0
                );
                $thisCo = (float) (
                    $currentOfficeTotals[$allocationId]['capital_outlay']
                    ?? 0
                );

                $allocationSummary[] = [
                    'id' => (int) $allocation->id,
                    'program_id' => (int) (
                        $allocation->program_id
                        ?? 0
                    ),
                    'program' => (string) (
                        $allocation->program?->program
                        ?? 'Unassigned Program'
                    ),
                    'level_code' => (string) (
                        $allocation->level?->level_code
                        ?? ''
                    ),
                    'level_description' => (string) (
                        $allocation->level?->level_description
                        ?? ''
                    ),
                    'mooe_budget' => (float) $allocation->mooe_budget,
                    'co_budget' => (float) $allocation->co_budget,
                    'total_budget' =>
                        (float) $allocation->mooe_budget
                        + (float) $allocation->co_budget,
                    'other_mooe' => max(
                        0,
                        $totalMooe - $thisMooe
                    ),
                    'other_capital_outlay' => max(
                        0,
                        $totalCo - $thisCo
                    ),
                    'this_mooe' => $thisMooe,
                    'this_capital_outlay' => $thisCo,
                ];
            }
        }

        return view('financial-plans.index', [
            'fiscalYear' => $fiscalYear,
            'staffId' => $staffId,
            'officeName' => $officeName,
            'offices' => $offices,
            'months' => self::MONTHS,
            'allocationSummary' => $allocationSummary,
        ]);
    }

    public function builder(Request $request): View
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();
        $isAdmin = auth()->user()->isAdministrator();
        $accessPlan = null;
        if ($officeName !== '') {
            $accessPlan = $this->findPlanForAccess(
                $fiscalYear,
                $officeName,
                $request->filled('staff_id')
                    ? (int) $request->input('staff_id')
                    : null
            );
            if ($accessPlan) {
                $this->authorize('view', $accessPlan);
            } else {
                $this->authorize('create', FinancialPlan::class);
            }
        } else {
            $this->authorize('create', FinancialPlan::class);
        }
        $personnelStaffId = $accessPlan?->staff_id
            ?? $this->resolvePlanStaffId(
                $fiscalYear,
                $officeName,
                $request->filled('staff_id')
                    ? (int) $request->input('staff_id')
                    : null,
                false
            );
        $personnelOptions = collect();
        if ($personnelStaffId !== null) {
            $personnelOptions = StaffPersonnel::query()
                ->active()
                ->where('staff_id', $personnelStaffId)
                ->orderBy('name')
                ->get(['id', 'name', 'position']);
        }
        $programClassificationTree = $this->cachedProgramClassificationTree();
        $levels = Cache::remember(
            'financial-plans:levels:v1',
            now()->addMinutes(10),
            function () {
                return DB::table('levels')
                    ->orderBy('level_code')
                    ->get([
                        'id',
                        'level_code',
                        'level_description',
                    ]);
            }
        );
        $selectedLevelId = null;
        if ($accessPlan) {
            $selectedLevelId = $accessPlan->allocation?->level_id;
            if ($selectedLevelId === null) {
                $selectedLevelId = FinancialPlan::query()
                    ->where('financial_plans.fiscal_year', $fiscalYear)
                    ->where('financial_plans.staff_id', (int) $personnelStaffId)
                    ->where(
                        'financial_plans.office_name',
                        $this->normalizePlanOfficeName($officeName)
                    )
                    ->whereNotNull('financial_plans.allocation_id')
                    ->join(
                        'allocations',
                        'allocations.id',
                        '=',
                        'financial_plans.allocation_id'
                    )
                    ->value('allocations.level_id');
            }
        }
        if ($selectedLevelId === null && $request->filled('level_id')) {
            $selectedLevelId = (int) $request->input('level_id');
        }
        $prexcProgramMap = $this->buildPrexcProgramMap();
        $programAllocations = $selectedLevelId
            ? $this->buildProgramAllocationContext($fiscalYear, (int) $selectedLevelId, $personnelStaffId, $officeName)
            : [];
        $staffOptions = collect();
        if ($isAdmin) {
            $staffOptions = DB::table('staffs')->orderBy('name')->get(['id', 'name', 'abbreviation']);
        }
        $planOptions = collect();
        if ($personnelStaffId !== null) {
            $planOptions = FinancialPlan::query()
                ->where('fiscal_year', $fiscalYear)
                ->where('staff_id', (int) $personnelStaffId)
                ->whereNotNull('office_name')
                ->where('office_name', '!=', '')
                ->select('office_name')
                ->selectRaw('COUNT(*) as row_count')
                ->groupBy('office_name')
                ->orderBy('office_name')
                ->get();
        }
        return view('financial-plans.builder', [
            'fiscalYear' => $fiscalYear,
            'officeName' => $officeName,
            'months' => self::MONTHS,
            'isAdmin' => $isAdmin,
            'staffOptions' => $staffOptions,
            'planOptions' => $planOptions,
            'personnelStaffId' => $personnelStaffId,
            'staffId' => $personnelStaffId,
            'personnelOptions' => $personnelOptions,
            'programClassificationTree' => $programClassificationTree,
            'levels' => $levels,
            'selectedLevelId' => $selectedLevelId,
            'prexcProgramMap' => $prexcProgramMap,
            'programAllocations' => $programAllocations,
            'expenseItemOptions' => collect(),
            'allocations' => collect(),
            'allocationExpenseOptions' => collect(),
            'allocationProgrammedTotals' => [],
        ]);
    }
    // Data
    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();
        $requestedStaffId = $request->filled('staff_id')
            ? (int) $request->input('staff_id')
            : null;

        $officeName = $this->normalizePlanOfficeName($officeName);
        $query = FinancialPlan::query()
            ->with([
                'targets',
                'allocation.fiscalYear',
                'allocation.level',
                'allocation.program',
                'allocation.expenses.expenseType',
            ]);

        if ($officeName !== '') {
            $accessPlan = $this->authorizePlanRead(
                $fiscalYear,
                $officeName,
                $requestedStaffId
            );
            $resolvedStaffId = $accessPlan?->staff_id
                ?? $this->resolvePlanStaffId(
                    $fiscalYear,
                    $officeName,
                    $requestedStaffId,
                    false
                );

            if ($resolvedStaffId === null) {
                $rows = collect();
            } else {
                $rows = $this->applyPlanIdentityScope(
                    $query,
                    $fiscalYear,
                    $officeName,
                    (int) $resolvedStaffId
                )
                    ->orderBy('sort_order')
                    ->get();
            }
        } else {
            $query->where('fiscal_year', $fiscalYear);
            $this->applyStaffScope($query);
            $rows = $query
                ->orderBy('sort_order')
                ->get();
        }
        $allocationIds = $rows
            ->pluck('allocation_id')
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $allocationUsage = [];
        if ($allocationIds->isNotEmpty()) {
            $allAllocationPlans = FinancialPlan::query()
                ->whereIn('allocation_id', $allocationIds->all())
                ->get([
                    'id',
                    'allocation_id',
                    'fiscal_year',
                    'office_name',
                    'mooe',
                    'capital_outlay',
                    'contract_amount',
                ]);
            $allTotals = [];
            foreach ($allAllocationPlans as $plan) {
                $allocationId = (int) $plan->allocation_id;
                if (! isset($allTotals[$allocationId])) {
                    $allTotals[$allocationId] = [
                        'mooe' => 0.0,
                        'capital_outlay' => 0.0,
                    ];
                }
                [$effectiveMooe, $effectiveCapitalOutlay] = $this->effectiveAmounts(
                    (float) $plan->mooe,
                    (float) $plan->capital_outlay,
                    $plan->contract_amount
                );
                $allTotals[$allocationId]['mooe'] += $effectiveMooe;
                $allTotals[$allocationId]['capital_outlay'] += $effectiveCapitalOutlay;
            }
            $currentOfficeTotals = [];
            foreach ($rows as $plan) {
                if ($plan->allocation_id === null) {
                    continue;
                }
                $allocationId = (int) $plan->allocation_id;
                if (! isset($currentOfficeTotals[$allocationId])) {
                    $currentOfficeTotals[$allocationId] = [
                        'mooe' => 0.0,
                        'capital_outlay' => 0.0,
                    ];
                }
                [$effectiveMooe, $effectiveCapitalOutlay] = $this->effectiveAmounts(
                    (float) $plan->mooe,
                    (float) $plan->capital_outlay,
                    $plan->contract_amount
                );
                $currentOfficeTotals[$allocationId]['mooe'] += $effectiveMooe;
                $currentOfficeTotals[$allocationId]['capital_outlay'] += $effectiveCapitalOutlay;
            }
            foreach ($allocationIds as $allocationId) {
                $allocationId = (int) $allocationId;
                $totalMooe = (float) ($allTotals[$allocationId]['mooe'] ?? 0);
                $totalCapitalOutlay = (float) ($allTotals[$allocationId]['capital_outlay'] ?? 0);
                $officeMooe = (float) ($currentOfficeTotals[$allocationId]['mooe'] ?? 0);
                $officeCapitalOutlay = (float) ($currentOfficeTotals[$allocationId]['capital_outlay'] ?? 0);
                $allocationUsage[$allocationId] = [
                    'total_mooe' => $totalMooe,
                    'total_capital_outlay' => $totalCapitalOutlay,
                    'total' => $totalMooe + $totalCapitalOutlay,
                    'office_mooe' => $officeMooe,
                    'office_capital_outlay' => $officeCapitalOutlay,
                    'office_total' => $officeMooe + $officeCapitalOutlay,
                    'other_mooe' => max(0, $totalMooe - $officeMooe),
                    'other_capital_outlay' => max(0, $totalCapitalOutlay - $officeCapitalOutlay),
                    'other_total' => max(0, ($totalMooe + $totalCapitalOutlay) - ($officeMooe + $officeCapitalOutlay)),
                ];
            }
        }
        $allocationCatalog = [];
        $catalogStaffId = $rows->first()?->staff_id;
        if ($catalogStaffId === null && $officeName !== '') {
            $catalogStaffId = FinancialPlan::query()
                ->where('fiscal_year', $fiscalYear)
                ->where('office_name', $officeName)
                ->whereNotNull('staff_id')
                ->value('staff_id');
        }
        if ($catalogStaffId === null && auth()->user()->isAdministrator()) {
            $catalogStaffId = DB::table('staffs')
                ->whereRaw('LOWER(TRIM(name)) = LOWER(TRIM(?))', [$officeName])
                ->value('id');
        }
        if ($catalogStaffId !== null) {
            $catalogAllocations = Allocation::query()
                ->with(['fiscalYear', 'level', 'program'])
                ->where('staff_id', (int) $catalogStaffId)
                ->whereHas('fiscalYear', function ($query) use ($fiscalYear) {
                    $query->where('year', $fiscalYear);
                })
                ->orderBy('program_id')
                ->orderBy('id')
                ->get();

            $catalogAllocationIds = $catalogAllocations
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();

            $catalogPlansByAllocation = collect();
            if ($catalogAllocationIds->isNotEmpty()) {
                $catalogPlansByAllocation = FinancialPlan::query()
                    ->whereIn('allocation_id', $catalogAllocationIds->all())
                    ->get([
                        'allocation_id',
                        'fiscal_year',
                        'office_name',
                        'mooe',
                        'capital_outlay',
                        'contract_amount',
                    ])
                    ->groupBy('allocation_id');
            }

            foreach ($catalogAllocations as $allocation) {
                $otherMooe = 0.0;
                $otherCo = 0.0;
                $thisMooe = 0.0;
                $thisCo = 0.0;

                foreach ($catalogPlansByAllocation->get((int) $allocation->id, collect()) as $plan) {
                    [$effectiveMooe, $effectiveCo] = $this->effectiveAmounts(
                        (float) $plan->mooe,
                        (float) $plan->capital_outlay,
                        $plan->contract_amount
                    );
                    $isCurrentPlan = (int) $plan->fiscal_year === $fiscalYear
                        && (string) $plan->office_name === $officeName;
                    if ($isCurrentPlan) {
                        $thisMooe += $effectiveMooe;
                        $thisCo += $effectiveCo;
                    } else {
                        $otherMooe += $effectiveMooe;
                        $otherCo += $effectiveCo;
                    }
                }

                $allocationCatalog[] = [
                    'id' => (int) $allocation->id,
                    'program_id' => (int) ($allocation->program_id ?? 0),
                    'program' => (string) ($allocation->program?->program ?? 'Unassigned Program'),
                    'level_code' => (string) ($allocation->level?->level_code ?? ''),
                    'level_description' => (string) ($allocation->level?->level_description ?? ''),
                    'mooe_budget' => (float) $allocation->mooe_budget,
                    'co_budget' => (float) $allocation->co_budget,
                    'total_budget' => (float) $allocation->mooe_budget + (float) $allocation->co_budget,
                    'other_mooe' => $otherMooe,
                    'other_capital_outlay' => $otherCo,
                    'this_mooe' => $thisMooe,
                    'this_capital_outlay' => $thisCo,
                ];
            }
        }

        $firstRowId = $rows->first()?->id;
        return response()->json($rows->map(function (FinancialPlan $p) use ($firstRowId, $allocationCatalog) {
            [$effMooe, $effCapitalOutlay] = $this->effectiveAmounts(
                (float) $p->mooe,
                (float) $p->capital_outlay,
                $p->contract_amount
            );
            return [
                'id'                      => $p->id,
                'allocation_id'            => $p->allocation_id,
                'program_id'               => (int) ($p->allocation?->program_id ?? 0),
                'allocation_catalog'      => $p->id === $firstRowId ? $allocationCatalog : null,
                'allocation_usage'        => $p->allocation_id !== null
                    ? ($allocationUsage[(int) $p->allocation_id] ?? null)
                    : null,
                'allocation'               => $p->allocation ? [
                    'id' => (int) $p->allocation->id,
                    'fiscal_year' => (int) ($p->allocation->fiscalYear?->year ?? $p->fiscal_year),
                    'level_id' => (int) ($p->allocation->level_id ?? 0),
                    'level' => [
                        'code' => (string) ($p->allocation->level?->level_code ?? ''),
                        'description' => (string) ($p->allocation->level?->level_description ?? ''),
                    ],
                    'program' => $p->allocation->program ? [
                        'id' => (int) $p->allocation->program->id,
                        'name' => (string) ($p->allocation->program->program ?? ''),
                    ] : null,
                    'mooe_budget' => (float) $p->allocation->mooe_budget,
                    'co_budget' => (float) $p->allocation->co_budget,
                    'total_budget' => (float) $p->allocation->mooe_budget
                        + (float) $p->allocation->co_budget,
                    'expenses' => $p->allocation->expenses->map(fn ($expense) => [
                        'id' => (int) $expense->id,
                        'expense_id' => (int) $expense->expense_id,
                        'type' => (string) ($expense->expenseType?->type ?? ''),
                        'description' => (string) ($expense->expenseType?->expense_description ?? ''),
                        'cost' => (float) $expense->cost,
                    ])->values(),
                    'total' => (float) $p->allocation->expenses->sum('cost'),
                ] : null,
                'row_type'                => $p->row_type,
                'program_classification'  => $p->program_classification,
                'prexc_code'              => $p->prexc_code,
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

        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
            'staff_id' => ['nullable', 'integer', 'exists:staffs,id'],
        ]);

        $fiscalYear = (int) $validated['fiscal_year'];
        $officeName = trim((string) $validated['office_name']);
        $requestedStaffId = isset($validated['staff_id'])
            && $validated['staff_id'] !== null
                ? (int) $validated['staff_id']
                : null;

        $accessPlan = $this->authorizePlanRead(
            $fiscalYear,
            $officeName,
            $requestedStaffId
        );

        $staffId = $accessPlan?->staff_id ?? $requestedStaffId;

        if ($staffId === null && ! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;
        }

        $signatory = null;

        if ($staffId !== null) {
            $signatory = FinancialPlanSignatory::query()
                ->where('fiscal_year', $fiscalYear)
                ->where('staff_id', $staffId)
                ->where('office_name', $officeName)
                ->first();
        }

        return response()->json([
            'prepared_by' => $signatory->prepared_by ?? '',
            'prepared_by_position' => $signatory->prepared_by_position ?? '',
            'reviewed_by' => $signatory->reviewed_by ?? '',
            'reviewed_by_position' => $signatory->reviewed_by_position ?? '',
            'recommended_by' => $signatory->recommended_by ?? '',
            'recommended_by_position' => $signatory->recommended_by_position ?? '',
            'approved_by' => $signatory->approved_by ?? '',
            'approved_by_position' => $signatory->approved_by_position ?? '',
        ]);
    }

    public function saveSignatories(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
            'staff_id' => ['nullable', 'integer', 'exists:staffs,id'],
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
        $office = trim((string) $validated['office_name']);
        $requestedStaffId = isset($validated['staff_id'])
            && $validated['staff_id'] !== null
                ? (int) $validated['staff_id']
                : null;

        $accessPlan = $this->authorizePlanWrite(
            $year,
            $office,
            $requestedStaffId
        );

        $staffId = $accessPlan?->staff_id
            ?? $requestedStaffId
            ?? auth()->user()->staff_id;

        if ($staffId === null) {
            throw ValidationException::withMessages([
                'staff_id' => 'The Staff/Office could not be determined.',
            ]);
        }

        if ($this->planIsLocked($year, $office, (int) $staffId)) {
            return $this->lockedResponse();
        }

        $signatory = FinancialPlanSignatory::firstOrNew([
            'fiscal_year' => $year,
            'staff_id' => (int) $staffId,
            'office_name' => $office,
        ]);

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
        $request->merge(['rows' => $this->normalizeRows($request->input('rows', []))]);
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'staff_id' => ['nullable', 'integer', 'exists:staffs,id'],
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'office_name' => ['required', 'string', 'max:150'],
            'rows' => ['present', 'array'],
            'rows.*.id' => ['nullable', 'integer', 'exists:financial_plans,id'],
            'rows.*.program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'rows.*.allocation_id' => ['nullable', 'integer', 'exists:allocations,id'],
            'rows.*.row_type' => ['required', 'string', 'in:header,subheader,item'],
            'rows.*.program_classification' => ['nullable', 'string', 'max:500'],
            'rows.*.prexc_code' => ['nullable', 'string', 'max:50'],
            'rows.*.staff_unit_project' => ['nullable', 'string', 'max:150'],
            'rows.*.specific_activity' => ['nullable', 'string'],
            'rows.*.procurement_status' => ['nullable', 'string', 'max:150'],
            'rows.*.expense_item' => ['nullable', 'string', 'max:150'],
            'rows.*.assigned_personnel' => ['nullable', 'string', 'max:150'],
            'rows.*.mooe' => ['nullable', 'numeric', 'min:0'],
            'rows.*.capital_outlay' => ['nullable', 'numeric', 'min:0'],
            'rows.*.contract_amount' => ['nullable', 'numeric', 'min:0'],
            'rows.*.months' => ['nullable', 'array'],
            'rows.*.months.*' => ['nullable', 'numeric', 'min:0'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $this->normalizePlanOfficeName(
            (string) $validated['office_name']
        );
        $requestedStaffId = isset($validated['staff_id']) && $validated['staff_id'] !== null
            ? (int) $validated['staff_id']
            : null;
        if (! auth()->user()->isAdministrator()) {
            $requestedStaffId = auth()->user()->staff_id !== null ? (int) auth()->user()->staff_id : null;
        }
        $rows = $validated['rows'] ?? [];
        $levelId = (int) $validated['level_id'];
        $accessPlan = $this->authorizePlanWrite(
            $year,
            $office,
            $requestedStaffId
        );
        $planStaffId = $accessPlan?->staff_id
            ?? $this->resolvePlanStaffId(
                $year,
                $office,
                $requestedStaffId,
                true
            );
        $prexcProgramMap = $this->buildPrexcProgramMap();
        $submittedAllocationIds = collect($rows)
            ->filter(fn ($row) => ($row['row_type'] ?? null) === 'item')
            ->pluck('allocation_id')
            ->filter(fn ($id) => (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $submittedAllocations = $submittedAllocationIds->isNotEmpty()
            ? Allocation::query()
                ->with(['fiscalYear', 'program'])
                ->whereIn('id', $submittedAllocationIds->all())
                ->get()
                ->keyBy('id')
            : collect();
        foreach ($rows as $index => &$row) {
            if (($row['row_type'] ?? null) !== 'item') {
                $row['_program_id'] = null;
                continue;
            }
            $submittedProgramId = (int) ($row['program_id'] ?? 0);
            $submittedAllocationId = (int) ($row['allocation_id'] ?? 0);
            if ($submittedAllocationId > 0) {
                $submittedAllocation = $submittedAllocations->get(
                    $submittedAllocationId
                );
                if (! $submittedAllocation) {
                    throw ValidationException::withMessages([
                        "rows.{$index}.allocation_id" => 'The selected Allocation could not be found.'
                    ]);
                }
                if ((int) ($submittedAllocation->fiscalYear?->year ?? 0) !== $year) {
                    throw ValidationException::withMessages([
                        "rows.{$index}.allocation_id" => 'The selected Allocation does not belong to the selected Fiscal Year.'
                    ]);
                }
                if ((int) $submittedAllocation->level_id !== $levelId) {
                    throw ValidationException::withMessages([
                        "rows.{$index}.allocation_id" => 'The selected Allocation does not belong to the selected Allocation Level.'
                    ]);
                }
                if ($submittedAllocation->staff_id !== null && $planStaffId !== null && (int) $submittedAllocation->staff_id !== (int) $planStaffId) {
                    throw ValidationException::withMessages([
                        "rows.{$index}.allocation_id" => 'The selected Allocation does not belong to the selected Staff/Office.'
                    ]);
                }
                if ($submittedProgramId > 0 && (int) $submittedAllocation->program_id !== $submittedProgramId) {
                    throw ValidationException::withMessages([
                        "rows.{$index}.allocation_id" => 'The selected Allocation does not match the selected Program.'
                    ]);
                }
                $row['_program_id'] = (int) $submittedAllocation->program_id;
                continue;
            }
            if ($submittedProgramId > 0) {
                $row['_program_id'] = $submittedProgramId;
                continue;
            }
            $prexc = trim((string) ($row['prexc_code'] ?? ''));
            $row['_program_id'] = $prexc !== ''
                ? (int) ($prexcProgramMap[$prexc]['program_id'] ?? 0)
                : 0;
        }
        unset($row);
        $programAllocations = $this->buildProgramAllocationContext($year, $levelId, $planStaffId, $office);
        if ($accessPlan) {
            $existingLevelIds = FinancialPlan::query()
                ->where('financial_plans.fiscal_year', $year)
                ->where('financial_plans.staff_id', (int) $planStaffId)
                ->where(
                    'financial_plans.office_name',
                    $this->normalizePlanOfficeName($office)
                )
                ->whereNotNull('financial_plans.allocation_id')
                ->join(
                    'allocations',
                    'allocations.id',
                    '=',
                    'financial_plans.allocation_id'
                )
                ->distinct()
                ->pluck('allocations.level_id')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values();
            if ($existingLevelIds->isNotEmpty() && ! $existingLevelIds->contains($levelId)) {
                throw ValidationException::withMessages([
                    'level_id' => 'The selected Allocation Level does not match the existing Financial Plan. Create a separate Financial Plan for another Level.'
                ]);
            }
        }
        $this->validateProgramAllocationRows($rows, $programAllocations, false);
        $groupedRows = collect($rows)
            ->filter(fn ($row) => ($row['row_type'] ?? null) === 'item' && ! empty($row['_program_id']))
            ->groupBy('_program_id')
            ->map(fn ($programRows) => $programRows->values()->all())
            ->all();
        $this->validatePrexcRows(
            $rows,
            $year,
            $office,
            (int) $planStaffId
        );
        if ($this->planIsLocked($year, $office, $planStaffId !== null ? (int) $planStaffId : null)) {
            return $this->lockedResponse();
        }
        DB::beginTransaction();
        try {
            $allocationIdsByProgram = collect(array_keys($groupedRows))
                ->mapWithKeys(function ($programId) use ($programAllocations) {
                    $context = $programAllocations[(int) $programId] ?? null;
                    return $context
                        ? [(int) $programId => (int) $context['allocation_id']]
                        : [];
                });
            $lockedAllocationModels = $allocationIdsByProgram->isNotEmpty()
                ? Allocation::query()
                    ->with('fiscalYear')
                    ->whereIn('id', $allocationIdsByProgram->values()->all())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id')
                : collect();
            $lockedAllocations = [];
            foreach ($allocationIdsByProgram as $programId => $allocationId) {
                $allocation = $lockedAllocationModels->get((int) $allocationId);
                if (! $allocation) {
                    throw ValidationException::withMessages([
                        'allocation_id' => 'One of the selected Allocations could not be found.',
                    ]);
                }
                $lockedAllocations[(int) $programId] = $allocation;
                $this->validateAllocationAvailableBudget(
                    $groupedRows[$programId],
                    $allocation,
                    $year,
                    $office
                );
            }
            $existingPlans = $this->applyPlanIdentityScope(
                FinancialPlan::query(),
                $year,
                $office,
                (int) $planStaffId
            )
                ->get()
                ->keyBy('id');
            $existingIds = $existingPlans
                ->keys()
                ->map(fn ($id) => (int) $id)
                ->all();
            $savedIds = [];
            $targetUpserts = [];
            $targetDeletePlanIds = [];
            $targetTimestamp = now();
            $sortOrder = 10;
            foreach ($rows as $row) {
                $plan = ! empty($row['id'])
                    ? $existingPlans->get((int) $row['id'])
                    : null;
                $isNew = ! $plan;
                $plan ??= new FinancialPlan();
                $allocationId = null;
                if (($row['row_type'] ?? null) === 'item' && ! empty($row['_program_id'])) {
                    $allocationId = $lockedAllocations[(int) $row['_program_id']]->id
                        ?? ($programAllocations[(int) $row['_program_id']]['allocation_id'] ?? null);
                }
                $incoming = [
                    'fiscal_year' => $year,
                    'allocation_id' => $allocationId,
                    'office_name' => $office,
                    'staff_id' => (int) $planStaffId,
                    'division_id' => $plan->division_id ?? $accessPlan?->division_id ?? auth()->user()->division_id,
                    'row_type' => $row['row_type'],
                    'program_classification' => $row['program_classification'] ?? null,
                    'prexc_code' => $row['prexc_code'] ?? null,
                    'staff_unit_project' => $row['staff_unit_project'] ?? null,
                    'specific_activity' => $row['specific_activity'] ?? null,
                    'procurement_status' => array_key_exists('procurement_status', $row) ? $row['procurement_status'] : ($plan->procurement_status ?? null),
                    'expense_item' => $row['expense_item'] ?? null,
                    'assigned_personnel' => $row['assigned_personnel'] ?? null,
                    'mooe' => (float) ($row['mooe'] ?? 0),
                    'capital_outlay' => (float) ($row['capital_outlay'] ?? 0),
                    'contract_amount' => array_key_exists('contract_amount', $row) && $row['contract_amount'] !== null ? (float) $row['contract_amount'] : null,
                    'sort_order' => $sortOrder,
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
                        $targetUpserts[] = [
                            'financial_plan_id' => (int) $plan->id,
                            'month' => $m,
                            'amount' => (float) ($row['months'][$m] ?? 0),
                            'created_at' => $targetTimestamp,
                            'updated_at' => $targetTimestamp,
                        ];
                    }
                } else {
                    $targetDeletePlanIds[] = (int) $plan->id;
                }
                $sortOrder += 10;
            }
            if (! empty($targetUpserts)) {
                FinancialPlanTarget::upsert(
                    $targetUpserts,
                    ['financial_plan_id', 'month'],
                    ['amount', 'updated_at']
                );
            }
            if (! empty($targetDeletePlanIds)) {
                FinancialPlanTarget::whereIn(
                    'financial_plan_id',
                    array_values(array_unique($targetDeletePlanIds))
                )->delete();
            }
            $deleteIds = array_diff($existingIds, $savedIds);
            if (! empty($deleteIds)) {
                FinancialPlanTarget::whereIn('financial_plan_id', $deleteIds)->delete();
                FinancialPlan::whereIn('id', $deleteIds)->delete();
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Financial Plan saved successfully.',
                'redirect' => route('financial-plans.index', ['fiscal_year' => $year, 'office_name' => $office]),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Financial Plan save failed', ['exception' => $e]);
            throw $e;
        }
    }
    public function submitForApproval(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
            'staff_id' => ['nullable', 'integer', 'exists:staffs,id'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $requestedStaffId = isset($validated['staff_id']) && $validated['staff_id'] !== null
            ? (int) $validated['staff_id']
            : null;
        // Resolve the Financial Plan inside the current user's Staff/Office scope.
        $plan = $this->findPlanForAccess(
            $year,
            $office,
            $requestedStaffId
        );
        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'No financial plan was found.',
            ], 404);
        }
        $this->authorize('submit', $plan);
        if ($this->planIsLocked($year, $office, $plan->staff_id)) {
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
        $submissionPlans = $this->applyPlanIdentityScope(
            FinancialPlan::query(),
            $year,
            $office,
            (int) $plan->staff_id
        )
            ->whereNotNull('allocation_id')
            ->get([
                'allocation_id',
                'row_type',
                'mooe',
                'capital_outlay',
                'contract_amount',
            ]);

        $submissionPlansByAllocation = $submissionPlans
            ->groupBy('allocation_id');

        $submissionAllocationIds = $submissionPlansByAllocation
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();

        $submissionAllocations = collect();
        if ($submissionAllocationIds->isNotEmpty()) {
            $submissionAllocations = Allocation::query()
                ->with('fiscalYear')
                ->whereIn('id', $submissionAllocationIds->all())
                ->get()
                ->keyBy('id');
        }

        foreach ($submissionAllocationIds as $allocationId) {
            $submissionAllocation = $submissionAllocations->get(
                (int) $allocationId
            );

            if (! $submissionAllocation) {
                continue;
            }

            $submissionRows = $submissionPlansByAllocation
                ->get((int) $allocationId, collect())
                ->map(fn ($item) => [
                    'row_type' => $item->row_type,
                    'mooe' => $item->mooe,
                    'capital_outlay' => $item->capital_outlay,
                    'contract_amount' => $item->contract_amount,
                ])
                ->values()
                ->all();

            $this->validateAllocationAvailableBudget(
                $submissionRows,
                $submissionAllocation,
                $year,
                $office
            );
        }
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
            'staff_id' => ['nullable', 'integer', 'exists:staffs,id'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $requestedStaffId = isset($validated['staff_id']) && $validated['staff_id'] !== null
            ? (int) $validated['staff_id']
            : null;
        // Resolve the plan first so policy authorization can enforce staff_id.
        $plan = $this->findPlanForAccess(
            $year,
            $office,
            $requestedStaffId
        );
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
            'staff_id' => ['nullable', 'integer', 'exists:staffs,id'],
            'return_remarks' => ['required', 'string', 'max:2000'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $requestedStaffId = isset($validated['staff_id']) && $validated['staff_id'] !== null
            ? (int) $validated['staff_id']
            : null;
        // Resolve the plan first so policy authorization can enforce staff_id.
        $plan = $this->findPlanForAccess(
            $year,
            $office,
            $requestedStaffId
        );
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
            'staff_id' => ['nullable', 'integer', 'exists:staffs,id'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $requestedStaffId = isset($validated['staff_id']) && $validated['staff_id'] !== null
            ? (int) $validated['staff_id']
            : null;
        // Resolve the plan first so policy authorization can enforce staff_id.
        $plan = $this->findPlanForAccess(
            $year,
            $office,
            $requestedStaffId
        );
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
            'staff_id' => ['nullable', 'integer', 'exists:staffs,id'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $requestedStaffId = isset($validated['staff_id']) && $validated['staff_id'] !== null
            ? (int) $validated['staff_id']
            : null;
        // Resolve the plan first so policy authorization can enforce staff_id.
        $plan = $this->findPlanForAccess(
            $year,
            $office,
            $requestedStaffId
        );
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
            'staff_id' => ['nullable', 'integer', 'exists:staffs,id'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $requestedStaffId = isset($validated['staff_id']) && $validated['staff_id'] !== null
            ? (int) $validated['staff_id']
            : null;
        $plan = $this->authorizePlanRead(
            $year,
            $office,
            $requestedStaffId
        );

        $staffId = $plan?->staff_id ?? $requestedStaffId;

        $submissionQuery = FinancialPlanSubmission::query()
            ->where('fiscal_year', $year)
            ->where('office_name', $office);

        if ($staffId !== null) {
            $submissionQuery->where('staff_id', $staffId);
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
    // Delete (entire filed plan for one fiscal year + office)
    public function destroyPlan(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
            'staff_id' => ['nullable', 'integer', 'exists:staffs,id'],
        ]);
        $year = (int) $validated['fiscal_year'];
        $office = $validated['office_name'];
        $requestedStaffId = isset($validated['staff_id']) && $validated['staff_id'] !== null
            ? (int) $validated['staff_id']
            : null;
        $staffId = $requestedStaffId ?? auth()->user()->staff_id;
        if (auth()->user()->isAdministrator() && $staffId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Staff/Office is required to identify the Financial Plan to delete.',
            ], 422);
        }
        if ($this->planIsLocked($year, $office, $staffId)) {
            return $this->lockedResponse();
        }
        $plansQuery = FinancialPlan::query()
            ->where('fiscal_year', $year)
            ->where('office_name', $office)
            ->where('staff_id', $staffId);
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
                'staff_id' => $staffId,
                'office_name' => $office,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting the financial plan.',
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\WorkPlan;
use App\Models\WorkPlanClassification;
use App\Models\WorkPlanItem;
use App\Models\WorkPlanSignatory;
use App\Models\WorkPlanSubmission;
use App\Models\WorkPlanTarget;
use App\Models\WorkPlanTargetMonth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\FinancialPlan;

class WorkPlanController extends Controller
{
    private const MONTHS = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    public function plans(Request $request): View
    {
        $this->authorize('viewAny', WorkPlan::class);

        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $staffId = $request->filled('staff_id')
            ? (int) $request->input('staff_id')
            : null;

        $query = WorkPlan::query()
            ->with('staff')
            ->withCount('items')
            ->where('fiscal_year', $fiscalYear);

        $this->applyStaffScope($query);

        if ($staffId !== null) {
            $this->ensureStaffAccess($staffId);
            $query->where('staff_id', $staffId);
        }

        $plans = $query
            ->orderBy('staff_id')
            ->get();

        $fiscalYearsQuery = WorkPlan::query();
        $this->applyStaffScope($fiscalYearsQuery);

        $fiscalYears = $fiscalYearsQuery
            ->distinct()
            ->orderByDesc('fiscal_year')
            ->pluck('fiscal_year');

        return view('work-plans.plans', [
            'plans' => $plans,
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => $fiscalYears,
            'staffId' => $staffId,
            'staffs' => $this->availableStaffs(),
        ]);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WorkPlan::class);

        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $staffId = $this->resolveRequestedStaffId($request);

        $plan = null;

        if ($staffId !== null) {
            $plan = $this->findPlanForAccess($fiscalYear, $staffId);

            if ($plan) {
                $this->authorize('view', $plan);

                $plan->load([
                    'staff',
                    'signatory',
                    'items.parent',
                    'items.children',
                    'items.classification',
                    'items.targets.months',
                    'submissions.actor',
                ]);
            }
        }

        return view('work-plans.index', [
            'plan' => $plan,
            'fiscalYear' => $fiscalYear,
            'staffId' => $staffId,
            'staffs' => $this->availableStaffs(),
            'months' => self::MONTHS,
        ]);
    }

    public function builder(Request $request): View
    {
        $this->authorize('viewAny', WorkPlan::class);

        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $staffId = $this->resolveRequestedStaffId($request);

        $plan = null;

        if ($staffId !== null) {
            $plan = $this->findPlanForAccess($fiscalYear, $staffId);

            if ($plan) {
                $this->authorize('view', $plan);

                $plan->load([
                    'staff',
                    'signatory',
                    'items.parent',
                    'items.children',
                    'items.classification',
                    'items.targets.months',
                    'submissions.actor',
                ]);
            } else {
                $this->authorize('create', WorkPlan::class);
                $this->ensureStaffAccess($staffId);
            }
        } else {
            $this->authorize('create', WorkPlan::class);
        }

        $classifications = WorkPlanClassification::query()
            ->forFiscalYear($fiscalYear)
            ->active()
            ->ordered()
            ->get();

        $financialPlanActivities = collect();

        if ($staffId !== null) {
            $financialPlanActivities = $this->financialPlanActivities(
                $fiscalYear,
                $staffId
            );
        }

        return view('work-plans.builder', [
            'plan' => $plan,
            'fiscalYear' => $fiscalYear,
            'staffId' => $staffId,
            'staffs' => $this->availableStaffs(),
            'classifications' => $classifications,
            'financialPlanActivities' => $financialPlanActivities,
            'months' => self::MONTHS,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkPlan::class);

        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'staff_id' => ['required', 'integer', 'exists:staffs,id'],
        ]);

        $plan = $this->findPlanForAccess(
            (int) $validated['fiscal_year'],
            (int) $validated['staff_id']
        );

        if (! $plan) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        $this->authorize('view', $plan);

        $plan->load([
            'staff',
            'signatory',
            'items.parent',
            'items.children',
            'items.classification',
            'items.targets.months',
            'submissions.actor',
        ]);

        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'staff_id' => ['required', 'integer', 'exists:staffs,id'],

            'signatory' => ['nullable', 'array'],
            'signatory.prepared_by' => ['nullable', 'string', 'max:150'],
            'signatory.prepared_by_position' => ['nullable', 'string', 'max:150'],
            'signatory.reviewed_by' => ['nullable', 'string', 'max:150'],
            'signatory.reviewed_by_position' => ['nullable', 'string', 'max:150'],
            'signatory.recommended_by' => ['nullable', 'string', 'max:150'],
            'signatory.recommended_by_position' => ['nullable', 'string', 'max:150'],
            'signatory.approved_by' => ['nullable', 'string', 'max:150'],
            'signatory.approved_by_position' => ['nullable', 'string', 'max:150'],

            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.row_key' => ['nullable', 'string', 'max:100'],
            'items.*.parent_key' => ['nullable', 'string', 'max:100'],
            'items.*.parent_id' => ['nullable', 'integer'],
            'items.*.row_type' => [
                'required',
                'string',
                'in:header,subheader,item',
            ],
            'items.*.title' => ['nullable', 'string'],
            'items.*.classification_id' => [
                'nullable',
                'integer',
                'exists:work_plan_classifications,id',
            ],
            'items.*.financial_plan_id' => [
                'nullable',
                'integer',
                'exists:financial_plans,id',
            ],
            'items.*.program_classification' => [
                'nullable',
                'string',
            ],
            'items.*.prexc_code' => [
                'nullable',
                'string',
                'max:100',
            ],
            'items.*.specific_activity' => [
                'nullable',
                'string',
            ],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'items.*.targets' => ['nullable', 'array'],
            'items.*.targets.*.id' => ['nullable', 'integer'],
            'items.*.targets.*.month' => [
                'nullable',
                'integer',
                'between:1,12',
            ],
            'items.*.targets.*.months' => ['nullable', 'array', 'min:1'],
            'items.*.targets.*.months.*' => [
                'required',
                'integer',
                'between:1,12',
            ],
            'items.*.targets.*.target_output' => [
                'required',
                'string',
            ],
            'items.*.targets.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $year = (int) $validated['fiscal_year'];
        $staffId = (int) $validated['staff_id'];
        $items = $validated['items'] ?? [];

        $this->ensureStaffAccess($staffId);

        $plan = $this->findPlanForAccess($year, $staffId);

        if ($plan) {
            $this->authorize('update', $plan);

            if (! $plan->isEditable()) {
                return $this->notEditableResponse();
            }
        } else {
            $this->authorize('create', WorkPlan::class);
        }

        $this->validateItemStructure($items);
        $this->validateClassifications($year, $items);
        $this->validateTargetMonths($items);

        $plan = DB::transaction(function () use (
            $validated,
            $year,
            $staffId,
            $items,
            $plan
        ) {
            if (! $plan) {
                $plan = WorkPlan::create([
                    'fiscal_year' => $year,
                    'staff_id' => $staffId,
                    'status' => 'draft',
                    'finalized' => 'no',
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            } else {
                $plan->updated_by = auth()->id();
                $plan->save();
            }

            $this->saveSignatory(
                $plan,
                $validated['signatory'] ?? []
            );

            $keepItemIds = [];
            $savedRowKeys = [];

            foreach ($items as $itemIndex => $itemData) {
                $item = null;

                if (! empty($itemData['id'])) {
                    $item = WorkPlanItem::query()
                        ->where('id', (int) $itemData['id'])
                        ->where('work_plan_id', $plan->id)
                        ->first();
                }

                if (! $item) {
                    $item = new WorkPlanItem();
                    $item->work_plan_id = $plan->id;
                }

                $rowType = $itemData['row_type'];
                $parentId = $this->resolveParentId(
                    $plan,
                    $itemData,
                    $savedRowKeys
                );

                $item->parent_id = $parentId;
                $item->row_type = $rowType;
                $item->sort_order = (int) (
                    $itemData['sort_order']
                    ?? (($itemIndex + 1) * 10)
                );

                if ($rowType === 'item') {
                    $item->title = null;
                    $item->classification_id = ! empty(
                        $itemData['classification_id']
                    )
                        ? (int) $itemData['classification_id']
                        : null;
                    $item->financial_plan_id = ! empty(
                        $itemData['financial_plan_id']
                    )
                        ? (int) $itemData['financial_plan_id']
                        : null;
                    $item->program_classification = $this->nullableTrim(
                        $itemData['program_classification'] ?? null
                    );
                    $item->prexc_code = $this->nullableTrim(
                        $itemData['prexc_code'] ?? null
                    );
                    $item->specific_activity = $this->nullableTrim(
                        $itemData['specific_activity'] ?? null
                    );
                } else {
                    $item->title = $this->nullableTrim(
                        $itemData['title'] ?? null
                    );
                    $item->classification_id = null;
                    $item->financial_plan_id = null;
                    $item->program_classification = null;
                    $item->prexc_code = null;
                    $item->specific_activity = null;
                }

                $item->save();

                $keepItemIds[] = $item->id;

                if (! empty($itemData['row_key'])) {
                    $savedRowKeys[$itemData['row_key']] = $item->id;
                }

                if ($rowType === 'item') {
                    $this->saveTargets(
                        $item,
                        $itemData['targets'] ?? []
                    );
                } else {
                    WorkPlanTarget::query()
                        ->where('work_plan_item_id', $item->id)
                        ->delete();
                }
            }

            $itemDeleteQuery = WorkPlanItem::query()
                ->where('work_plan_id', $plan->id);

            if (! empty($keepItemIds)) {
                $itemDeleteQuery->whereNotIn('id', $keepItemIds);
            }

            $itemDeleteQuery->delete();

            return $plan;
        });

        return response()->json([
            'success' => true,
            'message' => 'Work Plan saved successfully.',
            'data' => $plan->load([
                'staff',
                'signatory',
                'items.parent',
                'items.children',
                'items.classification',
                'items.targets.months',
            ]),
        ]);
    }

    public function exportPdf(WorkPlan $workPlan)
    {
        $this->authorize('view', $workPlan);
        $workPlan->load([
            'staff',
            'signatory',
            'items.parent',
            'items.children',
            'items.classification',
            'items.targets.months',
        ]);
        $pdf = Pdf::loadView('work-plans.pdf', [
            'plan' => $workPlan,
            'months' => self::MONTHS,
        ])->setPaper('a4', 'landscape');
        return $pdf->download(
            'work-plan-fy-' . $workPlan->fiscal_year . '-' . $workPlan->id . '.pdf'
        );
    }

    public function destroy(WorkPlan $workPlan): RedirectResponse
    {
        $this->authorize('delete', $workPlan);

        if (! $workPlan->isEditable()) {
            return redirect()->back()->with(
                'error',
                'Only draft or returned Work Plans can be deleted.'
            );
        }

        $workPlan->delete();

        return redirect()
            ->route('work-plans.plans')
            ->with('succes', 'Work Plan deleted successfully.');
    }

    public function submit(WorkPlan $workPlan): JsonResponse
    {
        $this->authorize('submit', $workPlan);

        if (! $workPlan->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft or returned Work Plans can be submitted.',
            ], 422);
        }

        $this->validateForSubmit($workPlan);

        DB::transaction(function () use ($workPlan) {
            $fromStatus = $workPlan->status;

            $workPlan->update([
                'status' => 'submitted',
                'finalized' => 'no',
                'submitted_at' => now(),
                'submitted_by' => auth()->id(),
                'approved_at' => null,
                'approved_by' => null,
                'finalized_at' => null,
                'finalized_by' => null,
                'updated_by' => auth()->id(),
            ]);

            $this->recordSubmission(
                $workPlan,
                'submit',
                $fromStatus,
                'submitted'
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Work Plan submitted for approval.',
        ]);
    }

    public function approve(WorkPlan $workPlan): JsonResponse
    {
        $this->authorize('approve', $workPlan);

        if ($workPlan->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Only submitted Work Plans can be approved.',
            ], 422);
        }

        DB::transaction(function () use ($workPlan) {
            $workPlan->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->recordSubmission(
                $workPlan,
                'approve',
                'submitted',
                'approved'
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Work Plan approved.',
        ]);
    }

    public function returnPlan(
        Request $request,
        WorkPlan $workPlan
    ): JsonResponse {
        $this->authorize('return', $workPlan);

        $validated = $request->validate([
            'remarks' => ['required', 'string', 'max:5000'],
        ]);

        if (! in_array(
            $workPlan->status,
            ['submitted', 'approved'],
            true
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Only submitted or approved Work Plans can be returned.',
            ], 422);
        }

        DB::transaction(function () use (
            $workPlan,
            $validated
        ) {
            $fromStatus = $workPlan->status;

            $workPlan->update([
                'status' => 'returned',
                'finalized' => 'no',
                'approved_at' => null,
                'approved_by' => null,
                'finalized_at' => null,
                'finalized_by' => null,
                'updated_by' => auth()->id(),
            ]);

            $this->recordSubmission(
                $workPlan,
                'return',
                $fromStatus,
                'returned',
                $validated['remarks']
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Work Plan returned for revision.',
        ]);
    }

    public function finalize(WorkPlan $workPlan): JsonResponse
    {
        $this->authorize('finalize', $workPlan);

        if ($workPlan->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved Work Plans can be finalized.',
            ], 422);
        }

        DB::transaction(function () use ($workPlan) {
            $workPlan->update([
                'status' => 'finalized',
                'finalized' => 'yes',
                'finalized_at' => now(),
                'finalized_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->recordSubmission(
                $workPlan,
                'finalize',
                'approved',
                'finalized'
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Work Plan finalized.',
        ]);
    }

    public function reopen(WorkPlan $workPlan): JsonResponse
    {
        $this->authorize('reopen', $workPlan);

        if (! $workPlan->isFinalized()) {
            return response()->json([
                'success' => false,
                'message' => 'Only finalized Work Plans can be reopened.',
            ], 422);
        }

        DB::transaction(function () use ($workPlan) {
            $workPlan->update([
                'status' => 'draft',
                'finalized' => 'no',
                'submitted_at' => null,
                'submitted_by' => null,
                'approved_at' => null,
                'approved_by' => null,
                'finalized_at' => null,
                'finalized_by' => null,
                'updated_by' => auth()->id(),
            ]);

            $this->recordSubmission(
                $workPlan,
                'reopen',
                'finalized',
                'draft'
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Work Plan reopened as draft.',
        ]);
    }

    private function saveSignatory(
        WorkPlan $plan,
        array $signatoryData
    ): void {
        $fields = [
            'prepared_by',
            'prepared_by_position',
            'reviewed_by',
            'reviewed_by_position',
            'recommended_by',
            'recommended_by_position',
            'approved_by',
            'approved_by_position',
        ];

        $data = [];

        foreach ($fields as $field) {
            $data[$field] = $this->nullableTrim(
                $signatoryData[$field] ?? null
            );
        }

        WorkPlanSignatory::updateOrCreate(
            ['work_plan_id' => $plan->id],
            $data
        );
    }

    private function saveTargets(
        WorkPlanItem $item,
        array $targets
    ): void {
        $keepTargetIds = [];

        foreach ($targets as $targetIndex => $targetData) {
            $target = null;

            if (! empty($targetData['id'])) {
                $target = WorkPlanTarget::query()
                    ->where('id', (int) $targetData['id'])
                    ->where('work_plan_item_id', $item->id)
                    ->first();
            }

            if (! $target) {
                $target = new WorkPlanTarget();
                $target->work_plan_item_id = $item->id;
            }

            $months = collect($targetData['months'] ?? [])
                ->map(fn ($month) => (int) $month)
                ->filter(fn ($month) => $month >= 1 && $month <= 12)
                ->unique()
                ->sort()
                ->values();

            if ($months->isEmpty() && ! empty($targetData['month'])) {
                $months = collect([(int) $targetData['month']]);
            }

            $target->month = $months->first();
            $target->target_output = trim(
                $targetData['target_output']
            );
            $target->sort_order = (int) (
                $targetData['sort_order']
                ?? (($targetIndex + 1) * 10)
            );

            $target->save();

            WorkPlanTargetMonth::query()
                ->where('work_plan_target_id', $target->id)
                ->whereNotIn('month', $months->all())
                ->delete();

            foreach ($months as $month) {
                WorkPlanTargetMonth::updateOrCreate(
                    [
                        'work_plan_target_id' => $target->id,
                        'month' => $month,
                    ],
                    []
                );
            }

            $keepTargetIds[] = $target->id;
        }

        $deleteQuery = WorkPlanTarget::query()
            ->where('work_plan_item_id', $item->id);

        if (! empty($keepTargetIds)) {
            $deleteQuery->whereNotIn('id', $keepTargetIds);
        }

        $deleteQuery->delete();
    }

    private function resolveParentId(
        WorkPlan $plan,
        array $itemData,
        array $savedRowKeys
    ): ?int {
        if (! empty($itemData['parent_key'])) {
            $parentKey = $itemData['parent_key'];

            if (! isset($savedRowKeys[$parentKey])) {
                throw ValidationException::withMessages([
                    'items' => [
                        'A Work Plan row references an invalid parent row.',
                    ],
                ]);
            }

            return (int) $savedRowKeys[$parentKey];
        }

        if (! empty($itemData['parent_id'])) {
            $parentId = (int) $itemData['parent_id'];

            $exists = WorkPlanItem::query()
                ->where('id', $parentId)
                ->where('work_plan_id', $plan->id)
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'items' => [
                        'A Work Plan row references an invalid parent item.',
                    ],
                ]);
            }

            return $parentId;
        }

        return null;
    }

    private function applyStaffScope($query)
    {
        if ($this->isAdministrator()) {
            return $query;
        }

        $staffId = auth()->user()->staff_id;

        if ($staffId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('staff_id', (int) $staffId);
    }

    private function findPlanForAccess(
        int $fiscalYear,
        int $staffId
    ): ?WorkPlan {
        $query = WorkPlan::query()
            ->where('fiscal_year', $fiscalYear)
            ->where('staff_id', $staffId);

        $this->applyStaffScope($query);

        return $query->first();
    }

    private function resolveRequestedStaffId(
        Request $request
    ): ?int {
        if ($request->filled('staff_id')) {
            $staffId = (int) $request->input('staff_id');

            $this->ensureStaffAccess($staffId);

            return $staffId;
        }

        if (! $this->isAdministrator()) {
            return auth()->user()->staff_id !== null
                ? (int) auth()->user()->staff_id
                : null;
        }

        return null;
    }

    private function ensureStaffAccess(int $staffId): void
    {
        $exists = Staff::query()
            ->where('id', $staffId)
            ->exists();

        abort_unless($exists, 404);

        if ($this->isAdministrator()) {
            return;
        }

        abort_unless(
            auth()->user()->staff_id !== null
                && (int) auth()->user()->staff_id === $staffId,
            403
        );
    }

    private function availableStaffs()
    {
        $query = Staff::query()
            ->orderBy('name');

        if (! $this->isAdministrator()) {
            $staffId = auth()->user()->staff_id;

            if ($staffId === null) {
                return collect();
            }

            $query->where('id', (int) $staffId);
        }

        return $query->get([
            'id',
            'name',
            'abbreviation',
            'office_id',
            'group_id',
        ]);
    }

    private function validateItemStructure(array $items): void
    {
        $rowKeys = [];

        foreach ($items as $index => $item) {
            $rowType = $item['row_type'] ?? 'item';

            if (! empty($item['row_key'])) {
                if (isset($rowKeys[$item['row_key']])) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Duplicate Work Plan row identifiers were found.',
                        ],
                    ]);
                }

                $rowKeys[$item['row_key']] = true;
            }

            if (in_array(
                $rowType,
                ['header', 'subheader'],
                true
            )) {
                if (trim((string) ($item['title'] ?? '')) === '') {
                    throw ValidationException::withMessages([
                        "items.$index.title" => [
                            'Header and Subheader rows require a title.',
                        ],
                    ]);
                }

                continue;
            }

            $hasLegacyClassification = ! empty(
                $item['classification_id']
            );

            $hasFpClassification = ! empty(
                $item['financial_plan_id']
            ) && trim(
                (string) ($item['program_classification'] ?? '')
            ) !== '';

            if (! $hasLegacyClassification && ! $hasFpClassification) {
                throw ValidationException::withMessages([
                    "items.$index.program_classification" => [
                        'Program Classification is required for Budget Lines.',
                    ],
                ]);
            }

            if (
                trim(
                    (string) ($item['specific_activity'] ?? '')
                ) === ''
            ) {
                throw ValidationException::withMessages([
                    "items.$index.specific_activity" => [
                        'Specific Activity is required for Budget Lines.',
                    ],
                ]);
            }
        }

        foreach ($items as $index => $item) {
            if (
                ! empty($item['parent_key'])
                && ! isset($rowKeys[$item['parent_key']])
            ) {
                throw ValidationException::withMessages([
                    "items.$index.parent_key" => [
                        'The selected parent row is invalid.',
                    ],
                ]);
            }
        }
    }

    private function validateTargetMonths(array $items): void
    {
        foreach ($items as $itemIndex => $item) {
            if (($item['row_type'] ?? 'item') !== 'item') {
                continue;
            }

            foreach (($item['targets'] ?? []) as $targetIndex => $target) {
                $months = $target['months'] ?? [];

                if (empty($months) && ! empty($target['month'])) {
                    $months = [(int) $target['month']];
                }

                if (empty($months)) {
                    throw ValidationException::withMessages([
                        "items.$itemIndex.targets.$targetIndex.months" => [
                            'Select at least one month for every Target Output.',
                        ],
                    ]);
                }
            }
        }
    }

    private function validateClassifications(
        int $fiscalYear,
        array $items
    ): void {
        $classificationIds = collect($items)
            ->filter(
                fn ($item) => ($item['row_type'] ?? 'item') === 'item'
            )
            ->pluck('classification_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($classificationIds->isEmpty()) {
            return;
        }

        $validIds = WorkPlanClassification::query()
            ->where('fiscal_year', $fiscalYear)
            ->where('is_active', true)
            ->whereIn('id', $classificationIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $invalidIds = $classificationIds->diff($validIds);

        if ($invalidIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => [
                    'One or more classifications are not available for the selected fiscal year.',
                ],
            ]);
        }
    }

    private function validateForSubmit(WorkPlan $workPlan): void
    {
        $workPlan->load([
            'items.targets.months',
            'signatory',
        ]);

        $budgetLines = $workPlan->items
            ->where('row_type', 'item')
            ->values();

        if ($budgetLines->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => [
                    'Add at least one Budget Line before submitting the Work Plan.',
                ],
            ]);
        }

        foreach ($workPlan->items as $item) {
            if (in_array(
                $item->row_type,
                ['header', 'subheader'],
                true
            )) {
                if (trim((string) $item->title) === '') {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Every Section Header and Subheader must have a title.',
                        ],
                    ]);
                }

                continue;
            }

            $hasLegacyClassification = ! empty(
                $item->classification_id
            );

            $hasFpClassification = ! empty(
                $item->financial_plan_id
            ) && trim(
                (string) $item->program_classification
            ) !== '';

            if (! $hasLegacyClassification && ! $hasFpClassification) {
                throw ValidationException::withMessages([
                    'items' => [
                        'Every Budget Line must have a Program Classification.',
                    ],
                ]);
            }

            if (trim((string) $item->specific_activity) === '') {
                throw ValidationException::withMessages([
                    'items' => [
                        'Every Budget Line must have a Specific Activity.',
                    ],
                ]);
            }

            if ($item->targets->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => [
                        'Every Budget Line must have at least one monthly Target Output.',
                    ],
                ]);
            }
        }
    }

    private function nullableTrim($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function recordSubmission(
        WorkPlan $workPlan,
        string $action,
        ?string $fromStatus,
        string $toStatus,
        ?string $remarks = null
    ): void {
        WorkPlanSubmission::create([
            'work_plan_id' => $workPlan->id,
            'staff_id' => $workPlan->staff_id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'remarks' => $remarks,
            'acted_by' => auth()->id(),
            'acted_at' => now(),
        ]);
    }

    private function isAdministrator(): bool
    {
        return in_array(
            (int) auth()->user()->role_id,
            [1, 29],
            true
        );
    }

    private function notEditableResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'This Work Plan cannot be edited in its current status.',
        ], 403);
    }

    private function financialPlanActivities(
        int $fiscalYear,
        int $staffId,
        ?int $divisionId = null
    ) {
        $query = FinancialPlan::query()
            ->where('fiscal_year', $fiscalYear)
            ->where('staff_id', $staffId)
            ->where('row_type', 'item')
            ->whereNotNull('program_classification')
            ->whereNotNull('specific_activity')
            ->where('program_classification', '!=', '')
            ->where('specific_activity', '!=', '');

        if ($divisionId !== null) {
            $query->where('division_id', $divisionId);
        }

        return $query
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'division_id',
                'program_classification',
                'prexc_code',
                'specific_activity',
                'sort_order',
            ])
            ->unique(function ($row) {
                return implode('|', [
                    mb_strtolower(trim((string) $row->program_classification)),
                    mb_strtolower(trim((string) $row->prexc_code)),
                    mb_strtolower(trim((string) $row->specific_activity)),
                ]);
            })
            ->map(function ($row) {
                return [
                    'financial_plan_id' => (int) $row->id,
                    'division_id' => $row->division_id
                        ? (int) $row->division_id
                        : null,
                    'program_classification' =>
                        trim((string) $row->program_classification),
                    'prexc_code' =>
                        trim((string) $row->prexc_code),
                    'specific_activity' =>
                        trim((string) $row->specific_activity),
                ];
            })
            ->values();
    }

}

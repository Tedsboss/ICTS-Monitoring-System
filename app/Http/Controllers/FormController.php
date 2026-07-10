<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormFieldRequest;
use App\Http\Requests\FormDuplicateRequest;
use App\Http\Requests\FormRequest;
use App\Models\Agency;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Staff;
use App\Traits\GenerateLogs;
use App\Traits\TracksHistoryTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class FormController extends Controller
{
    use GenerateLogs;
    use TracksHistoryTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Form::class);

        $formsCount = $this->visibleFormsQuery()->count();
        $duplicateErrorForm = old('_form_action') === 'duplicate' && old('_duplicate_form_id')
            ? Form::find(old('_duplicate_form_id'))
            : null;

        $agencies = Agency::query()
            ->when(!auth()->user()->isSuperAdmin(), function ($query) {
                $query->where('id', auth()->user()->agency_id);
            })
            ->orderBy('UACS_AGY_DSC')
            ->get();

        $sectors = Staff::orderBy('office_id')->orderBy('name')->get();

        return view('forms.index', compact('agencies', 'sectors', 'formsCount', 'duplicateErrorForm'));
    }

    public function getforms()
    {
        $this->authorize('viewAny', Form::class);

        $forms = $this->visibleFormsQuery()
            ->with(['agency', 'templateSource'])
            ->withCount(['fields' => function ($query) {
                $query->where('status', 1);
            }]);

        return DataTables::of($forms)
            ->addIndexColumn()
            ->addColumn('agency', function (Form $form) {
                $agencyName = optional($form->agency)->display_name ?? 'No agency assigned';
                $avatar = strtoupper(substr($agencyName, 0, 1));

                return '<div class="agency-cell">'
                    . '<div class="agency-avatar">' . e($avatar) . '</div>'
                    . '<div>'
                    . '<div class="text-sm font-weight-semibold text-dark">' . e($agencyName) . '</div>'
                    . '<div class="text-xs text-secondary">Assigned agency</div>'
                    . '</div>'
                    . '</div>';
            })
            ->addColumn('form_title', function (Form $form) {
                $source = $form->templateSource
                    ? 'Copied from ' . $form->templateSource->title
                    : 'Weekly report form';

                return '<div class="text-sm font-weight-bold text-dark">' . e($form->title) . '</div>'
                    . '<div class="text-xs text-secondary">' . e($source) . '</div>';
            })
            ->editColumn('fields_count', function (Form $form) {
                return '<span class="field-count-pill">' . e($form->fields_count) . '</span>';
            })
            ->addColumn('status_label', function (Form $form) {
                return (int) $form->status === 1
                    ? '<span class="status-pill status-active">Active</span>'
                    : '<span class="status-pill status-inactive">Inactive</span>';
            })
            ->addColumn('actions', function (Form $form) {
                $canUpdate = auth()->user()->can('update', $form);
                $label = $canUpdate ? 'Manage' : 'View';
                $icon = $canUpdate ? 'fa-pencil' : 'fa-eye';
                $buttonClass = $canUpdate ? 'btn-outline-primary' : 'btn-outline-secondary';

                $actions = '<a href="' . route('forms.edit', $form) . '" '
                    . 'data-bs-toggle="tooltip" '
                    . 'data-bs-original-title="' . ($canUpdate ? 'Manage Form' : 'View Form') . '" '
                    . 'class="btn btn-sm ' . $buttonClass . ' action-btn mb-0">'
                    . '<i class="fa ' . $icon . ' me-1"></i>' . $label
                    . '</a>';

                if (auth()->user()->can('create', Form::class)) {
                    $actions .= '<button class="btn btn-sm btn-primary action-btn mb-0 js-duplicate-form" '
                        . 'type="button" '
                        . 'data-bs-toggle="modal" '
                        . 'data-bs-target="#duplicate-form-modal" '
                        . 'data-form-id="' . e($form->id) . '" '
                        . 'data-form-title="' . e($form->title) . '" '
                        . 'data-form-agency-id="' . e($form->agency_id) . '" '
                        . 'data-duplicate-url="' . route('forms.duplicate', $form) . '">'
                        . '<i class="fa fa-copy me-1"></i>Duplicate'
                        . '</button>';
                }

                return '<div class="d-inline-flex gap-2">' . $actions . '</div>';
            })
            ->filterColumn('agency', function ($query, $keyword) {
                $query->whereHas('agency', function ($query) use ($keyword) {
                    $query->where('UACS_AGY_DSC', 'like', "%{$keyword}%")
                        ->orWhere('Abbreviation', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('form_title', function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->orWhereHas('templateSource', function ($query) use ($keyword) {
                            $query->where('title', 'like', "%{$keyword}%");
                        });
                });
            })
            ->filterColumn('fields_count', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->having('fields_count', (int) $keyword);
                }
            })
            ->filterColumn('status_label', function ($query, $keyword) {
                $keyword = strtolower(trim((string) $keyword));

                if (str_contains('active', $keyword)) {
                    $query->where('status', 1);
                } elseif (str_contains('inactive', $keyword)) {
                    $query->where('status', 0);
                }
            })
            ->orderColumn('agency', function ($query, $order) {
                $query->leftJoin('agencies as order_agencies', 'forms.agency_id', '=', 'order_agencies.id')
                    ->orderBy('order_agencies.UACS_AGY_DSC', $order)
                    ->select('forms.*');
            })
            ->orderColumn('form_title', function ($query, $order) {
                $query->orderBy('title', $order);
            })
            ->orderColumn('status_label', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->rawColumns(['agency', 'form_title', 'fields_count', 'status_label', 'actions'])
            ->toJson();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FormRequest $request)
    {
        $this->authorize('create', Form::class);

        $agencyIds = collect($request->input('agency_ids', [$request->agency_id]))
            ->filter()
            ->unique()
            ->values();

        DB::transaction(function () use ($request, $agencyIds, &$forms) {
            $forms = collect();

            foreach ($agencyIds as $agencyId) {
                $form = new Form();
                $form->agency_id = $agencyId;
                $form->title = $request->title;
                $form->status = $request->status;
                $form->assigned_sector_id = $request->assigned_sector_id;
                $form->save();

                $forms->push($form);

                $this->logSystemActivity('Created form: ' . $form->title, 'forms', $form->id);
            }
        });

        if ($forms->count() === 1) {
            return redirect()
                ->route('forms.edit', $forms->first())
                ->with('success', 'Form successfully saved.');
        }

        return redirect()
            ->route('forms.index')
            ->with('success', $forms->count() . ' forms successfully saved.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Form $form)
    {
        $this->authorize('view', $form);

        $form->load([
            'agency',
            'fields' => function ($query) {
                $query->orderBy('row_number')
                    ->orderBy('order')
                    ->orderBy('id');
            },
        ]);

        $initialAgencies = Agency::query()
            ->select([
                'id',
                'Abbreviation',
                'UACS_AGY_DSC',
                'UACS_AGY_ID',
            ])
            ->when(!auth()->user()->isSuperAdmin(), function ($query) {
                $query->where('id', auth()->user()->agency_id);
            })
            ->orderByRaw("CASE WHEN Abbreviation IS NULL OR Abbreviation = '' THEN 1 ELSE 0 END")
            ->orderBy('Abbreviation')
            ->orderBy('UACS_AGY_DSC')
            ->get();

        $sectors = Staff::orderBy('office_id')->orderBy('name')->get();

        return view('forms.edit', compact('form', 'initialAgencies', 'sectors'));
    }

    /**
     * Preview the form.
     */
    public function preview(Form $form)
    {
        $this->authorize('view', $form);

        $form->load([
            'agency',
            'fields' => function ($query) {
                $query->where('status', 1)
                    ->orderBy('row_number')
                    ->orderBy('order')
                    ->orderBy('id');
            },
        ]);

        return view('forms.preview', compact('form'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FormRequest $request, Form $form)
    {
        $this->authorize('update', $form);

        $fieldData = $this->validatedFieldData($request, $form);

        DB::transaction(function () use ($request, $form, $fieldData) {
            $lockedForm = Form::where('id', $form->id)->lockForUpdate()->firstOrFail();

            $lockedForm->agency_id = $request->agency_id;
            $lockedForm->title = $request->title;
            $lockedForm->status = $request->status;
            $lockedForm->assigned_sector_id = $request->assigned_sector_id;

            if ($lockedForm->isDirty()) {
                $logId = $this->logSystemActivity('Updated form: ' . $lockedForm->title, 'forms', $lockedForm->id);
                $this->track($lockedForm, $logId);
            }

            $lockedForm->save();

            if ($fieldData !== null) {
                $this->saveFieldData($fieldData, $lockedForm);
            }
        });

        return redirect()
            ->route('forms.edit', $form)
            ->with('success', 'Form successfully updated.');
    }

    public function duplicate(FormDuplicateRequest $request, Form $form)
    {
        $this->authorize('create', Form::class);

        DB::transaction(function () use ($request, $form, &$duplicatedForm) {
            $sourceForm = Form::with(['fields' => function ($query) {
                $query->orderBy('row_number')
                    ->orderBy('order')
                    ->orderBy('id');
            }])->where('id', $form->id)->lockForUpdate()->firstOrFail();

            $targetAgencyId = auth()->user()->agency_id ?? $request->agency_id ?? $sourceForm->agency_id;

            $duplicatedForm = new Form();
            $duplicatedForm->agency_id = $targetAgencyId;
            $duplicatedForm->template_source_form_id = $sourceForm->id;
            $duplicatedForm->assigned_sector_id = $sourceForm->assigned_sector_id;
            $duplicatedForm->title = $request->title;
            $duplicatedForm->status = $sourceForm->status;
            $duplicatedForm->save();

            foreach ($sourceForm->fields as $sourceField) {
                $field = new FormField();
                $field->form_id = $duplicatedForm->id;
                $field->label = $sourceField->label;
                $field->subtitle = $sourceField->subtitle;
                $field->value_type = $sourceField->value_type;
                $field->options = $sourceField->options;
                $field->column_size = $sourceField->column_size;
                $field->row_number = $sourceField->row_number;
                $field->order = $sourceField->order;
                $field->is_required = $sourceField->is_required;
                $field->has_remarks = $sourceField->has_remarks;
                $field->status = $sourceField->status;
                $field->save();
            }

            $this->logSystemActivity(
                'Duplicated form template: ' . $sourceForm->title . ' to ' . $duplicatedForm->title,
                'forms',
                $duplicatedForm->id
            );
        });

        return redirect()
            ->route('forms.edit', $duplicatedForm)
            ->with('success', 'Form structure duplicated. You can now customize your copy.');
    }

    /**
     * Store a single field.
     */
    public function storeField(FormFieldRequest $request, Form $form)
    {
        $this->authorize('update', $form);

        DB::transaction(function () use ($request, $form) {
            $isSection = $request->value_type === 'section';
            $isFullRowType = in_array($request->value_type, ['section', 'repeating_group'], true);

            $field = new FormField();
            $field->form_id = $form->id;
            $field->label = $request->label;
            $field->subtitle = $request->subtitle;
            $field->value_type = $request->value_type;
            $field->options = $this->normalizeRepeatingGroupOptions($request->value_type, $request->input('options'));
            $field->column_size = $isFullRowType ? 12 : ((int) $request->input('column_size', 4) === 12 ? 12 : 4);
            $field->row_number = $request->row_number;
            $field->order = $request->order ?? 0;
            $field->is_required = $isSection ? 0 : $request->is_required;
            $field->has_remarks = $isSection ? 0 : $request->has_remarks;
            $field->status = $request->status;
            $field->save();

            $this->logSystemActivity('Added form field: ' . $field->label . ' (' . $form->title . ')', 'form_fields', $field->id);

            $this->syncColumnSizes($form->id);
        });

        return redirect()
            ->route('forms.edit', $form)
            ->with('success', 'Field successfully saved.');
    }

    /**
     * Update a single field.
     */
    public function updateField(FormFieldRequest $request, Form $form, FormField $form_field)
    {
        $this->authorize('update', $form);

        abort_if($form_field->form_id !== $form->id, 404);

        DB::transaction(function () use ($request, $form, $form_field) {
            $lockedField = FormField::where('id', $form_field->id)->lockForUpdate()->firstOrFail();

            $isSection = $request->value_type === 'section';
            $isFullRowType = in_array($request->value_type, ['section', 'repeating_group'], true);

            $lockedField->label = $request->label;
            $lockedField->subtitle = $request->subtitle;
            $lockedField->value_type = $request->value_type;
            $lockedField->options = $this->normalizeRepeatingGroupOptions($request->value_type, $request->input('options'));
            $lockedField->column_size = $isFullRowType ? 12 : ((int) $request->input('column_size', 4) === 12 ? 12 : 4);
            $lockedField->row_number = $request->row_number;
            $lockedField->order = $request->order ?? 0;
            $lockedField->is_required = $isSection ? 0 : $request->is_required;
            $lockedField->has_remarks = $isSection ? 0 : $request->has_remarks;
            $lockedField->status = $request->status;

            if ($lockedField->isDirty()) {
                $logId = $this->logSystemActivity('Updated form field: ' . $lockedField->label . ' (' . $form->title . ')', 'form_fields', $lockedField->id);
                $this->track($lockedField, $logId);
            }

            $lockedField->save();

            $this->syncColumnSizes($form->id);
        });

        return redirect()
            ->route('forms.edit', $form)
            ->with('success', 'Field successfully updated.');
    }

    /**
     * Update fields only.
     */
    public function updateFields(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $data = $this->validatedFieldData($request, $form, true);

        DB::transaction(function () use ($data, $form) {
            $this->saveFieldData($data, $form);
            $this->logSystemActivity('Updated form fields: ' . $form->title, 'forms', $form->id);
        });

        return redirect()
            ->route('forms.edit', $form)
            ->with('success', 'Fields successfully updated.');
    }

    /**
     * Validate existing and newly added builder fields.
     */
    private function validatedFieldData(Request $request, Form $form, bool $required = false): ?array
    {
        if (!$request->has('fields') && !$request->has('new_fields') && !$required) {
            return null;
        }

        $rules = [
            'fields' => [$required ? 'required' : 'nullable', 'array'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.subtitle' => ['nullable', 'string', 'max:1000'],
            'fields.*.value_type' => ['required', 'in:section,integer,decimal,text,date,date_range,repeating_group'],
            'fields.*.options' => ['nullable', 'string', 'max:1048576'],
            'fields.*.column_size' => ['required', 'integer', 'in:4,12'],
            'fields.*.row_number' => ['required', 'integer', 'min:1'],
            'fields.*.order' => ['required', 'integer', 'min:0'],
            'fields.*.is_required' => ['required', 'in:0,1'],
            'fields.*.has_remarks' => ['required', 'in:0,1'],
            'fields.*.status' => ['required', 'in:0,1'],

            'new_fields' => ['nullable', 'array'],
            'new_fields.*.label' => ['required', 'string', 'max:255'],
            'new_fields.*.subtitle' => ['nullable', 'string', 'max:1000'],
            'new_fields.*.value_type' => ['required', 'in:section,integer,decimal,text,date,date_range,repeating_group'],
            'new_fields.*.options' => ['nullable', 'string', 'max:1048576'],
            'new_fields.*.column_size' => ['required', 'integer', 'in:4,12'],
            'new_fields.*.row_number' => ['required', 'integer', 'min:1'],
            'new_fields.*.order' => ['required', 'integer', 'min:0'],
            'new_fields.*.is_required' => ['required', 'in:0,1'],
            'new_fields.*.has_remarks' => ['required', 'in:0,1'],
            'new_fields.*.status' => ['required', 'in:0,1'],
        ];

        $data = $request->validate($rules);

        $data['fields'] = $data['fields'] ?? [];
        $data['new_fields'] = $data['new_fields'] ?? [];

        $formFieldIds = FormField::where('form_id', $form->id)
            ->pluck('id')
            ->map(fn ($id) => (string) $id);

        foreach (array_keys($data['fields']) as $fieldId) {
            abort_if(!$formFieldIds->contains((string) $fieldId), 404);
        }

        return $this->normalizeFieldDataLayout($data);
    }

    /**
     * Save existing and newly added builder fields.
     */
    private function saveFieldData(array $data, Form $form): void
    {
        foreach ($data['fields'] ?? [] as $fieldId => $fieldData) {
            $field = FormField::where('form_id', $form->id)
                ->where('id', $fieldId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->fillField($field, $fieldData);
            $field->save();
        }

        $createdNewFields = [];

        foreach ($data['new_fields'] ?? [] as $fieldData) {
            $dedupeKey = $this->fieldDataKey($fieldData);

            if (isset($createdNewFields[$dedupeKey])) {
                continue;
            }

            $createdNewFields[$dedupeKey] = true;

            $field = $this->matchingNewField($form, $fieldData) ?? new FormField();
            $field->form_id = $form->id;
            $field->column_size = $field->column_size ?? ($this->isFullRowType($fieldData['value_type']) ? 12 : (int) $fieldData['column_size']);

            $this->fillField($field, $fieldData);
            $field->save();
        }

        $this->removeDuplicateFields($form);
        $this->syncColumnSizes($form->id);
    }

    private function fieldDataKey(array $fieldData): string
    {
        return implode('|', [
            (int) $fieldData['row_number'],
            (int) $fieldData['order'],
            $this->isFullRowType($fieldData['value_type']) ? 12 : (int) $fieldData['column_size'],
            (string) $fieldData['value_type'],
            (int) ($fieldData['column_size'] ?? 12),
            strtolower(trim((string) $fieldData['label'])),
            strtolower(trim((string) ($fieldData['subtitle'] ?? ''))),
            md5(json_encode($this->normalizeRepeatingGroupOptions($fieldData['value_type'], $fieldData['options'] ?? null))),
        ]);
    }

    private function removeDuplicateFields(Form $form): void
    {
        $seen = [];

        FormField::where('form_id', $form->id)
            ->withCount('values')
            ->orderBy('row_number')
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->each(function (FormField $field) use (&$seen) {
                $key = implode('|', [
                    (int) $field->row_number,
                    (int) $field->order,
                    (int) $field->column_size,
                    (string) $field->value_type,
                    (int) ($field->column_size ?? 12),
                    strtolower(trim((string) $field->label)),
                    strtolower(trim((string) ($field->subtitle ?? ''))),
                    md5(json_encode($field->options ?? [])),
                ]);

                if (isset($seen[$key]) && (int) $field->values_count === 0) {
                    $field->delete();
                    return;
                }

                $seen[$key] = true;
            });
    }

    /**
     * Make new field saves idempotent when the same edit payload is submitted twice.
     */
    private function matchingNewField(Form $form, array $fieldData): ?FormField
    {
        $subtitle = $fieldData['subtitle'] ?? null;

        return FormField::where('form_id', $form->id)
            ->where('row_number', (int) $fieldData['row_number'])
            ->where('order', (int) $fieldData['order'])
            ->where('column_size', $this->isFullRowType($fieldData['value_type']) ? 12 : (int) $fieldData['column_size'])
            ->where('value_type', $fieldData['value_type'])
            ->where('column_size', (int) ($fieldData['column_size'] ?? 12))
            ->where('label', $fieldData['label'])
            ->where(function ($query) use ($subtitle) {
                if ($subtitle === null || $subtitle === '') {
                    $query->whereNull('subtitle')
                        ->orWhere('subtitle', '');
                    return;
                }

                $query->where('subtitle', $subtitle);
            })
            ->lockForUpdate()
            ->first();
    }

    /**
     * Fill common field data.
     */
    private function fillField(FormField $field, array $fieldData): void
    {
        $isSection = $fieldData['value_type'] === 'section';

        $field->label = $fieldData['label'];
        $field->subtitle = $fieldData['subtitle'] ?? null;
        $field->value_type = $fieldData['value_type'];
        $field->options = $this->normalizeRepeatingGroupOptions($fieldData['value_type'], $fieldData['options'] ?? null);
        $field->column_size = $this->isFullRowType($fieldData['value_type']) ? 12 : ((int) $fieldData['column_size'] === 12 ? 12 : 4);
        $field->row_number = (int) $fieldData['row_number'];
        $field->order = (int) $fieldData['order'];
        $field->is_required = $isSection ? 0 : (int) $fieldData['is_required'];
        $field->has_remarks = $isSection ? 0 : (int) $fieldData['has_remarks'];
        $field->status = (int) $fieldData['status'];
    }

    /**
     * Delete a field.
     */
    public function destroyField(Form $form, FormField $form_field)
    {
        $this->authorize('update', $form);

        abort_if($form_field->form_id !== $form->id, 404);

        DB::transaction(function () use ($form, $form_field) {
            $this->logSystemActivity('Removed form field: ' . $form_field->label . ' (' . $form->title . ')', 'form_fields', $form_field->id);

            if ($form_field->value_type === 'section') {
                FormField::where('form_id', $form->id)
                    ->where('row_number', $form_field->row_number)
                    ->where('value_type', '!=', 'section')
                    ->where('id', '!=', $form_field->id)
                    ->get()
                    ->each(function (FormField $field) {
                        $field->forceDelete();
                    });
            }

            $form_field->forceDelete();

            $this->syncColumnSizes($form->id);
        });

        return redirect()
            ->route('forms.edit', $form)
            ->with('success', 'Field successfully removed.');
    }

    /**
     * Kept for compatibility, but not used by the section-based builder.
     */
    private function rowIsFull(Form $form, int $rowNumber, ?FormField $field = null, int $status = 1): bool
    {
        return false;
    }

    /**
     * Keep full-row field types full width and regular fields to supported widths.
     */
    private function syncColumnSizes(int $formId): void
    {
        FormField::where('form_id', $formId)
            ->whereIn('value_type', ['section', 'repeating_group'])
            ->update([
                'column_size' => 12,
            ]);

        FormField::where('form_id', $formId)
            ->whereNotIn('value_type', ['section', 'repeating_group'])
            ->whereNotIn('column_size', [4, 12])
            ->update([
                'column_size' => 4,
            ]);
    }

    private function normalizeFieldDataLayout(array $data): array
    {
        foreach (['fields', 'new_fields'] as $group) {
            foreach ($data[$group] ?? [] as $key => $fieldData) {
                $isSection = $fieldData['value_type'] === 'section';
                $isFullRow = $this->isFullRowType($fieldData['value_type']) || (int) $fieldData['column_size'] === 12;

                $data[$group][$key]['column_size'] = $isFullRow ? 12 : 4;
                $data[$group][$key]['options'] = json_encode(
                    $this->normalizeRepeatingGroupOptions($fieldData['value_type'], $fieldData['options'] ?? null)
                );

                if ($isSection) {
                    $data[$group][$key]['order'] = 0;
                    $data[$group][$key]['is_required'] = 0;
                    $data[$group][$key]['has_remarks'] = 0;
                }
            }
        }

        $sectionFields = [];

        foreach (['fields', 'new_fields'] as $group) {
            foreach ($data[$group] ?? [] as $key => $fieldData) {
                if ($fieldData['value_type'] === 'section') {
                    continue;
                }

                $sectionFields[(int) $fieldData['row_number']][] = [
                    'group' => $group,
                    'key' => $key,
                    'order' => max(1, (int) $fieldData['order']),
                    'column_size' => (int) $fieldData['column_size'],
                ];
            }
        }

        foreach ($sectionFields as $items) {
            usort($items, function ($a, $b) {
                if ($a['order'] !== $b['order']) {
                    return $a['order'] <=> $b['order'];
                }

                return (string) $a['key'] <=> (string) $b['key'];
            });

            $usedOrders = [];

            foreach ($items as $item) {
                $order = max(1, (int) $item['order']);
                $columnSize = (int) $data[$item['group']][$item['key']]['column_size'];

                if ($columnSize === 12) {
                    $slot = $this->fieldSlot($order);
                    $order = $this->slotOrder($slot['row'], 1);
                }

                while ($this->coveredOrdersTaken($order, $columnSize, $usedOrders)) {
                    $order++;

                    if ($columnSize === 12) {
                        $slot = $this->fieldSlot($order);
                        $order = $this->slotOrder($slot['row'] + 1, 1);
                    }
                }

                $data[$item['group']][$item['key']]['order'] = $order;

                foreach ($this->coveredOrders($order, $columnSize) as $coveredOrder) {
                    $usedOrders[$coveredOrder] = true;
                }
            }
        }

        return $data;
    }

    private function isFullRowType(string $valueType): bool
    {
        return in_array($valueType, ['section', 'repeating_group'], true);
    }

    private function normalizeRepeatingGroupOptions(string $valueType, mixed $options): array
    {
        if ($valueType !== 'repeating_group') {
            return [];
        }

        if (is_string($options)) {
            $decodedOptions = json_decode($options, true);
            $options = is_array($decodedOptions) ? $decodedOptions : [];
        }

        if (!is_array($options)) {
            $options = [];
        }

        $columns = $options['columns'] ?? $options;

        if (!is_array($columns)) {
            $columns = [];
        }

        $normalizedColumns = [];
        $seenIds = [];

        foreach ($columns as $index => $column) {
            if (!is_array($column)) {
                continue;
            }

            $label = trim((string) ($column['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $id = preg_replace('/[^A-Za-z0-9_\\-]/', '', (string) ($column['id'] ?? ''));

            if ($id === '' || isset($seenIds[$id])) {
                $id = 'col_' . ($index + 1);
            }

            while (isset($seenIds[$id])) {
                $id .= '_1';
            }

            $seenIds[$id] = true;
            $columnType = $this->normalizeRepeatingGroupColumnType($column['type'] ?? null);

            $normalizedColumns[] = [
                'id' => $id,
                'label' => mb_substr($label, 0, 100),
                'type' => $columnType,
                'source' => $columnType === 'select'
                    ? $this->normalizeRepeatingGroupColumnSource($column['source'] ?? null)
                    : null,
            ];
        }

        if (empty($normalizedColumns)) {
            $normalizedColumns[] = [
                'id' => 'col_1',
                'label' => 'Column 1',
                'type' => 'text',
                'source' => null,
            ];
        }

        return [
            'columns' => array_slice($normalizedColumns, 0, 12),
        ];
    }

    private function normalizeRepeatingGroupColumnSource(mixed $source): ?string
    {
        $source = (string) $source;

        return in_array($source, ['user_name', 'designation', 'status'], true)
            ? $source
            : null;
    }

    private function normalizeRepeatingGroupColumnType(mixed $type): string
    {
        $type = (string) $type;

        return in_array($type, ['text', 'select', 'date'], true) ? $type : 'text';
    }

    private function slotOrder(int $row, int $column): int
    {
        return ((max(1, $row) - 1) * 3) + max(1, $column);
    }

    private function fieldSlot(int $order): array
    {
        $order = max(1, $order);

        return [
            'row' => (int) floor(($order - 1) / 3) + 1,
            'column' => (($order - 1) % 3) + 1,
        ];
    }

    private function coveredOrders(int $order, int $columnSize): array
    {
        $slot = $this->fieldSlot($order);

        if ($columnSize === 12) {
            return array_map(
                fn ($column) => $this->slotOrder($slot['row'], $column),
                [1, 2, 3]
            );
        }

        return [$order];
    }

    private function coveredOrdersTaken(int $order, int $columnSize, array $usedOrders): bool
    {
        foreach ($this->coveredOrders($order, $columnSize) as $coveredOrder) {
            if (isset($usedOrders[$coveredOrder])) {
                return true;
            }
        }

        return false;
    }

    private function visibleFormsQuery()
    {
        $depDevIds = Agency::depDevIds();

        return Form::query()
            ->when(!auth()->user()->isSuperAdmin(), function ($query) use ($depDevIds) {
                $query->where(function ($query) use ($depDevIds) {
                    $query->where('agency_id', auth()->user()->agency_id)
                        ->orWhere(function ($query) use ($depDevIds) {
                            $query->where('status', 1)
                                ->whereIn('agency_id', $depDevIds);
                        });
                });
            })
            ->orderBy('agency_id')
            ->orderBy('title');
    }

    public function searchAgencies(Request $request)
    {
        $this->authorize('viewAny', Form::class);

        $search = trim((string) $request->get('q', ''));
        $page = max((int) $request->get('page', 1), 1);
        $perPage = 20;

        $query = Agency::query()
            ->select([
                'id',
                'Abbreviation',
                'UACS_AGY_DSC',
                'UACS_AGY_ID',
            ]);

        if (!auth()->user()->isSuperAdmin()) {
            $query->where('id', auth()->user()->agency_id);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('Abbreviation', 'like', "%{$search}%")
                    ->orWhere('UACS_AGY_DSC', 'like', "%{$search}%")
                    ->orWhere('UACS_AGY_ID', 'like', "%{$search}%");
            });
        }

        $agencies = $query
            ->orderByRaw("CASE WHEN Abbreviation IS NULL OR Abbreviation = '' THEN 1 ELSE 0 END")
            ->orderBy('Abbreviation')
            ->orderBy('UACS_AGY_DSC')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'results' => $agencies->getCollection()->map(function ($agency) {
                $abbr = trim((string) ($agency->Abbreviation ?? ''));
                $name = trim((string) ($agency->UACS_AGY_DSC ?? ''));
                $uacs = trim((string) ($agency->UACS_AGY_ID ?? ''));

                if ($abbr !== '' && $name !== '') {
                    $label = "{$abbr} - {$name}";
                } elseif ($abbr !== '') {
                    $label = $abbr;
                } elseif ($name !== '') {
                    $label = $name;
                } else {
                    $label = "Agency #{$agency->id}";
                }

                if ($uacs !== '') {
                    $label .= " ({$uacs})";
                }

                return [
                    'id' => $agency->id,
                    'text' => $label,
                ];
            })->values(),
            'pagination' => [
                'more' => $agencies->hasMorePages(),
            ],
        ]);
    }

    private function logSystemActivity(string $activity, ?string $table = null, $referenceId = null): ?int
    {
        if (!auth()->check()) {
            return null;
        }

        return $this->addSystemLogs(
            $activity,
            auth()->id(),
            auth()->user()->email,
            request()->getClientIp(true),
            $table,
            $referenceId
        );
    }
}

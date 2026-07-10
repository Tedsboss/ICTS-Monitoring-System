<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\UpliftIndicator;
use App\Models\UpliftMeasure;
use App\Models\UpliftPillar;
use App\Models\UpliftPillarField;
use App\Models\UpliftSubmission;
use App\Models\Staff;
use App\Traits\GenerateLogs;
use App\Traits\TracksHistoryTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpliftFormBuilderController extends Controller
{
  use GenerateLogs;
  use TracksHistoryTrait;

  private const PREDEFINED_TEMPLATE_KEY = 'uplift_measure_progress_tracking';

  public function index()
  {
    $this->authorize('viewAny', UpliftPillar::class);

    $pillars = UpliftPillar::withCount('measures')->orderBy('title')->get();
    $measures = UpliftMeasure::with(['pillar', 'leadAgency', 'supportingAgencies', 'assignedSector'])
      ->withCount(['fields', 'fields as indicators_count' => function ($query) {
        $query->join('uplift_indicators', 'uplift_pillar_fields.id', '=', 'uplift_indicators.uplift_pillar_field_id');
      }])
      ->orderBy('uplift_pillar_id')
      ->orderBy('title')
      ->get();
    $agencies = Agency::orderBy('UACS_AGY_DSC')->get();
    $sectors = Staff::orderBy('office_id')->orderBy('name')->get();
    $predefinedTemplate = $this->predefinedTemplateViewData();

    return view('uplift-builder.index', compact('pillars', 'measures', 'agencies', 'sectors', 'predefinedTemplate'));
  }

  public function storePillar(Request $request)
  {
    $this->authorize('create', UpliftPillar::class);

    $data = $request->validate([
      'title' => ['required', 'string', 'max:255', 'unique:uplift_pillars,title'],
      'status' => ['required', 'in:0,1'],
    ]);

    $pillar = UpliftPillar::create($data);
    $this->logSystemActivity('Created UPLIFT pillar: ' . $pillar->title, 'uplift_pillars', $pillar->id);

    return redirect()->route('uplift-builder.index')->with('succes', 'UPLIFT pillar succesfully saved');
  }

  public function updatePillar(Request $request, UpliftPillar $uplift_pillar)
  {
    $this->authorize('update', $uplift_pillar);

    $data = $request->validate([
      'title' => ['required', 'string', 'max:255', Rule::unique('uplift_pillars', 'title')->ignore($uplift_pillar->id)],
      'status' => ['required', 'in:0,1'],
    ]);

    foreach ($data as $key => $value) {
      $uplift_pillar->{$key} = $value;
    }

    if ($uplift_pillar->isDirty()) {
      $logId = $this->logSystemActivity('Updated UPLIFT pillar: ' . $uplift_pillar->title, 'uplift_pillars', $uplift_pillar->id);
      $this->track($uplift_pillar, $logId);
    }

    $uplift_pillar->save();

    return redirect()->route('uplift-builder.index')->with('succes', 'UPLIFT pillar succesfully updated');
  }

  public function storeMeasure(Request $request)
  {
    $this->authorize('create', UpliftPillar::class);

    $template = $this->predefinedTemplateFromRequest($request);
    $data = $this->measureData($request);

    DB::transaction(function () use ($data, $template, &$measure) {
      $measure = UpliftMeasure::create($data);

      if ($template != null) {
        $this->copyMeasureTemplateFields($template, $measure);
      }

      $this->logSystemActivity(
        'Created UPLIFT measure: ' . $measure->title . ($template != null ? ' from predefined template: ' . $template['name'] : ''),
        'uplift_measures',
        $measure->id
      );
    });

    return redirect()->route('uplift-builder.edit', $measure)->with('succes', 'UPLIFT measure succesfully saved');
  }

  public function edit(UpliftMeasure $uplift_measure)
  {
    $this->authorize('viewAny', UpliftPillar::class);

    $uplift_measure->load([
      'pillar',
      'leadAgency',
      'assignedSector',
      'supportingAgencies',
      'fields.parent',
      'fields.children',
      'fields.indicators',
    ]);

    $pillars = UpliftPillar::orderBy('title')->get();
    $agencies = Agency::orderBy('UACS_AGY_DSC')->get();
    $sectors = Staff::orderBy('office_id')->orderBy('name')->get();
    $fields = $uplift_measure->fields;
    $sections = UpliftPillarField::query()
      ->whereNotNull('section')
      ->where('section', '!=', '')
      ->distinct()
      ->orderBy('section')
      ->pluck('section');

    return view('uplift-builder.edit', [
      'measure' => $uplift_measure,
      'pillars' => $pillars,
      'agencies' => $agencies,
      'sectors' => $sectors,
      'fields' => $fields,
      'sections' => $sections,
    ]);
  }

  public function preview(UpliftMeasure $uplift_measure)
  {
    $this->authorize('viewAny', UpliftPillar::class);

    $uplift_measure->load($this->previewRelations());

    return view('uplift-builder.preview', [
      'measure' => $uplift_measure,
      'submission' => new UpliftSubmission([
        'uplift_measure_id' => $uplift_measure->id,
        'agency_id' => auth()->user()->agency_id,
        'status' => 'draft',
      ]),
      'fieldValues' => collect(),
      'indicatorValues' => collect(),
    ]);
  }

  public function updateMeasure(Request $request, UpliftMeasure $uplift_measure)
  {
    $this->authorize('update', $uplift_measure->pillar);

    $data = $this->measureData($request, $uplift_measure);
    $builderFieldData = $this->builderFieldData($request, $uplift_measure);

    DB::transaction(function () use ($data, $builderFieldData, $uplift_measure) {
      foreach ($data as $key => $value) {
        $uplift_measure->{$key} = $value;
      }

      if ($uplift_measure->isDirty()) {
        $logId = $this->logSystemActivity('Updated UPLIFT measure: ' . $uplift_measure->title, 'uplift_measures', $uplift_measure->id);
        $this->track($uplift_measure, $logId);
      }

      $uplift_measure->save();

      if ($builderFieldData !== null) {
        $this->saveBuilderFieldData($uplift_measure, $builderFieldData);
      }
    });

    return redirect()->route('uplift-builder.edit', $uplift_measure)->with('succes', 'UPLIFT measure succesfully updated');
  }

  public function duplicateMeasure(UpliftMeasure $uplift_measure)
  {
    $this->authorize('create', UpliftPillar::class);
    $this->authorize('viewAny', UpliftPillar::class);

    $uplift_measure->load(['supportingAgencies', 'fields.indicators']);

    DB::transaction(function () use ($uplift_measure, &$copy) {
      $copy = $uplift_measure->replicate();
      $copy->title = $this->uniqueMeasureTitle($uplift_measure);
      $copy->save();

      $copy->supportingAgencies()->sync($uplift_measure->supportingAgencies->pluck('id')->all());

      $fieldIdMap = [];

      foreach ($uplift_measure->fields->whereNull('parent_id')->sortBy([['row_number', 'asc'], ['order', 'asc'], ['id', 'asc']]) as $field) {
        $this->duplicateFieldTree($field, $copy, $fieldIdMap);
      }

      $this->logSystemActivity('Duplicated UPLIFT measure: ' . $uplift_measure->title . ' to ' . $copy->title, 'uplift_measures', $copy->id);
    });

    return redirect()->route('uplift-builder.edit', $copy)->with('succes', 'UPLIFT measure duplicated. You can now customize your copy.');
  }

  public function updateFieldsOrder(Request $request, UpliftMeasure $uplift_measure)
  {
    $this->authorize('update', $uplift_measure->pillar);

    $data = $request->validate([
      'fields' => ['required', 'array'],
      'fields.*.id' => ['required', 'integer', Rule::exists('uplift_pillar_fields', 'id')->where('uplift_measure_id', $uplift_measure->id)],
      'fields.*.section' => ['nullable', 'string', 'max:255'],
      'fields.*.row_number' => ['required', 'integer', 'min:1'],
      'fields.*.order' => ['required', 'integer', 'min:1'],
    ]);

    DB::transaction(function () use ($data, $uplift_measure) {
      foreach ($data['fields'] as $fieldData) {
        UpliftPillarField::where('uplift_measure_id', $uplift_measure->id)
          ->where('id', $fieldData['id'])
          ->whereNull('parent_id')
          ->update([
            'section' => $fieldData['section'] ?? null,
            'row_number' => $fieldData['row_number'],
            'order' => $fieldData['order'],
          ]);
      }

      $this->syncColumnSizes($uplift_measure);
      $this->logSystemActivity('Updated UPLIFT field layout: ' . $uplift_measure->title, 'uplift_measures', $uplift_measure->id);
    });

    return redirect()->route('uplift-builder.edit', $uplift_measure)->with('succes', 'UPLIFT field layout succesfully saved');
  }

  public function storeSupportingAgency(Request $request, UpliftMeasure $uplift_measure)
  {
    $this->authorize('update', $uplift_measure->pillar);

    $data = $request->validate([
      'agency_id' => ['required', 'integer', 'exists:agencies,id'],
    ]);

    DB::table('uplift_measure_supporting_agencies')->updateOrInsert(
      [
        'uplift_measure_id' => $uplift_measure->id,
        'agency_id' => $data['agency_id'],
      ],
      [
        'created_at' => now(),
        'updated_at' => now(),
      ]
    );
    $this->logSystemActivity('Added UPLIFT supporting agency to measure: ' . $uplift_measure->title, 'uplift_measures', $uplift_measure->id);

    return redirect()->route('uplift-builder.edit', $uplift_measure)->with('succes', 'Supporting agency succesfully added');
  }

  public function destroySupportingAgency(UpliftMeasure $uplift_measure, Agency $agency)
  {
    $this->authorize('update', $uplift_measure->pillar);

    DB::table('uplift_measure_supporting_agencies')
      ->where('uplift_measure_id', $uplift_measure->id)
      ->where('agency_id', $agency->id)
      ->delete();
    $this->logSystemActivity('Removed UPLIFT supporting agency from measure: ' . $uplift_measure->title, 'uplift_measures', $uplift_measure->id);

    return redirect()->route('uplift-builder.edit', $uplift_measure)->with('succes', 'Supporting agency succesfully removed');
  }

  public function storeField(Request $request, UpliftMeasure $uplift_measure)
  {
    $this->authorize('create', UpliftPillar::class);

    $data = $this->fieldData($request, $uplift_measure);

    DB::transaction(function () use ($uplift_measure, $data) {
      $field = $uplift_measure->fields()->create($data);
      $this->logSystemActivity('Created UPLIFT field: ' . $field->label . ' (' . $uplift_measure->title . ')', 'uplift_pillar_fields', $field->id);
      $this->syncColumnSizes($uplift_measure);
    });

    return redirect()->route('uplift-builder.edit', $uplift_measure)->with('succes', 'Field succesfully saved');
  }

  public function updateField(Request $request, UpliftMeasure $uplift_measure, UpliftPillarField $uplift_pillar_field)
  {
    $this->authorize('update', $uplift_measure->pillar);
    abort_if($uplift_pillar_field->uplift_measure_id != $uplift_measure->id, 404);

    $data = $this->fieldData($request, $uplift_measure, $uplift_pillar_field);

    DB::transaction(function () use ($uplift_measure, $uplift_pillar_field, $data) {
      foreach ($data as $key => $value) {
        $uplift_pillar_field->{$key} = $value;
      }

      if ($uplift_pillar_field->isDirty()) {
        $logId = $this->logSystemActivity('Updated UPLIFT field: ' . $uplift_pillar_field->label . ' (' . $uplift_measure->title . ')', 'uplift_pillar_fields', $uplift_pillar_field->id);
        $this->track($uplift_pillar_field, $logId);
      }

      $uplift_pillar_field->save();
      $this->syncColumnSizes($uplift_measure);
    });

    return redirect()->route('uplift-builder.edit', $uplift_measure)->with('succes', 'Field succesfully updated');
  }

  public function destroyField(UpliftMeasure $uplift_measure, UpliftPillarField $uplift_pillar_field)
  {
    $this->authorize('delete', $uplift_measure->pillar);
    abort_if($uplift_pillar_field->uplift_measure_id != $uplift_measure->id, 404);

    DB::transaction(function () use ($uplift_measure, $uplift_pillar_field) {
      if ($uplift_pillar_field->value_type === 'section') {
        $sectionFields = UpliftPillarField::query()
          ->where('uplift_measure_id', $uplift_measure->id)
          ->where('value_type', '!=', 'section')
          ->where('row_number', $uplift_pillar_field->row_number)
          ->lockForUpdate()
          ->get();

        foreach ($sectionFields as $sectionField) {
          $this->logSystemActivity('Removed UPLIFT field: ' . $sectionField->label . ' (' . $uplift_measure->title . ')', 'uplift_pillar_fields', $sectionField->id);
          $sectionField->delete();
        }
      }

      $this->logSystemActivity('Removed UPLIFT field: ' . $uplift_pillar_field->label . ' (' . $uplift_measure->title . ')', 'uplift_pillar_fields', $uplift_pillar_field->id);
      $uplift_pillar_field->delete();
      $this->syncColumnSizes($uplift_measure);
    });

    return redirect()->route('uplift-builder.edit', $uplift_measure)->with('succes', 'Field succesfully removed');
  }

  public function storeIndicator(Request $request, UpliftMeasure $uplift_measure, UpliftPillarField $uplift_pillar_field)
  {
    $this->authorize('create', UpliftPillar::class);
    abort_if($uplift_pillar_field->uplift_measure_id != $uplift_measure->id, 404);

    $indicator = $uplift_pillar_field->indicators()->create($this->indicatorData($request));
    $this->logSystemActivity('Created UPLIFT indicator: ' . $indicator->label . ' (' . $uplift_measure->title . ')', 'uplift_indicators', $indicator->id);

    return redirect()->route('uplift-builder.edit', $uplift_measure)->with('succes', 'Indicator succesfully saved');
  }

  public function updateIndicator(Request $request, UpliftMeasure $uplift_measure, UpliftPillarField $uplift_pillar_field, UpliftIndicator $uplift_indicator)
  {
    $this->authorize('update', $uplift_measure->pillar);
    abort_if($uplift_pillar_field->uplift_measure_id != $uplift_measure->id || $uplift_indicator->uplift_pillar_field_id != $uplift_pillar_field->id, 404);

    foreach ($this->indicatorData($request) as $key => $value) {
      $uplift_indicator->{$key} = $value;
    }

    if ($uplift_indicator->isDirty()) {
      $logId = $this->logSystemActivity('Updated UPLIFT indicator: ' . $uplift_indicator->label . ' (' . $uplift_measure->title . ')', 'uplift_indicators', $uplift_indicator->id);
      $this->track($uplift_indicator, $logId);
    }

    $uplift_indicator->save();

    return redirect()->route('uplift-builder.edit', $uplift_measure)->with('succes', 'Indicator succesfully updated');
  }

  public function destroyIndicator(UpliftMeasure $uplift_measure, UpliftPillarField $uplift_pillar_field, UpliftIndicator $uplift_indicator)
  {
    $this->authorize('delete', $uplift_measure->pillar);
    abort_if($uplift_pillar_field->uplift_measure_id != $uplift_measure->id || $uplift_indicator->uplift_pillar_field_id != $uplift_pillar_field->id, 404);

    $this->logSystemActivity('Removed UPLIFT indicator: ' . $uplift_indicator->label . ' (' . $uplift_measure->title . ')', 'uplift_indicators', $uplift_indicator->id);
    $uplift_indicator->delete();

    return redirect()->route('uplift-builder.edit', $uplift_measure)->with('succes', 'Indicator succesfully removed');
  }

  private function measureData(Request $request, ?UpliftMeasure $measure = null): array
  {
    return $request->validate([
      'uplift_pillar_id' => ['required', 'integer', 'exists:uplift_pillars,id'],
      'title' => [
        'required',
        'string',
        'max:255',
        Rule::unique('uplift_measures', 'title')
          ->where('uplift_pillar_id', $request->input('uplift_pillar_id'))
          ->ignore($measure?->id),
      ],
      'brief_description' => ['nullable', 'string', 'max:1048576'],
      'lead_agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
      'assigned_sector_id' => ['nullable', 'integer', 'exists:staffs,id'],
      'status' => ['required', 'in:0,1'],
    ]);
  }

  private function predefinedTemplateFromRequest(Request $request): ?array
  {
    if (!$request->filled('predefined_template_key')) {
      return null;
    }

    $data = $request->validate([
      'predefined_template_key' => [
        'nullable',
        'string',
        Rule::in([self::PREDEFINED_TEMPLATE_KEY]),
      ],
    ]);

    $template = $this->predefinedTemplateDefinition();

    return ($template['key'] ?? null) === $data['predefined_template_key'] ? $template : null;
  }

  private function predefinedTemplateDefinition(): array
  {
    return config('uplift_templates.predefined', []);
  }

  private function predefinedTemplateViewData(): ?array
  {
    $template = $this->predefinedTemplateDefinition();

    if (($template['key'] ?? null) !== self::PREDEFINED_TEMPLATE_KEY) {
      return null;
    }

    $fields = collect($template['fields'] ?? []);

    return [
      'key' => $template['key'],
      'name' => $template['name'],
      'description' => $template['description'] ?? null,
      'fields_count' => $fields->count(),
      'indicators_count' => $fields->sum(fn($field) => count($field['indicators'] ?? [])),
    ];
  }

  private function previewRelations(): array
  {
    return [
      'pillar',
      'leadAgency',
      'supportingAgencies',
      'fields' => function ($query) {
        $query->where('status', 1)
          ->where('value_type', '!=', 'section')
          ->orderBy('row_number')
          ->orderBy('order')
          ->orderBy('id');
      },
      'fields.children' => function ($query) {
        $query->where('status', 1)
          ->orderBy('row_number')
          ->orderBy('order')
          ->orderBy('id');
      },
      'fields.indicators' => function ($query) {
        $query->where('status', 1)
          ->orderBy('order')
          ->orderBy('id');
      },
    ];
  }

  private function fieldData(Request $request, UpliftMeasure $measure, ?UpliftPillarField $field = null): array
  {
    $data = $request->validate([
      'parent_id' => ['nullable', 'integer', Rule::exists('uplift_pillar_fields', 'id')->where('uplift_measure_id', $measure->id)],
      'section' => ['nullable', 'string', 'max:255'],
      'label' => ['required', 'string', 'max:255'],
      'guide' => ['nullable', 'string', 'max:2000'],
      'value_type' => ['required', 'in:integer,decimal,text,date,date_range,select,boolean,repeating_group,user_picker'],
      'options_text' => ['nullable', 'string', 'max:10000'],
      'options' => ['nullable', 'string', 'max:1048576'],
      'repeating_columns_text' => ['nullable', 'string', 'max:10000'],
      'row_number' => ['required', 'integer', 'min:1'],
      'order' => ['required', 'integer', 'min:1'],
      'is_required' => ['required', 'in:0,1'],
      'has_remarks' => ['required', 'in:0,1'],
      'status' => ['required', 'in:0,1'],
    ]);

    if ($field != null && (int) ($data['parent_id'] ?? 0) == $field->id) {
      throw ValidationException::withMessages(['parent_id' => 'A field cannot be nested under itself.']);
    }

    if ($field != null && $data['parent_id'] != null && $this->isDescendantField($field, (int) $data['parent_id'])) {
      throw ValidationException::withMessages(['parent_id' => 'A field cannot be nested under one of its child questions.']);
    }

    if ($data['value_type'] === 'select') {
      $data['options'] = $this->optionLines($data['options_text'] ?? '');
    } elseif ($data['value_type'] === 'repeating_group') {
      $data['options'] = $this->normalizeRepeatingGroupOptions(
        $data['value_type'],
        !empty($data['repeating_columns_text'])
          ? ['columns' => collect($this->optionLines($data['repeating_columns_text']))->map(fn($label, $index) => [
            'id' => 'col_' . ($index + 1),
            'label' => $label,
          ])->all()]
          : ($data['options'] ?? null)
      );
      $data['column_size'] = 12;
    } else {
      $data['options'] = null;
    }

    unset($data['options_text'], $data['repeating_columns_text']);

    return $data;
  }

  private function indicatorData(Request $request): array
  {
    return $request->validate([
      'label' => ['required', 'string', 'max:255'],
      'unit' => ['nullable', 'string', 'max:100'],
      'value_type' => ['required', 'in:integer,decimal,text,date,date_range,boolean'],
      'order' => ['required', 'integer', 'min:1'],
      'is_required' => ['required', 'in:0,1'],
      'status' => ['required', 'in:0,1'],
    ]);
  }

  private function builderFieldData(Request $request, UpliftMeasure $measure): ?array
  {
    if (!$request->has('fields') && !$request->has('new_fields') && !$request->has('deleted_fields')) {
      return null;
    }

    $data = $request->validate([
      'fields' => ['nullable', 'array'],
      'fields.*.label' => ['required', 'string', 'max:255'],
      'fields.*.subtitle' => ['nullable', 'string', 'max:2000'],
      'fields.*.value_type' => ['required', 'in:section,integer,decimal,text,date,date_range,select,boolean,repeating_group,user_picker'],
      'fields.*.options' => ['nullable', 'string', 'max:1048576'],
      'fields.*.column_size' => ['required', 'integer', 'in:4,12'],
      'fields.*.row_number' => ['required', 'integer', 'min:1'],
      'fields.*.order' => ['required', 'integer', 'min:0'],
      'fields.*.is_required' => ['required', 'in:0,1'],
      'fields.*.has_remarks' => ['required', 'in:0,1'],
      'fields.*.status' => ['required', 'in:0,1'],
      'new_fields' => ['nullable', 'array'],
      'new_fields.*.label' => ['required', 'string', 'max:255'],
      'new_fields.*.subtitle' => ['nullable', 'string', 'max:2000'],
      'new_fields.*.value_type' => ['required', 'in:section,integer,decimal,text,date,date_range,select,boolean,repeating_group,user_picker'],
      'new_fields.*.options' => ['nullable', 'string', 'max:1048576'],
      'new_fields.*.column_size' => ['required', 'integer', 'in:4,12'],
      'new_fields.*.row_number' => ['required', 'integer', 'min:1'],
      'new_fields.*.order' => ['required', 'integer', 'min:0'],
      'new_fields.*.is_required' => ['required', 'in:0,1'],
      'new_fields.*.has_remarks' => ['required', 'in:0,1'],
      'new_fields.*.status' => ['required', 'in:0,1'],
      'deleted_fields' => ['nullable', 'array'],
      'deleted_fields.*' => ['integer', Rule::exists('uplift_pillar_fields', 'id')->where('uplift_measure_id', $measure->id)],
    ]);

    $data['fields'] = $data['fields'] ?? [];
    $data['new_fields'] = $data['new_fields'] ?? [];
    $data['deleted_fields'] = $data['deleted_fields'] ?? [];

    $fieldIds = UpliftPillarField::where('uplift_measure_id', $measure->id)->pluck('id')->map(fn($id) => (string) $id);

    foreach ($data['fields'] as $fieldId => $fieldData) {
      if (($fieldData['value_type'] ?? null) !== 'section') {
        abort_if(!$fieldIds->contains((string) $fieldId), 404);
      }
    }

    return $data;
  }

  private function saveBuilderFieldData(UpliftMeasure $measure, array $data): void
  {
    $deletedIds = collect($data['deleted_fields'] ?? [])
      ->map(fn($id) => (int) $id)
      ->filter()
      ->unique()
      ->values();

    if ($deletedIds->isNotEmpty()) {
      $data['fields'] = collect($data['fields'])
        ->reject(fn($_, $fieldId) => $deletedIds->contains((int) $fieldId))
        ->all();

      $deletedFields = UpliftPillarField::query()
        ->where('uplift_measure_id', $measure->id)
        ->whereIn('id', $deletedIds)
        ->lockForUpdate()
        ->get();

      $sectionRows = $deletedFields
        ->where('value_type', 'section')
        ->pluck('row_number')
        ->map(fn($rowNumber) => (int) $rowNumber)
        ->unique()
        ->values();

      if ($sectionRows->isNotEmpty()) {
        $sectionFieldIds = UpliftPillarField::query()
          ->where('uplift_measure_id', $measure->id)
          ->where('value_type', '!=', 'section')
          ->whereIn('row_number', $sectionRows)
          ->pluck('id')
          ->map(fn($id) => (int) $id);

        $deletedIds = $deletedIds->merge($sectionFieldIds)->unique()->values();
        $data['fields'] = collect($data['fields'])
          ->reject(fn($_, $fieldId) => $deletedIds->contains((int) $fieldId))
          ->all();

        $deletedFields = UpliftPillarField::query()
          ->where('uplift_measure_id', $measure->id)
          ->whereIn('id', $deletedIds)
          ->lockForUpdate()
          ->get();
      }

      foreach ($deletedFields as $deletedField) {
        $this->logSystemActivity('Removed UPLIFT field: ' . $deletedField->label . ' (' . $measure->title . ')', 'uplift_pillar_fields', $deletedField->id);
        $deletedField->delete();
      }
    }

    $sectionsByRow = collect($data['fields'])
      ->merge($data['new_fields'])
      ->filter(fn($field) => ($field['value_type'] ?? null) === 'section')
      ->mapWithKeys(fn($field) => [(int) $field['row_number'] => $field['label']])
      ->all();

    foreach ($data['fields'] as $fieldId => $fieldData) {
      $field = UpliftPillarField::where('uplift_measure_id', $measure->id)->where('id', $fieldId)->lockForUpdate()->firstOrFail();

      if (($fieldData['value_type'] ?? null) === 'section') {
        $this->fillBuilderSection($field, $fieldData);
      } else {
        $this->fillBuilderField($field, $fieldData, $sectionsByRow);
      }

      $field->save();
    }

    foreach ($data['new_fields'] as $fieldData) {
      $field = new UpliftPillarField();
      $field->uplift_measure_id = $measure->id;

      if (($fieldData['value_type'] ?? null) === 'section') {
        $this->fillBuilderSection($field, $fieldData);
      } else {
        $this->fillBuilderField($field, $fieldData, $sectionsByRow);
      }

      $field->save();
    }

    $this->syncColumnSizes($measure);
  }

  private function fillBuilderSection(UpliftPillarField $field, array $fieldData): void
  {
    $field->parent_id = null;
    $field->section = null;
    $field->label = $fieldData['label'];
    $field->guide = $fieldData['subtitle'] ?? null;
    $field->value_type = 'section';
    $field->options = null;
    $field->column_size = 12;
    $field->row_number = (int) $fieldData['row_number'];
    $field->order = 0;
    $field->is_required = 0;
    $field->has_remarks = 0;
    $field->status = (int) $fieldData['status'];
  }

  private function fillBuilderField(UpliftPillarField $field, array $fieldData, array $sectionsByRow): void
  {
    $valueType = $fieldData['value_type'];

    $field->parent_id = null;
    $field->section = $sectionsByRow[(int) $fieldData['row_number']] ?? null;
    $field->label = $fieldData['label'];
    $field->guide = $fieldData['subtitle'] ?? null;
    $field->value_type = $valueType;
    $field->column_size = $valueType === 'repeating_group' || (int) $fieldData['column_size'] === 12 ? 12 : 4;
    $field->row_number = (int) $fieldData['row_number'];
    $field->order = (int) $fieldData['order'];
    $field->is_required = (int) $fieldData['is_required'];
    $field->has_remarks = (int) $fieldData['has_remarks'];
    $field->status = (int) $fieldData['status'];

    if ($valueType === 'repeating_group') {
      $field->options = $this->normalizeRepeatingGroupOptions($valueType, $fieldData['options'] ?? null);
    } elseif ($valueType === 'select') {
      $field->options = $this->builderSelectOptions($fieldData['options'] ?? null);
    } else {
      $field->options = null;
    }
  }

  private function builderSelectOptions(?string $options): array
  {
    $decoded = json_decode((string) $options, true);

    if (!is_array($decoded)) {
      return [];
    }

    return collect($decoded)
      ->map(fn($option) => trim((string) $option))
      ->filter()
      ->unique()
      ->values()
      ->all();
  }

  private function syncColumnSizes(UpliftMeasure $measure): void
  {
    UpliftPillarField::where('uplift_measure_id', $measure->id)
      ->whereIn('value_type', ['section', 'repeating_group'])
      ->update(['column_size' => 12]);

    UpliftPillarField::where('uplift_measure_id', $measure->id)
      ->whereNotIn('value_type', ['section', 'repeating_group'])
      ->whereNotIn('column_size', [4, 12])
      ->update(['column_size' => 4]);
  }

  private function isDescendantField(UpliftPillarField $field, int $parentId): bool
  {
    $childIds = UpliftPillarField::where('parent_id', $field->id)->pluck('id');

    if ($childIds->contains($parentId)) {
      return true;
    }

    foreach ($childIds as $childId) {
      $child = UpliftPillarField::find($childId);
      if ($child != null && $this->isDescendantField($child, $parentId)) {
        return true;
      }
    }

    return false;
  }

  private function optionLines(?string $options): array
  {
    return collect(preg_split('/\r\n|\r|\n/', $options ?? ''))
      ->map(fn($option) => trim($option))
      ->filter()
      ->unique()
      ->values()
      ->all();
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

      $id = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) ($column['id'] ?? ''));

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
    return 'text';
  }

  private function uniqueMeasureTitle(UpliftMeasure $measure): string
  {
    $baseTitle = 'Copy of ' . $measure->title;
    $title = $baseTitle;
    $counter = 2;

    while (UpliftMeasure::where('uplift_pillar_id', $measure->uplift_pillar_id)->where('title', $title)->exists()) {
      $title = $baseTitle . ' ' . $counter;
      $counter++;
    }

    return $title;
  }

  private function copyMeasureTemplateFields(array $template, UpliftMeasure $measure): void
  {
    foreach ($template['fields'] ?? [] as $fieldData) {
      $field = new UpliftPillarField();
      $field->uplift_measure_id = $measure->id;
      $field->parent_id = null;
      $field->section = $fieldData['section'] ?? null;
      $field->label = $fieldData['label'];
      $field->guide = $fieldData['guide'] ?? null;
      $field->value_type = $fieldData['value_type'];
      $field->options = $fieldData['options'] ?? null;
      $field->column_size = (int) ($fieldData['column_size'] ?? 4);
      $field->row_number = (int) ($fieldData['row_number'] ?? 1);
      $field->order = (int) ($fieldData['order'] ?? 1);
      $field->is_required = (int) ($fieldData['is_required'] ?? 0);
      $field->has_remarks = (int) ($fieldData['has_remarks'] ?? 0);
      $field->status = (int) ($fieldData['status'] ?? 1);
      $field->save();

      foreach ($fieldData['indicators'] ?? [] as $indicatorData) {
        $indicator = new UpliftIndicator();
        $indicator->uplift_pillar_field_id = $field->id;
        $indicator->label = $indicatorData['label'];
        $indicator->unit = $indicatorData['unit'] ?? null;
        $indicator->value_type = $indicatorData['value_type'] ?? 'decimal';
        $indicator->order = (int) ($indicatorData['order'] ?? 1);
        $indicator->is_required = (int) ($indicatorData['is_required'] ?? 0);
        $indicator->status = (int) ($indicatorData['status'] ?? 1);
        $indicator->save();
      }
    }

    $this->syncColumnSizes($measure);
  }

  private function duplicateFieldTree(UpliftPillarField $sourceField, UpliftMeasure $targetMeasure, array &$fieldIdMap, ?int $parentId = null): UpliftPillarField
  {
    $field = $sourceField->replicate();
    $field->uplift_measure_id = $targetMeasure->id;
    $field->parent_id = $parentId;
    $field->save();

    $fieldIdMap[$sourceField->id] = $field->id;

    foreach ($sourceField->indicators as $sourceIndicator) {
      $indicator = $sourceIndicator->replicate();
      $indicator->uplift_pillar_field_id = $field->id;
      $indicator->save();
    }

    $sourceField->children()->with(['children.indicators', 'indicators'])->get()->each(function ($child) use ($targetMeasure, &$fieldIdMap, $field) {
      $this->duplicateFieldTree($child, $targetMeasure, $fieldIdMap, $field->id);
    });

    return $field;
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

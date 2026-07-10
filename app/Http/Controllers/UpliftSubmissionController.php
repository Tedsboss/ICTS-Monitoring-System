<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpliftSubmissionRequest;
use App\Models\UpliftMeasure;
use App\Models\UpliftPillarField;
use App\Models\UpliftSubmission;
use App\Models\SubmissionApprovalHistory;
use App\Models\SubmissionNotification;
use App\Models\User;
use App\Traits\GenerateLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpliftSubmissionController extends Controller
{
  use GenerateLogs;

  public function index()
  {
    $this->authorize('viewAny', UpliftSubmission::class);

    $canViewAll = $this->canViewAllData();
    $submissions = UpliftSubmission::with(['measure.pillar', 'agency', 'user'])
      ->when(!$canViewAll, function ($query) {
        $query->where(function ($query) {
          $query->where('agency_id', auth()->user()->agency_id);

          if (auth()->user()->isDepDevStaff()) {
            $query->orWhereHas('measure', function ($query) {
              $query->where('assigned_sector_id', auth()->user()->staff_id);
            });
          }
        });
      })
      ->orderBy('reporting_cutoff_date', 'desc')
      ->orderBy('created_at', 'desc')
      ->get();

    $measures = $this->availableMeasuresForUser()->get();

    return view('uplift-submissions.index', compact('submissions', 'measures', 'canViewAll'));
  }

  public function create(UpliftSubmissionRequest $request)
  {
    $measure = $this->availableMeasuresForUser()
      ->with($this->formDefinitionRelations())
      ->find($request->integer('measure_id'));

    if ($measure == null) {
      return redirect()->route('uplift-submissions.index')->with('error', 'Select an available UPLIFT measure for your agency.');
    }

    $submission = new UpliftSubmission([
      'uplift_measure_id' => $measure->id,
      'agency_id' => auth()->user()->agency_id,
      'status' => 'draft',
    ]);
    $fieldValues = collect();
    $indicatorValues = collect();

    return view('uplift-submissions.create', compact('measure', 'submission', 'fieldValues', 'indicatorValues'));
  }

  public function store(UpliftSubmissionRequest $request)
  {
    $measure = $this->availableMeasuresForUser()
      ->with($this->formDefinitionRelations())
      ->findOrFail($request->integer('uplift_measure_id'));
    $data = $request->validated();

    DB::transaction(function () use ($data, $measure, &$submission) {
      $submission = UpliftSubmission::create([
        'uplift_measure_id' => $measure->id,
        'agency_id' => auth()->user()->agency_id,
        'user_id' => auth()->id(),
        'reporting_cutoff_date' => $data['reporting_cutoff_date'],
        'status' => 'draft',
      ]);

      $this->syncValues($submission, $measure, $data);
    });

    return redirect()->route('uplift-submissions.edit', $submission)->with('succes', 'UPLIFT submission succesfully saved as draft');
  }

  public function show(UpliftSubmission $uplift_submission)
  {
    $this->guardView($uplift_submission);
    $this->loadSubmission($uplift_submission);

    return view('uplift-submissions.show', [
      'measure' => $uplift_submission->measure,
      'submission' => $uplift_submission,
      'fieldValues' => $uplift_submission->fieldValues->keyBy('uplift_pillar_field_id'),
      'indicatorValues' => $uplift_submission->indicatorValues->keyBy('uplift_indicator_id'),
    ]);
  }

  public function edit(UpliftSubmission $uplift_submission)
  {
    $this->guardChange($uplift_submission);
    $this->loadSubmission($uplift_submission);

    return view('uplift-submissions.edit', [
      'measure' => $uplift_submission->measure,
      'submission' => $uplift_submission,
      'fieldValues' => $uplift_submission->fieldValues->keyBy('uplift_pillar_field_id'),
      'indicatorValues' => $uplift_submission->indicatorValues->keyBy('uplift_indicator_id'),
    ]);
  }

  public function update(UpliftSubmissionRequest $request, UpliftSubmission $uplift_submission)
  {
    $this->guardChange($uplift_submission);
    $uplift_submission->load(['measure' => fn($query) => $query->with($this->formDefinitionRelations())]);
    $data = $request->validated();

    DB::transaction(function () use ($data, &$uplift_submission) {
      $uplift_submission = UpliftSubmission::where('id', $uplift_submission->id)->lockForUpdate()->first();
      $uplift_submission->reporting_cutoff_date = $data['reporting_cutoff_date'];
      $uplift_submission->save();

      $measure = $uplift_submission->measure()->with($this->formDefinitionRelations())->first();
      $this->syncValues($uplift_submission, $measure, $data);
    });

    return redirect()->route('uplift-submissions.edit', $uplift_submission)->with('succes', 'UPLIFT submission succesfully updated');
  }

  public function submit(UpliftSubmissionRequest $request, UpliftSubmission $uplift_submission)
  {
    $this->guardChange($uplift_submission);
    $wasReturned = $uplift_submission->status === 'returned';

    DB::transaction(function () use (&$uplift_submission) {
      $uplift_submission = UpliftSubmission::where('id', $uplift_submission->id)->lockForUpdate()->first();
      $uplift_submission->status = 'submitted';
      $uplift_submission->submitted_at = Carbon::now();
      $uplift_submission->approved_by = null;
      $uplift_submission->approved_at = null;
      $uplift_submission->returned_by = null;
      $uplift_submission->returned_at = null;
      $uplift_submission->rejected_by = null;
      $uplift_submission->rejected_at = null;
      $uplift_submission->approval_remarks = null;
      $uplift_submission->save();
    });

    $uplift_submission->loadMissing('measure');
    $this->createSubmissionNotification($uplift_submission, $wasReturned);
    $this->logSystemActivity('Submitted UPLIFT submission: ' . optional($uplift_submission->measure)->title, 'uplift_submissions', $uplift_submission->id);

    return redirect()->route('uplift-submissions.show', $uplift_submission)->with('succes', 'UPLIFT submission succesfully submitted');
  }

  public function approve(Request $request, UpliftSubmission $uplift_submission)
  {
    $this->authorize('approve', $uplift_submission);

    $this->applyApprovalAction($request, $uplift_submission, 'approved');

    return redirect()->route('uplift-submissions.show', $uplift_submission)->with('succes', 'UPLIFT submission successfully approved');
  }

  public function return(Request $request, UpliftSubmission $uplift_submission)
  {
    $this->authorize('return', $uplift_submission);

    $this->applyApprovalAction($request, $uplift_submission, 'returned');

    return redirect()->route('uplift-submissions.show', $uplift_submission)->with('succes', 'UPLIFT submission successfully returned');
  }

  public function reject(Request $request, UpliftSubmission $uplift_submission)
  {
    $this->authorize('reject', $uplift_submission);

    $this->applyApprovalAction($request, $uplift_submission, 'rejected');

    return redirect()->route('uplift-submissions.show', $uplift_submission)->with('succes', 'UPLIFT submission successfully rejected');
  }

  private function availableMeasuresForUser()
  {
    return UpliftMeasure::with(['pillar', 'leadAgency'])
      ->where('status', 1)
      ->whereHas('pillar', function ($query) {
        $query->where('status', 1);
      })
      ->when(!$this->canViewAllData(), function ($query) {
        $agencyId = auth()->user()->agency_id;
        $query->where(function ($query) use ($agencyId) {
          $query->where('lead_agency_id', $agencyId)
            ->orWhereHas('supportingAgencies', function ($query) use ($agencyId) {
              $query->where('agencies.id', $agencyId);
            });
        });
      })
      ->orderBy('uplift_pillar_id')
      ->orderBy('title');
  }

  private function canViewAllData(): bool
  {
    return auth()->user()->isSuperAdmin()
      || auth()->user()->role_id === 29;
  }

  private function syncValues(UpliftSubmission $submission, UpliftMeasure $measure, array $data): void
  {
    $fieldInputs = $data['fields'] ?? request()->input('fields', []);
    $indicatorInputs = $data['indicators'] ?? request()->input('indicators', []);

    foreach ($this->visibleFields($measure) as $field) {
      $submission->fieldValues()->updateOrCreate(
        ['uplift_pillar_field_id' => $field->id],
        $this->valueAttributes($field->value_type, $fieldInputs[$field->id] ?? [], true, $field, $submission)
      );

      foreach ($field->indicators->where('status', 1) as $indicator) {
        $submission->indicatorValues()->updateOrCreate(
          ['uplift_indicator_id' => $indicator->id],
          $this->valueAttributes($indicator->value_type, $indicatorInputs[$indicator->id] ?? [])
        );
      }
    }
  }

  private function valueAttributes(string $valueType, array $value, bool $withRemarks = false, ?UpliftPillarField $field = null, ?UpliftSubmission $submission = null): array
  {
    $attributes = [
      'integer_value' => null,
      'decimal_value' => null,
      'text_value' => null,
      'date_value' => null,
      'date_start_value' => null,
      'date_end_value' => null,
    ];

    if ($withRemarks) {
      $attributes['remarks'] = $value['remarks'] ?? null;
    }

    if ($valueType == 'decimal') {
      $attributes['decimal_value'] = $value['value'] ?? null;
    } elseif (in_array($valueType, ['text', 'select', 'boolean'])) {
      $attributes['text_value'] = $value['value'] ?? null;
    } elseif ($valueType == 'user_picker') {
      $attributes['text_value'] = $this->normalizeUserPickerValue($value['value'] ?? null, $submission?->agency_id);
    } elseif ($valueType == 'repeating_group') {
      $attributes['text_value'] = $field != null
        ? $this->normalizeRepeatingGroupValue($field, $value['value'] ?? null)
        : null;
    } elseif ($valueType == 'date') {
      $attributes['date_value'] = $value['value'] ?? null;
    } elseif ($valueType == 'date_range') {
      $attributes['date_start_value'] = $value['start_date'] ?? null;
      $attributes['date_end_value'] = $value['end_date'] ?? null;
    } else {
      $attributes['integer_value'] = $value['value'] ?? null;
    }

    return $attributes;
  }


  private function normalizeUserPickerValue(mixed $value, ?int $agencyId): ?string
  {
    if ($value == null || trim((string) $value) === '') {
      return null;
    }

    $payload = json_decode((string) $value, true);

    if (!is_array($payload) || empty($payload['user_id'])) {
      return null;
    }

    $user = User::query()
      ->with('position')
      ->where('id', (int) $payload['user_id'])
      ->when($agencyId != null, fn($query) => $query->where('agency_id', $agencyId))
      ->first();

    if ($user == null) {
      return null;
    }

    return json_encode([
      'user_id' => $user->id,
      'name' => trim((string) $user->full_name),
      'designation' => optional($user->position)->name,
    ], JSON_UNESCAPED_UNICODE);
  }

  private function normalizeRepeatingGroupValue(UpliftPillarField $field, mixed $value): ?string
  {
    if ($value == null || trim((string) $value) === '') {
      return null;
    }

    $rows = json_decode((string) $value, true);

    if (!is_array($rows)) {
      return null;
    }

    $columns = $this->repeatingGroupColumns($field);
    $columnIds = collect($columns)->pluck('id')->all();
    $normalizedRows = [];

    foreach ($rows as $row) {
      if (!is_array($row)) {
        continue;
      }

      $normalizedRow = [];
      $hasValue = false;

      foreach ($columnIds as $columnId) {
        $cellValue = trim((string) ($row[$columnId] ?? ''));
        $normalizedRow[$columnId] = $cellValue;

        if ($cellValue !== '') {
          $hasValue = true;
        }
      }

      if ($hasValue) {
        $normalizedRows[] = $normalizedRow;
      }
    }

    return !empty($normalizedRows) ? json_encode($normalizedRows) : null;
  }

  private function repeatingGroupColumns(UpliftPillarField $field): array
  {
    $options = is_array($field->options) ? $field->options : [];
    $columns = is_array($options['columns'] ?? null) ? $options['columns'] : [];

    $normalizedColumns = collect($columns)
      ->map(function ($column, $index) {
        $type = is_array($column) && in_array(($column['type'] ?? null), ['select', 'date'], true) ? $column['type'] : 'text';

        return [
          'id' => is_array($column) ? (string) ($column['id'] ?? ('col_' . ($index + 1))) : 'col_' . ($index + 1),
          'label' => is_array($column) ? (string) ($column['label'] ?? ('Column ' . ($index + 1))) : 'Column ' . ($index + 1),
          'type' => $type,
          'source' => $type === 'select' && is_array($column) ? ($column['source'] ?? null) : null,
        ];
      })
      ->filter(fn($column) => trim($column['id']) !== '')
      ->values()
      ->all();

    return !empty($normalizedColumns)
      ? $normalizedColumns
      : [
        [
          'id' => 'col_1',
          'label' => 'Column 1',
        ],
      ];
  }

  private function loadSubmission(UpliftSubmission $submission): void
  {
    $submission->load([
      'measure' => fn($query) => $query->with($this->formDefinitionRelations()),
      'agency',
      'user',
      'fieldValues',
      'indicatorValues',
    ]);
  }

  private function formDefinitionRelations(): array
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

  private function visibleFields(UpliftMeasure $measure)
  {
    $fields = $measure->fields
      ->where('status', 1)
      ->where('value_type', '!=', 'section');

    return $fields->filter(function ($field) use ($fields) {
      $parentId = $field->parent_id;

      while ($parentId != null) {
        $parent = $fields->firstWhere('id', $parentId);

        if ($parent == null || $parent->status != 1) {
          return false;
        }

        $parentId = $parent->parent_id;
      }

      return true;
    })->sortBy([
      ['row_number', 'asc'],
      ['order', 'asc'],
      ['id', 'asc'],
    ]);
  }

  private function guardView(UpliftSubmission $submission): void
  {
    $this->authorize('view', $submission);
  }

  private function guardChange(UpliftSubmission $submission): void
  {
    $this->authorize('update', $submission);
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

  private function applyApprovalAction(Request $request, UpliftSubmission $submission, string $status): void
  {
    $data = $request->validate([
      'approval_remarks' => ['required', 'string', 'max:5000'],
    ]);

    DB::transaction(function () use (&$submission, $status, $data) {
      $submission = UpliftSubmission::where('id', $submission->id)->lockForUpdate()->firstOrFail();
      abort_unless($submission->status === 'submitted', 409);

      $submission->status = $status;
      $submission->approval_remarks = $data['approval_remarks'] ?? null;

      if ($status === 'approved') {
        $submission->approved_by = auth()->id();
        $submission->approved_at = Carbon::now();
        $submission->returned_by = null;
        $submission->returned_at = null;
        $submission->rejected_by = null;
        $submission->rejected_at = null;
      } elseif ($status === 'returned') {
        $submission->returned_by = auth()->id();
        $submission->returned_at = Carbon::now();
        $submission->approved_by = null;
        $submission->approved_at = null;
        $submission->rejected_by = null;
        $submission->rejected_at = null;
      } else {
        $submission->rejected_by = auth()->id();
        $submission->rejected_at = Carbon::now();
        $submission->approved_by = null;
        $submission->approved_at = null;
        $submission->returned_by = null;
        $submission->returned_at = null;
      }

      $submission->save();

      SubmissionApprovalHistory::create([
        'submission_type' => UpliftSubmission::class,
        'submission_id' => $submission->id,
        'user_id' => auth()->id(),
        'action' => $status,
        'remarks' => $data['approval_remarks'],
      ]);
    });

    $this->logSystemActivity(ucfirst($status) . ' UPLIFT submission: ' . optional($submission->measure)->title, 'uplift_submissions', $submission->id);
    $this->createAgencyApprovalNotification($submission, $status, $data['approval_remarks'] ?? null);
  }

  private function createSubmissionNotification(UpliftSubmission $submission, bool $wasReturned = false): void
  {
    $submission->loadMissing(['agency', 'measure']);

    $agencyName = optional($submission->agency)->display_name ?? 'Unknown agency';
    $measureTitle = optional($submission->measure)->title ?? 'Untitled UPLIFT measure';
    $title = $wasReturned ? 'Returned UPLIFT report resubmitted' : 'UPLIFT report submitted';

    foreach ($this->upliftApproverRecipients($submission) as $recipient) {
      SubmissionNotification::create([
        'submission_type' => 'uplift',
        'uplift_submission_id' => $submission->id,
        'uplift_measure_id' => $submission->uplift_measure_id,
        'agency_id' => $submission->agency_id,
        'recipient_user_id' => $recipient->id,
        'title' => $title,
        'message' => $agencyName . ($wasReturned ? ' resubmitted ' : ' submitted ') . $measureTitle,
        'action' => $wasReturned ? 'resubmitted' : 'submitted',
      ]);
    }
  }

  private function createAgencyApprovalNotification(UpliftSubmission $submission, string $status, ?string $remarks): void
  {
    $submission->loadMissing(['agency', 'measure']);

    $measureTitle = optional($submission->measure)->title ?? 'Untitled UPLIFT measure';
    $statusLabel = ucfirst($status);
    $message = 'Your UPLIFT submission for ' . $measureTitle . ' was ' . $status . '.';

    if ($status === 'rejected') {
      $message .= ' Please create a new submission for this report.';
    }

    SubmissionNotification::create([
      'submission_type' => 'uplift',
      'uplift_submission_id' => $submission->id,
      'uplift_measure_id' => $submission->uplift_measure_id,
      'agency_id' => $submission->agency_id,
      'title' => 'UPLIFT Submission ' . $statusLabel,
      'message' => $message,
      'action' => $status,
      'remarks' => $remarks,
    ]);
  }

  private function upliftApproverRecipients(UpliftSubmission $submission)
  {
    $submission->loadMissing('measure');
    $assignedSectorId = optional($submission->measure)->assigned_sector_id;

    return User::query()
      ->where(function ($query) use ($assignedSectorId) {
        $query->where('role_id', 1);

        if (!empty($assignedSectorId)) {
          $query->orWhere(function ($query) use ($assignedSectorId) {
            $query->where('agency_id', \App\Models\Agency::DEPDEV_ID)
              ->where('staff_id', $assignedSectorId);
          });
        }
      })
      ->get()
      ->unique('id')
      ->values();
  }
}

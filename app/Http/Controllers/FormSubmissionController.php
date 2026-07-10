<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormSubmissionRequest;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\SubmissionApprovalHistory;
use App\Models\SubmissionNotification;
use App\Models\User;
use App\Traits\GenerateLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;

class FormSubmissionController extends Controller
{
  use GenerateLogs;

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $this->authorize('viewAny', FormSubmission::class);

    $submissions = FormSubmission::with(['form', 'agency', 'user'])
      ->when(!$this->canViewAllSubmissions(), function ($query) {
        $query->where(function ($query) {
          $query->where('agency_id', auth()->user()->agency_id);

          if (auth()->user()->isDepDevStaff()) {
            $query->orWhereHas('form', function ($query) {
              $query->where('assigned_sector_id', auth()->user()->staff_id);
            });
          }
        });
      })
      ->orderBy('reporting_cutoff_date', 'desc')
      ->orderBy('created_at', 'desc')
      ->get();

    $form = $this->activeFormForUser();
    $activeForm = $this->activeFormForUser(false);
    $weeklySubmissionLocked = $activeForm != null && $this->hasSubmittedThisWeek($activeForm->agency_id, $activeForm->id);

    return view('submissions.index', compact('submissions', 'form', 'weeklySubmissionLocked'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $this->authorize('create', FormSubmission::class);

    $form = $this->activeFormForUser();

    if ($form == null) {
      return redirect()->route('submissions.index')->with('error', 'No active form is available for your agency.');
    }

    if ($this->hasSubmittedThisWeek($form->agency_id, $form->id)) {
      return redirect()->route('submissions.index')->with('error', $this->weeklySubmissionLockMessage());
    }

    $submission = new FormSubmission();
    $values = collect();

    return view('submissions.create', compact('form', 'submission', 'values'));
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(FormSubmissionRequest $request)
  {
    $form = Form::with(['fields' => function ($query) {
      $query->where('status', 1)->orderBy('row_number')->orderBy('order');
    }])->findOrFail($request->form_id);

    if ($this->hasSubmittedThisWeek($form->agency_id, $form->id)) {
      return redirect()->route('submissions.index')->with('error', $this->weeklySubmissionLockMessage());
    }

    DB::transaction(function () use ($request, $form, &$submission) {
      $submission = new FormSubmission();
      $submission->form_id = $form->id;
      $submission->agency_id = $form->agency_id;
      $submission->user_id = auth()->id();
      $submission->reporting_cutoff_date = $request->reporting_cutoff_date;
      $submission->status = 'draft';
      $submission->save();

      $this->syncValues($submission, $form, $request->values);
    });

    return redirect()->route('submissions.edit', $submission)->with('succes', 'Submission succesfully saved as draft');
  }

  /**
   * Display the specified resource.
   */
  public function show(FormSubmission $form_submission)
  {
    $this->guardView($form_submission);

    $form_submission->load(['form.fields', 'agency', 'user', 'values.field']);
    $form = $form_submission->form;
    $submission = $form_submission;
    $values = $submission->values->keyBy('form_field_id');

    return view('submissions.show', compact('form', 'submission', 'values'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(FormSubmission $form_submission)
  {
    $this->guardChange($form_submission);

    $form_submission->load(['form.fields' => function ($query) {
      $query->where('status', 1)->orderBy('row_number')->orderBy('order');
    }, 'values']);
    $form = $form_submission->form;
    $submission = $form_submission;
    $values = $submission->values->keyBy('form_field_id');

    return view('submissions.edit', compact('form', 'submission', 'values'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(FormSubmissionRequest $request, FormSubmission $form_submission)
  {
    $this->guardChange($form_submission);
    $form = Form::with(['fields' => function ($query) {
      $query->where('status', 1)->orderBy('row_number')->orderBy('order');
    }])->findOrFail($request->form_id);

    DB::transaction(function () use ($request, $form, &$form_submission) {
      $form_submission = FormSubmission::where('id', $form_submission->id)->lockForUpdate()->first();
      $form_submission->reporting_cutoff_date = $request->reporting_cutoff_date;
      $form_submission->save();

      $this->syncValues($form_submission, $form, $request->values);
    });

    return redirect()->route('submissions.edit', $form_submission)->with('succes', 'Submission succesfully updated');
  }

  public function submit(FormSubmissionRequest $request, FormSubmission $form_submission)
  {
    $this->guardChange($form_submission);
    $wasReturned = $form_submission->status === 'returned';

    DB::transaction(function () use (&$form_submission) {
      $form_submission = FormSubmission::where('id', $form_submission->id)->lockForUpdate()->first();
      $form_submission->status = 'submitted';
      $form_submission->submitted_at = Carbon::now();
      $form_submission->approved_by = null;
      $form_submission->approved_at = null;
      $form_submission->returned_by = null;
      $form_submission->returned_at = null;
      $form_submission->rejected_by = null;
      $form_submission->rejected_at = null;
      $form_submission->approval_remarks = null;
      $form_submission->save();
    });

    $this->createSubmissionNotification($form_submission, $wasReturned);
    $this->logSystemActivity('Submitted headline indicator submission: ' . optional($form_submission->form)->title, 'form_submissions', $form_submission->id);

    return redirect()->route('submissions.show', $form_submission)->with('succes', 'Submission succesfully submitted');
  }

  public function approve(Request $request, FormSubmission $form_submission)
  {
    $this->authorize('approve', $form_submission);

    $this->applyApprovalAction($request, $form_submission, 'approved');

    return redirect()->route('submissions.show', $form_submission)->with('succes', 'Submission successfully approved');
  }

  public function return(Request $request, FormSubmission $form_submission)
  {
    $this->authorize('return', $form_submission);

    $this->applyApprovalAction($request, $form_submission, 'returned');

    return redirect()->route('submissions.show', $form_submission)->with('succes', 'Submission successfully returned');
  }

  public function reject(Request $request, FormSubmission $form_submission)
  {
    $this->authorize('reject', $form_submission);

    $this->applyApprovalAction($request, $form_submission, 'rejected');

    return redirect()->route('submissions.show', $form_submission)->with('succes', 'Submission successfully rejected');
  }

  private function activeFormForUser(bool $respectWeeklyLock = true)
  {
    if (auth()->user()->isSuperAdmin()) {
      return null;
    }

    return Form::with(['agency', 'fields' => function ($query) {
      $query->where('status', 1)->orderBy('row_number')->orderBy('order');
    }])
      ->where('agency_id', auth()->user()->agency_id)
      ->where('status', 1)
      ->when($respectWeeklyLock, function ($query) {
        $query->whereDoesntHave('submissions', function ($query) {
          $query->where('status', 'submitted')
            ->whereBetween('submitted_at', $this->currentSubmissionWeek());
        });
      })
      ->orderBy('title')
      ->first();
  }

  private function syncValues(FormSubmission $submission, Form $form, array $values): void
  {
    foreach ($form->fields->where('value_type', '!=', 'section') as $field) {
      $value = $values[$field->id] ?? [];

      $submission->values()->updateOrCreate(
        ['form_field_id' => $field->id],
        $this->fieldValueAttributes($field, $value)
      );
    }
  }

  private function fieldValueAttributes(FormField $field, array $value): array
  {
    $valueType = $field->value_type;
    $remarks = $value['remarks'] ?? null;
    if ($this->isEmptyRichText($remarks)) {
      $remarks = null;
    } elseif ($remarks != null) {
      $remarks = Purifier::clean($remarks, 'allow_quilljs_element');
    }

    $attributes = [
      'integer_value' => null,
      'decimal_value' => null,
      'text_value' => null,
      'date_value' => null,
      'date_start_value' => null,
      'date_end_value' => null,
      'remarks' => $remarks,
    ];

    if ($valueType == 'decimal') {
      $attributes['decimal_value'] = $value['value'] ?? null;
    } elseif ($valueType == 'text') {
      $attributes['text_value'] = $value['value'] ?? null;
    } elseif ($valueType == 'repeating_group') {
      $attributes['text_value'] = $this->normalizeRepeatingGroupValue($field, $value['value'] ?? null);
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

  private function normalizeRepeatingGroupValue(FormField $field, mixed $value): ?string
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

  private function repeatingGroupColumns(FormField $field): array
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
      ->filter(fn ($column) => trim($column['id']) !== '')
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

  private function isEmptyRichText(?string $value): bool
  {
    if ($value == null) {
      return true;
    }

    if (preg_match('/<(img|iframe)\b/i', $value)) {
      return false;
    }

    return trim(strip_tags(str_replace('&nbsp;', ' ', $value))) == '';
  }

  private function createSubmissionNotification(FormSubmission $submission, bool $wasReturned = false): void
  {
    $submission->loadMissing(['agency', 'form']);

    $agencyName = optional($submission->agency)->display_name ?? 'Unknown agency';
    $formTitle = optional($submission->form)->title ?? 'Untitled form';
    $title = $wasReturned ? 'Returned report resubmitted' : 'Report submitted';

    foreach ($this->indicatorApproverRecipients($submission) as $recipient) {
      SubmissionNotification::create([
        'submission_type' => 'indicator',
        'form_submission_id' => $submission->id,
        'form_id' => $submission->form_id,
        'agency_id' => $submission->agency_id,
        'recipient_user_id' => $recipient->id,
        'title' => $title,
        'message' => $agencyName . ($wasReturned ? ' resubmitted ' : ' submitted ') . $formTitle,
        'action' => $wasReturned ? 'resubmitted' : 'submitted',
      ]);
    }
  }

  private function createAgencyApprovalNotification(FormSubmission $submission, string $status, ?string $remarks): void
  {
    $submission->loadMissing(['agency', 'form']);

    $formTitle = optional($submission->form)->title ?? 'Untitled form';
    $statusLabel = ucfirst($status);
    $message = 'Your headline indicator submission for ' . $formTitle . ' was ' . $status . '.';

    if ($status === 'rejected') {
      $message .= ' Please create a new submission for this report.';
    }

    SubmissionNotification::create([
      'submission_type' => 'indicator',
      'form_submission_id' => $submission->id,
      'form_id' => $submission->form_id,
      'agency_id' => $submission->agency_id,
      'title' => 'Submission ' . $statusLabel,
      'message' => $message,
      'action' => $status,
      'remarks' => $remarks,
    ]);
  }

  private function indicatorApproverRecipients(FormSubmission $submission)
  {
    $submission->loadMissing('form');
    $assignedSectorId = optional($submission->form)->assigned_sector_id;

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

  private function hasSubmittedThisWeek(int $agencyId, int $formId): bool
  {
    return FormSubmission::where('agency_id', $agencyId)
      ->where('form_id', $formId)
      ->whereIn('status', ['submitted', 'approved'])
      ->whereBetween('submitted_at', $this->currentSubmissionWeek())
      ->exists();
  }

  private function currentSubmissionWeek(): array
  {
    return [
      now()->startOfWeek(),
      now()->endOfWeek(),
    ];
  }

  private function weeklySubmissionLockMessage(): string
  {
    return 'A report has already been submitted this week. Submissions will reopen next Monday.';
  }

  private function canViewAllSubmissions(): bool
  {
    return auth()->user()->isSuperAdmin()
      || auth()->user()->role_id === 29;
  }

  private function applyApprovalAction(Request $request, FormSubmission $submission, string $status): void
  {
    $data = $request->validate([
      'approval_remarks' => ['required', 'string', 'max:5000'],
    ]);

    DB::transaction(function () use (&$submission, $status, $data) {
      $submission = FormSubmission::where('id', $submission->id)->lockForUpdate()->firstOrFail();
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
        'submission_type' => FormSubmission::class,
        'submission_id' => $submission->id,
        'user_id' => auth()->id(),
        'action' => $status,
        'remarks' => $data['approval_remarks'],
      ]);
    });

    $this->logSystemActivity(ucfirst($status) . ' headline indicator submission: ' . optional($submission->form)->title, 'form_submissions', $submission->id);
    $this->createAgencyApprovalNotification($submission, $status, $data['approval_remarks'] ?? null);
  }

  private function guardView(FormSubmission $submission): void
  {
    $this->authorize('view', $submission);
  }

  private function guardChange(FormSubmission $submission): void
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
}

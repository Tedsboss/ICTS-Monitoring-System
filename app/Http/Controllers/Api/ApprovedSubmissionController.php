<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;

class ApprovedSubmissionController extends Controller
{
  public function indicatorSubmissions()
  {
    return response()->json([
      'as_of' => now()->toDateTimeString(),
      'data' => $this->indicatorRows(),
    ]);
  }

  private function indicatorRows()
  {
    return FormSubmission::with(['agency', 'form.assignedSector', 'form.fields', 'values.field', 'approver'])
      ->where('status', 'approved')
      ->latest('approved_at')
      ->latest('submitted_at')
      ->get()
      ->unique(fn($submission) => $submission->agency_id . ':' . $submission->form_id)
      ->values()
      ->map(fn($submission) => $this->indicatorPayload($submission))
      ->values();
  }

  private function indicatorPayload(FormSubmission $submission): array
  {
    $sectionsByRow = optional($submission->form)->fields
      ? $submission->form->fields
        ->where('value_type', 'section')
        ->keyBy(fn($field) => (int) $field->row_number)
      : collect();

    return [
      'submission_type' => 'headline_indicator',
      'agency_code' => optional($submission->agency)->abbreviation,
      'agency_name' => optional($submission->agency)->name,
      'form_title' => optional($submission->form)->title,
      'reporting_cutoff_date' => optional($submission->reporting_cutoff_date)->format('Y-m-d'),
      'answers' => $submission->values
      ->sortByDesc(fn($value) => optional($value->updated_at)->timestamp ?? 0)
      ->sortByDesc('id')
      ->values()
      ->map(function ($value) use ($submission, $sectionsByRow) {
        $field = $value->field;
        $section = $field ? $sectionsByRow->get((int) $field->row_number) : null;

        return [
          '_sort_order' => optional($field)->order ?? 0,
          '_unique_key' => strtolower(trim((string) optional($section)->label)) . '|' . strtolower(trim((string) optional($field)->label)),
          'section_title' => optional($section)->label,
          'question_title' => optional($field)->label,
          'answer_type' => optional($field)->value_type,
          'answer' => $this->normalizeAnswer($this->valuePayload($value, optional($field)->value_type)),
        ];
      })
      ->unique('_unique_key')
      ->sortBy('_sort_order')
      ->map(function ($answer) {
        unset($answer['_sort_order'], $answer['_unique_key']);

        return $answer;
      })
      ->values(),
    ];
  }

  private function normalizeAnswer($answer)
  {
    if (is_array($answer)) {
      return json_encode($answer, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    return $answer;
  }

  private function valuePayload($value, ?string $type)
  {
    return match ($type) {
      'decimal' => $value->decimal_value,
      'text', 'select', 'boolean', 'repeating_group' => $type === 'repeating_group'
        ? (json_decode((string) $value->text_value, true) ?: $value->text_value)
        : $value->text_value,
      'date' => optional($value->date_value)->format('Y-m-d'),
      'date_range' => [
        'start_date' => optional($value->date_start_value)->format('Y-m-d'),
        'end_date' => optional($value->date_end_value)->format('Y-m-d'),
      ],
      default => $value->integer_value,
    };
  }
}

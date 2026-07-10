<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class DashboardReportController extends Controller
{
  public function upliftReports(Request $request)
  {
    $agencies = $request->input('agencies', 'DMW,DOE');
    $agencyCodes = collect(explode(',', $agencies))
      ->map(fn($agency) => strtoupper(trim($agency)))
      ->filter()
      ->values();

    $submissions = FormSubmission::with(['agency', 'form', 'values.field'])
      ->where('status', 'submitted')
      ->whereHas('agency', function ($query) use ($agencyCodes) {
        $query->whereIn('Abbreviation', $agencyCodes);
      })
      ->latest('submitted_at')
      ->get()
      ->unique('agency_id')
      ->values();

    return response()->json([
      'as_of' => now()->toDateTimeString(),
      'reports' => $submissions->map(fn($submission) => $this->reportPayload($submission)),
    ]);
  }

  private function reportPayload(FormSubmission $submission): array
  {
    return [
      'agency' => [
        'id' => $submission->agency_id,
        'code' => optional($submission->agency)->abbreviation,
        'name' => optional($submission->agency)->name,
        'display_name' => optional($submission->agency)->display_name,
      ],
      'form' => [
        'id' => $submission->form_id,
        'title' => optional($submission->form)->title,
      ],
      'submission' => [
        'id' => $submission->id,
        'week_ending' => optional($submission->reporting_cutoff_date)->format('Y-m-d'),
        'submitted_at' => optional($submission->submitted_at)->toDateTimeString(),
      ],
      'metrics' => $submission->values
        ->sortBy(fn($value) => optional($value->field)->order)
        ->values()
        ->map(fn($value) => [
          'field_id' => $value->form_field_id,
          'label' => optional($value->field)->label,
          'type' => optional($value->field)->value_type,
          'value' => $this->fieldValue($value),
          'remarks' => $value->remarks,
        ]),
    ];
  }

  private function fieldValue($value)
  {
    return match (optional($value->field)->value_type) {
      'decimal' => $value->decimal_value,
      'text' => $value->text_value,
      'repeating_group' => json_decode((string) $value->text_value, true) ?: $value->text_value,
      'date' => optional($value->date_value)->format('Y-m-d'),
      'date_range' => [
        'start_date' => optional($value->date_start_value)->format('Y-m-d'),
        'end_date' => optional($value->date_end_value)->format('Y-m-d'),
        'days' => $value->date_start_value && $value->date_end_value
          ? $value->date_start_value->diffInDays($value->date_end_value) + 1
          : null,
      ],
      default => $value->integer_value,
    };
  }
}

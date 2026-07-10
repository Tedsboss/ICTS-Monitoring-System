<?php

namespace App\Console\Commands;

use App\Models\FormSubmission;
use App\Models\SubmissionApprovalHistory;
use App\Models\UpliftSubmission;
use Illuminate\Console\Command;

class BackfillSubmissionApprovalHistory extends Command
{
  protected $signature = 'submission:backfill-approval-history {--force : Run without confirmation}';

  protected $description = 'Create approval history rows from existing submission approval audit columns';

  public function handle(): int
  {
    if (!$this->option('force') && !$this->confirm('Backfill approval history from existing approved/returned/rejected submissions?')) {
      $this->info('Cancelled.');
      return self::SUCCESS;
    }

    $created = 0;
    $created += $this->backfill(FormSubmission::class, 'Indicator/Form');
    $created += $this->backfill(UpliftSubmission::class, 'UPLIFT');

    $this->info("Backfill complete. Created {$created} approval history row(s).");

    return self::SUCCESS;
  }

  private function backfill(string $submissionClass, string $label): int
  {
    $created = 0;

    $submissionClass::query()
      ->whereIn('status', ['approved', 'returned', 'rejected'])
      ->whereNotNull('approval_remarks')
      ->orderBy('id')
      ->each(function ($submission) use (&$created, $submissionClass) {
        $action = $submission->status;
        $userId = match ($action) {
          'approved' => $submission->approved_by,
          'returned' => $submission->returned_by,
          'rejected' => $submission->rejected_by,
          default => null,
        };
        $createdAt = match ($action) {
          'approved' => $submission->approved_at,
          'returned' => $submission->returned_at,
          'rejected' => $submission->rejected_at,
          default => $submission->updated_at,
        };

        $exists = SubmissionApprovalHistory::where('submission_type', $submissionClass)
          ->where('submission_id', $submission->id)
          ->where('action', $action)
          ->where('remarks', $submission->approval_remarks)
          ->exists();

        if ($exists) {
          return;
        }

        SubmissionApprovalHistory::create([
          'submission_type' => $submissionClass,
          'submission_id' => $submission->id,
          'user_id' => $userId,
          'action' => $action,
          'remarks' => $submission->approval_remarks,
          'created_at' => $createdAt ?? now(),
          'updated_at' => $createdAt ?? now(),
        ]);

        $created++;
      });

    $this->line("{$label}: {$created} row(s) created.");

    return $created;
  }
}

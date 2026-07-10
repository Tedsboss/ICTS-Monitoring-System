<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshSubmissions extends Command
{
  protected $signature = 'submission:refresh
    {--indicator : Clear only Indicator/Form submissions}
    {--uplift : Clear only UPLIFT submissions}
    {--force : Run without confirmation}';

  protected $description = 'Delete all Indicator/Form and UPLIFT submission rows while keeping form definitions intact';

  public function handle(): int
  {
    $clearIndicator = $this->option('indicator');
    $clearUplift = $this->option('uplift');

    if (!$clearIndicator && !$clearUplift) {
      $clearIndicator = true;
      $clearUplift = true;
    }

    $tables = collect();

    if ($clearIndicator) {
      $tables = $tables->merge([
        'submission_notifications',
        'form_submission_values',
        'form_submissions',
      ]);
    }

    if ($clearUplift) {
      $tables = $tables->merge([
        'uplift_submission_indicator_values',
        'uplift_submission_field_values',
        'uplift_submissions',
      ]);
    }

    $tables = $tables->filter(fn($table) => Schema::hasTable($table))->values();

    if ($tables->isEmpty()) {
      $this->warn('No submission tables were found to clear.');
      return self::SUCCESS;
    }

    if (!$this->option('force')) {
      $this->warn('This will permanently delete rows from: ' . $tables->implode(', '));

      if (!$this->confirm('Continue?')) {
        $this->info('Cancelled.');
        return self::SUCCESS;
      }
    }

    Schema::disableForeignKeyConstraints();

    try {
      if (Schema::hasTable('submission_approval_histories')) {
        if ($clearIndicator && $clearUplift) {
          DB::table('submission_approval_histories')->truncate();
          $this->line('Cleared submission_approval_histories');
        } elseif ($clearIndicator) {
          DB::table('submission_approval_histories')->where('submission_type', 'App\\Models\\FormSubmission')->delete();
          $this->line('Cleared Indicator submission approval histories');
        } elseif ($clearUplift) {
          DB::table('submission_approval_histories')->where('submission_type', 'App\\Models\\UpliftSubmission')->delete();
          $this->line('Cleared UPLIFT submission approval histories');
        }
      }

      foreach ($tables as $table) {
        DB::table($table)->truncate();
        $this->line("Cleared {$table}");
      }
    } finally {
      Schema::enableForeignKeyConstraints();
    }

    $this->info('Submission data refreshed.');

    return self::SUCCESS;
  }
}

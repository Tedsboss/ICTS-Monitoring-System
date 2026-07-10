<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshBuilders extends Command
{
  protected $signature = 'builder:refresh
    {--forms : Clear only Form Management definitions and related submissions}
    {--uplift : Clear only UPLIFT Builder definitions and related submissions}
    {--force : Run without confirmation}';

  protected $description = 'Delete Form Management and/or UPLIFT Builder definitions with their dependent submission data';

  public function handle(): int
  {
    $clearForms = $this->option('forms');
    $clearUplift = $this->option('uplift');

    if (!$clearForms && !$clearUplift) {
      $clearForms = true;
      $clearUplift = true;
    }

    $tables = collect();

    if ($clearForms) {
      $tables = $tables->merge([
        'submission_notifications',
        'form_submission_values',
        'form_submissions',
        'form_fields',
        'forms',
      ]);
    }

    if ($clearUplift) {
      $tables = $tables->merge([
        'submission_notifications',
        'uplift_submission_indicator_values',
        'uplift_submission_field_values',
        'uplift_submissions',
        'uplift_indicators',
        'uplift_pillar_fields',
        'uplift_measure_supporting_agencies',
        'uplift_measures',
        'uplift_pillars',
      ]);
    }

    $tables = $tables->unique()->filter(fn($table) => Schema::hasTable($table))->values();

    if ($tables->isEmpty()) {
      $this->warn('No builder tables were found to clear.');
      return self::SUCCESS;
    }

    if (!$this->option('force')) {
      $this->warn('This will permanently delete builder definitions and dependent submission data from:');
      $this->line($tables->implode(', '));

      if (!$this->confirm('Continue?')) {
        $this->info('Cancelled.');
        return self::SUCCESS;
      }
    }

    Schema::disableForeignKeyConstraints();

    try {
      $this->clearApprovalHistories($clearForms, $clearUplift);

      foreach ($tables as $table) {
        DB::table($table)->truncate();
        $this->line("Cleared {$table}");
      }
    } finally {
      Schema::enableForeignKeyConstraints();
    }

    $this->info('Builder data refreshed.');

    return self::SUCCESS;
  }

  private function clearApprovalHistories(bool $clearForms, bool $clearUplift): void
  {
    if (!Schema::hasTable('submission_approval_histories')) {
      return;
    }

    if ($clearForms && $clearUplift) {
      DB::table('submission_approval_histories')->truncate();
      $this->line('Cleared submission_approval_histories');
      return;
    }

    if ($clearForms) {
      DB::table('submission_approval_histories')
        ->where('submission_type', 'App\\Models\\FormSubmission')
        ->delete();
      $this->line('Cleared Indicator submission approval histories');
    }

    if ($clearUplift) {
      DB::table('submission_approval_histories')
        ->where('submission_type', 'App\\Models\\UpliftSubmission')
        ->delete();
      $this->line('Cleared UPLIFT submission approval histories');
    }
  }
}

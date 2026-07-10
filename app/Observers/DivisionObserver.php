<?php

namespace App\Observers;

use App\Models\Division;
use App\Traits\GenerateLogs;
use App\Traits\TracksHistoryTrait;

class DivisionObserver
{
  use GenerateLogs;
  use TracksHistoryTrait;
  /**
   * Handle the Division "created" event.
   */
  public function created(Division $division): void
  {
    //
  }

  /**
   * Handle the Division "updated" event.
   */
  public function updated(Division $division): void
  {
    //
  }

  /**
   * Handle the Division "updating" event.
   */
  public function updating(Division $division): void
  {
    $log_id = $this->addSystemLogs("Updated division: " . $division->name, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'divisions', $division->id);
    $this->track($division, $log_id);
  }

  /**
   * Handle the Division "deleted" event.
   */
  public function deleted(Division $division): void
  {
    //
  }

  /**
   * Handle the Division "restored" event.
   */
  public function restored(Division $division): void
  {
    //
  }

  /**
   * Handle the Division "force deleted" event.
   */
  public function forceDeleted(Division $division): void
  {
    //
  }
}

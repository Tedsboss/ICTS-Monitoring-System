<?php

namespace App\Observers;

use App\Models\Holiday;
use App\Traits\GenerateLogs;
use App\Traits\TracksHistoryTrait;

class HolidayObserver
{
  use GenerateLogs;
  use TracksHistoryTrait;
  /**
   * Handle the Holiday "created" event.
   */
  public function created(Holiday $holiday): void
  {
    $this->addSystemLogs("Created new holiday: " . $holiday->name, null, auth()->user()->email, request()->getClientIp(true), 'holidays', $holiday->id);
  }

  /**
   * Handle the Holiday "updated" event.
   */
  public function updated(Holiday $holiday): void
  {
    //
  }

  /**
   * Handle the Holiday "updating" event.
   */
  public function updating(Holiday $holiday): void
  {
    $log_id = $this->addSystemLogs("Updated holiday: " . $holiday->name, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'holidays', $holiday->id);
    $this->track($holiday, $log_id);
  }

  /**
   * Handle the Holiday "deleted" event.
   */
  public function deleted(Holiday $holiday): void
  {
    $this->addSystemLogs("Deleted holiday: " . $holiday->name, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'holidays', $holiday->id);
  }

  /**
   * Handle the Holiday "restored" event.
   */
  public function restored(Holiday $holiday): void
  {
    //
  }

  /**
   * Handle the Holiday "force deleted" event.
   */
  public function forceDeleted(Holiday $holiday): void
  {
    //
  }
}

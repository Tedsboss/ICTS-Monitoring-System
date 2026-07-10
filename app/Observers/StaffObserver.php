<?php

namespace App\Observers;

use App\Models\Staff;
use App\Traits\GenerateLogs;
use App\Traits\TracksHistoryTrait;

class StaffObserver
{
  use GenerateLogs;
  use TracksHistoryTrait;
  /**
   * Handle the Staff "created" event.
   */
  public function created(Staff $staff): void
  {
    //
  }

  /**
   * Handle the Staff "updated" event.
   */
  public function updated(Staff $staff): void
  {
    //
  }

  /**
   * Handle the Staff "updating" event.
   */
  public function updating(Staff $staff): void
  {
    $log_id = $this->addSystemLogs("Updated staff: " . $staff->name, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'staffs', $staff->id);
    $this->track($staff, $log_id);
  }

  /**
   * Handle the Staff "deleted" event.
   */
  public function deleted(Staff $staff): void
  {
    //
  }

  /**
   * Handle the Staff "restored" event.
   */
  public function restored(Staff $staff): void
  {
    //
  }

  /**
   * Handle the Staff "force deleted" event.
   */
  public function forceDeleted(Staff $staff): void
  {
    //
  }
}

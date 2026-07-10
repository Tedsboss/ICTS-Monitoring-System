<?php

namespace App\Observers;

use App\Models\Role;
use App\Traits\GenerateLogs;
use App\Traits\TracksHistoryTrait;

class RoleObserver
{
  use GenerateLogs;
  use TracksHistoryTrait;
  /**
   * Handle the Role "created" event.
   */
  public function created(Role $role): void
  {
    $this->addSystemLogs("Added role: " . $role->name, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'roles', $role->id);
  }

  /**
   * Handle the Role "updated" event.
   */
  public function updated(Role $role): void
  {
    //
  }

  /**
   * Handle the Role "updating" event.
   */
  public function updating(Role $role): void
  {
    $log_id = $this->addSystemLogs("Updated role: " . $role->name, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'roles', $role->id);
    $this->track($role, $log_id);
  }

  /**
   * Handle the Role "deleted" event.
   */
  public function deleted(Role $role): void
  {
    //
  }

  /**
   * Handle the Role "restored" event.
   */
  public function restored(Role $role): void
  {
    //
  }

  /**
   * Handle the Role "force deleted" event.
   */
  public function forceDeleted(Role $role): void
  {
    //
  }
}

<?php

namespace App\Observers;

use App\Models\RestrictedIp;
use App\Traits\GenerateLogs;

class RestrictedIpObserver
{
  use GenerateLogs;
  /**
   * Handle the RestrictedIp "created" event.
   */
  public function created(RestrictedIp $restrictedIp): void
  {
    //
  }

  /**
   * Handle the RestrictedIp "updated" event.
   */
  public function updated(RestrictedIp $restrictedIp): void
  {
    $this->addSystemLogs("Updated status(" . ($restrictedIp->status == 1 ? 'block' : 'unblock') . ") of restricted ip: " . $restrictedIp->ipaddress, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'restricted_ips', $restrictedIp->id);
  }

  /**
   * Handle the RestrictedIp "deleted" event.
   */
  public function deleted(RestrictedIp $restrictedIp): void
  {
    $this->addSystemLogs("Deleted restricted ip: " . $restrictedIp->ipaddress, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'restricted_ips', $restrictedIp->id);
  }

  /**
   * Handle the RestrictedIp "restored" event.
   */
  public function restored(RestrictedIp $restrictedIp): void
  {
    //
  }

  /**
   * Handle the RestrictedIp "force deleted" event.
   */
  public function forceDeleted(RestrictedIp $restrictedIp): void
  {
    //
  }
}

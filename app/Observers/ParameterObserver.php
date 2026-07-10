<?php

namespace App\Observers;

use App\Models\Parameter;
use App\Traits\GenerateLogs;
use App\Traits\TracksHistoryTrait;

class ParameterObserver
{
  use GenerateLogs;
  use TracksHistoryTrait;
  /**
   * Handle the Parameter "created" event.
   */
  public function created(Parameter $parameter): void
  {
    //
  }

  /**
   * Handle the Parameter "updated" event.
   */
  public function updated(Parameter $parameter): void
  {
    //
  }

  /**
   * Handle the Parameter "updating" event.
   */
  public function updating(Parameter $parameter): void
  {
    $log_id = $this->addSystemLogs("Updated parameter: " . $parameter->name, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'parameters', $parameter->id);
    $this->track($parameter, $log_id);
  }

  /**
   * Handle the Parameter "deleted" event.
   */
  public function deleted(Parameter $parameter): void
  {
    $this->addSystemLogs("Deleted parameter: " . $parameter->email, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'parameters', $parameter->id);
  }

  /**
   * Handle the Parameter "restored" event.
   */
  public function restored(Parameter $parameter): void
  {
    //
  }

  /**
   * Handle the Parameter "force deleted" event.
   */
  public function forceDeleted(Parameter $parameter): void
  {
    //
  }
}

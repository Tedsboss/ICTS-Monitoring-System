<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\File;
use App\Traits\GenerateLogs;
use App\Traits\TracksHistoryTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

// class UserObserver implements ShouldHandleEventsAfterCommit
class UserObserver
{
  use GenerateLogs;
  use TracksHistoryTrait;
  /**
   * Handle the User "created" event.
   */
  public function created(User $user): void
  {
    $this->addSystemLogs("Added user: " . $user->email, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'users', $user->id);
  }

  /**
   * Handle the User "updated" event.
   */
  public function updated(User $user): void
  {
    //
  }

  /**
   * Handle the User "updating" event.
   */
  public function updating(User $user): void
  {
    // dd($user->getOriginal(), $user->getDirty());`
    // Log::info('Updating observer triggered', ['model' => $user->id, 'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)]);

    if ($user->isDirty('remember_token') && count($user->getDirty()) === 1) {
      return;
    }

    if (Auth::check()) {
      if ($user->avatar != $user->getOriginal('avatar')) {
        File::delete(storage_path("/app/public/avatars/{$user->getOriginal('avatar')}"));
      }
      $log_id = $this->addSystemLogs("Updated user: " . $user->email, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'users', $user->id);
      $this->track($user, $log_id);
    }
  }

  /**
   * Handle the User "deleted" event.
   */
  public function deleted(User $user): void
  {
    if ($user->avatar == '' || $user->avatar == null) {
    } else {
      File::delete(storage_path("/app/public/avatars/{$user->avatar}"));
    }
    $this->addSystemLogs("Deleted user: " . $user->email, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'users', $user->id);
  }

  /**
   * Handle the User "restored" event.
   */
  public function restored(User $user): void
  {
    //
  }

  /**
   * Handle the User "force deleted" event.
   */
  public function forceDeleted(User $user): void
  {
    //
  }
}

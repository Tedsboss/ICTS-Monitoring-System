<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events as LaravelEvents;
use Illuminate\Support\Facades\Log;
use App\Traits\GenerateLogs;

class LogActivity
{
  use GenerateLogs;
  /**
   * Create the event listener.
   */
  public function __construct()
  {
    //
  }

  /**
   * Handle the event.
   */
  public function handle(object $event): void
  {
    //
  }

  public function login(LaravelEvents\Login $event)
  {
    $ip = request()->getClientIp(true);
    $provider_name = session('provider_name', null);
    $remarks = 'Logged in';
    if ($provider_name == null || $provider_name == '') {
    } else {
      $remarks = 'Logged in via ' . $provider_name;
    }
    $this->addSystemLogs($remarks, $event->user->id, $event->user->email, $ip);
  }

  public function logout(LaravelEvents\Logout $event)
  {
    if ($event->user != null) {
      $ip = request()->getClientIp(true);
      $this->addSystemLogs("Logged out", $event->user->id, $event->user->email, $ip);
    }
  }

  public function registered(LaravelEvents\Registered $event)
  {
    $ip = request()->getClientIp(true);
    $this->addSystemLogs("Registered", $event->user->id, $event->user->email, $ip);
  }

  public function failed(LaravelEvents\Failed $event)
  {
    $ip = request()->getClientIp(true);
    $this->addSystemLogs("Login failed", null, $event->credentials['email'], $ip);
  }

  public function passwordReset(LaravelEvents\PasswordReset $event)
  {
    $ip = request()->getClientIp(true);
    $this->addSystemLogs("Password reset", $event->user->id, $event->user->email, $ip);
  }

  protected function info(object $event, string $message, array $context = [])
  {
    $class = get_class($event);
    Log::info("[{$class}] {$message}", $context);
  }
}

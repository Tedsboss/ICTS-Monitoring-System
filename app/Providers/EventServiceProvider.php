<?php

namespace App\Providers;

use App\Listeners\JobEventListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\LogActivity;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use SocialiteProviders\Azure\AzureExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event listener mappings for the application.
   *
   * @var array<class-string, array<int, class-string>>
   */
  protected $listen = [
    Registered::class => [
      SendEmailVerificationNotification::class,
      LogActivity::class . '@registered',
    ],
    Login::class => [
      LogActivity::class . '@login',
    ],
    Logout::class => [
      LogActivity::class . '@logout',
    ],
    Failed::class => [
      LogActivity::class . '@failed',
    ],
    PasswordReset::class => [
      LogActivity::class . '@passwordReset',
    ],
    SocialiteWasCalled::class => [
      AzureExtendSocialite::class . '@handle',
    ],
    JobProcessing::class => [
      JobEventListener::class . '@handleJobProcessing',
    ],
    JobProcessed::class => [
      JobEventListener::class . '@handleJobProcessed',
    ],
    JobFailed::class => [
      JobEventListener::class . '@handleJobFailed',
    ],
  ];

  /**
   * Register any events for your application.
   *
   * @return void
   */
  public function boot()
  {
    //
  }

  /**
   * Determine if events and listeners should be automatically discovered.
   *
   * @return bool
   */
  public function shouldDiscoverEvents()
  {
    return false;
  }
}

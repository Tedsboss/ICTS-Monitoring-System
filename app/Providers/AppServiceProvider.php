<?php

namespace App\Providers;

use App\Models\Division;
use App\Models\Holiday;
use App\Models\User;
use App\Models\Parameter;
use App\Models\RestrictedIp;
use App\Models\Role;
use App\Models\Staff;
use App\Observers\DivisionObserver;
use App\Observers\HolidayObserver;
use App\Observers\UserObserver;
use App\Observers\ParameterObserver;
use App\Observers\RestrictedIpObserver;
use App\Observers\RoleObserver;
use App\Observers\StaffObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    if ($this->app->environment('local')) {
      $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
      $this->app->register(TelescopeServiceProvider::class);
    }
  }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    User::observe(UserObserver::class);
    Role::observe(RoleObserver::class);
    RestrictedIp::observe(RestrictedIpObserver::class);
    Parameter::observe(ParameterObserver::class);
    Holiday::observe(HolidayObserver::class);
    Staff::observe(StaffObserver::class);
    Division::observe(DivisionObserver::class);
    require_once app_path('Helpers/helpers.php');
  }
}

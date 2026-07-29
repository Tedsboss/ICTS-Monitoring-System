<?php

namespace App\Providers;

use App\Models\Division;
use App\Models\Agency;
use App\Models\FinancialPlan;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Holiday;
use App\Models\Inquiry;
use App\Models\UpliftPillar;
use App\Models\UpliftSubmission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\RestrictedIp;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Parameter;
use App\Models\Saeb;
use App\Models\Procurement;
use App\Models\TrustedDevice;
use App\Policies\DivisionPolicy;
use App\Policies\AgencyPolicy;
use App\Policies\FinancialPlanPolicy;
use App\Policies\FormPolicy;
use App\Policies\FormSubmissionPolicy;
use App\Policies\HolidayPolicy;
use App\Policies\InquiryPolicy;
use App\Policies\UpliftPillarPolicy;
use App\Policies\UpliftSubmissionPolicy;
use App\Policies\UserPolicy;
use App\Policies\RestrictedIpPolicy;
use App\Policies\RolePolicy;
use App\Policies\ParameterPolicy;
use App\Policies\ProcurementPolicy;
use App\Policies\SaebPolicy;
use App\Policies\StaffPolicy;
use App\Policies\TrustedDevicePolicy;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The policy mappings for the application.
   *
   * @var array<class-string, class-string>
   */
  protected $policies = [
    User::class => UserPolicy::class,
    Agency::class => AgencyPolicy::class,
    Form::class => FormPolicy::class,
    FormSubmission::class => FormSubmissionPolicy::class,
    Role::class => RolePolicy::class,
    RestrictedIp::class => RestrictedIpPolicy::class,
    Parameter::class => ParameterPolicy::class,
    Inquiry::class => InquiryPolicy::class,
    Holiday::class => HolidayPolicy::class,
    Staff::class => StaffPolicy::class,
    Division::class => DivisionPolicy::class,
    TrustedDevice::class => TrustedDevicePolicy::class,
    UpliftPillar::class => UpliftPillarPolicy::class,
    UpliftSubmission::class => UpliftSubmissionPolicy::class,
    Saeb::class        => SaebPolicy::class,
    Procurement ::class => ProcurementPolicy::class,
    FinancialPlan::class => FinancialPlanPolicy::class,
  ];

  /**
   * Register any authentication / authorization services.
   *
   * @return void
   */
  public function boot()
  {
    $this->registerPolicies();
    Gate::define('manage-items', 'App\Policies\UserPolicy@manageItems');
    Gate::define('manage-users', 'App\Policies\UserPolicy@manageUsers');
    Gate::define('manage-users', 'App\Policies\UserPolicy@updatecategory');
  }
}

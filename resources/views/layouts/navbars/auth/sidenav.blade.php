@php
  $isFormSubmissionRoute = request()->routeIs('submissions.*');
  $isUpliftSubmissionRoute = request()->routeIs('uplift-submissions.*');
  $isAdministratorRoute = request()->routeIs(
    'users.*',
    'agencies.*',
    'forms.*',
    'uplift-builder.*',
    'inquiries.*',
    'roles.*',
    'staffs.*',
    'divisions.*',
    'parameters.*',
    'holidays.*',
    'restrictedips.*',
    'api-clients.*',
    'systemlogs.*'
  );
  $isUpliftBuilderRoute = request()->routeIs('uplift-builder.*');
@endphp

{{-- <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-2 {{ $bg ?? ''}} {{ $box ?? ''}}" id="sidenav-main"> --}}
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-2 {{ isset($class_theme) && $class_theme == 'dark' ? 'dark-version' : 'bg-white' }}" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fa fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand m-0" href="{{ route('home') }}">
      <img src="{{ $logo ?? '/assets/img/neda/logo.png'}}" class="navbar-brand-img h-100" alt="main_logo">
      <span class="ms-3 font-weight-bold">D.I.R.E.K. Application</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0 mb-0">
  <div class="collapse navbar-collapse  w-auto h-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a data-bs-toggle="collapse" href="#profileExamples" class="nav-link" aria-controls="profileExamples" role="button" aria-expanded="false">
          <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center">
            <i class="ni ni-shop text-primary text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">{{ auth()->user()->full_name }}</span>
        </a>
        <div class="collapse {{ Route::currentRouteName() == 'user-profile' ? 'show' : '' }}" id="profileExamples">
          <ul class="nav ms-4">
            <li class="nav-item {{ Route::currentRouteName() == 'user-profile' ? 'active' : '' }}">
              <a class="nav-link {{ Route::currentRouteName() == 'user-profile' ? 'active' : '' }}" href="{{ route('user-profile') }}">
                <span class="sidenav-mini-icon">
                  <i class="fa fa-user align-middle"></i>
                </span>
                <span class="sidenav-normal">
                  <div class="row">
                    <div class="col-4 pe-0 ps-0">
                      <i class="fa fa-user align-middle"></i>
                    </div>
                    <div class="col-8 ps-0">
                      <span class="align-middle">My profile</span>
                    </div>
                  </div>
                </span>
              </a>
            </li>
            <li class="nav-item">
              <form role="form" method="post" action="{{ route('logout') }}" id="logout-form-sidenav">
                @csrf
                <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-sidenav').submit();">
                  <span class="sidenav-mini-icon">
                    <i class="fa fa-power-off align-middle"></i>
                  </span>
                  <span class="sidenav-normal">
                    <div class="row">
                      <div class="col-4 pe-0 ps-0">
                        <i class="fa fa-power-off align-middle"></i>
                      </div>
                      <div class="col-8 ps-0">
                        <span class="align-middle ps-1">Log out</span>
                      </div>
                    </div>
                  </span>
                </a>
              </form>
            </li>
          </ul>
        </div>
      </li>
      <hr class="horizontal dark mt-0 ">
      <li class="nav-item">
        <a class="nav-link {{ Route::currentRouteName() == 'home' ? 'active' : '' }}" href="{{ route('home') }}">
          <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center pt-1">
            <i class="fa fa-home text-primary opacity-10" style="font-size: large; font-weight: 500 "></i>
          </div>
          <span class="nav-link-text ms-1">Home</span>
        </a>
      </li>

    @can('viewAny', App\Models\Saeb::class)
    <li class="nav-item {{ Route::currentRouteName() == 'saeb.index' ? 'active' : '' }}">
        <a class="nav-link {{ Route::currentRouteName() == 'saeb.index' ? 'active' : '' }}" href="{{ route('saeb.index') }}">
            <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center pt-1">
                <i class="fa fa-pie-chart text-primary opacity-10" style="font-size: large;"></i>
            </div>
            <span class="nav-link-text ms-1">SAEB Summary</span>
        </a>
    </li>

    <li class="nav-item {{ str_contains(request()->url(), 'administrator/saebs') ? 'active' : '' }}">
        <a class="nav-link {{ str_contains(request()->url(), 'administrator/saebs') ? 'active' : '' }}" href="{{ route('saebs.index') }}">
            <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center pt-1">
                <i class="fa fa-line-chart text-primary opacity-10" style="font-size: large;"></i>
            </div>
            <span class="nav-link-text ms-1">SAEB</span>
        </a>
    </li>
    @endcan

    @can('viewAny', App\Models\Procurement::class)
    <li class="nav-item {{ str_contains(request()->url(), 'administrator/procurements') ? 'active' : '' }}">
        <a class="nav-link {{ str_contains(request()->url(), 'administrator/procurements') ? 'active' : '' }}" href="{{ route('procurements.index') }}">
            <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center pt-1">
                <i class="fa fa-shopping-cart text-primary opacity-10" style="font-size: large;"></i>
            </div>
            <span class="nav-link-text ms-1">Procurements</span>
        </a>
    </li>
    @endcan

    {{-- resources/views/layouts/navbars/sidebar (or similar) --}}

    @can('viewAny', App\Models\FinancialPlan::class)
    <li class="nav-item {{ request()->routeIs('financial-plans.*') ? 'active' : '' }}">
        <a class="nav-link {{ request()->routeIs('financial-plans.*') ? 'active' : '' }}"
        href="{{ route('financial-plans.plans') }}">
            <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center pt-1">
                <i class="fa fa-money text-primary opacity-10" style="font-size: large;"></i>
            </div>
            <span class="nav-link-text ms-1">Financial Plan</span>
        </a>
    </li>
    @endcan

      @php
        $admin_modules = App\Models\Module::where('administrator', 'Y')->get();
      @endphp
      @if (auth()->user()->isSuperAdmin() || count(array_intersect(auth()->user()->role->permissions->pluck('module_id')->toArray(), $admin_modules->pluck('id')->toArray())) > 0)
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#administrator" class="nav-link {{ $isAdministratorRoute ? 'active' : '' }}" aria-controls="administrator" role="button" aria-expanded="{{ $isAdministratorRoute ? 'true' : 'false' }}">
            <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center">
              <i class="ni ni-settings text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Administration</span>
          </a>
          <div class="collapse {{ $isAdministratorRoute ? 'show' : '' }}" id="administrator">
            <ul class="nav ms-4">

              @can('viewAny', App\Models\User::class)
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/users') == true ? 'active' : '' }}">
                  <a class="nav-link {{ str_contains(request()->url(), 'administrator/users') == true ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <span class="sidenav-mini-icon">UM</span>
                    <span class="sidenav-normal"> User Management </span>
                  </a>
                </li>
              @endcan

              @if(auth()->user()->isSuperAdmin())
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/agencies') == true ? 'active' : '' }}">
                  <a class="nav-link {{ str_contains(request()->url(), 'administrator/agencies') == true ? 'active' : '' }}" href="{{ route('agencies.index') }}">
                    <span class="sidenav-mini-icon">AM</span>
                    <span class="sidenav-normal"> Agency Management </span>
                  </a>
                </li>
              @endif

              @can('viewAny', App\Models\Form::class)
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/forms') == true ? 'active' : '' }}">
                  <a class="nav-link {{ str_contains(request()->url(), 'administrator/forms') == true ? 'active' : '' }}" href="{{ route('forms.index') }}">
                    <span class="sidenav-mini-icon">FM</span>
                    <span class="sidenav-normal"> Form Management </span>
                  </a>
                </li>
              @endcan

              @can('viewAny', App\Models\UpliftPillar::class)
                <li class="nav-item {{ $isUpliftBuilderRoute ? 'active' : '' }}">
                  <a class="nav-link {{ $isUpliftBuilderRoute ? 'active' : '' }}" href="{{ route('uplift-builder.index') }}">
                    <span class="sidenav-mini-icon">UB</span>
                    <span class="sidenav-normal"> UPLIFT Form Management </span>
                  </a>
                </li>
              @endcan

              @can('viewAny', App\Models\Inquiry::class)
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/inquiries') == true ? 'active' : '' }}">
                  <a class="nav-link {{ str_contains(request()->url(), 'administrator/inquiries') == true ? 'active' : '' }}" href="{{ route('inquiries.index') }}">
                    <span class="sidenav-mini-icon">UI</span>
                    <span class="sidenav-normal"> User Inquiry </span>
                  </a>
                </li>
              @endcan


              @can('viewAny', App\Models\Role::class)
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/roles') == true ? 'active' : '' }}">
                  <a class="nav-link {{ str_contains(request()->url(), 'administrator/roles') == true ? 'active' : '' }}" href="{{ route('roles.index') }}">
                    <span class="sidenav-mini-icon">RP</span>
                    <span class="sidenav-normal"> Roles and Permissions </span>
                  </a>
                </li>
              @endcan

              @can('viewAny', App\Models\Staff::class)
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/staffs') == true ? 'active' : '' }}">
                    <a class="nav-link {{ str_contains(request()->url(), 'administrator/staffs') == true ? 'active' : '' }}" href="{{ route('staffs.index') }}">
                    <span class="sidenav-mini-icon">SM</span>
                    <span class="sidenav-normal"> Staff Management </span>
                    </a>
                </li>
              @endcan

              @can('viewAny', App\Models\Division::class)
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/divisions') == true ? 'active' : '' }}">
                    <a class="nav-link {{ str_contains(request()->url(), 'administrator/divisions') == true ? 'active' : '' }}" href="{{ route('divisions.index') }}">
                    <span class="sidenav-mini-icon">DM</span>
                    <span class="sidenav-normal"> Division Management</span>
                    </a>
                </li>
              @endcan

              @can('viewAny', App\Models\Parameter::class)
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/parameters') == true ? 'active' : '' }}">
                  <a class="nav-link {{ str_contains(request()->url(), 'administrator/parameters') == true ? 'active' : '' }}" href="{{ route('parameters.index') }}">
                    <span class="sidenav-mini-icon">SP</span>
                    <span class="sidenav-normal"> System Parameters </span>
                  </a>
                </li>
              @endcan

              @can('viewAny', App\Models\Holiday::class)
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/holidays') == true ? 'active' : '' }}">
                  <a class="nav-link {{ str_contains(request()->url(), 'administrator/holidays') == true ? 'active' : '' }}" href="{{ route('holidays.index') }}">
                    <span class="sidenav-mini-icon">HS</span>
                    <span class="sidenav-normal"> Holidays and Suspensions</span>
                  </a>
                </li>
              @endcan

              @can('viewAny', App\Models\RestrictedIp::class)
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/restrictedips') == true ? 'active' : '' }}">
                  <a class="nav-link {{ str_contains(request()->url(), 'administrator/restrictedips') == true ? 'active' : '' }}" href="{{ route('restrictedips.index') }}">
                    <span class="sidenav-mini-icon">IP</span>
                    <span class="sidenav-normal"> Restricted IPs</span>
                  </a>
                </li>
              @endcan

              @if(App\Http\Controllers\ApiClientController::canView(auth()->user()))
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/api-clients') == true ? 'active' : '' }}">
                  <a class="nav-link {{ str_contains(request()->url(), 'administrator/api-clients') == true ? 'active' : '' }}" href="{{ route('api-clients.index') }}">
                    <span class="sidenav-mini-icon">API</span>
                    <span class="sidenav-normal"> API Clients</span>
                  </a>
                </li>
              @endif

              @can('viewAny', App\Models\SystemLog::class)
                <li class="nav-item {{ str_contains(request()->url(), 'administrator/systemlogs') == true ? 'active' : '' }}">
                  <a class="nav-link {{ str_contains(request()->url(), 'administrator/systemlogs') == true ? 'active' : '' }}" href="{{ route('systemlogs.index') }}">
                    <span class="sidenav-mini-icon">SL</span>
                    <span class="sidenav-normal"> System Logs</span>
                  </a>
                </li>
              @endcan
            </ul>
          </div>
        </li>
      @endif


      <li class="nav-item">
        <a class="nav-link {{ str_contains(request()->url(), 'contact-us') == true ? 'active' : '' }}" href="{{ route('auth.contactus.create') }}">
          <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center pt-1">
            <i class="fa fa-comments-o text-primary opacity-10" style="font-size: large; font-weight: 500 "></i>
          </div>
          <span class="nav-link-text ms-1">Contact Us</span>
        </a>
      </li>

    </ul>
  </div>

</aside>

@push('js')

@endpush

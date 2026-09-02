@php
    $isAdministratorRoute = request()->routeIs(
        'users.*',
        'agencies.*',
        'forms.*',
        'uplift-builder.*',
        'inquiries.*',
        'roles.*',
        'staffs.*',
        'staff-personnel.*',
        'divisions.*',
        'parameters.*',
        'holidays.*',
        'restrictedips.*',
        'api-clients.*',
        'systemlogs.*'
    );

    $isUpliftBuilderRoute = request()->routeIs('uplift-builder.*');

    $admin_modules = App\Models\Module::where('administrator', 'Y')->get();

    $hasAdministratorAccess =
        auth()->user()->isSuperAdmin()
        || count(
            array_intersect(
                auth()->user()->role->permissions->pluck('module_id')->toArray(),
                $admin_modules->pluck('id')->toArray()
            )
        ) > 0;
@endphp

<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-2 bg-white shadow-sm"
    id="sidenav-main"
    style="overflow: hidden;"
>
    <div class="sidenav-header">

        <i
            class="fa fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true"
            id="iconSidenav"
        ></i>

        <a
            class="navbar-brand m-0 d-flex align-items-center"
            href="{{ route('home') }}"
        >
            <img
                src="{{ $logo ?? '/assets/img/neda/logo.png' }}"
                class="navbar-brand-img"
                alt="main_logo"
                style="max-height: 34px;"
            >

            <span class="ms-3 font-weight-bold text-dark">
                D.I.R.E.K. Application
            </span>
        </a>

    </div>

    <hr class="horizontal dark mt-0 mb-2">

    <div
        class="navbar-collapse w-auto h-auto"
        id="sidenav-collapse-main"
        style="
            display: block !important;
            height: calc(100vh - 120px);
            overflow-y: auto;
            overflow-x: hidden;
        "
    >
        <ul class="navbar-nav">

    {{-- User --}}
    <li class="nav-item">

        <a
            href="javascript:void(0);"
            class="nav-link {{ Route::currentRouteName() == 'user-profile' ? 'active' : '' }}"
            id="profile-menu-toggle"
            role="button"
            aria-expanded="{{ Route::currentRouteName() == 'user-profile' ? 'true' : 'false' }}"
        >

            <div
                class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center"
            >
                <i class="fa fa-user text-primary"></i>
            </div>

            <span class="nav-link-text ms-1">
                {{ auth()->user()->full_name }}
            </span>

            <i
                class="fa fa-angle-down ms-auto"
                id="profile-menu-icon"
                style="transition: transform 0.2s ease;"
            ></i>

        </a>


        <div
            id="profile-menu"
            style="{{ Route::currentRouteName() == 'user-profile' ? '' : 'display: none;' }}"
        >

            <ul class="nav ms-4">

                <li class="nav-item">

                    <a
                        class="nav-link {{ Route::currentRouteName() == 'user-profile' ? 'active' : '' }}"
                        href="{{ route('user-profile') }}"
                    >

                        <span class="sidenav-mini-icon">
                            <i class="fa fa-user"></i>
                        </span>

                        <span class="sidenav-normal">
                            My profile
                        </span>

                    </a>

                </li>


                <li class="nav-item">

                    <form
                        method="post"
                        action="{{ route('logout') }}"
                        id="logout-form-sidenav"
                    >
                        @csrf

                        <a
                            class="nav-link"
                            href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form-sidenav').submit();"
                        >

                            <span class="sidenav-mini-icon">
                                <i class="fa fa-power-off"></i>
                            </span>

                            <span class="sidenav-normal">
                                Log out
                            </span>

                        </a>

                    </form>

                </li>

            </ul>

        </div>

    </li>


            <hr class="horizontal dark mt-2 mb-2">


            {{-- Home --}}
            <li class="nav-item">

                <a
                    class="nav-link {{ Route::currentRouteName() == 'home' ? 'active' : '' }}"
                    href="{{ route('home') }}"
                >
                    <div
                        class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center"
                    >
                        <i class="fa fa-home text-primary"></i>
                    </div>

                    <span class="nav-link-text ms-1">
                        Home
                    </span>
                </a>

            </li>


            {{-- SAEB --}}
            @can('viewAny', App\Models\Saeb::class)

                <li class="nav-item">

                    <a
                        class="nav-link {{ Route::currentRouteName() == 'saeb.index' ? 'active' : '' }}"
                        href="{{ route('saeb.index') }}"
                    >
                        <div
                            class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center"
                        >
                            <i class="fa fa-pie-chart text-primary"></i>
                        </div>

                        <span class="nav-link-text ms-1">
                            SAEB Summary
                        </span>
                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link {{ str_contains(request()->url(), 'administrator/saebs') ? 'active' : '' }}"
                        href="{{ route('saebs.index') }}"
                    >
                        <div
                            class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center"
                        >
                            <i class="fa fa-line-chart text-primary"></i>
                        </div>

                        <span class="nav-link-text ms-1">
                            SAEB
                        </span>
                    </a>

                </li>

            @endcan


            {{-- Procurement --}}
            @can('viewAny', App\Models\Procurement::class)

                <li class="nav-item">

                    <a
                        class="nav-link {{ str_contains(request()->url(), 'administrator/procurements') ? 'active' : '' }}"
                        href="{{ route('procurements.index') }}"
                    >
                        <div
                            class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center"
                        >
                            <i class="fa fa-shopping-cart text-primary"></i>
                        </div>

                        <span class="nav-link-text ms-1">
                            Procurements
                        </span>
                    </a>

                </li>

            @endcan


            {{-- Financial Plan --}}
            @can('viewAny', App\Models\FinancialPlan::class)

                <li class="nav-item">

                    <a
                        class="nav-link {{ request()->routeIs('financial-plans.*') ? 'active' : '' }}"
                        href="{{ route('financial-plans.plans') }}"
                    >
                        <div
                            class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center"
                        >
                            <i class="fa fa-money text-primary"></i>
                        </div>

                        <span class="nav-link-text ms-1">
                            Financial Plan
                        </span>
                    </a>

                </li>

            @endcan


    {{-- Administration --}}
    @if ($hasAdministratorAccess)
        <li class="nav-item">
            <a
                href="javascript:void(0);"
                class="nav-link {{ $isAdministratorRoute ? 'active' : '' }}"
                id="administrator-menu-toggle"
                role="button"
                aria-expanded="{{ $isAdministratorRoute ? 'true' : 'false' }}"
            >
                <div
                    class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center"
                >
                    <i class="fa fa-cog text-primary"></i>
                </div>

                <span class="nav-link-text ms-1">
                    Administration
                </span>

                <i
                    class="fa fa-angle-down ms-auto"
                    id="administrator-menu-icon"
                    style="transition: transform 0.2s ease;"
                ></i>
            </a>

            <div
                id="administrator-menu"
                style="{{ $isAdministratorRoute ? '' : 'display: none;' }}"
            >
                <ul class="nav ms-4">

                    @can('viewAny', App\Models\User::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/users') ? 'active' : '' }}"
                                href="{{ route('users.index') }}"
                            >
                                <span class="sidenav-mini-icon">UM</span>
                                <span class="sidenav-normal">User Management</span>
                            </a>
                        </li>
                    @endcan

                    @if (auth()->user()->isSuperAdmin())
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/agencies') ? 'active' : '' }}"
                                href="{{ route('agencies.index') }}"
                            >
                                <span class="sidenav-mini-icon">AM</span>
                                <span class="sidenav-normal">Agency Management</span>
                            </a>
                        </li>
                    @endif

                    @can('viewAny', App\Models\Form::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/forms') ? 'active' : '' }}"
                                href="{{ route('forms.index') }}"
                            >
                                <span class="sidenav-mini-icon">FM</span>
                                <span class="sidenav-normal">Form Management</span>
                            </a>
                        </li>
                    @endcan

                    @can('viewAny', App\Models\UpliftPillar::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ $isUpliftBuilderRoute ? 'active' : '' }}"
                                href="{{ route('uplift-builder.index') }}"
                            >
                                <span class="sidenav-mini-icon">UB</span>
                                <span class="sidenav-normal">UPLIFT Form Management</span>
                            </a>
                        </li>
                    @endcan

                    @can('viewAny', App\Models\Inquiry::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/inquiries') ? 'active' : '' }}"
                                href="{{ route('inquiries.index') }}"
                            >
                                <span class="sidenav-mini-icon">UI</span>
                                <span class="sidenav-normal">User Inquiry</span>
                            </a>
                        </li>
                    @endcan

                    @can('viewAny', App\Models\Role::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/roles') ? 'active' : '' }}"
                                href="{{ route('roles.index') }}"
                            >
                                <span class="sidenav-mini-icon">RP</span>
                                <span class="sidenav-normal">Roles and Permissions</span>
                            </a>
                        </li>
                    @endcan

                    @can('viewAny', App\Models\Staff::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/staffs') ? 'active' : '' }}"
                                href="{{ route('staffs.index') }}"
                            >
                                <span class="sidenav-mini-icon">SM</span>
                                <span class="sidenav-normal">Staff Management</span>
                            </a>
                        </li>
                    @endcan

                    {{-- Staff Personnel --}}
                    @if (in_array((int) auth()->user()->role_id, [1, 29], true))
                        <li class="nav-item">
                            <a
                                class="nav-link {{ request()->routeIs('staff-personnel.*') ? 'active' : '' }}"
                                href="{{ route('staff-personnel.index') }}"
                            >
                                <span class="sidenav-mini-icon">SP</span>
                                <span class="sidenav-normal">Staff Personnel</span>
                            </a>
                        </li>
                    @endif

                    @can('viewAny', App\Models\Division::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/divisions') ? 'active' : '' }}"
                                href="{{ route('divisions.index') }}"
                            >
                                <span class="sidenav-mini-icon">DM</span>
                                <span class="sidenav-normal">Division Management</span>
                            </a>
                        </li>
                    @endcan

                    @can('viewAny', App\Models\Parameter::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/parameters') ? 'active' : '' }}"
                                href="{{ route('parameters.index') }}"
                            >
                                <span class="sidenav-mini-icon">SP</span>
                                <span class="sidenav-normal">System Parameters</span>
                            </a>
                        </li>
                    @endcan

                    @can('viewAny', App\Models\Holiday::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/holidays') ? 'active' : '' }}"
                                href="{{ route('holidays.index') }}"
                            >
                                <span class="sidenav-mini-icon">HS</span>
                                <span class="sidenav-normal">Holidays and Suspensions</span>
                            </a>
                        </li>
                    @endcan

                    @can('viewAny', App\Models\RestrictedIp::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/restrictedips') ? 'active' : '' }}"
                                href="{{ route('restrictedips.index') }}"
                            >
                                <span class="sidenav-mini-icon">IP</span>
                                <span class="sidenav-normal">Restricted IPs</span>
                            </a>
                        </li>
                    @endcan

                    @if (App\Http\Controllers\ApiClientController::canView(auth()->user()))
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/api-clients') ? 'active' : '' }}"
                                href="{{ route('api-clients.index') }}"
                            >
                                <span class="sidenav-mini-icon">API</span>
                                <span class="sidenav-normal">API Clients</span>
                            </a>
                        </li>
                    @endif

                    @can('viewAny', App\Models\SystemLog::class)
                        <li class="nav-item">
                            <a
                                class="nav-link {{ str_contains(request()->url(), 'administrator/systemlogs') ? 'active' : '' }}"
                                href="{{ route('systemlogs.index') }}"
                            >
                                <span class="sidenav-mini-icon">SL</span>
                                <span class="sidenav-normal">System Logs</span>
                            </a>
                        </li>
                    @endcan

                </ul>
            </div>
        </li>
    @endif

<script>
document.addEventListener('DOMContentLoaded', function () {

    function setupSidenavMenu(toggleId, menuId, iconId) {

        const toggle = document.getElementById(toggleId);
        const menu = document.getElementById(menuId);
        const icon = document.getElementById(iconId);

        if (!toggle || !menu) {
            return;
        }

        function isMenuOpen() {
            return menu.style.display !== 'none';
        }

        function updateMenuState() {

            const isOpen = isMenuOpen();

            toggle.setAttribute(
                'aria-expanded',
                isOpen ? 'true' : 'false'
            );

            if (icon) {
                icon.style.transform = isOpen
                    ? 'rotate(180deg)'
                    : 'rotate(0deg)';
            }
        }

        toggle.addEventListener('click', function (event) {

            event.preventDefault();

            menu.style.display = isMenuOpen()
                ? 'none'
                : 'block';

            updateMenuState();

        });

        updateMenuState();
    }


    // User profile menu
    setupSidenavMenu(
        'profile-menu-toggle',
        'profile-menu',
        'profile-menu-icon'
    );


    // Administration menu
    setupSidenavMenu(
        'administrator-menu-toggle',
        'administrator-menu',
        'administrator-menu-icon'
    );

});
</script>


            {{-- Contact Us --}}
            <li class="nav-item">

                <a
                    class="nav-link {{ str_contains(request()->url(), 'contact-us') ? 'active' : '' }}"
                    href="{{ route('auth.contactus.create') }}"
                >
                    <div
                        class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center"
                    >
                        <i class="fa fa-comments-o text-primary"></i>
                    </div>

                    <span class="nav-link-text ms-1">
                        Contact Us
                    </span>
                </a>

            </li>

        </ul>
    </div>

</aside>

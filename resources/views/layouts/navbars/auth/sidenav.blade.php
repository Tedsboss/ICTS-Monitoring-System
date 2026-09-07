@php
    $isAdministratorRoute = request()->routeIs(
        'users.*',
        'roles.*',
        'staffs.*',
        'staff-personnel.*',
        'divisions.*',
        'parameters.*',
        'systemlogs.*'
    );
    $isProfileRoute = request()->routeIs('user-profile');
    $canViewFinancialPlan = auth()->user()->can(
        'viewAny',
        App\Models\FinancialPlan::class
    );
    $canManageFinancialPlanAllocationTypes =
        in_array((int) auth()->user()->role_id, [1, 29], true)
        || (
            ! empty(auth()->user()->staff_id)
            && auth()->user()->role
            && auth()->user()->role->permissions->contains(function ($permission) {
                return strcasecmp((string) optional($permission->module)->name, 'Allocation Type Management') === 0
                    && strcasecmp((string) $permission->name, 'view') === 0;
            })
        );
    $administratorModuleIds = App\Models\Module::query()
        ->where('administrator', 'Y')
        ->pluck('id')
        ->toArray();
    $userModuleIds = [];
    if (
        auth()->user()->role &&
        auth()->user()->role->permissions
    ) {
        $userModuleIds = auth()->user()
            ->role
            ->permissions
            ->pluck('module_id')
            ->toArray();
    }
    $hasAdministratorAccess =
        auth()->user()->isSuperAdmin()
        || count(
            array_intersect(
                $userModuleIds,
                $administratorModuleIds
            )
        ) > 0;
@endphp
<style>
    #sidenav-main {
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
        border-radius: 18px !important;
        box-shadow:
            0 10px 30px rgba(15, 23, 42, 0.08) !important;
    }
    #sidenav-main .sidenav-header {
        height: auto;
        min-height: 76px;
        padding: 14px 12px;
    }
    #sidenav-main .navbar-brand {
        min-width: 0;
        padding: 0.5rem;
        border-radius: 12px;
    }
    #sidenav-main .navbar-brand:hover {
        background: #f8fafc;
    }
    #sidenav-main .navbar-brand-img {
        width: auto;
        max-width: 38px;
        max-height: 38px !important;
        object-fit: contain;
    }
    #sidenav-main .direk-brand-title {
        min-width: 0;
        margin-left: 0.75rem;
        color: #0f172a;
        font-size: 0.82rem;
        font-weight: 700;
        line-height: 1.25;
    }
    #sidenav-main .direk-brand-subtitle {
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 600;
    }
    #sidenav-main .direk-sidebar-divider {
        height: 1px;
        margin: 0 14px 8px;
        border: 0;
        background: #e2e8f0;
    }
    #sidenav-main .navbar-nav {
        padding: 0 8px 18px;
    }
    #sidenav-main .nav-link {
        display: flex;
        align-items: center;
        min-height: 42px;
        margin: 2px 0;
        border-radius: 10px;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 600;
        transition:
            background-color 0.15s ease,
            color 0.15s ease,
            box-shadow 0.15s ease;
    }
    #sidenav-main a.nav-link:hover,
    #sidenav-main button.nav-link:hover {
        background: #f1f5f9 !important;
        color: #0f172a;
    }
    #sidenav-main .nav-link.active {
        background: #f0f9ff !important;
        color: #0369a1;
        box-shadow: inset 3px 0 0 #0284c7;
    }
    #sidenav-main button.nav-link {
        width: 100%;
        padding-right: 1rem;
        border: 0;
        background: transparent;
        font-family: inherit;
        text-align: left;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
    }
    #sidenav-main button.nav-link:focus,
    #sidenav-main button.nav-link:active {
        outline: none;
    }
    #sidenav-main button.nav-link .nav-link-text {
        flex: 1;
        min-width: 0;
        text-align: left;
    }
    #sidenav-main .nav-link .icon {
        width: 30px;
        min-width: 30px;
        height: 30px;
        margin-right: 2px;
        border-radius: 8px;
        background: #f8fafc;
    }
    #sidenav-main .nav-link.active .icon {
        background: #e0f2fe;
    }
    #sidenav-main .nav-link .icon i {
        color: #64748b !important;
        font-size: 0.82rem;
    }
    #sidenav-main .nav-link.active .icon i {
        color: #0284c7 !important;
    }
    #sidenav-main .direk-section-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 12px;
    }
    #sidenav-main .direk-section-heading span {
        color: #94a3b8;
        font-size: 0.63rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    #sidenav-main .direk-section-heading::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }
    #sidenav-main .direk-submenu {
        margin: 2px 0 6px;
        padding-left: 31px;
    }
    #sidenav-main .direk-submenu .nav-link {
        min-height: 34px;
        padding: 0.4rem 0.75rem;
        color: #64748b;
        font-size: 0.76rem;
        font-weight: 500;
    }
    #sidenav-main .direk-submenu .nav-link:hover {
        color: #0f172a;
    }
    #sidenav-main .direk-submenu .nav-link.active {
        background: #f8fafc !important;
        color: #0369a1;
        box-shadow: none;
        font-weight: 700;
    }
    #sidenav-main .sidenav-mini-icon {
        display: inline-flex;
        width: 22px;
        min-width: 22px;
        justify-content: center;
        margin-right: 5px;
        color: #94a3b8;
    }
    #sidenav-main .direk-menu-chevron {
        margin-left: auto;
        color: #94a3b8;
        font-size: 0.7rem;
        transition: transform 0.2s ease;
    }
    #sidenav-collapse-main::-webkit-scrollbar {
        width: 5px;
    }
    #sidenav-collapse-main::-webkit-scrollbar-track {
        background: transparent;
    }
    #sidenav-collapse-main::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #cbd5e1;
    }
    #sidenav-collapse-main::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    #sidenav-main .direk-profile-item {
        position: relative;
        z-index: 20;
    }
    #sidenav-main .direk-profile-menu {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1000;
        padding: 5px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    }
    #sidenav-main .direk-profile-menu .direk-submenu {
        margin: 0;
        padding-left: 0;
    }
    #sidenav-main .direk-profile-menu .nav-link {
        min-height: 34px;
        margin: 0;
        padding: 0.45rem 0.65rem;
        border-radius: 7px;
    }
    #sidenav-main .direk-profile-menu form {
        margin: 0;
    }
</style>
<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs
           border-0 border-radius-xl my-3 fixed-start
           ms-2 bg-white shadow-sm"
    id="sidenav-main"
>
    <div class="sidenav-header">
        <i
            class="fa fa-times p-3 cursor-pointer text-secondary
                   opacity-5 position-absolute end-0 top-0
                   d-none d-xl-none"
            aria-hidden="true"
            id="iconSidenav"
        ></i>
        <a
            class="navbar-brand m-0 d-flex align-items-center"
            href="{{ route('home') }}"
            title="DIREK Home"
        >
            <img
                src="{{ $logo ?? '/assets/img/neda/logo.png' }}"
                class="navbar-brand-img"
                alt="DIREK logo"
            >
            <span class="direk-brand-title">
                D.I.R.E.K.
                <br>
                <span class="direk-brand-subtitle">
                    Integrated Reporting
                </span>
            </span>
        </a>
    </div>
    <hr class="direk-sidebar-divider">
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
            <li class="nav-item direk-profile-item">
                <button
                    type="button"
                    class="nav-link {{ $isProfileRoute ? 'active' : '' }}"
                    id="profile-menu-toggle"
                    aria-expanded="{{ $isProfileRoute
                        ? 'true'
                        : 'false' }}"
                    aria-controls="profile-menu"
                >
                    <div
                        class="icon icon-shape icon-sm text-center
                               d-flex align-items-center
                               justify-content-center"
                    >
                        <i
                            class="fa fa-user"
                            aria-hidden="true"
                        ></i>
                    </div>
                    <span
                        class="nav-link-text ms-1 text-truncate"
                        title="{{ auth()->user()->full_name }}"
                    >
                        {{ auth()->user()->full_name }}
                    </span>
                    <i
                        class="fa fa-angle-down
                               direk-menu-chevron"
                        id="profile-menu-icon"
                        aria-hidden="true"
                    ></i>
                </button>
                <div
                    id="profile-menu"
                    class="direk-profile-menu"
                    style="{{ $isProfileRoute ? '' : 'display: none;' }}"
                >
                    <ul class="nav direk-submenu">
                        <li class="nav-item">
                            <a
                                class="nav-link
                                       {{ $isProfileRoute
                                            ? 'active'
                                            : '' }}"
                                href="{{ route('user-profile') }}"
                            >
                                <span class="sidenav-mini-icon">
                                    <i
                                        class="fa fa-user"
                                        aria-hidden="true"
                                    ></i>
                                </span>
                                <span class="sidenav-normal">
                                    My Profile
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <form
                                method="POST"
                                action="{{ route('logout') }}"
                                id="logout-form-sidenav"
                            >
                                @csrf
                                <button
                                    type="submit"
                                    class="nav-link"
                                >
                                    <span class="sidenav-mini-icon">
                                        <i
                                            class="fa fa-power-off"
                                            aria-hidden="true"
                                        ></i>
                                    </span>
                                    <span class="sidenav-normal">
                                        Log Out
                                    </span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </li>
            <hr class="direk-sidebar-divider mt-2 mb-2">
            <li class="nav-item">
                <a
                    class="nav-link
                           {{ request()->routeIs('home')
                                ? 'active'
                                : '' }}"
                    href="{{ route('home') }}"
                >
                    <div
                        class="icon icon-shape icon-sm text-center
                               d-flex align-items-center
                               justify-content-center"
                    >
                        <i
                            class="fa fa-home"
                            aria-hidden="true"
                        ></i>
                    </div>
                    <span class="nav-link-text ms-1">
                        Home
                    </span>
                </a>
            </li>
            @if ($canViewFinancialPlan || $canManageFinancialPlanAllocationTypes)
                <li class="nav-item mt-4 mb-2">
                    <div class="direk-section-heading">
                        <span>Financial Management</span>
                    </div>
                </li>
                @if ($canViewFinancialPlan)
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('financial-plans.*') ? 'active' : '' }}"
                            href="{{ route('financial-plans.plans') }}"
                        >
                            <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center">
                                <i class="fa fa-money" aria-hidden="true"></i>
                            </div>
                            <span class="nav-link-text ms-1">Financial Plan</span>
                        </a>
                    </li>
                @endif
                @if ($canManageFinancialPlanAllocationTypes)
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('financial-plan-allocation-types.*') ? 'active' : '' }}"
                            href="{{ route('financial-plan-allocation-types.index') }}"
                        >
                            <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center">
                                <i class="fa fa-tags" aria-hidden="true"></i>
                            </div>
                            <span class="nav-link-text ms-1">Allocation Types</span>
                        </a>
                    </li>
                @endif
            @endif
            @if ($hasAdministratorAccess)
                <li class="nav-item mt-4 mb-2">
                    <div class="direk-section-heading">
                        <span>
                            Administration
                        </span>
                    </div>
                </li>
                <li class="nav-item">
                <button
                    type="button"
                    class="nav-link
                        {{ $isAdministratorRoute
                                ? 'active'
                                : '' }}"
                    id="administrator-menu-toggle"
                    aria-expanded="{{ $isAdministratorRoute
                        ? 'true'
                        : 'false' }}"
                    aria-controls="administrator-menu"
                >
                    <div
                        class="icon icon-shape icon-sm text-center
                            d-flex align-items-center
                            justify-content-center"
                    >
                        <i
                            class="fa fa-cog"
                            aria-hidden="true"
                        ></i>
                    </div>
                    <span class="nav-link-text ms-1">
                        Administration
                    </span>
                    <i
                        class="fa fa-angle-down
                            direk-menu-chevron"
                        id="administrator-menu-icon"
                        aria-hidden="true"
                        style="{{ $isAdministratorRoute
                            ? 'transform: rotate(180deg);'
                            : 'transform: rotate(0deg);' }}"
                    ></i>
                </button>
                        <div
                            id="administrator-menu"
                            style="{{ $isAdministratorRoute
                                ? 'display: block;'
                                : 'display: none;' }}"
                        >
                        <ul class="nav direk-submenu">
                            @can(
                                'viewAny',
                                App\Models\User::class
                            )
                                <li class="nav-item">
                                    <a
                                        class="nav-link
                                               {{ request()->routeIs(
                                                    'users.*'
                                               )
                                                    ? 'active'
                                                    : '' }}"
                                        href="{{ route(
                                            'users.index'
                                        ) }}"
                                    >
                                        <span class="sidenav-mini-icon">
                                            <i
                                                class="fa fa-users"
                                                aria-hidden="true"
                                            ></i>
                                        </span>
                                        <span class="sidenav-normal">
                                            User Management
                                        </span>
                                    </a>
                                </li>
                            @endcan
                            @can(
                                'viewAny',
                                App\Models\Role::class
                            )
                                <li class="nav-item">
                                    <a
                                        class="nav-link
                                               {{ request()->routeIs(
                                                    'roles.*'
                                               )
                                                    ? 'active'
                                                    : '' }}"
                                        href="{{ route(
                                            'roles.index'
                                        ) }}"
                                    >
                                        <span class="sidenav-mini-icon">
                                            <i
                                                class="fa fa-user-secret"
                                                aria-hidden="true"
                                            ></i>
                                        </span>
                                        <span class="sidenav-normal">
                                            Roles & Permissions
                                        </span>
                                    </a>
                                </li>
                            @endcan
                            @can(
                                'viewAny',
                                App\Models\Staff::class
                            )
                                <li class="nav-item">
                                    <a
                                        class="nav-link
                                               {{ request()->routeIs(
                                                    'staffs.*'
                                               )
                                                    ? 'active'
                                                    : '' }}"
                                        href="{{ route(
                                            'staffs.index'
                                        ) }}"
                                    >
                                        <span class="sidenav-mini-icon">
                                            <i
                                                class="fa fa-building"
                                                aria-hidden="true"
                                            ></i>
                                        </span>
                                        <span class="sidenav-normal">
                                            Staff Management
                                        </span>
                                    </a>
                                </li>
                            @endcan
                            {{-- Preserve legacy administrator***
**                                 access until role 29 cleanup. --}}
                            @if (
                                in_array(
                                    (int) auth()->user()->role_id,
                                    [1, 29],
                                    true
                                )
                            )
                                <li class="nav-item">
                                    <a
                                        class="nav-link
                                               {{ request()->routeIs(
                                                    'staff-personnel.*'
                                               )
                                                    ? 'active'
                                                    : '' }}"
                                        href="{{ route(
                                            'staff-personnel.index'
                                        ) }}"
                                    >
                                        <span class="sidenav-mini-icon">
                                            <i
                                                class="fa fa-address-book"
                                                aria-hidden="true"
                                            ></i>
                                        </span>
                                        <span class="sidenav-normal">
                                            Staff Personnel
                                        </span>
                                    </a>
                                </li>
                            @endif
                            @can(
                                'viewAny',
                                App\Models\Division::class
                            )
                                <li class="nav-item">
                                    <a
                                        class="nav-link
                                               {{ request()->routeIs(
                                                    'divisions.*'
                                               )
                                                    ? 'active'
                                                    : '' }}"
                                        href="{{ route(
                                            'divisions.index'
                                        ) }}"
                                    >
                                        <span class="sidenav-mini-icon">
                                            <i
                                                class="fa fa-sitemap"
                                                aria-hidden="true"
                                            ></i>
                                        </span>
                                        <span class="sidenav-normal">
                                            Division Management
                                        </span>
                                    </a>
                                </li>
                            @endcan
                            @can(
                                'viewAny',
                                App\Models\Parameter::class
                            )
                                <li class="nav-item">
                                    <a
                                        class="nav-link
                                               {{ request()->routeIs(
                                                    'parameters.*'
                                               )
                                                    ? 'active'
                                                    : '' }}"
                                        href="{{ route(
                                            'parameters.index'
                                        ) }}"
                                    >
                                        <span class="sidenav-mini-icon">
                                            <i
                                                class="fa fa-sliders"
                                                aria-hidden="true"
                                            ></i>
                                        </span>
                                        <span class="sidenav-normal">
                                            System Parameters
                                        </span>
                                    </a>
                                </li>
                            @endcan
                            @can(
                                'viewAny',
                                App\Models\SystemLog::class
                            )
                                <li class="nav-item">
                                    <a
                                        class="nav-link
                                               {{ request()->routeIs(
                                                    'systemlogs.*'
                                               )
                                                    ? 'active'
                                                    : '' }}"
                                        href="{{ route(
                                            'systemlogs.index'
                                        ) }}"
                                    >
                                        <span class="sidenav-mini-icon">
                                            <i
                                                class="fa fa-list-alt"
                                                aria-hidden="true"
                                            ></i>
                                        </span>
                                        <span class="sidenav-normal">
                                            System Logs
                                        </span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endif
        </ul>
    </div>
</aside>
<script>
(function () {
    function setupSidenavMenu(toggleId, menuId, iconId) {
        const toggle = document.getElementById(toggleId);
        const menu = document.getElementById(menuId);
        const icon = document.getElementById(iconId);
        if (!toggle || !menu) return;
        const isOpen = () => window.getComputedStyle(menu).display !== 'none';
        const sync = open => {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (icon) icon.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
        };
        toggle.onclick = function (event) {
            event.preventDefault();
            event.stopPropagation();
            const nextOpen = !isOpen();
            menu.style.display = nextOpen ? 'block' : 'none';
            sync(nextOpen);
        };
        sync(isOpen());
        return { toggle, menu, icon, sync, isOpen };
    }
    const profile = setupSidenavMenu('profile-menu-toggle', 'profile-menu', 'profile-menu-icon');
    setupSidenavMenu('administrator-menu-toggle', 'administrator-menu', 'administrator-menu-icon');
    if (profile) {
        profile.menu.addEventListener('click', event => event.stopPropagation());
        document.addEventListener('click', () => {
            if (!profile.isOpen()) return;
            profile.menu.style.display = 'none';
            profile.sync(false);
        });
    }
})();
</script>

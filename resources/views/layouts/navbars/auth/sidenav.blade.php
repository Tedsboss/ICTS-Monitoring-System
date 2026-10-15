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
    $isAllocationManagementRoute = request()->routeIs(
        'allocations.*',
        'fiscal-years.*',
        'levels.*'
    );
    $isExpenseManagementRoute = request()->routeIs('expense-types.*');
    $isProfileRoute = request()->routeIs('user-profile');
    $canViewFinancialPlan = auth()->user()->can(
        'viewAny',
        App\Models\FinancialPlan::class
    );
    $canViewWorkPlan = auth()->user()->can(
        'viewAny',
        App\Models\WorkPlan::class
    );
    $canViewExpenseManagement =
        auth()->user()->isSuperAdmin()
        || (
            auth()->user()->role
            && auth()->user()->role->permissions->contains(function ($permission) {
                return strcasecmp(
                    (string) optional($permission->module)->name,
                    'Allocation Management'
                ) === 0
                && strcasecmp(
                    (string) $permission->name,
                    'view'
                ) === 0;
            })
        );
    $canViewAllocationManagement =
        auth()->user()->isSuperAdmin()
        || (
            auth()->user()->role
            && auth()->user()->role->permissions->contains(function ($permission) {
                return strcasecmp(
                    (string) optional($permission->module)->name,
                    'Allocation Management'
                ) === 0
                && strcasecmp(
                    (string) $permission->name,
                    'view'
                ) === 0;
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
    $sidebarDark = session('user_settings.class_theme', '') === 'dark';
    $sidebarSurface = '';
    $sidebarMuted = 'direk-muted';
    $sidebarDivider = 'direk-divider';
    $sidebarInactive = 'direk-rest';
    $sidebarActive = 'direk-selected';
    $sidebarLink = 'direk-tile';
    $sidebarSubLink = 'direk-subtile';
    $administratorOpen = $isAdministratorRoute || $isExpenseManagementRoute;
@endphp
{{-- Preserve Argon's sizing, mobile drawer, and miniature-sidebar hooks. --}}
<aside class="direk-sidebar direk-sidebar-3d sidenav navbar navbar-vertical navbar-expand-xs fixed-start !my-3 !ms-2 !flex !h-[calc(100vh-2rem)] !max-h-[calc(100vh-2rem)] !flex-col !items-stretch !overflow-hidden !rounded-2xl !border !border-solid !p-0 !shadow-sm {{ $sidebarSurface }}" id="sidenav-main" data-design="immersive-3d" data-theme="{{ $sidebarDark ? 'dark' : 'light' }}" aria-label="Main navigation">
    <div class="sidenav-header !relative !h-auto !min-h-0 !shrink-0 !bg-transparent !px-4 !py-5">
        <button type="button" id="iconSidenav" aria-label="Close navigation" class="!absolute !right-1 !top-1 !grid !h-9 !w-9 !place-items-center !rounded !border-0 !bg-transparent !p-0 {{ $sidebarMuted }} hover:!bg-violet-500/10 focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#8276ef] min-[1200px]:!hidden"><i class="fa fa-times" aria-hidden="true"></i></button>
        <a data-direk-route-family="home" class="navbar-brand !m-0 !flex !min-w-0 !items-center !gap-3 !rounded-lg !p-0 !no-underline focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#8276ef]" href="{{ route('home') }}" aria-label="DIREK Home">
            <img src="{{ $logo ?? asset('assets/img/neda/logo.png') }}" class="navbar-brand-img !h-10 !w-10 !max-h-10 !shrink-0 !object-contain" width="40" height="40" alt="DEPDev logo">
            <span class="nav-link-text !min-w-0 !leading-tight">
                <strong class="!block !text-base !font-semibold !tracking-[0.05em] direk-brand-title">D.I.R.E.K.</strong>
                <span class="!mt-1 !block !text-[11px] !font-normal {{ $sidebarMuted }}">Planning workspace</span>
            </span>
        </a>
    </div>
    <div class="navbar-collapse !block !h-auto !max-h-none !min-h-0 !w-full !flex-1 !overflow-x-hidden !overflow-y-auto !px-3 !pb-4 !pt-3" id="sidenav-collapse-main">
        <ul class="navbar-nav !m-0 !flex !list-none !flex-col !gap-1 !p-0">
            <li class="nav-item" id="direk-profile-item">
                <button type="button" id="profile-menu-toggle" class="nav-link {{ $sidebarLink }} {{ $isProfileRoute ? $sidebarActive : $sidebarInactive }}" aria-expanded="{{ $isProfileRoute ? 'true' : 'false' }}" aria-controls="profile-menu">
                    <span class="icon !m-0 !grid !h-8 !w-8 !shrink-0 !place-items-center !rounded-lg direk-profile-symbol !shadow-none"><i class="fa fa-user !text-sm !text-inherit !opacity-100" aria-hidden="true"></i></span>
                    <span class="nav-link-text !min-w-0 !flex-1 !truncate" title="{{ auth()->user()->full_name }}">{{ auth()->user()->full_name }}</span>
                    <i class="fa fa-angle-down !ml-auto !shrink-0 !text-xs !transition-transform motion-reduce:!transition-none {{ $sidebarMuted }}" id="profile-menu-icon" aria-hidden="true"></i>
                </button>
                <div id="profile-menu" data-direk-menu @if (!$isProfileRoute) hidden @endif>
                    <ul class="nav !my-1 !ml-4 !flex !list-none !flex-col !gap-1 !border-0 !border-l !border-solid !pl-3 {{ $sidebarDivider }}">
                        <li class="nav-item !w-full"><a data-direk-route-family="user-profile" class="nav-link {{ $sidebarSubLink }} {{ $isProfileRoute ? $sidebarActive : $sidebarInactive }}" href="{{ route('user-profile') }}" @if ($isProfileRoute) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-user !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">My Profile</span></a></li>
                        <li class="nav-item !w-full">
                            <form method="POST" action="{{ route('logout') }}" id="logout-form-sidenav" class="!m-0">
                                @csrf
                                <button type="submit" class="nav-link {{ $sidebarSubLink }} {{ $sidebarInactive }}"><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-power-off !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">Log Out</span></button>
                            </form>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="!my-3 !border-0 !border-t !border-solid {{ $sidebarDivider }}" aria-hidden="true"></li>
            <li class="nav-item">
                <a class="nav-link {{ $sidebarLink }} {{ request()->routeIs('home') ? $sidebarActive : $sidebarInactive }}" href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>
                    <span class="icon !m-0 !grid !h-7 !w-7 !shrink-0 !place-items-center !bg-transparent !shadow-none"><i class="fa fa-home !text-base !text-inherit !opacity-100" aria-hidden="true"></i></span><span class="nav-link-text">Home</span>
                </a>
            </li>
            @if ($canViewFinancialPlan || $canViewWorkPlan || $canViewAllocationManagement)
                <li data-direk-section class="nav-item !mb-1 !mt-5 !px-3"><span class="nav-link-text !text-[10px] !font-semibold !uppercase !tracking-[0.1em] {{ $sidebarMuted }}">Planning & resources</span></li>
            @endif
            @if ($canViewAllocationManagement)
                <li class="nav-item">
                    <button type="button" id="allocation-management-menu-toggle" class="nav-link {{ $sidebarLink }} {{ $isAllocationManagementRoute ? $sidebarActive : $sidebarInactive }}" aria-expanded="{{ $isAllocationManagementRoute ? 'true' : 'false' }}" aria-controls="allocation-management-menu">
                        <span class="icon !m-0 !grid !h-7 !w-7 !shrink-0 !place-items-center !bg-transparent !shadow-none"><i class="fa fa-calculator !text-base !text-inherit !opacity-100" aria-hidden="true"></i></span><span class="nav-link-text !min-w-0 !flex-1">Allocation Management</span><i class="fa fa-angle-down !ml-auto !shrink-0 !text-xs !transition-transform motion-reduce:!transition-none {{ $sidebarMuted }}" id="allocation-management-menu-icon" aria-hidden="true"></i>
                    </button>
                    <div id="allocation-management-menu" data-direk-menu @if (!$isAllocationManagementRoute) hidden @endif>
                        <ul class="nav !my-1 !ml-4 !flex !list-none !flex-col !gap-1 !border-0 !border-l !border-solid !pl-3 {{ $sidebarDivider }}">
                            <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ request()->routeIs('allocations.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('allocations.index') }}" @if (request()->routeIs('allocations.*')) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-money !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">Allocations</span></a></li>
                            <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ request()->routeIs('fiscal-years.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('fiscal-years.index') }}" @if (request()->routeIs('fiscal-years.*')) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-calendar !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">Fiscal Years</span></a></li>
                            <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ request()->routeIs('levels.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('levels.index') }}" @if (request()->routeIs('levels.*')) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-sitemap !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">Levels</span></a></li>
                        </ul>
                    </div>
                </li>
            @endif
            @if ($canViewFinancialPlan)
                <li class="nav-item"><a class="nav-link {{ $sidebarLink }} {{ request()->routeIs('financial-plans.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('financial-plans.plans') }}" @if (request()->routeIs('financial-plans.*')) aria-current="page" @endif><span class="icon !m-0 !grid !h-7 !w-7 !shrink-0 !place-items-center !bg-transparent !shadow-none"><i class="fa fa-money !text-base !text-inherit !opacity-100" aria-hidden="true"></i></span><span class="nav-link-text">Financial Plans</span></a></li>
            @endif
            @if ($canViewWorkPlan)
                <li class="nav-item"><a class="nav-link {{ $sidebarLink }} {{ request()->routeIs('work-plans.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('work-plans.plans') }}" @if (request()->routeIs('work-plans.*')) aria-current="page" @endif><span class="icon !m-0 !grid !h-7 !w-7 !shrink-0 !place-items-center !bg-transparent !shadow-none"><i class="fa fa-tasks !text-base !text-inherit !opacity-100" aria-hidden="true"></i></span><span class="nav-link-text">Work Plans</span></a></li>
            @endif
            @if ($canViewFinancialPlan && $canViewWorkPlan)
                <li class="nav-item">
                    <a
                        class="nav-link {{ $sidebarLink }} {{ request()->routeIs('mdp.*') ? $sidebarActive : $sidebarInactive }}"
                        href="{{ route('mdp.index') }}"
                        title="Monthly Disbursement Program"
                        @if (request()->routeIs('mdp.*')) aria-current="page" @endif
                    >
                        <span class="icon !m-0 !grid !h-7 !w-7 !shrink-0 !place-items-center !bg-transparent !shadow-none">
                            <i class="fa fa-calendar !text-base !text-inherit !opacity-100" aria-hidden="true"></i>
                        </span>
                        <span class="nav-link-text !min-w-0 !flex-1 !whitespace-normal !text-left !leading-4">
                            Monthly Disbursement Program
                        </span>
                    </a>
                </li>
            @endif
            {{-- Preserve the supplied administrator visibility rule and legacy role exception. --}}
            @if (auth()->user()->isSuperAdmin() || $canViewExpenseManagement)
                <li data-direk-section class="nav-item !mb-1 !mt-5 !px-3"><span class="nav-link-text !text-[10px] !font-semibold !uppercase !tracking-[0.1em] {{ $sidebarMuted }}">Administration</span></li>
                <li class="nav-item">
                    <button type="button" id="administrator-menu-toggle" class="nav-link {{ $sidebarLink }} {{ $administratorOpen ? $sidebarActive : $sidebarInactive }}" aria-expanded="{{ $administratorOpen ? 'true' : 'false' }}" aria-controls="administrator-menu">
                        <span class="icon !m-0 !grid !h-7 !w-7 !shrink-0 !place-items-center !bg-transparent !shadow-none"><i class="fa fa-cog !text-base !text-inherit !opacity-100" aria-hidden="true"></i></span><span class="nav-link-text !min-w-0 !flex-1">Administration</span><i class="fa fa-angle-down !ml-auto !shrink-0 !text-xs !transition-transform motion-reduce:!transition-none {{ $sidebarMuted }}" id="administrator-menu-icon" aria-hidden="true"></i>
                    </button>
                    <div id="administrator-menu" data-direk-menu @if (!$administratorOpen) hidden @endif>
                        <ul class="nav !my-1 !ml-4 !flex !list-none !flex-col !gap-1 !border-0 !border-l !border-solid !pl-3 {{ $sidebarDivider }}">
                            @can('viewAny', App\Models\User::class)
                            <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ request()->routeIs('users.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('users.index') }}" @if (request()->routeIs('users.*')) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-users !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">User Management</span></a></li>
                            @endcan
                            @can('viewAny', App\Models\Role::class)
                            <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ request()->routeIs('roles.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('roles.index') }}" @if (request()->routeIs('roles.*')) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-user-secret !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">Roles & Permissions</span></a></li>
                            @endcan
                            @can('viewAny', App\Models\Staff::class)
                            <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ request()->routeIs('staffs.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('staffs.index') }}" @if (request()->routeIs('staffs.*')) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-building !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">Staff Management</span></a></li>
                            @endcan
                            @if (in_array((int) auth()->user()->role_id, [1, 29], true))
                            <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ request()->routeIs('staff-personnel.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('staff-personnel.index') }}" @if (request()->routeIs('staff-personnel.*')) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-address-book !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">Staff Personnel</span></a></li>
                            @endif
                            @can('viewAny', App\Models\Division::class)
                            <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ request()->routeIs('divisions.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('divisions.index') }}" @if (request()->routeIs('divisions.*')) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-sitemap !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">Division Management</span></a></li>
                            @endcan
                            @can('viewAny', App\Models\Parameter::class)
                            <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ request()->routeIs('parameters.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('parameters.index') }}" @if (request()->routeIs('parameters.*')) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-sliders !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">System Parameters</span></a></li>
                            @endcan
                            @if ($canViewExpenseManagement)
                                <li class="nav-item !w-full">
                                    <button type="button" id="expense-management-menu-toggle" class="nav-link {{ $sidebarSubLink }} {{ $isExpenseManagementRoute ? $sidebarActive : $sidebarInactive }}" aria-expanded="{{ $isExpenseManagementRoute ? 'true' : 'false' }}" aria-controls="expense-management-menu"><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-tags !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal !min-w-0 !flex-1">Expense Management</span><i class="fa fa-angle-down !ml-auto !shrink-0 !text-xs !transition-transform motion-reduce:!transition-none {{ $sidebarMuted }}" id="expense-management-menu-icon" aria-hidden="true"></i></button>
                                    <div id="expense-management-menu" data-direk-menu @if (!$isExpenseManagementRoute) hidden @endif>
                                        <ul class="nav !my-1 !ml-3 !flex !list-none !flex-col !border-0 !border-l !border-solid !pl-2 {{ $sidebarDivider }}">
                                            <li class="nav-item !w-full"><a data-direk-route-family="expense-types" class="nav-link {{ $sidebarSubLink }} {{ $isExpenseManagementRoute ? $sidebarActive : $sidebarInactive }}" href="{{ route('expense-types.index') }}" @if ($isExpenseManagementRoute) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-tags !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">Expense Types</span></a></li>
                                        </ul>
                                    </div>
                                </li>
                            @endif
                            @can('viewAny', App\Models\SystemLog::class)
                                <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ request()->routeIs('systemlogs.*') ? $sidebarActive : $sidebarInactive }}" href="{{ route('systemlogs.index') }}" @if (request()->routeIs('systemlogs.*')) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-list-alt !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">System Logs</span></a></li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endif
        </ul>
    </div>
    <div data-direk-footer class="!shrink-0 !border-0 !border-t !border-solid !px-5 !py-3 {{ $sidebarDivider }}"><span class="nav-link-text !text-[10px] !tracking-wide {{ $sidebarMuted }}">DIREK · Connected workspace</span></div>
</aside>
@once
<style>
/*Scoped sidebar layout. */
#sidenav-main.direk-sidebar-3d {
    --side-text:#24304b; --side-copy:#5b6680; --side-muted:#748099;
    --side-border:#dce1ee; --side-accent:#6555dc; --side-ring:#8276ef;
    --side-tile-top:#ffffff; --side-tile-bottom:#f0f2fa; --side-edge:#d3d9e8;
    --side-shadow:rgba(49,58,96,.12); --side-highlight:rgba(255,255,255,.96);
    display:flex!important; flex-direction:column; align-items:stretch!important;
    height:calc(100vh - 2rem); max-height:calc(100vh - 2rem);
    height:calc(100dvh - 2rem); max-height:calc(100dvh - 2rem);
    padding:0!important; margin-top:1rem!important; margin-bottom:1rem!important;
    overflow:hidden; isolation:isolate; border:1px solid var(--side-border)!important;
    border-radius:22px!important; color:var(--side-text);
    background:linear-gradient(155deg,#fafbff 0%,#edf0f8 60%,#e7eaf5 100%)!important;
    box-shadow:inset 1px 1px 0 #fff,inset -1px -1px 0 #d5dbea,5px 0 0 #dce1ee,12px 18px 34px rgba(35,43,79,.16)!important;
    font-family:inherit;
}
#sidenav-main.direk-sidebar-3d[data-theme="dark"] {
    --side-text:#f0efff; --side-copy:#c4c7dd; --side-muted:#a5abc4;
    --side-border:#353b58; --side-accent:#b5a9ff; --side-ring:#b5a9ff;
    --side-tile-top:#303650; --side-tile-bottom:#252a42; --side-edge:#171b2d;
    --side-shadow:rgba(0,0,0,.3); --side-highlight:rgba(255,255,255,.09);
    background:linear-gradient(155deg,#252a41 0%,#1b2035 65%,#24203c 100%)!important;
    box-shadow:inset 1px 1px 0 #3c425f,inset -1px -1px 0 #141829,5px 0 0 #111528,12px 18px 34px rgba(0,0,0,.32)!important;
}
#sidenav-main.direk-sidebar-3d::before {content:"";position:absolute;left:20px;right:20px;top:0;height:3px;background:linear-gradient(90deg,#6555dc,#c0b5ff,transparent);border-radius:0 0 9px 9px;z-index:1;pointer-events:none}
#sidenav-main.direk-sidebar-3d .sidenav-header {position:relative;height:auto!important;min-height:0;flex-shrink:0;padding:24px 18px 20px!important;border-bottom:1px solid var(--side-border);background:transparent!important}
#sidenav-main.direk-sidebar-3d .navbar-brand {display:flex!important;align-items:center;gap:11px!important;min-width:0;margin:0!important;padding:0!important;text-decoration:none;border-radius:10px}
#sidenav-main.direk-sidebar-3d .navbar-brand-img {width:40px!important;height:40px!important;max-height:40px!important;flex-shrink:0;object-fit:contain;padding:4px;border-radius:12px;background:#fff;border:1px solid #e0e4ef;box-shadow:0 3px 0 var(--side-edge),0 7px 12px var(--side-shadow)}
#sidenav-main.direk-sidebar-3d .direk-brand-title {display:block;color:var(--side-text)!important;font-size:15px!important;font-weight:800!important;letter-spacing:.1em!important}
#sidenav-main.direk-sidebar-3d .direk-muted {color:var(--side-muted)!important}
#sidenav-main.direk-sidebar-3d .direk-divider {border-color:var(--side-border)!important}
#sidenav-main.direk-sidebar-3d #iconSidenav {position:absolute;top:3px;right:3px;width:32px;height:32px;display:grid;place-items:center;border:0;border-radius:8px;background:transparent;color:var(--side-muted)}
#sidenav-main.direk-sidebar-3d .navbar-collapse {display:block!important;flex:1;min-height:0;height:auto!important;max-height:none!important;width:100%;overflow-x:hidden;overflow-y:auto;padding:16px 12px 22px!important;scrollbar-width:thin;scrollbar-color:var(--side-border) transparent}
#sidenav-main.direk-sidebar-3d .navbar-collapse::-webkit-scrollbar {width:5px}
#sidenav-main.direk-sidebar-3d .navbar-collapse::-webkit-scrollbar-thumb {background:var(--side-border);border-radius:8px}
#sidenav-main.direk-sidebar-3d .navbar-nav {display:flex;flex-direction:column;list-style:none;margin:0;padding:0;gap:8px!important}
#sidenav-main.direk-sidebar-3d .nav-item {max-width:100%;min-width:0}
#sidenav-main.direk-sidebar-3d .direk-tile,
#sidenav-main.direk-sidebar-3d .direk-subtile {
    position:relative;display:flex!important;align-items:center;gap:10px!important;
    width:100%;min-height:48px;border:1px solid var(--side-border)!important;
    border-radius:12px!important;padding:10px 11px!important;text-align:left;
    color:var(--side-copy)!important;background:linear-gradient(150deg,var(--side-tile-top),var(--side-tile-bottom))!important;
    font-family:inherit;font-size:12px!important;font-weight:600;line-height:1.45!important;
    white-space:normal!important;text-decoration:none!important;
    box-shadow:inset 0 1px 0 var(--side-highlight),0 3px 0 var(--side-edge),0 6px 10px var(--side-shadow)!important;
    transform:translateY(0);transition:transform .18s ease,box-shadow .18s ease,color .18s ease!important;
}
#sidenav-main.direk-sidebar-3d .nav-link::after {display:none!important}
@media(hover:hover) and (pointer:fine) {
    #sidenav-main.direk-sidebar-3d .nav-link:hover {transform:translateY(-2px);color:var(--side-text)!important;box-shadow:inset 0 1px 0 var(--side-highlight),0 5px 0 var(--side-edge),0 10px 16px var(--side-shadow)!important}
}
#sidenav-main.direk-sidebar-3d .nav-link:active {transform:translateY(2px);box-shadow:inset 0 1px 0 var(--side-highlight),0 1px 0 var(--side-edge),0 3px 5px var(--side-shadow)!important}
#sidenav-main.direk-sidebar-3d .direk-selected,
#sidenav-main.direk-sidebar-3d .nav-link[aria-current="page"],
#sidenav-main.direk-sidebar-3d button.nav-link[aria-expanded="true"] {
    color:#fff!important;border-color:#7565e5!important;background:linear-gradient(145deg,#8574ee,#6555d7 65%,#5949c8)!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.32),0 3px 0 #47369f,0 8px 15px rgba(92,73,196,.26)!important;
    font-weight:700;
}
#sidenav-main.direk-sidebar-3d .nav-link[aria-current="page"]::before {content:"";position:absolute;left:0;top:12px;bottom:12px;width:3px;border-radius:0 4px 4px 0;background:#d8d0ff;box-shadow:0 0 7px rgba(255,255,255,.35)}
#sidenav-main.direk-sidebar-3d .nav-link .icon {display:grid!important;place-items:center;width:29px!important;height:29px!important;margin:0!important;flex-shrink:0;border:0!important;border-radius:0!important;background:transparent!important;color:var(--side-accent)!important;box-shadow:none!important;transform:none!important;}
#sidenav-main.direk-sidebar-3d .nav-link .icon i {font-size:14px!important;color:inherit!important;opacity:1!important}
#sidenav-main.direk-sidebar-3d .direk-selected>.icon,
#sidenav-main.direk-sidebar-3d .nav-link[aria-current="page"]>.icon,
#sidenav-main.direk-sidebar-3d button.nav-link[aria-expanded="true"]>.icon {background:transparent!important;color:#fff!important;border:0!important;box-shadow:none!important;transform:none!important;}
#sidenav-main.direk-sidebar-3d .nav-link-text,
#sidenav-main.direk-sidebar-3d .sidenav-normal {min-width:0;white-space:normal!important;line-height:1.5!important;overflow-wrap:break-word}
#sidenav-main.direk-sidebar-3d .nav-link>.nav-link-text,
#sidenav-main.direk-sidebar-3d .nav-link>.sidenav-normal {flex:1}
#sidenav-main.direk-sidebar-3d .fa-angle-down {margin-left:auto;font-size:10px!important;color:inherit!important;transition:transform .18s ease!important}
#sidenav-main.direk-sidebar-3d button[aria-expanded="true"]>.fa-angle-down {transform:rotate(180deg)}
#sidenav-main.direk-sidebar-3d [data-direk-section] {margin-top:20px!important;margin-bottom:2px!important;padding-left:11px!important}
#sidenav-main.direk-sidebar-3d [data-direk-section] .nav-link-text {display:block;color:var(--side-muted)!important;font-size:9px!important;font-weight:800!important;letter-spacing:.13em!important;text-transform:uppercase}
#sidenav-main.direk-sidebar-3d #profile-menu-toggle .nav-link-text {white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;font-size:11px!important}
#sidenav-main.direk-sidebar-3d .direk-profile-symbol {width:33px!important;height:33px!important;background:transparent!important;border:0!important;box-shadow:none!important;transform:none!important;}
#sidenav-main.direk-sidebar-3d [data-direk-menu] .nav {display:flex;flex-direction:column;list-style:none;margin:10px 0 5px 20px!important;padding:2px 0 2px 10px!important;border:0;border-left:1px solid var(--side-border)!important;gap:7px!important}
#sidenav-main.direk-sidebar-3d [data-direk-menu] .direk-subtile {min-height:40px!important;padding:8px!important;font-size:11px!important;border-radius:9px!important;gap:8px!important}
#sidenav-main.direk-sidebar-3d .sidenav-mini-icon {display:inline-flex;width:15px;flex-shrink:0;justify-content:center;color:inherit!important}
#sidenav-main.direk-sidebar-3d .sidenav-mini-icon i {font-size:12px!important;color:inherit!important}
#sidenav-main.direk-sidebar-3d [data-direk-footer] {flex-shrink:0;padding:15px 18px!important;border-top:1px solid var(--side-border)!important;background:transparent;box-shadow:inset 0 1px 0 var(--side-highlight)}
#sidenav-main.direk-sidebar-3d [data-direk-footer] .nav-link-text {font-size:9px!important;letter-spacing:.03em!important;color:var(--side-muted)!important}
#sidenav-main.direk-sidebar-3d [hidden] {display:none!important}
#sidenav-main.direk-sidebar-3d[data-direk-collapsed="true"] .nav-link-text,
#sidenav-main.direk-sidebar-3d[data-direk-collapsed="true"] .sidenav-normal,
#sidenav-main.direk-sidebar-3d[data-direk-collapsed="true"] [data-direk-section],
#sidenav-main.direk-sidebar-3d[data-direk-collapsed="true"] [data-direk-footer],
#sidenav-main.direk-sidebar-3d[data-direk-collapsed="true"] [data-direk-menu],
#sidenav-main.direk-sidebar-3d[data-direk-collapsed="true"] .fa-angle-down {display:none!important}
#sidenav-main.direk-sidebar-3d[data-direk-collapsed="true"] .nav-link {justify-content:center!important;gap:0!important;padding-left:0!important;padding-right:0!important}
#sidenav-main.direk-sidebar-3d[data-direk-collapsed="true"] .nav-link::before {display:none!important}
#sidenav-main.direk-sidebar-3d[data-direk-collapsed="true"] .navbar-brand {justify-content:center!important;gap:0!important}
#sidenav-main.direk-sidebar-3d .nav-link:focus-visible,
#sidenav-main.direk-sidebar-3d .navbar-brand:focus-visible,
#sidenav-main.direk-sidebar-3d #iconSidenav:focus-visible {outline:2px solid var(--side-ring)!important;outline-offset:3px;z-index:2}
@media(min-width:1200px){#sidenav-main.direk-sidebar-3d #iconSidenav{display:none!important}}
@media(max-width:1199px){#sidenav-main.direk-sidebar-3d{box-shadow:inset 1px 1px 0 var(--side-highlight),3px 0 0 var(--side-edge),8px 12px 24px var(--side-shadow)!important}}
@media(prefers-reduced-motion:reduce){#sidenav-main.direk-sidebar-3d .nav-link,#sidenav-main.direk-sidebar-3d .nav-link .icon,#sidenav-main.direk-sidebar-3d .fa-angle-down{transition:none!important}#sidenav-main.direk-sidebar-3d .nav-link:hover,#sidenav-main.direk-sidebar-3d .nav-link:active,#sidenav-main.direk-sidebar-3d .nav-link .icon{transform:none!important}}
@media(forced-colors:active){#sidenav-main.direk-sidebar-3d .nav-link[aria-current="page"]{outline:2px solid Highlight!important}#sidenav-main.direk-sidebar-3d .nav-link{box-shadow:none!important;border:1px solid ButtonText!important}}
/*Variant colors are independent of the application palette. Plain icons are retained. */
#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] {
 --side-text:#f4f0ff;--side-copy:#d3cce8;--side-muted:#b4a9cf;--side-border:#4b4268;
 --side-accent:#c2acff;--side-ring:#c2acff;--side-tile-top:#39334f;--side-tile-bottom:#29253e;
 --side-edge:#141125;--side-shadow:rgba(0,0,0,.35);--side-highlight:rgba(239,221,255,.12);
 background:radial-gradient(ellipse at 100% 0%,rgba(140,92,217,.20),transparent 48%),linear-gradient(155deg,#302a46,#1b1930 75%,#292040)!important;box-shadow:inset 1px 1px 0 rgba(240,219,255,.18),5px 0 0 #131122,12px 18px 35px rgba(0,0,0,.35)!important;border-radius:22px!important;
}
#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"]::before{background:linear-gradient(90deg,#c2acff,#85dcea,transparent);height:2px}
#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] .direk-tile,#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] .direk-subtile{background:linear-gradient(145deg,#39334f,#29253e)!important;box-shadow:inset 0 1px 0 rgba(239,221,255,.13),0 3px 0 #141125,0 8px 14px rgba(0,0,0,.25)!important;border-radius:12px!important;transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease!important}
#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] :is(.direk-selected,.nav-link[aria-current="page"],button.nav-link[aria-expanded="true"]){color:#ffffff!important;background:linear-gradient(145deg,#8d76e9,#6551be 70%,#51429f)!important;border-color:#a48bee!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.3),0 4px 0 #36246f,0 12px 23px rgba(119,76,210,.25)!important}
#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] .nav-link[aria-current="page"]::before{background:#c2acff;box-shadow:none}
#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] :is(.direk-selected,.nav-link[aria-current="page"],button.nav-link[aria-expanded="true"])>.icon{color:#ffffff!important;background:transparent!important;box-shadow:none!important;border:0!important}
#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] .navbar-brand-img{border-color:#4b4268;box-shadow:0 3px 8px rgba(0,0,0,.35)}
#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] [data-direk-section] .nav-link-text{letter-spacing:.13em!important}
#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] [data-direk-footer]{background:rgba(179,140,248,.035)!important}
@media(hover:hover) and (pointer:fine){#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] .nav-link:hover{transform:translateY(-2px);box-shadow:inset 0 1px 0 rgba(239,221,255,.18),0 5px 0 #141125,0 12px 20px rgba(0,0,0,.30)!important;border-color:#c2acff!important}#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] :is(.direk-selected,.nav-link[aria-current="page"],button.nav-link[aria-expanded="true"]):hover{box-shadow:inset 0 1px 0 rgba(255,255,255,.3),0 4px 0 #36246f,0 12px 23px rgba(119,76,210,.25)!important}}
#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] .nav-link:active{transform:translateY(1px);box-shadow:inset 0 1px 0 rgba(239,221,255,.13),0 3px 0 #141125,0 8px 14px rgba(0,0,0,.25)!important}
@media(prefers-reduced-motion:reduce){#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] .nav-link:hover,#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] .nav-link:active{transform:none!important;transition:none!important}}
@media(forced-colors:active){#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] .nav-link{color:ButtonText!important;background:Canvas!important;border:1px solid ButtonText!important;box-shadow:none!important}#sidenav-main.direk-sidebar-3d[data-design="immersive-3d"] .nav-link[aria-current="page"]{outline:2px solid Highlight!important}}
</style>
@endonce
<script>
(function () {
    'use strict';
    const root = document.getElementById('sidenav-main');
    if (!root || root.dataset.direkBound === 'true') return;
    root.dataset.direkBound = 'true';
    // Keep icon-only navigation named for both assistive technology and hover.
    root.querySelectorAll('a.nav-link, button.nav-link').forEach(function (link) {
        const label = link.querySelector('.nav-link-text, .sidenav-normal');
        const name = label ? label.textContent.trim() : '';
        if (name) {
            link.setAttribute('aria-label', name);
            if (!link.hasAttribute('title')) link.setAttribute('title', name);
        }
    });
    // Use the sidebar's actual width so this follows Argon's click and hover states.
    function syncCollapsedState() {
        const desktop = window.matchMedia('(min-width: 1200px)').matches;
        const narrow = root.getBoundingClientRect().width <= 120;
        root.setAttribute('data-direk-collapsed', String(desktop && narrow));
    }
    syncCollapsedState();
    window.addEventListener('resize', syncCollapsedState);
    if (typeof ResizeObserver !== 'undefined') {
        const sidebarObserver = new ResizeObserver(syncCollapsedState);
        sidebarObserver.observe(root);
    } else if (typeof MutationObserver !== 'undefined') {
        const bodyObserver = new MutationObserver(syncCollapsedState);
        bodyObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });
    }
    function expandSidebarIfNeeded() {
        if (root.getAttribute('data-direk-collapsed') !== 'true') return false;
        if (window.DirekWorkspace) {
            window.DirekWorkspace.toggleSidebar(true);
            return true;
        }
        const trigger = document.querySelector('.sidenav-toggler button')
            || document.querySelector('.sidenav-toggler');
        if (!trigger) return false;
        trigger.click();
        return true;
    }
    function setupSidenavMenu(toggleId, menuId, iconId) {
        const toggle = document.getElementById(toggleId);
        const menu = document.getElementById(menuId);
        const icon = document.getElementById(iconId);
        if (!toggle || !menu) return null;
        function setOpen(open) {
            menu.hidden = !open;
            toggle.setAttribute('aria-expanded', String(open));
            if (icon) icon.classList.toggle('!rotate-180', open);
        }
        toggle.addEventListener('click', function () {
    // Open the full navigation before exposing a submenu from the icon rail.
            if (expandSidebarIfNeeded()) setOpen(true);
            else setOpen(menu.hidden);
        });
        menu.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape' || menu.hidden) return;
            event.preventDefault();
            event.stopPropagation();
            setOpen(false);
            toggle.focus();
        });
        toggle.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !menu.hidden) {
                event.preventDefault();
                event.stopPropagation();
                setOpen(false);
            }
        });
        setOpen(!menu.hidden);
        return { toggle, menu, setOpen };
    }
    const profile = setupSidenavMenu('profile-menu-toggle', 'profile-menu', 'profile-menu-icon');
    setupSidenavMenu('allocation-management-menu-toggle', 'allocation-management-menu', 'allocation-management-menu-icon');
    setupSidenavMenu('administrator-menu-toggle', 'administrator-menu', 'administrator-menu-icon');
    setupSidenavMenu('expense-management-menu-toggle', 'expense-management-menu', 'expense-management-menu-icon');
    if (profile) {
        const profileItem = document.getElementById('direk-profile-item');
        document.addEventListener('click', function (event) {
            if (profileItem && !profileItem.contains(event.target)) profile.setOpen(false);
        });
    }
    const activeLink = root.querySelector('a[aria-current="page"]');
    if (activeLink) activeLink.classList.add('active');
})();
</script>

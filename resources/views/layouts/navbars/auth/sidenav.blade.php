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
    $sidebarSurface = $sidebarDark ? '!bg-[#192130] !border-[#2c3546]' : '!bg-white !border-[#e8ebf2]';
    $sidebarMuted = $sidebarDark ? '!text-[#aab5c8]' : '!text-[#69758b]';
    $sidebarDivider = $sidebarDark ? '!border-[#2c3546]' : '!border-[#e8ebf2]';
    $sidebarInactive = $sidebarDark
        ? '!bg-transparent !text-[#aab5c8] hover:!bg-[#252d40] hover:!text-[#eef2fb]'
        : '!bg-transparent !text-[#637086] hover:!bg-[#f6f5ff] hover:!text-[#5146c8]';
    $sidebarActive = $sidebarDark ? '!bg-[#353052] !text-[#d3ceff]' : '!bg-[#efedff] !text-[#5b4ed2]';
    $sidebarLink = '!relative !flex !min-h-[44px] !w-full !items-center !gap-3 !rounded-lg !border-0 !px-3 !py-2.5 !font-sans !text-[13px] !font-medium !leading-5 !text-left !no-underline !shadow-none !transition-colors after:!hidden focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-inset focus-visible:!ring-[#8276ef] motion-reduce:!transition-none';
    $sidebarSubLink = '!flex !min-h-[40px] !w-full !items-center !gap-3 !rounded-lg !border-0 !px-3 !py-2 !font-sans !text-xs !font-medium !leading-5 !text-left !no-underline !shadow-none !transition-colors after:!hidden focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-inset focus-visible:!ring-[#8276ef] motion-reduce:!transition-none';
    $administratorOpen = $isAdministratorRoute || $isExpenseManagementRoute;
@endphp
{{-- Preserve Argon's sizing, mobile drawer, and miniature-sidebar hooks. --}}
<aside class="direk-sidebar [&[data-direk-collapsed=true]_.nav-link-text]:!hidden [&[data-direk-collapsed=true]_.sidenav-normal]:!hidden [&[data-direk-collapsed=true]_[data-direk-section]]:!hidden [&[data-direk-collapsed=true]_[data-direk-footer]]:!hidden [&[data-direk-collapsed=true]_[data-direk-menu]]:!hidden [&[data-direk-collapsed=true]_.fa-angle-down]:!hidden [&[data-direk-collapsed=true]_.nav-link]:!justify-center [&[data-direk-collapsed=true]_.nav-link]:!gap-0 [&[data-direk-collapsed=true]_.nav-link]:!px-0 [&[data-direk-collapsed=true]_.navbar-brand]:!justify-center sidenav navbar navbar-vertical navbar-expand-xs fixed-start !my-3 !ms-2 !flex !h-[calc(100vh-2rem)] !max-h-[calc(100vh-2rem)] !flex-col !items-stretch !overflow-hidden !rounded-2xl !border !border-solid !p-0 !shadow-sm {{ $sidebarSurface }}" id="sidenav-main" data-theme="{{ $sidebarDark ? 'dark' : 'light' }}" aria-label="Main navigation">
    <div class="sidenav-header !relative !h-auto !min-h-0 !shrink-0 !bg-transparent !px-4 !py-5">
        <button type="button" id="iconSidenav" aria-label="Close navigation" class="!absolute !right-1 !top-1 !grid !h-9 !w-9 !place-items-center !rounded !border-0 !bg-transparent !p-0 {{ $sidebarMuted }} hover:!bg-violet-500/10 focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#8276ef] min-[1200px]:!hidden"><i class="fa fa-times" aria-hidden="true"></i></button>
        <a class="navbar-brand !m-0 !flex !min-w-0 !items-center !gap-3 !rounded-lg !p-0 !no-underline focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#8276ef]" href="{{ route('home') }}" aria-label="DIREK Home">
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
                        <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ $isProfileRoute ? $sidebarActive : $sidebarInactive }}" href="{{ route('user-profile') }}" @if ($isProfileRoute) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-user !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">My Profile</span></a></li>
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
                                            <li class="nav-item !w-full"><a class="nav-link {{ $sidebarSubLink }} {{ $isExpenseManagementRoute ? $sidebarActive : $sidebarInactive }}" href="{{ route('expense-types.index') }}" @if ($isExpenseManagementRoute) aria-current="page" @endif><span class="sidenav-mini-icon !inline-flex !w-4 !shrink-0 !justify-center"><i class="fa fa-tags !text-inherit" aria-hidden="true"></i></span><span class="sidenav-normal">Expense Types</span></a></li>
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
@push('css')
<style>
#sidenav-main.direk-sidebar{--side-surface:#fff;--side-text:#172033;--side-copy:#637086;--side-muted:#69758b;--side-border:#e8ebf2;--side-subtle:#f6f7fb;--side-accent:#635bff;--side-active:#efedff;border-radius:20px!important;box-shadow:0 12px 35px rgba(31,35,70,.06)!important;font-family:inherit}
#sidenav-main.direk-sidebar[data-theme="dark"]{--side-surface:#192130;--side-text:#eef2fb;--side-copy:#aab5c8;--side-muted:#aab5c8;--side-border:#2c3546;--side-subtle:#252d40;--side-accent:#a19cff;--side-active:#353052}
#sidenav-main.direk-sidebar::before{content:"";position:absolute;left:18px;right:18px;top:0;height:2px;background:linear-gradient(90deg,#635bff,#b69cff,transparent);z-index:1}
#sidenav-main.direk-sidebar .sidenav-header{border-bottom:1px solid var(--side-border);padding:24px 18px 21px!important;background:var(--side-surface)!important}
#sidenav-main.direk-sidebar .navbar-brand{gap:11px!important}
#sidenav-main.direk-sidebar .navbar-brand-img{width:36px!important;height:36px!important;max-height:36px!important;padding:2px;border-radius:10px}
#sidenav-main.direk-sidebar .direk-brand-title{color:var(--side-text)!important;font-size:16px!important;font-weight:700!important;letter-spacing:.04em!important}
#sidenav-main.direk-sidebar .navbar-collapse{scrollbar-width:thin;scrollbar-color:var(--side-border) transparent;padding:16px 12px!important}
#sidenav-main.direk-sidebar .navbar-collapse::-webkit-scrollbar{width:4px}
#sidenav-main.direk-sidebar .navbar-collapse::-webkit-scrollbar-thumb{background:var(--side-border);border-radius:8px}
#sidenav-main.direk-sidebar .navbar-nav{gap:5px!important}
#sidenav-main.direk-sidebar .nav-link{position:relative;min-height:44px!important;gap:10px!important;border-radius:10px!important;font-size:12px!important;line-height:1.45!important;padding:11px 12px!important;transition:background .18s,color .18s!important;white-space:normal!important}
#sidenav-main.direk-sidebar .nav-link:focus-visible{outline:2px solid var(--side-accent)!important;outline-offset:2px}
#sidenav-main.direk-sidebar .nav-link .icon{width:26px!important;height:26px!important;border-radius:8px!important;flex-shrink:0}
#sidenav-main.direk-sidebar .nav-link .icon i{font-size:15px!important}
#sidenav-main.direk-sidebar .nav-link-text,#sidenav-main.direk-sidebar .sidenav-normal{min-width:0;white-space:normal!important;line-height:1.5!important;overflow-wrap:break-word}
#sidenav-main.direk-sidebar .nav-link[aria-current="page"]{font-weight:600!important}
#sidenav-main.direk-sidebar .nav-link[aria-current="page"]>.icon{background:rgba(99,91,255,.09)!important}
#sidenav-main.direk-sidebar[data-theme="dark"] .nav-link[aria-current="page"]>.icon{background:rgba(161,156,255,.12)!important}
#sidenav-main.direk-sidebar [data-direk-section]{margin-top:22px!important;margin-bottom:4px!important;padding-left:13px!important}
#sidenav-main.direk-sidebar [data-direk-section] .nav-link-text{color:var(--side-muted)!important;font-size:9px!important;font-weight:600!important;letter-spacing:.11em!important}
#sidenav-main.direk-sidebar #profile-menu-toggle{border:1px solid var(--side-border)!important;background:var(--side-subtle)!important;padding:12px 10px!important;border-radius:12px!important;color:var(--side-text)!important}
#sidenav-main.direk-sidebar #profile-menu-toggle .nav-link-text{white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;font-size:11px!important;font-weight:600!important}
#sidenav-main.direk-sidebar .direk-profile-symbol{width:32px!important;height:32px!important;background:var(--side-active)!important;color:var(--side-accent)!important;border-radius:10px!important}
#sidenav-main.direk-sidebar .fa-angle-down{font-size:10px!important;color:var(--side-muted)!important}
#sidenav-main.direk-sidebar [data-direk-menu] .nav{margin-left:24px!important;padding-left:12px!important;border-left-color:var(--side-border)!important;gap:3px!important}
#sidenav-main.direk-sidebar [data-direk-menu] .nav-link{min-height:38px!important;font-size:11px!important;padding:9px 10px!important}
#sidenav-main.direk-sidebar .sidenav-mini-icon i{font-size:12px!important}
#sidenav-main.direk-sidebar [data-direk-footer]{padding:16px 18px!important;background:var(--side-subtle);border-top-color:var(--side-border)!important}
#sidenav-main.direk-sidebar [data-direk-footer] .nav-link-text{font-size:9px!important;letter-spacing:.03em!important;color:var(--side-muted)!important}
#sidenav-main.direk-sidebar [hidden]{display:none!important}
#sidenav-main.direk-sidebar[data-direk-collapsed="true"] .nav-link-text,#sidenav-main.direk-sidebar[data-direk-collapsed="true"] .sidenav-normal,#sidenav-main.direk-sidebar[data-direk-collapsed="true"] [data-direk-section],#sidenav-main.direk-sidebar[data-direk-collapsed="true"] [data-direk-footer],#sidenav-main.direk-sidebar[data-direk-collapsed="true"] [data-direk-menu],#sidenav-main.direk-sidebar[data-direk-collapsed="true"] .fa-angle-down{display:none!important}
#sidenav-main.direk-sidebar[data-direk-collapsed="true"] .nav-link{justify-content:center!important;gap:0!important;padding-left:0!important;padding-right:0!important}
#sidenav-main.direk-sidebar[data-direk-collapsed="true"] .navbar-brand{justify-content:center!important;gap:0!important}
#sidenav-main.direk-sidebar[data-direk-collapsed="true"] #profile-menu-toggle{border-color:transparent!important;background:transparent!important}
@media(prefers-reduced-motion:reduce){#sidenav-main.direk-sidebar .nav-link,#sidenav-main.direk-sidebar .fa-angle-down{transition:none!important}}
</style>
@endpush
@push('js')
<script>
(function () {
    'use strict';
    const root = document.getElementById('sidenav-main');
    if (!root) return;
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
        const trigger = document.querySelector('.sidenav-toggler button')
            || document.querySelector('.sidenav-toggler');
        if (trigger) trigger.click();
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
@endpush

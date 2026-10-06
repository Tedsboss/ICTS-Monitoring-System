@php
    $publicNavDark = session('user_settings.class_theme', '') === 'dark';
    $publicNavSurface = $publicNavDark ? '!bg-[#1a293a] !border-[#34465a]' : '!bg-white !border-slate-200';
    $publicNavHeading = $publicNavDark ? '!text-slate-100' : '!text-[#203b55]';
    $publicNavInactive = $publicNavDark
        ? '!text-slate-300 hover:!bg-[#263b51] hover:!text-white'
        : '!text-slate-600 hover:!bg-[#f0f4f8] hover:!text-[#203b55]';
    $publicNavActive = $publicNavDark ? '!bg-[#2b4660] !text-[#e4cf9d]' : '!bg-[#eaf1f8] !text-[#203b55]';
    $publicNavDivider = $publicNavDark ? '!border-[#34465a]' : '!border-slate-200';
    $publicNavLink = '!flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !border-0 !px-4 !py-2.5 !font-sans !text-xs !font-semibold !leading-5 !no-underline !shadow-none !transition-colors focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-2 motion-reduce:!transition-none';
    $showPublicNavButtons = !($hide_nav_buttons ?? false);
    $publicNavTitle = $title ?? 'Department of Economy, Planning, and Development';
    $publicLoginActive = request()->routeIs('login') || request()->is('login');
    $publicResetActive = request()->routeIs('reset') || request()->is('reset-password');
@endphp

{{-- Public-page navbar. Preserve its placement for the existing guest layouts. --}}
<nav class="navbar position-absolute top-0 start-0 end-0 z-index-3 !mx-4 !mt-4 !rounded-xl !border !border-solid !px-4 !py-3 !shadow-sm sm:!mx-6 {{ $publicNavSurface }}" aria-label="Public navigation">
    <div class="container-fluid !flex !w-full !flex-wrap !items-center !justify-between !gap-x-3 !p-0">
        <a class="navbar-brand !m-0 !flex !min-w-0 !flex-1 !items-center !gap-3 !rounded-lg !p-0 !no-underline !whitespace-normal focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-2 {{ $publicNavHeading }} {{ $text ?? '' }}" href="https://depdev.gov.ph/">
            <img src="{{ $logo ?? asset('assets/img/neda/logo.png') }}" class="!h-10 !w-10 !shrink-0 !object-contain" width="40" height="40" alt="DEPDev logo">
            <span class="!min-w-0 !break-words !text-xs !font-semibold !leading-5 sm:!text-sm">{{ $publicNavTitle }}</span>
        </a>

        @if ($showPublicNavButtons)
            <button class="navbar-toggler !inline-flex !h-11 !w-11 !shrink-0 !items-center !justify-center !rounded-lg !border !border-solid !bg-transparent !p-0 !shadow-none focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] lg:!hidden {{ $publicNavDivider }} {{ $publicNavHeading }}" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa fa-bars !text-base" aria-hidden="true"></i>
            </button>
            {{-- Bootstrap owns mobile display and animation; Tailwind expands it on desktop. --}}
            <div class="collapse navbar-collapse !w-full !basis-full !pt-3 lg:!flex lg:!w-auto lg:!flex-none lg:!basis-auto lg:!pt-0" id="navigation">
                <ul class="navbar-nav !m-0 !flex !list-none !flex-col !gap-1 !p-0 lg:!flex-row lg:!items-center lg:!gap-2">
                    @guest
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="nav-link {{ $publicNavLink }} {{ $publicLoginActive ? $publicNavActive : $publicNavInactive }}" @if ($publicLoginActive) aria-current="page" @endif>
                                <i class="fa fa-sign-in !text-sm" aria-hidden="true"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reset') }}" class="nav-link {{ $publicNavLink }} {{ $publicResetActive ? $publicNavActive : $publicNavInactive }}" @if ($publicResetActive) aria-current="page" @endif>Reset Password</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('home') }}" class="nav-link {{ $publicNavLink }} {{ request()->routeIs('home') ? $publicNavActive : $publicNavInactive }}" @if (request()->routeIs('home')) aria-current="page" @endif>
                                <i class="fa fa-home !text-sm" aria-hidden="true"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" id="logout-form" class="!m-0">
                                @csrf
                                <button type="submit" class="nav-link !w-full !bg-transparent {{ $publicNavLink }} {{ $publicNavInactive }}"><i class="fa fa-sign-out !text-sm" aria-hidden="true"></i> Log out</button>
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
        @endif
    </div>
</nav>

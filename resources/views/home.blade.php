@extends('layouts.app')

@section('content')
@php
    $homeNow = now();
    $isDark = session('user_settings.class_theme', '') === 'dark';
    $announcement = $homeannouncement ?? null;
    $birthday = auth()->user()?->birthday;
    $birthdate = $birthday ? \Carbon\Carbon::parse($birthday) : null;

    // Literal utility names remain discoverable by Tailwind's template scanner.
    $surface = $isDark ? '!bg-[#1a293a] !border-[#34465a]' : '!bg-white !border-slate-200';
    $heading = $isDark ? '!text-slate-100' : '!text-[#203b55]';
    $copy = $isDark ? '!text-slate-300' : '!text-slate-600';
    $muted = $isDark ? '!text-slate-400' : '!text-slate-500';
    $accentSurface = $isDark ? '!bg-[#243b52] !text-[#bed8f4]' : '!bg-[#eef4fa] !text-[#315e87]';
    $separator = $isDark ? '!border-[#34465a]' : '!border-slate-200';

    $capabilities = [
        ['icon' => 'fa-th-large', 'title' => 'A consolidated view', 'description' => 'See administrative, financial, operational, and performance information in one connected platform.'],
        ['icon' => 'fa-bolt', 'title' => 'Information for decisions', 'description' => 'Access critical information to support timely, informed decisions across your directorate.'],
        ['icon' => 'fa-line-chart', 'title' => 'Progress in perspective', 'description' => 'Monitor programs, projects, resources, and organizational performance from a common workspace.'],
    ];
    $coverage = [
        ['number' => '01', 'title' => 'Administrative', 'description' => 'Organizational information and resources.'],
        ['number' => '02', 'title' => 'Financial', 'description' => 'Financial planning and resource information.'],
        ['number' => '03', 'title' => 'Operational', 'description' => 'Programs, projects, and implementation.'],
        ['number' => '04', 'title' => 'Performance', 'description' => 'Progress, results, and organizational performance.'],
    ];
@endphp

{{-- Retain the existing navigation and Philippine Standard Time component. --}}
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Home'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div id="direk-home" class="!box-border !px-4 !pb-8 !pt-4 md:!px-6">
    {{-- Welcome: navy brand panel with the existing agency photograph. --}}
    <section class="!relative !isolate !overflow-hidden !rounded-2xl !bg-[#142d45] !text-white !shadow-sm" aria-labelledby="direk-home-title">
        <div class="!absolute !inset-y-0 !right-0 !-z-20 !hidden !w-[48%] lg:!block" aria-hidden="true">
            <img src="{{ asset('assets/img/neda/19276.jpg') }}" alt="" class="!h-full !w-full !object-cover !object-center !opacity-70">
            <div class="!absolute !inset-0 !bg-gradient-to-r !from-[#142d45] !via-[#142d45]/30 !to-[#142d45]/15"></div>
        </div>

        <div class="!relative !grid !gap-8 !p-6 md:!p-8 lg:!grid-cols-12 xl:!p-10">
            <div class="lg:!col-span-8 xl:!col-span-7">
                <span class="!inline-flex !items-center !gap-2.5 !text-xs !font-semibold !uppercase !tracking-[0.12em] !text-[#e4cf9d]">
                    <span class="!h-1.5 !w-1.5 !shrink-0 !rounded-full !bg-[#e4cf9d]" aria-hidden="true"></span>
                    Your executive workspace
                </span>
                <h1 id="direk-home-title" class="!m-0 !mt-5 !text-3xl !font-semibold !leading-tight !tracking-tight !text-white md:!text-4xl xl:!text-5xl">
                    A shared direction.<br>
                    <span class="!font-normal !text-[#e4cf9d]">A clearer view.</span>
                </h1>
                <p class="!m-0 !mt-5 !max-w-xl !text-sm !leading-7 !text-slate-200 md:!text-base">
                    Welcome to D.I.R.E.K. — your connected platform for administrative,
                    financial, operational, and performance information.
                </p>
                <p class="!m-0 !mt-3 !max-w-xl !text-sm !leading-7 !text-slate-300">
                    Designed for DEPDev Directors to bring information together,
                    support decisions, and keep organizational progress in view.
                </p>
                <a href="#direk-coverage" class="!mt-6 !inline-flex !min-h-[44px] !items-center !justify-center !gap-3 !rounded-lg !border !border-solid !border-white/30 !bg-white/10 !px-4 !py-2.5 !text-sm !font-semibold !text-white !no-underline !transition hover:!bg-white/20 focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#e4cf9d] focus-visible:!ring-offset-2 focus-visible:!ring-offset-[#142d45] motion-reduce:!transition-none">
                    Explore DIREK
                    <i class="fa fa-arrow-down" aria-hidden="true"></i>
                </a>
            </div>
            <div class="!flex !items-end lg:!col-span-4 xl:!col-span-5">
                <div class="!w-full !border-0 !border-t !border-solid !border-white/25 !pt-4 lg:!rounded-xl lg:!border lg:!bg-[#142d45]/80 lg:!p-5">
                    <span class="!block !text-sm !font-semibold !tracking-[0.15em] !text-white">D.I.R.E.K.</span>
                    <p class="!m-0 !mt-2 !text-xs !leading-6 !text-slate-200">DEPDev Integrated Reporting<br>and Executive Kiosk</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Keep current announcements near the top of the home page. --}}
    @if (
        $announcement
        && $announcement->start_date
        && $announcement->end_date
        && \Carbon\Carbon::parse($announcement->start_date)->lte($homeNow)
        && \Carbon\Carbon::parse($announcement->end_date)->gte($homeNow)
    )
        <div class="!mt-6">
            @include('components.home-announcement')
        </div>
    @endif

    {{-- Preserve the existing birthday component and its date condition. --}}
    @if ($birthdate && $birthdate->month === $homeNow->month && $birthdate->day === $homeNow->day)
        <div class="!mt-6">
            @include('others.hbd')
        </div>
    @endif

    <section class="!mt-8" aria-labelledby="direk-workspace-title">
        <div class="!mb-4 !flex !flex-wrap !items-end !justify-between !gap-3">
            <div>
                <p class="!m-0 !text-xs !font-semibold !uppercase !tracking-[0.12em] {{ $muted }}">Connected information</p>
                <h2 id="direk-workspace-title" class="!m-0 !mt-2 !text-xl !font-semibold !tracking-tight {{ $heading }}">Built around your decisions</h2>
            </div>
            <span class="!text-xs {{ $muted }}">One platform. A common perspective.</span>
        </div>
        <div class="!grid !gap-4 xl:!grid-cols-3">
            @foreach ($capabilities as $capability)
                <article class="!box-border !rounded-xl !border !border-solid !p-5 {{ $surface }}">
                    <div class="!mb-5 !grid !h-11 !w-11 !place-items-center !rounded-lg {{ $accentSurface }}">
                        <i class="fa {{ $capability['icon'] }} !text-base" aria-hidden="true"></i>
                    </div>
                    <h3 class="!m-0 !text-base !font-semibold {{ $heading }}">{{ $capability['title'] }}</h3>
                    <p class="!m-0 !mt-3 !text-sm !leading-7 {{ $copy }}">{{ $capability['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Informational categories, without inventing module routes or access permissions. --}}
    <section id="direk-coverage" class="!mt-6 !scroll-mt-24 !rounded-xl !border !border-solid {{ $surface }}" aria-labelledby="direk-coverage-title">
        <div class="!flex !flex-wrap !items-start !justify-between !gap-4 !border-0 !border-b !border-solid !p-5 md:!p-6 {{ $separator }}">
            <div>
                <span class="!text-xs !font-semibold !uppercase !tracking-[0.12em] {{ $muted }}">The bigger picture</span>
                <h2 id="direk-coverage-title" class="!m-0 !mt-2 !text-xl !font-semibold !tracking-tight {{ $heading }}">Four perspectives. One workspace.</h2>
            </div>
            <p class="!m-0 !max-w-sm !text-sm !leading-7 {{ $copy }}">A consolidated view of the information that supports your directorate.</p>
        </div>
        <div class="!grid !gap-6 !p-5 sm:!grid-cols-2 md:!p-6 xl:!grid-cols-4">
            @foreach ($coverage as $area)
                <article>
                    <span class="!text-xs !font-medium !tabular-nums !tracking-wider {{ $muted }}" aria-hidden="true">{{ $area['number'] }}</span>
                    <h3 class="!m-0 !mt-3 !text-base !font-semibold {{ $heading }}">{{ $area['title'] }}</h3>
                    <p class="!m-0 !mt-2 !text-sm !leading-7 {{ $copy }}">{{ $area['description'] }}</p>
                </article>
            @endforeach
        </div>
        <div class="!flex !items-start !gap-3 !border-0 !border-t !border-solid !px-5 !py-4 md:!px-6 {{ $separator }}">
            <i class="fa fa-compass !mt-1 {{ $muted }}" aria-hidden="true"></i>
            <p class="!m-0 !text-xs !leading-6 {{ $muted }}">Use the navigation menu to open the modules available to your account.</p>
        </div>
    </section>
</div>

@include('layouts.footers.auth.footer')
@endsection

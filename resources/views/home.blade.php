@extends('layouts.app')

@section('content')

<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-1 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Home'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="px-4 pb-8 pt-4">

    {{-- Hero Section --}}
    <section
        class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        style="
            background-image:
                linear-gradient(
                    90deg,
                    rgba(255,255,255,0.97) 0%,
                    rgba(255,255,255,0.90) 45%,
                    rgba(255,255,255,0.70) 100%
                ),
                url('{{ session('user_settings.class_theme', '') == 'dark'
                    ? asset('assets/img/blue gradient technology wave line_8802318.png')
                    : asset('assets/img/tp204-background-10.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        "
    >
        <div class="relative z-10 grid gap-8 p-6 md:p-8 xl:grid-cols-12 xl:p-10">

            {{-- Welcome --}}
            <div class="xl:col-span-7">

                <span
                    class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50
                           px-3 py-1 text-xs font-semibold uppercase tracking-wide text-sky-700"
                >
                    DEPDev Integrated Reporting and Executive Kiosk
                </span>

                <p class="mt-6 text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">
                    Welcome to
                </p>

                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl xl:text-5xl">
                    D.I.R.E.K.
                    <span class="text-sky-600">Application</span>
                </h1>

                <p class="mt-5 max-w-3xl text-base leading-7 text-slate-600 md:text-lg">
                    Project DIREK is a one-stop digital platform designed for
                    DEPDev Directors, providing a consolidated view of
                    administrative, financial, operational, and performance
                    information in a single dashboard.
                </p>

                <p class="mt-4 max-w-3xl text-sm leading-6 text-slate-500 md:text-base">
                    It serves as an executive command center that enables faster
                    decision-making, improves access to critical information,
                    and streamlines the monitoring of programs, projects,
                    resources, and organizational performance.
                </p>

            </div>

            {{-- Feature Cards --}}
            <div class="space-y-3 xl:col-span-5">

                <div class="rounded-xl border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur-sm">
                    <div class="flex items-start gap-4">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center
                                    rounded-lg bg-sky-50 text-sky-600">
                            <i class="fa fa-th-large"></i>
                        </div>

                        <div>
                            <h2 class="text-sm font-bold text-slate-900">
                                Consolidated View
                            </h2>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Bring administrative, financial, operational,
                                and performance information together in a
                                single dashboard.
                            </p>
                        </div>

                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur-sm">
                    <div class="flex items-start gap-4">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center
                                    rounded-lg bg-emerald-50 text-emerald-600">
                            <i class="fa fa-bolt"></i>
                        </div>

                        <div>
                            <h2 class="text-sm font-bold text-slate-900">
                                Faster Decision-Making
                            </h2>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Give Directors quicker access to the critical
                                information they need, right when they need it.
                            </p>
                        </div>

                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur-sm">
                    <div class="flex items-start gap-4">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center
                                    rounded-lg bg-violet-50 text-violet-600">
                            <i class="fa fa-line-chart"></i>
                        </div>

                        <div>
                            <h2 class="text-sm font-bold text-slate-900">
                                Streamlined Monitoring
                            </h2>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Track programs, projects, resources, and
                                organizational performance from one command
                                center.
                            </p>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </section>


    {{-- Information Cards --}}
    <section class="mt-6 grid gap-5 lg:grid-cols-2">

        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start gap-4">

                <div class="flex h-11 w-11 shrink-0 items-center justify-center
                            rounded-xl bg-sky-50 text-sky-600">
                    <i class="fa fa-dashboard"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        What D.I.R.E.K. Covers
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        DIREK consolidates administrative, financial,
                        operational, and performance information for DEPDev
                        Directors into a single dashboard — covering programs,
                        projects, resources, and organizational performance in
                        one place.
                    </p>
                </div>

            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start gap-4">

                <div class="flex h-11 w-11 shrink-0 items-center justify-center
                            rounded-xl bg-emerald-50 text-emerald-600">
                    <i class="fa fa-bar-chart"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        What This Portal Supports
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        As an executive command center, this portal supports
                        faster decision-making, improved access to critical
                        information, and streamlined monitoring across all
                        directorate activities.
                    </p>
                </div>

            </div>
        </article>

    </section>


    {{-- Announcement --}}
    @if (
        $homeannouncement
        && $homeannouncement->start_date
        && $homeannouncement->end_date
        && \Carbon\Carbon::parse($homeannouncement->start_date)->lte(now())
        && \Carbon\Carbon::parse($homeannouncement->end_date)->gte(now())
    )
        <div class="mt-6">
            @include('components.home-announcement')
        </div>
    @endif


    {{-- Birthday --}}
    @php
        $birthdate = auth()->user()->birthday
            ? \Carbon\Carbon::parse(auth()->user()->birthday)
            : null;
    @endphp

    @if (
        $birthdate
        && $birthdate->month === now()->month
        && $birthdate->day === now()->day
    )
        @include('others.hbd')
    @endif

</div>

@include('layouts.footers.auth.footer')

@endsection

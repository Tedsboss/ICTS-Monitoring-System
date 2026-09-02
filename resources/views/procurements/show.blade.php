@extends('layouts.app')

@section('content')

<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Procurement – Details'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="px-4 pb-8 pt-4">

    {{-- Back --}}
    <div class="mb-4">

        <a
            href="{{ route('procurements.index') }}"
            class="inline-flex items-center gap-2 rounded-lg
                   border border-slate-300 bg-white px-4 py-2
                   text-sm font-semibold text-slate-700 shadow-sm
                   transition hover:bg-slate-50"
        >
            <i class="fa fa-arrow-left"></i>

            <span>
                Back to Procurements
            </span>
        </a>

    </div>


    {{-- Procurement Details --}}
    <section class="max-w-6xl">

        <div
            class="overflow-hidden rounded-2xl border
                   border-slate-200 bg-white shadow-sm"
        >

            {{-- Header --}}
            <div
                class="flex flex-col gap-4 border-b border-slate-200
                       px-6 py-5 sm:flex-row sm:items-center
                       sm:justify-between"
            >

                <div class="min-w-0">

                    <h1
                        class="break-words text-xl font-bold
                               text-slate-900"
                    >
                        {{ $procurement->procurement_title ?: 'Procurement Entry' }}
                    </h1>


                    <div
                        class="mt-2 flex flex-wrap items-center
                               gap-x-2 gap-y-1 text-sm text-slate-500"
                    >

                        <span>
                            {{ $procurement->funding_source ?: '—' }}
                        </span>

                        <span class="text-slate-300">
                            ·
                        </span>

                        <span>
                            {{ $procurement->expense_class ?: '—' }}
                        </span>

                    </div>

                </div>


                @can('update', $procurement)

                    <a
                        href="{{ route('procurements.edit', $procurement) }}"
                        class="inline-flex w-fit shrink-0 items-center
                               gap-2 rounded-lg bg-amber-500 px-4
                               py-2 text-sm font-semibold text-white
                               shadow-sm transition hover:bg-amber-600"
                    >
                        <i class="fa fa-pencil"></i>

                        <span>
                            Edit
                        </span>
                    </a>

                @endcan

            </div>


            {{-- Body --}}
            <div class="p-6">

                {{-- Basic Information --}}
                <div>

                    <div class="mb-4">

                        <h2
                            class="text-sm font-bold uppercase
                                   tracking-[0.08em] text-slate-700"
                        >
                            Procurement Information
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            General information associated with this procurement entry.
                        </p>

                    </div>


                    <div
                        class="grid gap-x-8 gap-y-6
                               md:grid-cols-2 xl:grid-cols-3"
                    >

                        {{-- Funding Source --}}
                        <div>

                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-slate-500"
                            >
                                Funding Source
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold
                                       text-slate-900"
                            >
                                {{ $procurement->funding_source ?: '—' }}
                            </p>

                        </div>


                        {{-- Expense Class --}}
                        <div>

                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-slate-500"
                            >
                                Expense Class
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold
                                       text-slate-900"
                            >
                                {{ $procurement->expense_class ?: '—' }}
                            </p>

                        </div>


                        {{-- Division Assigned --}}
                        <div>

                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-slate-500"
                            >
                                Division Assigned
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold
                                       text-slate-900"
                            >
                                {{ $procurement->division_assigned ?: '—' }}
                            </p>

                        </div>


                        {{-- Procurement Title --}}
                        <div class="md:col-span-2 xl:col-span-3">

                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-slate-500"
                            >
                                Procurement Title
                            </p>

                            <p
                                class="mt-2 break-words text-sm
                                       font-semibold leading-relaxed
                                       text-slate-900"
                            >
                                {{ $procurement->procurement_title ?: '—' }}
                            </p>

                        </div>


                        {{-- Quarter --}}
                        <div>

                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-slate-500"
                            >
                                Quarter
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold
                                       text-slate-900"
                            >
                                {{ $procurement->quarter ?: '—' }}
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Amount & Status --}}
                <div class="mt-8 border-t border-slate-200 pt-6">

                    <div class="mb-4">

                        <h2
                            class="text-sm font-bold uppercase
                                   tracking-[0.08em] text-slate-700"
                        >
                            Amount &amp; Status
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Current amount and procurement processing status.
                        </p>

                    </div>


                    <div
                        class="grid gap-4 sm:grid-cols-2
                               lg:grid-cols-4"
                    >

                        {{-- Amount --}}
                        <div
                            class="rounded-xl border border-slate-200
                                   bg-slate-50 p-4"
                        >

                            <div
                                class="flex items-start
                                       justify-between gap-3"
                            >

                                <div>

                                    <p
                                        class="text-xs font-semibold
                                               uppercase tracking-wide
                                               text-slate-500"
                                    >
                                        Amount
                                    </p>

                                    <p
                                        class="mt-2 text-lg font-bold
                                               text-slate-900"
                                    >
                                        ₱{{ number_format($procurement->amount ?? 0, 2) }}
                                    </p>

                                </div>


                                <div
                                    class="flex h-9 w-9 shrink-0
                                           items-center justify-center
                                           rounded-lg bg-sky-100
                                           text-sky-600"
                                >
                                    <i class="fa fa-money"></i>
                                </div>

                            </div>

                        </div>


                        {{-- Status Cards --}}
                        @foreach ([
                            'procurement_status' => 'Procurement',
                            'payment_status' => 'Payment',
                            'retention_status' => 'Retention',
                        ] as $field => $label)

                            <div
                                class="rounded-xl border border-slate-200
                                       bg-slate-50 p-4"
                            >

                                <p
                                    class="text-xs font-semibold uppercase
                                           tracking-wide text-slate-500"
                                >
                                    {{ $label }}
                                </p>


                                <div class="mt-3">

                                    @if ($procurement->{$field} === 'OK')

                                        <span
                                            class="inline-flex items-center gap-1.5
                                                   rounded-full bg-emerald-100
                                                   px-3 py-1.5 text-xs
                                                   font-bold text-emerald-700"
                                        >
                                            <i class="fa fa-check"></i>

                                            <span>
                                                OK
                                            </span>
                                        </span>

                                    @else

                                        <span
                                            class="inline-flex items-center
                                                   rounded-full bg-slate-200
                                                   px-3 py-1.5 text-xs
                                                   font-semibold text-slate-500"
                                        >
                                            —
                                        </span>

                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- Footer --}}
    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>

</div>

@endsection

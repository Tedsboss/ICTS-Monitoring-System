@extends('layouts.app')

@section('content')

<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'SAEB – Entry Details'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="px-4 pb-8 pt-4">

    {{-- Back --}}
    <div class="mb-4">
        <a
            href="{{ route('saebs.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300
                   bg-white px-4 py-2 text-sm font-semibold text-slate-700
                   shadow-sm transition hover:bg-slate-50"
        >
            <i class="fa fa-arrow-left"></i>
            <span>Back to SAEB</span>
        </a>
    </div>


    {{-- Details Card --}}
    <section class="max-w-6xl">

        <div
            class="overflow-hidden rounded-2xl border border-slate-200
                   bg-white shadow-sm"
        >

            {{-- Header --}}
            <div
                class="flex flex-col gap-4 border-b border-slate-200
                       px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-xl font-bold text-slate-900">
                        {{ $saeb->funding_source }}
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $saeb->expense_class }}
                        <span class="mx-1 text-slate-300">·</span>
                        {{ $saeb->allotment_class }}
                    </p>
                </div>

                @can('update', $saeb)

                    <a
                        href="{{ route('saebs.edit', $saeb) }}"
                        class="inline-flex items-center gap-2 rounded-lg
                               bg-amber-500 px-4 py-2 text-sm font-semibold
                               text-white shadow-sm transition hover:bg-amber-600"
                    >
                        <i class="fa fa-pencil"></i>
                        <span>Edit</span>
                    </a>

                @endcan

            </div>


            {{-- Basic Information --}}
            <div class="p-6">

                <div class="grid gap-5 md:grid-cols-3">

                    <div>
                        <p
                            class="text-xs font-semibold uppercase
                                   tracking-wide text-slate-500"
                        >
                            As Of Date
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ $saeb->as_of_date
                                ? \Carbon\Carbon::parse($saeb->as_of_date)->format('F j, Y')
                                : '—' }}
                        </p>
                    </div>


                    <div>
                        <p
                            class="text-xs font-semibold uppercase
                                   tracking-wide text-slate-500"
                        >
                            Funding Source
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ $saeb->funding_source ?: '—' }}
                        </p>
                    </div>


                    <div>
                        <p
                            class="text-xs font-semibold uppercase
                                   tracking-wide text-slate-500"
                        >
                            Allotment Class
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ $saeb->allotment_class ?: '—' }}
                        </p>
                    </div>


                    <div class="md:col-span-3">
                        <p
                            class="text-xs font-semibold uppercase
                                   tracking-wide text-slate-500"
                        >
                            Expense Class
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ $saeb->expense_class ?: '—' }}
                        </p>
                    </div>

                </div>


                {{-- Financial Summary --}}
                <div class="mt-7 border-t border-slate-200 pt-6">

                    <div class="mb-4">
                        <h2
                            class="text-sm font-bold uppercase
                                   tracking-[0.08em] text-slate-700"
                        >
                            Financial Summary
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Current allotment, obligations, allocation allotment, and balance values.
                        </p>
                    </div>


                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

                        @foreach ([
                            'allotment' => 'Allotment',
                            'obligated' => 'Obligated',
                            'aa' => 'AA',
                            'balances' => 'Balances',
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

                                <p
                                    class="mt-2 text-lg font-bold
                                           text-slate-900"
                                >
                                    {{ number_format($saeb->{$field} ?? 0, 2) }}
                                </p>
                            </div>

                        @endforeach


                        <div
                            class="rounded-xl border border-slate-200
                                   bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-slate-500"
                            >
                                % Obligated
                            </p>

                            @php
                                $percentObligated = (float) ($saeb->percent_obligated ?? 0);
                            @endphp

                            <div class="mt-2">

                                <span
                                    class="
                                        inline-flex min-w-[80px] items-center justify-center
                                        rounded-full px-3 py-1.5 text-sm font-bold

                                        {{ $percentObligated >= 80
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : ($percentObligated >= 50
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-slate-200 text-slate-700') }}
                                    "
                                >
                                    {{ number_format($percentObligated, 2) }}%
                                </span>

                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>

</div>

@endsection

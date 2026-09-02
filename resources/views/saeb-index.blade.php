@extends('layouts.app')

@section('content')

<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-1 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'SAEB'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="px-4 pb-8 pt-4">

    {{-- KPI Summary --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

        {{-- Total Allotment --}}
        <div
            class="rounded-2xl border border-slate-200 border-l-4 border-l-sky-500
                   bg-white p-5 shadow-sm"
        >
            <div class="flex items-start justify-between gap-4">

                <div>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.12em]
                               text-slate-500"
                    >
                        Total Allotment
                    </p>

                    <h2
                        class="mt-2 text-2xl font-bold tracking-tight
                               text-slate-900"
                    >
                        {{ number_format($fundTotal->sum_allotment ?? 0, 2) }}
                    </h2>
                </div>

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center
                           rounded-xl bg-sky-50 text-sky-600"
                >
                    <i class="fa fa-money"></i>
                </div>

            </div>
        </div>


        {{-- Total Obligated --}}
        <div
            class="rounded-2xl border border-slate-200 border-l-4 border-l-cyan-500
                   bg-white p-5 shadow-sm"
        >
            <div class="flex items-start justify-between gap-4">

                <div>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.12em]
                               text-slate-500"
                    >
                        Total Obligated
                    </p>

                    <h2
                        class="mt-2 text-2xl font-bold tracking-tight
                               text-slate-900"
                    >
                        {{ number_format($fundTotal->sum_obligated ?? 0, 2) }}
                    </h2>
                </div>

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center
                           rounded-xl bg-cyan-50 text-cyan-600"
                >
                    <i class="fa fa-check-circle"></i>
                </div>

            </div>
        </div>


        {{-- Total Balances --}}
        <div
            class="rounded-2xl border border-slate-200 border-l-4 border-l-violet-500
                   bg-white p-5 shadow-sm"
        >
            <div class="flex items-start justify-between gap-4">

                <div>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.12em]
                               text-slate-500"
                    >
                        Total Balances
                    </p>

                    <h2
                        class="mt-2 text-2xl font-bold tracking-tight
                               text-slate-900"
                    >
                        {{ number_format($fundTotal->sum_balances ?? 0, 2) }}
                    </h2>
                </div>

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center
                           rounded-xl bg-violet-50 text-violet-600"
                >
                    <i class="fa fa-balance-scale"></i>
                </div>

            </div>
        </div>


        {{-- Needs Attention --}}
        <div
            class="rounded-2xl border border-slate-200 border-l-4
                   {{ $flaggedFunds->isEmpty() ? 'border-l-emerald-500' : 'border-l-rose-500' }}
                   bg-white p-5 shadow-sm"
        >
            <div class="flex items-start justify-between gap-4">

                <div>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.12em]
                               text-slate-500"
                    >
                        Needs Attention
                    </p>

                    <h2
                        class="mt-2 text-2xl font-bold tracking-tight
                               {{ $flaggedFunds->isEmpty() ? 'text-emerald-600' : 'text-rose-600' }}"
                    >
                        {{ $flaggedFunds->count() }}
                        {{ \Illuminate\Support\Str::plural('fund', $flaggedFunds->count()) }}
                    </h2>
                </div>

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                           {{ $flaggedFunds->isEmpty()
                               ? 'bg-emerald-50 text-emerald-600'
                               : 'bg-rose-50 text-rose-600' }}"
                >
                    <i
                        class="fa {{ $flaggedFunds->isEmpty()
                            ? 'fa-check'
                            : 'fa-exclamation-triangle' }}"
                    ></i>
                </div>

            </div>
        </div>

    </section>


    {{-- Attention Alert --}}
    @if ($flaggedFunds->isNotEmpty())

        <section class="mt-4">

            <div
                class="flex items-start gap-3 rounded-xl border border-rose-200
                       bg-rose-50 px-4 py-3 text-sm text-rose-800"
            >
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center
                           rounded-lg bg-rose-100 text-rose-600"
                >
                    <i class="fa fa-exclamation-triangle"></i>
                </div>

                <div class="leading-6">
                    <span class="font-semibold">
                        Obligation monitoring alert.
                    </span>

                    {{ $yearProgress }}% of the fiscal year has elapsed, but
                    {{ $flaggedFunds->pluck('funding_source')->join(', ', ' and ') }}
                    {{ $flaggedFunds->count() === 1 ? 'is' : 'are' }}
                    significantly behind on obligations.
                </div>
            </div>

        </section>

    @endif


    {{-- Detail Tables --}}
    <section class="mt-6 grid gap-6 xl:grid-cols-2">

        {{-- Estimated Balances by Class --}}
        <div
            class="overflow-hidden rounded-2xl border border-slate-200
                   bg-white shadow-sm"
        >
            <div
                class="flex items-center justify-between border-b
                       border-slate-200 px-5 py-4"
            >
                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Estimated Balances by Class
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Breakdown of balances by funding source and expense class.
                    </p>
                </div>

                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl
                           bg-sky-50 text-sky-600"
                >
                    <i class="fa fa-table"></i>
                </div>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">
                        <tr>

                            <th
                                scope="col"
                                class="px-5 py-3 text-left text-xs font-semibold
                                       uppercase tracking-wider text-slate-500"
                            >
                                Funding Source
                            </th>

                            <th
                                scope="col"
                                class="px-5 py-3 text-right text-xs font-semibold
                                       uppercase tracking-wider text-slate-500"
                            >
                                CO
                            </th>

                            <th
                                scope="col"
                                class="px-5 py-3 text-right text-xs font-semibold
                                       uppercase tracking-wider text-slate-500"
                            >
                                MOOE
                            </th>

                            <th
                                scope="col"
                                class="px-5 py-3 text-right text-xs font-semibold
                                       uppercase tracking-wider text-slate-500"
                            >
                                Total
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse ($saebBalancesByClass as $row)

                            @php
                                $isTotal = $row->funding_source === 'Grand Total';
                            @endphp

                            <tr
                                class="{{ $isTotal ? 'bg-slate-50 font-bold' : 'hover:bg-slate-50' }}"
                            >

                                <td
                                    class="whitespace-nowrap px-5 py-3 text-sm
                                           {{ $isTotal ? 'text-slate-900' : 'text-slate-600' }}"
                                >
                                    {{ $row->funding_source }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-3 text-right
                                           text-sm text-slate-600"
                                >
                                    {{ number_format($row->co ?? 0, 2) }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-3 text-right
                                           text-sm text-slate-600"
                                >
                                    {{ number_format($row->mooe ?? 0, 2) }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-3 text-right text-sm
                                           {{ $isTotal ? 'font-bold text-slate-900' : 'text-slate-600' }}"
                                >
                                    {{ number_format($row->grand_total ?? 0, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="4"
                                    class="px-5 py-10 text-center text-sm
                                           text-slate-500"
                                >
                                    No SAEB balance data available.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>
        </div>


        {{-- Fund Summary --}}
        <div
            class="overflow-hidden rounded-2xl border border-slate-200
                   bg-white shadow-sm"
        >
            <div
                class="flex items-center justify-between border-b
                       border-slate-200 px-5 py-4"
            >
                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Fund Summary
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Allotment, obligations, balances, and utilization by fund.
                    </p>
                </div>

                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl
                           bg-emerald-50 text-emerald-600"
                >
                    <i class="fa fa-bar-chart"></i>
                </div>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">
                        <tr>

                            <th
                                scope="col"
                                class="px-4 py-3 text-left text-xs font-semibold
                                       uppercase tracking-wider text-slate-500"
                            >
                                Funding Source
                            </th>

                            <th
                                scope="col"
                                class="px-4 py-3 text-right text-xs font-semibold
                                       uppercase tracking-wider text-slate-500"
                            >
                                Allotment
                            </th>

                            <th
                                scope="col"
                                class="px-4 py-3 text-right text-xs font-semibold
                                       uppercase tracking-wider text-slate-500"
                            >
                                Obligated
                            </th>

                            <th
                                scope="col"
                                class="px-4 py-3 text-right text-xs font-semibold
                                       uppercase tracking-wider text-slate-500"
                            >
                                Allocation Allotment
                            </th>

                            <th
                                scope="col"
                                class="px-4 py-3 text-right text-xs font-semibold
                                       uppercase tracking-wider text-slate-500"
                            >
                                Balances
                            </th>

                            <th
                                scope="col"
                                class="px-4 py-3 text-right text-xs font-semibold
                                       uppercase tracking-wider text-slate-500"
                            >
                                % Obl.
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse ($saebFundSummary as $row)

                            @php
                                $isTotal = $row->funding_source === 'Grand Total';

                                $isFlagged =
                                    ! $isTotal
                                    && (float) $row->pct_obligated
                                        < ($yearProgress - $attentionBuffer);
                            @endphp

                            <tr
                                class="
                                    {{ $isTotal ? 'bg-slate-50 font-bold' : 'hover:bg-slate-50' }}
                                    {{ $isFlagged ? 'bg-rose-50' : '' }}
                                "
                            >

                                <td
                                    class="whitespace-nowrap px-4 py-3 text-sm
                                           {{ $isTotal ? 'text-slate-900' : 'text-slate-600' }}"
                                >
                                    <div class="flex items-center gap-2">

                                        <span>
                                            {{ $row->funding_source }}
                                        </span>

                                        @if ($isFlagged)

                                            <span
                                                class="inline-flex h-6 w-6 items-center
                                                       justify-center rounded-full
                                                       bg-rose-100 text-rose-600"
                                                data-bs-toggle="tooltip"
                                                title="Behind schedule for this point in the fiscal year"
                                            >
                                                <i
                                                    class="fa fa-exclamation-triangle"
                                                    style="font-size: 10px;"
                                                ></i>
                                            </span>

                                        @endif

                                    </div>
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-3 text-right
                                           text-sm text-slate-600"
                                >
                                    {{ number_format($row->sum_allotment ?? 0, 2) }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-3 text-right
                                           text-sm text-slate-600"
                                >
                                    {{ number_format($row->sum_obligated ?? 0, 2) }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-3 text-right
                                           text-sm text-slate-600"
                                >
                                    {{ number_format($row->sum_aa ?? 0, 2) }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-3 text-right text-sm
                                           {{ $isTotal ? 'font-bold text-slate-900' : 'text-slate-600' }}"
                                >
                                    {{ number_format($row->sum_balances ?? 0, 2) }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-3 text-right text-sm"
                                >
                                    @php
                                        $pctObligated = (float) ($row->pct_obligated ?? 0);
                                    @endphp

                                    <span
                                        class="
                                            inline-flex min-w-[70px] items-center justify-center
                                            rounded-full px-2.5 py-1 text-xs font-semibold

                                            {{ $pctObligated >= 80
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : ($pctObligated >= 50
                                                    ? 'bg-amber-100 text-amber-700'
                                                    : 'bg-slate-100 text-slate-600') }}
                                        "
                                    >
                                        {{ number_format($pctObligated, 2) }}%
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="6"
                                    class="px-5 py-10 text-center text-sm
                                           text-slate-500"
                                >
                                    No SAEB fund summary data available.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </section>


    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>

</div>

@endsection

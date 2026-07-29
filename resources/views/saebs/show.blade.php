@extends('layouts.app')

@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'SAEB – Entry Details'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="container-fluid mt-4 px-4">

    <div class="mb-3">
        <a href="{{ route('saebs.index') }}" class="btn btn-sm btn-outline-dark px-3">
            <i class="fa fa-arrow-left me-1"></i> Back to SAEB
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-9">
            <div class="card shadow-sm border-0">

                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0">{{ $saeb->funding_source }}</h5>
                        <small class="text-muted">{{ $saeb->expense_class }} · {{ $saeb->allotment_class }}</small>
                    </div>
                    <a href="{{ route('saebs.edit', $saeb) }}" class="btn btn-warning btn-sm">Edit</a>
                </div>

                <div class="card-body">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size:0.75rem;">As Of Date</div>
                            <div class="fw-semibold mt-1">{{ $saeb->as_of_date?->format('F j, Y') ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size:0.75rem;">Funding Source</div>
                            <div class="fw-semibold mt-1">{{ $saeb->funding_source }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size:0.75rem;">Allotment Class</div>
                            <div class="fw-semibold mt-1">{{ $saeb->allotment_class }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted" style="font-size:0.75rem;">Expense Class</div>
                            <div class="fw-semibold mt-1">{{ $saeb->expense_class }}</div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size:0.72rem; letter-spacing:.06em;">
                        Financial Summary
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="p-3 rounded border bg-light">
                                <div class="text-muted" style="font-size:0.75rem;">Allotment</div>
                                <div class="fw-bold mt-1">{{ number_format($saeb->allotment, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded border bg-light">
                                <div class="text-muted" style="font-size:0.75rem;">Obligated</div>
                                <div class="fw-bold mt-1">{{ number_format($saeb->obligated, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded border bg-light">
                                <div class="text-muted" style="font-size:0.75rem;">AA</div>
                                <div class="fw-bold mt-1">{{ number_format($saeb->aa, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded border bg-light">
                                <div class="text-muted" style="font-size:0.75rem;">Balances</div>
                                <div class="fw-bold mt-1">{{ number_format($saeb->balances, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded border bg-light">
                                <div class="text-muted" style="font-size:0.75rem;">% Obligated</div>
                                <div class="fw-bold mt-1">{{ number_format($saeb->percent_obligated, 2) }}%</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footers.auth.footer')
@endsection

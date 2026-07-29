@extends('layouts.app')

@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Procurement – Details'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="container-fluid mt-4 px-4">

    <div class="mb-3">
        <a href="{{ route('procurements.index') }}" class="btn btn-sm btn-outline-dark px-3">
            <i class="fa fa-arrow-left me-1"></i> Back to Procurements
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-9">
            <div class="card shadow-sm border-0">

                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0">{{ $procurement->procurement_title }}</h5>
                        <small class="text-muted">{{ $procurement->funding_source }} · {{ $procurement->expense_class }}</small>
                    </div>
                    <a href="{{ route('procurements.edit', $procurement) }}" class="btn btn-warning btn-sm">Edit</a>
                </div>

                <div class="card-body">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size:0.75rem;">Funding Source</div>
                            <div class="fw-semibold mt-1">{{ $procurement->funding_source }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size:0.75rem;">Expense Class</div>
                            <div class="fw-semibold mt-1">{{ $procurement->expense_class }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size:0.75rem;">Division Assigned</div>
                            <div class="fw-semibold mt-1">{{ $procurement->division_assigned }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted" style="font-size:0.75rem;">Procurement Title</div>
                            <div class="fw-semibold mt-1">{{ $procurement->procurement_title }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size:0.75rem;">Quarter</div>
                            <div class="fw-semibold mt-1">{{ $procurement->quarter ?? '—' }}</div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size:0.72rem; letter-spacing:.06em;">
                        Amount &amp; Status
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="p-3 rounded border bg-light">
                                <div class="text-muted" style="font-size:0.75rem;">Amount</div>
                                <div class="fw-bold mt-1">{{ number_format($procurement->amount, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded border bg-light">
                                <div class="text-muted" style="font-size:0.75rem;">Procurement</div>
                                <div class="fw-bold mt-1">
                                    @if($procurement->procurement_status === 'OK')
                                        <span class="badge bg-success">OK</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded border bg-light">
                                <div class="text-muted" style="font-size:0.75rem;">Payment</div>
                                <div class="fw-bold mt-1">
                                    @if($procurement->payment_status === 'OK')
                                        <span class="badge bg-success">OK</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded border bg-light">
                                <div class="text-muted" style="font-size:0.75rem;">Retention</div>
                                <div class="fw-bold mt-1">
                                    @if($procurement->retention_status === 'OK')
                                        <span class="badge bg-success">OK</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
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
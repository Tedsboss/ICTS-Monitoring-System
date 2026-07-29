{{-- resources/views/financial-plans/plans.blade.php --}}
@extends('layouts.app')

@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Work & Financial Plans'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="container-fluid mt-4 px-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- File a new WFP --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fa fa-plus-circle me-1 text-success"></i> File a New Work & Financial Plan</h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">Fiscal Year</label>
                    <input type="number" id="newFiscalYear" class="form-control form-control-sm" style="width:120px;" value="{{ now()->year }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1">Name of Office/Staff</label>
                    <input type="text" id="newOfficeName" class="form-control form-control-sm" style="width:340px;"
                        list="officeSuggestions" placeholder="Type an existing office, or a brand-new one">
                    <datalist id="officeSuggestions">
                        @foreach($offices as $office)
                            <option value="{{ $office }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1 invisible">Action</label>
                    <button type="button" id="btnStartPlan" class="btn btn-sm btn-success d-flex align-items-center justify-content-center"
                            style="width:31px; height:31px;" data-bs-toggle="tooltip" title="Start / Open in Builder">
                        <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            <p class="text-muted small mb-0 mt-2">
                If that fiscal year + office combination already has rows on file, the builder loads them for editing.
                Otherwise it opens a blank plan ready to fill in.
            </p>
        </div>
    </div>

    {{-- All filed plans --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Filed Work & Financial Plans</h6>
            <span class="badge bg-gradient-info">{{ $plans->count() }} total</span>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="text-muted text-uppercase" style="font-size:0.72rem;">
                        <tr>
                            <th>Fiscal Year</th>
                            <th>Office/Staff</th>
                            <th class="text-end">Rows on File</th>
                            <th class="text-end">MOOE + CO Total</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                            <tr>
                                <td class="fw-semibold">{{ $plan->fiscal_year }}</td>
                                <td>{{ $plan->office_name }}</td>
                                <td class="text-end">{{ $plan->row_count }}</td>
                                <td class="text-end">{{ number_format($plan->total_budget, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('financial-plans.index', ['fiscal_year' => $plan->fiscal_year, 'office_name' => $plan->office_name]) }}"
                                       class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('financial-plans.builder', ['fiscal_year' => $plan->fiscal_year, 'office_name' => $plan->office_name]) }}"
                                       class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <form action="{{ route('financial-plans.destroy-plan') }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete the entire FY {{ $plan->fiscal_year }} plan for {{ $plan->office_name }}? This removes all {{ $plan->row_count }} row(s) and cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="fiscal_year" value="{{ $plan->fiscal_year }}">
                                        <input type="hidden" name="office_name" value="{{ $plan->office_name }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No Work & Financial Plans filed yet. Use the form above to start one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('layouts.footers.auth.footer')
@endsection

@push('js')
<script>
$(document).ready(function () {
    $('#btnStartPlan').on('click', function () {
        const fiscalYear = $('#newFiscalYear').val();
        const officeName = $('#newOfficeName').val().trim();

        if (!fiscalYear) {
            alert('Fiscal Year is required.');
            return;
        }
        if (!officeName) {
            alert('Name of Office/Staff is required.');
            return;
        }

        window.location.href =
            `{{ route('financial-plans.builder') }}?fiscal_year=${fiscalYear}&office_name=${encodeURIComponent(officeName)}`;
    });
});
</script>
@endpush

@extends('layouts.app')

@section('content')
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'SAEB'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">

    {{-- KPI summary --}}
    <div class="row mt-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
        <div class="card-body py-3 px-4">
            <p class="text-muted text-uppercase mb-1" style="font-size:0.68rem; letter-spacing:.06em;">Total Allotment</p>
            <h3 class="mb-0 fw-bold text-dark">{{ number_format($fundTotal->sum_allotment, 2) }}</h3>
        </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
        <div class="card-body py-3 px-4">
            <p class="text-muted text-uppercase mb-1" style="font-size:0.68rem; letter-spacing:.06em;">Total Obligated</p>
            <h3 class="mb-0 fw-bold text-dark">{{ number_format($fundTotal->sum_obligated, 2) }}</h3>
        </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
        <div class="card-body py-3 px-4">
            <p class="text-muted text-uppercase mb-1" style="font-size:0.68rem; letter-spacing:.06em;">Total Balances</p>
            <h3 class="mb-0 fw-bold text-dark">{{ number_format($fundTotal->sum_balances, 2) }}</h3>
        </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 {{ $flaggedFunds->isEmpty() ? 'border-success' : 'border-danger' }}">
        <div class="card-body py-3 px-4">
            <p class="text-muted text-uppercase mb-1" style="font-size:0.68rem; letter-spacing:.06em;">Needs Attention</p>
            <h3 class="mb-0 fw-bold {{ $flaggedFunds->isEmpty() ? 'text-success' : 'text-danger' }}">
            {{ $flaggedFunds->count() }} {{ Str::plural('fund', $flaggedFunds->count()) }}
            </h3>
        </div>
        </div>
    </div>
    </div>

    @if($flaggedFunds->isNotEmpty())
    <div class="row">
        <div class="col-12">
        <div class="alert alert-danger d-flex align-items-center py-2 px-3 mb-0" style="font-size:0.82rem;">
            <i class="fa fa-exclamation-triangle me-2"></i>
            <span>
            {{ $yearProgress }}% of the fiscal year has elapsed, but
            {{ $flaggedFunds->pluck('funding_source')->join(', ', ' and ') }}
            {{ $flaggedFunds->count() === 1 ? 'is' : 'are' }} significantly behind on obligations.
            </span>
        </div>
        </div>
    </div>
    @endif

    {{-- Detail tables --}}
    <div class="row mt-5">
      <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-header bg-white border-0 pt-3 pb-2 px-4">
            <h6 class="text-dark fw-bold mb-0" style="font-size:0.85rem;">Estimated Balances by Class</h6>
          </div>
          <div class="card-body p-4 pt-2">
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0" style="font-size:0.82rem;">
                <thead>
                  <tr class="text-muted text-uppercase" style="font-size:0.66rem; letter-spacing:.05em;">
                    <th class="border-bottom-0">Funding Source</th>
                    <th class="text-end border-bottom-0">CO</th>
                    <th class="text-end border-bottom-0">MOOE</th>
                    <th class="text-end border-bottom-0">Total</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($saebBalancesByClass as $row)
                    @php $isTotal = $row->funding_source === 'Grand Total'; @endphp
                    <tr class="{{ $isTotal ? 'fw-bold border-top' : '' }}">
                      <td class="{{ $isTotal ? 'text-dark' : 'text-secondary' }}">{{ $row->funding_source }}</td>
                      <td class="text-end text-secondary">{{ number_format($row->co, 2) }}</td>
                      <td class="text-end text-secondary">{{ number_format($row->mooe, 2) }}</td>
                      <td class="text-end {{ $isTotal ? 'text-dark' : 'text-secondary' }}">{{ number_format($row->grand_total, 2) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-header bg-white border-0 pt-3 pb-2 px-4">
            <h6 class="text-dark fw-bold mb-0" style="font-size:0.85rem;">Fund Summary</h6>
          </div>
          <div class="card-body p-4 pt-2">
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0" style="font-size:0.8rem;">
                <thead>
                  <tr class="text-muted text-uppercase" style="font-size:0.66rem; letter-spacing:.05em;">
                    <th class="border-bottom-0">Funding Source</th>
                    <th class="text-end border-bottom-0">Allotment</th>
                    <th class="text-end border-bottom-0">Obligated</th>
                    <th class="text-end border-bottom-0">Allocation Allotment</th>
                    <th class="text-end border-bottom-0">Balances</th>
                    <th class="text-end border-bottom-0">% Obl.</th>
                  </tr>
                </thead>
                <tbody>
                    @foreach($saebFundSummary as $row)
                    @php
                        $isTotal = $row->funding_source === 'Grand Total';
                        $isFlagged = ! $isTotal && $row->pct_obligated < ($yearProgress - 15);
                    @endphp
                    <tr class="{{ $isTotal ? 'fw-bold border-top' : '' }} {{ $isFlagged ? 'table-danger bg-opacity-10' : '' }}">
                        <td class="{{ $isTotal ? 'text-dark' : 'text-secondary' }}">
                        {{ $row->funding_source }}
                        @if($isFlagged)
                            <i class="fa fa-exclamation-triangle text-danger ms-1" data-bs-toggle="tooltip" title="Behind schedule for this point in the fiscal year"></i>
                        @endif
                        </td>
                        <td class="text-end text-secondary">{{ number_format($row->sum_allotment, 2) }}</td>
                        <td class="text-end text-secondary">{{ number_format($row->sum_obligated, 2) }}</td>
                        <td class="text-end text-secondary">{{ number_format($row->sum_aa, 2) }}</td>
                        <td class="text-end {{ $isTotal ? 'text-dark' : 'text-secondary' }}">{{ number_format($row->sum_balances, 2) }}</td>
                        <td class="text-end">
                        <span class="badge {{ $row->pct_obligated >= 80 ? 'bg-success' : ($row->pct_obligated >= 50 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                            {{ number_format($row->pct_obligated, 2) }}%
                        </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    @include('layouts.footers.auth.footer')
  </div>
@endsection
@extends('layouts.app')

@section('content')
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'SAEB'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row mt-4">
      <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="text-info mb-0">SAEB — Estimated Balances by Class</h6>
            <span class="badge bg-gradient-info">Live</span>
          </div>
          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0" style="font-size:0.82rem;">
                <thead>
                  <tr class="text-muted text-uppercase" style="font-size:0.68rem; letter-spacing:.04em;">
                    <th>Funding Source</th>
                    <th class="text-end">CO</th>
                    <th class="text-end">MOOE</th>
                    <th class="text-end">Total</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($saebBalancesByClass as $row)
                    @php $isTotal = $row->funding_source === 'Grand Total'; @endphp
                    <tr class="{{ $isTotal ? 'fw-bold border-top' : '' }}">
                      <td class="{{ $isTotal ? 'text-info' : '' }}">{{ $row->funding_source }}</td>
                      <td class="text-end">{{ number_format($row->co, 2) }}</td>
                      <td class="text-end">{{ number_format($row->mooe, 2) }}</td>
                      <td class="text-end {{ $isTotal ? 'text-info' : '' }}">{{ number_format($row->grand_total, 2) }}</td>
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
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="text-info mb-0">SAEB — Fund Summary</h6>
            <span class="badge bg-gradient-info">Live</span>
          </div>
          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0" style="font-size:0.8rem;">
                <thead>
                  <tr class="text-muted text-uppercase" style="font-size:0.66rem; letter-spacing:.04em;">
                    <th>Funding Source</th>
                    <th class="text-end">Allotment</th>
                    <th class="text-end">Obligated</th>
                    <th class="text-end">Balances</th>
                    <th class="text-end">% Obl.</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($saebFundSummary as $row)
                    @php $isTotal = $row->funding_source === 'Grand Total'; @endphp
                    <tr class="{{ $isTotal ? 'fw-bold border-top' : '' }}">
                      <td class="{{ $isTotal ? 'text-info' : '' }}">{{ $row->funding_source }}</td>
                      <td class="text-end">{{ number_format($row->sum_allotment, 2) }}</td>
                      <td class="text-end">{{ number_format($row->sum_obligated, 2) }}</td>
                      <td class="text-end {{ $isTotal ? 'text-info' : '' }}">{{ number_format($row->sum_balances, 2) }}</td>
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

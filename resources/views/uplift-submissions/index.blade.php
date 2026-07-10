@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <style>
    .uplift-submissions-page,
    .uplift-submissions-page .card,
    .uplift-submissions-page .card-body,
    .uplift-submissions-page .table-responsive {
      min-width: 0;
    }

    .uplift-form-card {
      min-width: 0;
      overflow-wrap: anywhere;
      word-break: break-word;
    }

    .uplift-form-card .badge {
      max-width: 100%;
      white-space: normal;
      text-align: left;
      line-height: 1.35;
    }

    #datatable-uplift-submissions {
      table-layout: fixed;
    }

    #datatable-uplift-submissions th,
    #datatable-uplift-submissions td {
      white-space: normal;
      overflow-wrap: anywhere;
      word-break: break-word;
      vertical-align: top;
    }

    #datatable-uplift-submissions th.text-center,
    #datatable-uplift-submissions td.text-center {
      vertical-align: middle;
    }

    #datatable-uplift-submissions .uplift-actions-cell {
      white-space: nowrap;
      overflow-wrap: normal;
      word-break: normal;
    }
  </style>

  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'UPLIFT Submissions'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid uplift-submissions-page">
    @if(session('error'))
      <div class="row mt-4">
        <div class="col-12">
          <div class="alert alert-danger text-white">{{ session('error') }}</div>
        </div>
      </div>
    @endif

    @can('create', App\Models\UpliftSubmission::class)
      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h5 class="mb-0">Available UPLIFT Forms</h5>
              <p class="text-sm mb-0">Only measures where your agency is lead or supporting are shown.</p>
            </div>
            <div class="card-body p-3">
              <div class="row">
                @forelse($measures as $measure)
                  <div class="col-lg-4 mb-3">
                    <div class="border rounded p-3 h-100 uplift-form-card">
                      <span class="badge bg-light text-dark mb-2">{{ optional($measure->pillar)->title }}</span>
                      <h6 class="mb-1">{{ $measure->title }}</h6>
                      <p class="text-xs text-muted mb-3">{{ optional($measure->leadAgency)->display_name }}</p>
                      <a href="{{ route('uplift-submissions.create', ['measure_id' => $measure->id]) }}" class="btn btn-primary btn-sm mb-0">
                        <i class="fa fa-plus me-1"></i> New Report
                      </a>
                    </div>
                  </div>
                @empty
                  <div class="col-12">
                    <p class="text-sm mb-0">No active UPLIFT form is assigned to your agency.</p>
                  </div>
                @endforelse
              </div>
            </div>
          </div>
        </div>
      </div>
    @endcan

    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header pb-0">
            <div class="d-flex align-items-center">
              <div>
                <h5 class="mb-0">UPLIFT Data</h5>
                <p class="text-sm mb-0">{{ $canViewAll ? 'All agency reports are visible to administrators.' : 'Your agency reports are listed here.' }}</p>
              </div>
              <div class="ms-auto">
                <span class="badge bg-info">{{ $submissions->count() }} total</span>
              </div>
            </div>
          </div>
          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-uplift-submissions" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Pillar</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Measure</th>
                    @if($canViewAll)
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Agency</th>
                    @endif
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Week Ending</th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Status</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Prepared By</th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Submitted At</th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($submissions as $submission)
                    <tr>
                      <td class="text-sm2 uplift-wrap-cell">{{ optional(optional($submission->measure)->pillar)->title }}</td>
                      <td class="text-sm2 uplift-wrap-cell">{{ optional($submission->measure)->title }}</td>
                      @if($canViewAll)
                        <td class="text-sm2 uplift-wrap-cell">{{ optional($submission->agency)->display_name }}</td>
                      @endif
                      <td class="text-sm2 text-center">{{ $submission->reporting_cutoff_date?->format('Y-m-d') }}</td>
                      <td class="text-sm2 text-center">
                        @php
                          $statusColor = match($submission->status) {
                            'approved' => 'success',
                            'submitted' => 'info',
                            'returned' => 'warning',
                            'rejected' => 'danger',
                            default => 'secondary',
                          };
                        @endphp
                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($submission->status) }}</span>
                      </td>
                      <td class="text-sm2 uplift-wrap-cell">{{ optional($submission->user)->full_name }}</td>
                      <td class="text-sm2 text-center">{{ $submission->submitted_at?->format('Y-m-d H:i:s') }}</td>
                      <td class="text-sm2 text-center uplift-actions-cell">
                        <a href="{{ route('uplift-submissions.show', $submission) }}" data-bs-toggle="tooltip" data-bs-original-title="View" class="border-0 bg-transparent px-1">
                          <i class="fa fa-eye text-secondary"></i>
                        </a>
                        @can('update', $submission)
                          <a href="{{ route('uplift-submissions.edit', $submission) }}" data-bs-toggle="tooltip" data-bs-original-title="Edit" class="border-0 bg-transparent px-1">
                            <i class="fa fa-pencil text-info"></i>
                          </a>
                        @endcan
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

@push('js')
  <script>
    $(document).ready(function() {
      let dtName = 'datatable-uplift-submissions';
      let disabledColumns = [{{ $canViewAll ? 7 : 6 }}];
      let centerColumns = [{{ $canViewAll ? '3, 4, 6' : '2, 3, 5' }}];

      createColumnSearch(dtName, disabledColumns, centerColumns);
      let table = $('#' + dtName).DataTable({
        stateSave: true,
        searchDelay: 500,
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        order: [[{{ $canViewAll ? 3 : 2 }}, 'desc']],
        columnDefs: [
          { targets: disabledColumns, orderable: false, searchable: false },
        ],
        language: getLanguageConfig('UPLIFT Submissions'),
        initComplete : function(settings, json){
          setupInitComplete(this.api(), dtName, {{ $canViewAll ? 3 : 2 }}, 'desc');
        }
      });
      setupKeyUpColumnSearch(table, dtName);
      refreshToolTip();
    });
  </script>
@endpush

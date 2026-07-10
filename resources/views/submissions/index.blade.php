@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Requests'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <h5 class="mb-0">Submissions</h5>
            </div>
            @can('create', App\Models\FormSubmission::class)
            @if($form != null)
              <div class="text-end ms-auto">
                <a href="{{ route('submissions.create') }}" class="btn btn-xs btn-dark mb-0">
                  <i class="fa fa-plus pe-2"></i> Submission
                </a>
              </div>
            @endif
            @endcan
          </div>
          <div class="card-body p-3">
            @if(session('error'))
              <div class="alert alert-danger text-white">{{ session('error') }}</div>
            @endif
            @if($weeklySubmissionLocked)
              <div class="alert alert-info text-white">
                A report has already been submitted this week. Submissions will reopen next Monday.
              </div>
            @endif

            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-submissions" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Form</th>
                    @if(auth()->user()->isSuperAdmin())
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Agency</th>
                    @endif
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Week Ending</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Status</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Prepared By</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Submitted At</th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($submissions as $submission)
                    <tr>
                      <td class="text-sm2">{{ optional($submission->form)->title }}</td>
                      @if(auth()->user()->isSuperAdmin())
                        <td class="text-sm2">{{ optional($submission->agency)->display_name }}</td>
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
                      <td class="text-sm2">{{ optional($submission->user)->full_name }}</td>
                      <td class="text-sm2 text-center">{{ $submission->submitted_at?->format('Y-m-d H:i:s') }}</td>
                      <td class="text-sm2 text-center">
                        <div class="btn-group" role="group">
                          <a href="{{ route('submissions.show', $submission) }}" data-bs-toggle="tooltip" data-bs-original-title="View" class="border-0 bg-transparent px-1">
                            <i class="fa fa-eye text-secondary"></i>
                          </a>
                          @can('update', $submission)
                            <a href="{{ route('submissions.edit', $submission) }}" data-bs-toggle="tooltip" data-bs-original-title="Edit" class="border-0 bg-transparent px-1">
                              <i class="fa fa-pencil text-info"></i>
                            </a>
                          @endcan
                        </div>
                      </td>
                    </tr>
                  @empty
                  @endforelse
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
      let dtName = 'datatable-submissions';
      let disabledColumns = [{{ auth()->user()->isSuperAdmin() ? 6 : 5 }}];
      let centerColumns = [{{ auth()->user()->isSuperAdmin() ? '2, 3, 5' : '1, 2, 4' }}];

      createColumnSearch(dtName, disabledColumns, centerColumns);
      let table = $('#' + dtName).DataTable({
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data);
        },
        searchDelay: 500,
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        order: [[{{ auth()->user()->isSuperAdmin() ? 2 : 1 }}, 'desc']],
        columnDefs: [
          { targets: disabledColumns, orderable: false, searchable: false },
        ],
        language: getLanguageConfig('Submissions'),
        initComplete : function(settings, json){
          setupInitComplete(this.api(), dtName, {{ auth()->user()->isSuperAdmin() ? 2 : 1 }}, 'desc');
        }
      });
      setupKeyUpColumnSearch(table, dtName);
      refreshToolTip();
    });

    $('#datatable-submissions').on('draw.dt', function() {
      refreshToolTip();
    });
  </script>
@endpush

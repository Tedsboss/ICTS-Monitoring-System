@props([
  'submission',
  'approveRoute',
  'returnRoute',
  'rejectRoute',
])

@php
  $approvalComponentId = 'submission-approval-' . $submission->getTable() . '-' . $submission->id;
  $approvalHistories = $submission->approvalHistories()->with('user')->get();
  $canApproveSubmission = auth()->user()?->can('approve', $submission);
  $actions = [
    'approve' => [
      'route' => $approveRoute,
      'label' => 'Approve',
      'icon' => 'fa-check',
      'class' => 'is-approve',
      'title' => 'Approve Submission',
      'placeholder' => 'Approval remarks',
    ],
    'return' => [
      'route' => $returnRoute,
      'label' => 'Return',
      'icon' => 'fa-undo',
      'class' => 'is-return',
      'title' => 'Return Submission',
      'placeholder' => 'Return remarks',
    ],
    'reject' => [
      'route' => $rejectRoute,
      'label' => 'Reject',
      'icon' => 'fa-times',
      'class' => 'is-reject',
      'title' => 'Reject Submission',
      'placeholder' => 'Reject remarks',
    ],
  ];
@endphp

  <style>
    .submission-approval-floating {
      position: fixed;
      top: 50%;
      right: 22px;
      transform: translateY(-50%);
      z-index: 1040;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 12px;
      pointer-events: none;
    }

    .submission-approval-action {
      display: inline-flex;
      align-items: center;
      justify-content: flex-start;
      gap: 10px;
      width: 52px;
      min-height: 52px;
      padding: 0 16px;
      overflow: hidden;
      border: 1px solid transparent;
      border-radius: 999px;
      color: #fff;
      font-size: .78rem;
      font-weight: 800;
      line-height: 1;
      white-space: nowrap;
      box-shadow: 0 12px 28px rgba(17, 24, 39, .18);
      transition: width .18s ease, background .18s ease, transform .18s ease;
      pointer-events: auto;
    }

    .submission-approval-action i {
      width: 18px;
      min-width: 18px;
      font-size: 1rem;
      text-align: center;
    }

    .submission-approval-action span {
      opacity: 0;
      transition: opacity .12s ease;
    }

    .submission-approval-action:hover,
    .submission-approval-action:focus {
      width: 154px;
      color: #fff;
      transform: translateX(-3px);
    }

    .submission-approval-action:hover span,
    .submission-approval-action:focus span {
      opacity: 1;
    }

    .submission-approval-action.is-approve {
      background: #2dce89;
      box-shadow: 0 12px 28px rgba(45, 206, 137, .28);
    }

    .submission-approval-action.is-approve:hover {
      background: #24a46d;
    }

    .submission-approval-action.is-return {
      background: #fb8c00;
      box-shadow: 0 12px 28px rgba(251, 140, 0, .25);
    }

    .submission-approval-action.is-return:hover {
      background: #d97900;
    }

    .submission-approval-action.is-reject {
      background: #f5365c;
      box-shadow: 0 12px 28px rgba(245, 54, 92, .25);
    }

    .submission-approval-action.is-reject:hover {
      background: #d9254b;
    }

    .submission-approval-action.is-history {
      background: #344767;
      box-shadow: 0 12px 28px rgba(52, 71, 103, .25);
    }

    .submission-approval-action.is-history:hover {
      background: #233044;
    }

    .submission-history-table td {
      vertical-align: top;
    }

    .submission-history-remarks-cell {
      max-width: 460px;
      white-space: pre-wrap;
    }

    .submission-history-date-filter {
      max-width: 190px;
    }

    @media (max-width: 767.98px) {
      .submission-approval-floating {
        top: auto;
        right: 14px;
        bottom: 18px;
        transform: none;
      }

      .submission-approval-action,
      .submission-approval-action:hover,
      .submission-approval-action:focus {
        width: 154px;
        transform: none;
      }

      .submission-approval-action span {
        opacity: 1;
      }
    }
  </style>

  <div class="submission-approval-floating" aria-label="Submission actions">
    @if($canApproveSubmission)
      @foreach($actions as $actionKey => $action)
        <button
          type="button"
          class="submission-approval-action {{ $action['class'] }}"
          data-bs-toggle="modal"
          data-bs-target="#{{ $approvalComponentId }}-{{ $actionKey }}"
        >
          <i class="fa {{ $action['icon'] }}"></i>
          <span>{{ $action['label'] }}</span>
        </button>
      @endforeach
    @endif

    <button
      type="button"
      class="submission-approval-action is-history"
      data-bs-toggle="modal"
      data-bs-target="#{{ $approvalComponentId }}-history"
    >
      <i class="fa fa-history"></i>
      <span>History</span>
    </button>
  </div>

  <div class="modal fade" id="{{ $approvalComponentId }}-history" tabindex="-1" aria-labelledby="{{ $approvalComponentId }}-history-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="{{ $approvalComponentId }}-history-label">Approval History</h5>
            <p class="text-xs text-secondary mb-0">Search, sort, and filter approval activity.</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mb-3">
            <div class="text-sm text-secondary">
              {{ $approvalHistories->count() }} {{ Str::plural('entry', $approvalHistories->count()) }}
            </div>
            <input type="date" class="form-control form-control-sm submission-history-date-filter" id="{{ $approvalComponentId }}-history-date" aria-label="Filter history by date">
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-hover submission-history-table" id="{{ $approvalComponentId }}-history-table" width="100%">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Action</th>
                  <th>User</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>
                @foreach($approvalHistories as $history)
                  @php
                    $badgeColor = match($history->action) {
                      'approved' => 'success',
                      'returned' => 'warning',
                      'rejected' => 'danger',
                      default => 'secondary',
                    };
                  @endphp
                  <tr>
                    <td data-order="{{ optional($history->created_at)->timestamp }}">
                      {{ optional($history->created_at)->format('Y-m-d H:i:s') }}
                    </td>
                    <td>
                      <span class="badge bg-{{ $badgeColor }}">{{ ucfirst($history->action) }}</span>
                    </td>
                    <td>{{ optional($history->user)->full_name ?? 'System' }}</td>
                    <td class="submission-history-remarks-cell">{{ $history->remarks }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light btn-sm mb-0" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tableId = @json($approvalComponentId . '-history-table');
      const dateInputId = @json($approvalComponentId . '-history-date');
      const tableElement = document.getElementById(tableId);

      if (!tableElement || typeof $ === 'undefined' || !$.fn.DataTable) {
        return;
      }

      const table = $('#' + tableId).DataTable({
        stateSave: true,
        searchDelay: 300,
        pagingType: "full_numbers",
        lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "All"]],
        order: [[0, 'desc']],
        language: typeof getLanguageConfig === 'function' ? getLanguageConfig('Approval History') : {},
      });

      const dateInput = document.getElementById(dateInputId);

      if (dateInput) {
        dateInput.addEventListener('change', function() {
          table.column(0).search(dateInput.value || '').draw();
        });
      }
    });
  </script>

  @if($canApproveSubmission)
    @foreach($actions as $actionKey => $action)
      <div class="modal fade" id="{{ $approvalComponentId }}-{{ $actionKey }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form method="post" action="{{ $action['route'] }}">
              @csrf
              <div class="modal-header">
                <div>
                  <h6 class="modal-title">{{ $action['title'] }}</h6>
                  <p class="text-xs text-secondary mb-0">Remarks are required and will be saved in history.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                <textarea
                  name="approval_remarks"
                  class="form-control"
                  rows="4"
                  placeholder="{{ $action['placeholder'] }}"
                  required
                ></textarea>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm mb-0" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm mb-0 {{ $actionKey === 'approve' ? 'btn-success' : ($actionKey === 'return' ? 'btn-warning' : 'btn-danger') }}" type="submit">
                  <i class="fa {{ $action['icon'] }} me-1"></i>
                  {{ $action['label'] }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    @endforeach
  @endif

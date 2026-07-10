{{-- <form method="POST" action="{{ route('reverttodraft', $fund->id) }}" enctype="multipart/form-data" id="frmRevertToDraft"> --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="frmRevertToDraft">
  @csrf
  @method('put')
  <div class="fixed-plugin align-middle" id="divRevertDraft" onclick="ocRevertToDraft()" >
    <a data-bs-toggle="tooltip" style="background-color: #b9b7b5 !important; bottom: 10vh !important;" class="fixed-plugin-button text-bold text-xs text-center font-weight-bolder position-fixed p-2 fixed-plugin-btn opacity-7 align-middle fixed-plugin-btn-custom">
      <div class="row text-center align-middle btn-text-align-center m-0">
        <div class="col-md-3 text-center align-middle btn-text-align-center p-0">
          <i class="fa fa-undo fa-2x"></i>
        </div>
        <div class="col-md-9 fixed-plugin-btn-custom-span text-center align-middle btn-text-align-center p-0 pt-1">
          <span>REVERT TO DRAFT</span>
        </div>
      </div>
    </a>
  </div>
</form>

@push('js')
  <script>
    function ocRevertToDraft() {
      if (confirm('Are you sure you want to revert this {{ $type }} to draft?') == true) {
        $('#frmRevertToDraft').submit();
      }
    }
  </script>
@endpush
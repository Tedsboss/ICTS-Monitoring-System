<div class="fixed-plugin align-middle" id="divSaveFinal" onclick="ocSaveAsFinal()" >
  <a data-bs-toggle="tooltip" style="background-color: #ad53ef !important; bottom: 20vh !important;" class="fixed-plugin-button text-bold text-xs text-center font-weight-bolder position-fixed p-2 fixed-plugin-btn opacity-7 align-middle fixed-plugin-btn-custom">
    <div class="row text-center align-middle btn-text-align-center m-0">
      <div class="col-md-3 text-center align-middle btn-text-align-center p-0">
        <i class="fa fa-floppy-o fa-2x"></i>
      </div>
      <div class="col-md-9 fixed-plugin-btn-custom-span text-center align-middle btn-text-align-center p-0 pt-1">
        <span>SUBMIT</span>
      </div>
    </div>
  </a>
</div>

@push('js')
  <script>
    function ocSaveAsFinal() {
      $("#save").val('endorsed');
      if (confirm('Are you sure you want to submit this {{ $type }}?') == true) {
        $("#btnSave").attr("hidden", true);
        $("#btnCancel").attr("disabled", true);
        $("#btnSaveDisabled").attr("hidden", false);
        $("#frmUpdate").submit();
      }
    }
  </script>
@endpush
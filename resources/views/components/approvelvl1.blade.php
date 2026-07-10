{{-- <form method="POST" action="{{ route('approvelvl1', $fund->id) }}" enctype="multipart/form-data" id="frmApproveLvl1"> --}}
{{-- <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="frmApproveLvl1">
  @csrf
  @method('put') --}}
  <div class="fixed-plugin align-middle" id="divApproveLvl1" onclick="ocApproveLvl1()" >
    <a data-bs-toggle="tooltip" style="background-color: #53b1ef !important; bottom: 20vh !important;" class="fixed-plugin-button text-bold text-xs text-center font-weight-bolder position-fixed p-2 fixed-plugin-btn opacity-7 align-middlee fixed-plugin-btn-custom">
      <div class="row text-center align-middle btn-text-align-center m-0">
        <div class="col-md-3 text-center align-middle btn-text-align-center p-0">
          <i class="fa fa-check-square-o fa-2x"></i>
        </div>
        <div class="col-md-9 fixed-plugin-btn-custom-span text-center align-middle btn-text-align-center p-0 pt-1">
          <span>ENDORSE</span>
        </div>
      </div>
    </a>
  </div>
{{-- </form> --}}

@push('js')
  <script>
    function ocApproveLvl1() {
      if (confirm('Are you sure you want to endorse this {{ $type }}?') == true) {
        // $('#frmApproveLvl1').submit();
        $('#save').val('endorsed');
        $('#frmUpdate').attr('action', '{{ $action }}');
        $('#frmUpdate').submit();
      }
    }
  </script>
@endpush
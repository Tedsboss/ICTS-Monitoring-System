<div class="position-fixed top-2 end-2 z-index-sub-max">
  <div class="toast fade hide p-2 mt-2 bg-gradient-info" role="alert" aria-live="assertive" id="infoToast" aria-atomic="true" data-bs-delay="10000">
    <div class="toast-header bg-transparent border-0">
      {{-- <i class="ni ni-bell-55 text-white me-2"></i> --}}
      <i class="fa fa-info-circle fa-2x text-white me-3"></i>
      <span class="me-auto text-white font-weight-bold" id="infoToastTitle" style="font-size: 1.2rem;">For Information</span>
      {{-- <small class="text-white">11 mins ago</small> --}}
      <i class="material-icons text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close" style="font-size: 18px;">close</i>
    </div>
    <hr class="horizontal light m-0">
    <div class="toast-body text-white">
      <span id="infoToastMessage"></span>
    </div>
  </div>
  <div class="toast fade hide p-2 mt-2 bg-gradient-success" role="alert" aria-live="assertive" id="successToast" aria-atomic="true" data-bs-delay="10000">
    <div class="toast-header bg-transparent border-0">
      {{-- <i class="ni ni-bell-55 text-white me-2"></i> --}}
      <i class="fa fa-check fa-2x text-white me-3"></i>
      <span class="me-auto text-white font-weight-bold" id="successToastTitle" style="font-size: 1.2rem;">Success</span>
      {{-- <small class="text-white">11 mins ago</small> --}}
      <i class="material-icons text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close" style="font-size: 18px;">close</i>
    </div>
    <hr class="horizontal light m-0">
    <div class="toast-body text-white">
      <span id="successToastMessage"></span>
    </div>
  </div>
  <div class="toast fade hide p-2 mt-2 bg-gradient-warning" role="alert" aria-live="assertive" id="warningToast" aria-atomic="true" data-bs-delay="10000">
    <div class="toast-header bg-transparent border-0">
      {{-- <i class="ni ni-bell-55 text-white me-2"></i> --}}
      <i class="fa fa-exclamation-triangle fa-2x text-white me-3"></i>
      <span class="me-auto text-white font-weight-bold" id="warningToastTitle" style="font-size: 1.2rem;">Warning</span>
      {{-- <small class="text-white">11 mins ago</small> --}}
      <i class="material-icons text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close" style="font-size: 18px;">close</i>
    </div>
    <hr class="horizontal light m-0">
    <div class="toast-body text-white">
      <span id="warningToastMessage"></span>
    </div>
  </div>
  <div class="toast fade hide p-2 mt-2 bg-gradient-danger" role="alert" aria-live="assertive" id="dangerToast" aria-atomic="true" data-bs-delay="10000">
    <div class="toast-header bg-transparent border-0">
      {{-- <i class="ni ni-bell-55 text-white me-2"></i> --}}
      <i class="fa fa-times-circle-o fa-2x text-white me-3"></i>
      <span class="me-auto text-white font-weight-bold" id="dangerToastTitle" style="font-size: 1.2rem;">Error</span>
      {{-- <small class="text-white">11 mins ago</small> --}}
      <i class="material-icons text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close" style="font-size: 18px;">close</i>
    </div>
    <hr class="horizontal light m-0">
    <div class="toast-body text-white">
      <span id="dangerToastMessage"></span>
    </div>
  </div>
</div>
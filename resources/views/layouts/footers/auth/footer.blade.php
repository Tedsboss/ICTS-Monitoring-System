<footer class="footer pt-3  ">
  <div class="container-fluid">
    <div class="row align-items-center justify-content-lg-between">
      <div class="col-lg-6 mb-lg-0 mb-4">
        <div class="copyright text-center text-sm text-muted text-lg-start">
          {{-- ©
          <script>
              document.write(new Date().getFullYear())
          </script>,
          made with <i class="fa fa-heart"></i> by
          <a href="https://www.creative-tim.com" class="font-weight-bold" target="_blank">Creative Tim</a>
          &
          <a href="https://www.updivision.com" class="font-weight-bold" target="_blank">UPDIVISION</a>
          for a better web. --}}

          Copyright ©
          <script>
              document.write(new Date().getFullYear())
          </script>
          <a href="https://depdev.gov.ph/" class="font-weight-bold" target="_blank">Information and Communications Technology Staff</a>
        </div>
      </div>
      <div class="col-lg-6">
        <ul class="nav nav-footer justify-content-center justify-content-lg-end">
          {{-- <li class="nav-item">
            <a href="https://www.updivision.com" class="nav-link text-muted" target="_blank">UPDIVISION</a>
          </li>
          <li class="nav-item">
            <a href="https://www.creative-tim.com" class="nav-link text-muted" target="_blank">Creative Tim</a>
          </li>
          <li class="nav-item">
            <a href="https://www.creative-tim.com/presentation" class="nav-link text-muted" target="_blank">About Us</a>
          </li> --}}
          <li class="nav-item">
            <div class="row">
              <img class="ml-auto" width="200" height="90" src="{{ $logo ?? '/assets/img/neda/bagongpilipinas.png'}}" alt="..." >
            </div>
          </li>
          <li class="nav-item">
            <div class="row pt-1 ps-3">
              {{-- <img class="ml-auto" width="200" height="90" src="{{ $logo ?? '/assets/img/neda/SOCOTEC-PAB-Logo.png'}}" alt="..." > --}}
              <img class="ml-auto" width="200" height="85" src="{{ $logo ?? '/assets/img/neda/logo.png'}}" alt="..." >
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</footer>

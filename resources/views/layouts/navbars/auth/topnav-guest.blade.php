<!-- Navbar -->
@php
  $icc_telephone = App\Models\Parameter::where('id', 27)->first()->value;
  $icc_email = App\Models\Parameter::where('id', 28)->first()->value;
  $icc_open = DateTime::createFromFormat('H:i', App\Models\Parameter::where('id', 23)->first()->value)->format('g:i A');
  $icc_close = DateTime::createFromFormat('H:i', App\Models\Parameter::where('id', 24)->first()->value)->format('g:i A');
  $icc_address = App\Models\Parameter::where('id', 29)->first()->value;
@endphp

<nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
  <div class="container px-0">
    <a class="navbar-brand font-weight-bolder ms-lg-0 me-0" target="_blank" href="https://depdev.gov.ph/">
      {{ $title }}
    </a>
    <button class="navbar-toggler shadow-none ms-2 text-center justify-content-center align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon mt-2">
        <span class="navbar-toggler-bar bg-light bar1"></span>
        <span class="navbar-toggler-bar bg-light bar2"></span>
        <span class="navbar-toggler-bar bg-light bar3"></span>
      </span>
    </button>
    <div class="collapse navbar-collapse w-100 py-3 py-lg-0 justify-content-end" id="navigation">
      <ul class="navbar-nav navbar-nav-hover">
        {{-- <li class="nav-item dropdown dropdown-hover mx-2">
          <a href="{{ route('register') }}" data-bs-toggle="tooltip" data-bs-original-title="Register" class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center @if(request()->is('register')) font-weight-bolder @endif" aria-expanded="false">
            Register
          </a>
        </li> --}}
        @guest
          <li class="nav-item dropdown dropdown-hover mx-2">
            <a href="{{ route('login') }}" data-bs-placement="bottom" data-bs-toggle="tooltip" data-bs-original-title="Login" class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center text-shadow-1 @if(request()->is('login')) font-weight-bolder @endif" aria-expanded="false">
              Login
            </a>
          </li>
          <li class="nav-item dropdown dropdown-hover mx-2">
            <a href="{{ route('reset') }}" data-bs-placement="bottom" data-bs-toggle="tooltip" data-bs-original-title="Reset Password" class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center text-shadow-1 @if(request()->is('reset-password')) font-weight-bolder @endif" aria-expanded="false">
              Reset Password
            </a>
          </li>
        @else
          <li class="nav-item dropdown dropdown-hover mx-2">
            <a href="{{ route('home') }}" data-bs-placement="bottom" data-bs-toggle="tooltip" data-bs-original-title="Home Page" class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center text-shadow-1" aria-expanded="false">
              Home
            </a>
          </li>
          <li class="nav-item dropdown dropdown-hover mx-2">
            <form role="form" method="post" action="{{ route('logout') }}" id="logout-form">
              @csrf
              <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" data-bs-toggle="tooltip" data-bs-original-title="Log out" class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center" aria-expanded="false">
                Log out
              </a>
            </form>
          </li>
        @endguest
        <li class="nav-item dropdown dropdown-hover mx-2">
          <a role="button" data-bs-placement="bottom" class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center text-shadow-1 @if(request()->is('contact-us')) font-weight-bolder @endif" id="dropdownMenuEcommerce" data-bs-toggle="dropdown" aria-expanded="false" data-bs-original-title="Contact Us">
            Contact Us
            <img src="/assets/img/down-arrow-white.svg" alt="down-arrow" class="arrow ms-1 d-lg-block d-none">
            <img src="/assets/img/down-arrow-dark.svg" alt="down-arrow" class="arrow ms-1 d-lg-none d-block">
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-animation dropdown-lg p-3 border-radius-xl mt-0 mt-lg-3 shadow-sm" aria-labelledby="dropdownMenuEcommerce">
            <div class="row d-none d-lg-block">
              <div class="col-12 px-4 py-2">
                <div class="row">
                  <div class="col-12 position-relative">
                    <div class="dropdown-header text-dark font-weight-bolder d-flex align-items-center px-0">
                      <i class="fa fa-commenting fa-lg text-info me-3"></i>
                      Message
                    </div>
                    <ul class="text-muted ps-5 mb-0">
                      <li>
                        <span class="text-sm">Have a question? <a href="@guest {{ route('guest.contactus.create') }} @else {{ route('auth.contactus.create') }} @endguest" class="text-primary text-gradient font-weight-bold">Message us here</a></span>
                      </li>
                    </ul>
                  </div>
                  <hr class="horizontal dark my-2">
                  <div class="col-12 position-relative">
                    <div class="dropdown-header text-dark font-weight-bolder d-flex align-items-center px-0 pt-0">
                      <i class="fa fa-phone fa-lg text-info me-3"></i>
                      Phone
                    </div>
                    <ul class="text-muted ps-5 mb-0">
                      <li>
                        <span class="text-sm">{{ $icc_telephone }}</span>
                      </li>
                    </ul>
                  </div>
                  <hr class="horizontal dark my-2">
                  <div class="col-12 position-relative">
                    <div class="dropdown-header text-dark font-weight-bolder d-flex align-items-center px-0 pt-0">
                      <i class="fa fa-envelope fa-lg text-info me-3"></i>
                      Email
                    </div>
                    <ul class="text-muted ps-5 mb-0">
                      <li>
                        <span class="text-sm"><a href="mailto:{{ $icc_email }}" target="_blank" rel="noopener noreferrer">{{ $icc_email }}</a></span>
                      </li>
                    </ul>
                  </div>
                  <hr class="horizontal dark my-2">
                  <div class="col-12 position-relative">
                    <div class="dropdown-header text-dark font-weight-bolder d-flex align-items-center px-0 pt-0">
                      <i class="fa fa-clock-o fa-lg text-info me-3"></i>
                      Hours
                    </div>
                    <ul class="text-muted ps-5 mb-0">
                      <li>
                        <span class="text-sm">Monday to Friday {{ $icc_open . ' - ' . $icc_close }}</span>
                      </li>
                    </ul>
                  </div>
                  <hr class="horizontal dark my-2">
                  <div class="col-12 position-relative">
                    <div class="dropdown-header text-dark font-weight-bolder d-flex align-items-center px-0 pt-0">
                      <i class="fa fa-map-marker fa-lg text-info me-3"></i>
                      Location
                    </div>
                    <ul class="text-muted ps-5 mb-0">
                      <li>
                        <span class="text-sm">{{ $icc_address }}</span>
                      </li>
                    </ul>
                  </div>
                  {{-- <div class="col-12 text-center">
                    <button class="btn btn-primary mt-4 mb-2">Send Message</button>
                  </div> --}}
                </div>
              </div>
            </div>

            <!-- responsive -->
            <div class="d-lg-none">
              <div class="dropdown-header text-dark font-weight-bolder d-flex align-items-center px-0">
                <i class="fa fa-envelope-o fa-lg text-info me-3"></i>
                Message
              </div>
              <ul class="text-muted ps-5 mb-0">
                <li>
                  <span class="text-sm">Have a question? <a href="{{ route('guest.contactus.create') }}" class="text-primary text-gradient font-weight-bold">Message us here</a></span>
                </li>
              </ul>
              <hr class="horizontal dark my-2">
              <div class="dropdown-header text-dark font-weight-bolder d-flex align-items-center px-0">
                <i class="fa fa-phone fa-lg text-info me-3"></i>
                Phone
              </div>
              <ul class="text-muted ps-5 mb-0">
                <li>
                  <span class="text-sm">{{ $icc_telephone }}</span>
                </li>
              </ul>
              <hr class="horizontal dark my-2">
              <div class="dropdown-header text-dark font-weight-bolder d-flex align-items-center px-0">
                <i class="fa fa-phone fa-lg text-info me-3"></i>
                Email
              </div>
              <ul class="text-muted ps-5 mb-0">
                <li>
                  <span class="text-sm">{{ $icc_email }}</span>
                </li>
              </ul>
              <hr class="horizontal dark my-2">
              <div class="dropdown-header text-dark font-weight-bolder d-flex align-items-center px-0">
                <i class="fa fa-clock-o fa-lg text-info me-3"></i>
                Hours
              </div>
              <ul class="text-muted ps-5 mb-0">
                <li>
                  <span class="text-sm">Monday to Friday 8:00 AM - 5:00 PM</span>
                </li>
              </ul>
              <hr class="horizontal dark my-2">

              <div class="dropdown-header text-dark font-weight-bolder d-flex align-items-center px-0">
                <i class="fa fa-map-marker fa-lg text-info me-3"></i>
                Location
              </div>
              <ul class="text-muted ps-5 mb-0">
                <li>
                  <span class="text-sm">34th Floor, SM Mega Tower, Ortigas Center, Mandaluyong City 1550</span>
                </li>
              </ul>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- End Navbar -->

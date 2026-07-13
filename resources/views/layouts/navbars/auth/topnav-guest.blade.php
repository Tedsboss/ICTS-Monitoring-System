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
      </ul>
    </div>
  </div>
</nav>
<!-- End Navbar -->

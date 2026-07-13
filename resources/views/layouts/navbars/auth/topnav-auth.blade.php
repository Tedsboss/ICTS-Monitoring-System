<!-- Navbar -->
<nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
  <div class="container ps-2 pe-0">
    <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 text-white" target="_blank" href="https://depdev.gov.ph/">
      {{ $title }}
    </a>
    <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon mt-2">
        <span class="navbar-toggler-bar bar1"></span>
        <span class="navbar-toggler-bar bar2"></span>
        <span class="navbar-toggler-bar bar3"></span>
      </span>
    </button>
    <div class="collapse navbar-collapse w-100 py-3 py-lg-0 justify-content-end" id="navigation">
      <ul class="navbar-nav navbar-nav-hover">
        <li class="nav-item dropdown dropdown-hover mx-2">
          <form role="form" method="post" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" data-bs-toggle="tooltip" data-bs-original-title="Log out" class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center @if(request()->is('login')) font-weight-bolder @endif  text-shadow-1" aria-expanded="false">
              Log out
            </a>
          </form>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- End Navbar -->

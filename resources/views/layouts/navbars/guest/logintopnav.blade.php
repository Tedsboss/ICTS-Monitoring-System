<!-- Navbar -->
<nav class="navbar navbar-expand-lg position-absolute {{ $classes }}">
    <div class="{{ $container ?? 'container-fluid'}} ps-2 pe-0">
        
        <div class="collapse navbar-collapse w-100 pt-3 pb-2 py-lg-0" id="navigation">
            <ul class="navbar-nav navbar-nav-hover ms-auto">
                <li class="nav-item dropdown dropdown-hover ms-2">
                    <a href="{{ route('register') }}"
                        class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center">
                        Contact Us
                    </a>
                </li>
                <li class="nav-item dropdown dropdown-hover ms-2">
                    <a href="{{ route('register') }}"
                        class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center">
                        Register
                    </a>
                </li>
                <li class="nav-item dropdown dropdown-hover ms-2">
                    <a href="{{ route('login') }}"
                        class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center">
                        Login
                    </a>
                </li>
            </ul>
            {{-- <ul class="navbar-nav d-lg-block d-none">
                <li class="nav-item">
                    <a href="https://www.creative-tim.com/product/argon-dashboard-pro-laravel"
                        class="btn btn-sm  btn-primary  mb-0 me-1" target="_blank"
                        onclick="smoothToPricing('pricing-argon')">Buy Now</a>
                </li>
            </ul> --}}
        </div>
    </div>
</nav>
<!-- End Navbar -->

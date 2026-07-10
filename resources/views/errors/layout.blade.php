<div class="container position-sticky z-index-sticky top-0">
  <div class="row">
    <div class="col-12">
      @include('layouts.navbars.topnav', [
        'title' => 'Department of Economy, Planning, and Development',
        'hide_nav_buttons' => $error_code == '503' ? true : false,
      ])
    </div>
  </div>
</div>

<div style="background: url('{{ asset('assets/img/abstract geometric poster cover hexagon_9040373.png') }}') no-repeat center center; background-size: cover; background-color: rgba(255, 255, 255, 0.8); background-blend-mode: lighten;">
  <main class="main-content mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-8 col-md-7 mx-auto text-center">
              <h1 class="display-1 text-bolder text-primary">Error {{ $error_code }}</h1>
              <h2>{{ $error_name }}</h2>
              <p class="lead">{{ $error_description }}</p>
              @if($error_code != '503')
                <a href="@guest {{ route('guest.contactus.create') }} @else {{ route('auth.contactus.create') }} @endguest" class="btn bg-gradient-dark mt-4">Contact Us</a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  @include('layouts.footers.auth.desc-footer')
</div>

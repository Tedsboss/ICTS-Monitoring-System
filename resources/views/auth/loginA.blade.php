@extends('layouts.app')

@section('content')
  @include('layouts.navbars.auth.topnav-guest', [
    'title' => 'Department of Economy, Planning, and Development',
  ])
<div style="background: url('{{ asset('assets/img/neda/10068.jpg') }}') no-repeat center center; background-size: cover; background-color: rgba(255, 255, 255, 0.8); background-blend-mode: lighten;">
  <main class="main-content mt-0">
    @include('layouts.cover')
    <div class="container">
      @include('components.loader')
      <div class="row mt-lg-n10 mt-md-n11 mt-n10 justify-content-center">
        <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
          <div class="card my-4 shadow-lg">
            <div class="card-body">
              <div class="pb-3 pt-0">
                <h4 class="font-weight-bolder">Login</h4>
                <p class="mb-0">Enter your email and password to login</p>
              </div>
              <form role="form" method="POST" action="{{route('login.perform') }}" class="text-start" id="loginForm">
                @csrf
                <div class="mb-3">
                  <input type="email" name="email" class="form-control" placeholder="Email" value="" aria-label="Email">
                  @error('email')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="mb-3">
                  <input type="password" name="password" class="form-control" placeholder="Password" value="" aria-label="Password">
                  @error('password')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                  @error('g-recaptcha-response')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="rememberMe">
                  <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-md btn-info w-100 mt-3 mb-0">Sign in</button>
                </div>
                <div class="row text-center mt-3 mb-0">
                  <p class="text-sm font-weight-bold mb-0 text-secondary text-border d-inline z-index-2 text-center">or</p>
                </div>
                <div class="row text-center">
                  <div class="col-12 col-lg-6 mt-3">
                    <a href="{{ route('azure.login') }}" class="btn btn-md btn-light w-100 mb-0" data-bs-toggle="tooltip" title="Signin using MS 365 - For DEPDev use only">Microsoft
                      <img class="ps-1" width="25px" height="20px" src="../../../assets/img/neda/microsoft.png">
                    </a>
                  </div>
                  <div class="col-12 col-lg-6 mt-3">
                    <a href="{{ route('auth.google.redirect') }}" class="btn btn-md btn-light w-100 mb-0"  data-bs-toggle="tooltip" title="Signin using Google">Google
                      <img class="ps-1" width="25px" height="20px" src="../../../assets/img/neda/google.png">
                    </a>
                  </div>
                </div>
              </form>
            </div>
            <div class="card-footer text-center py-0">
              <p class="mb-4 text-sm mx-auto">
                Forgot your password?
                <a href="{{ route('reset') }}" class="text-info text-gradient font-weight-bold">Reset here</a>
              </p>
              <p class="mb-4 text-sm mx-auto">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-warning text-gradient font-weight-bold">Register here</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  @include('layouts.footers.auth.desc-footer')
</div>
@endsection


@push('js')
  @if (app()->environment('production'))
  <script src="{{ config('recaptchav3.origin') }}/api.js?render={{ config('recaptchav3.sitekey') }}"></script>
  @endif
  <script>
    $('#loginForm').submit(function(event) {
      @if (app()->environment('production'))
      event.preventDefault();
      grecaptcha.ready(function() {
        grecaptcha.execute("{{ config('recaptchav3.sitekey') }}", { action: 'login' }).then(function(token) {
          $('#loginForm').prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
          $('#loginForm').unbind('submit').submit();
        });
      });
      @endif
    });
  </script>
@endpush



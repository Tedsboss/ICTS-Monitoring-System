@extends('layouts.app')

@section('content')
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
      <div class="col-12">
        @include('layouts.navbars.topnav', [
          'title' => 'Department of Economy, Planning, and Development',
        ])
      </div>
    </div>
  </div>

  <div style="background: url('{{ asset('assets/img/neda/19276.jpg') }}') no-repeat center center; background-size: cover; background-color: rgba(255, 255, 255, 0.8); background-blend-mode: lighten;">
    <main class="main-content mt-0">
      <section>
        <div class="page-header min-vh-100">
          <div class="container">
            <div class="row">
              <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                <div class="card fadeIn1 fadeInLeft">
                  <div class="card-body">
                    <div class="py-3">
                      <h4 class="font-weight-bolder">Sign In</h4>
                      <p class="mb-0">Enter your email and password to sign in</p>
                    </div>
                    <form role="form" method="POST" action="{{ route('login.perform') }}" id="loginForm">
                      @csrf
                      <div class="mb-3">
                        <input type="email" id="email" name="email" class="form-control" placeholder="Email" value="" onkeypress="submitViaEnterKey(event)" aria-label="Email">
                        @error('email')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                      </div>
                      <div class="mb-3">
                        <div class="input-group">
                          <input type="password" id="password" name="password" class="form-control" placeholder="Password" value="" onkeypress="submitViaEnterKey(event)" aria-label="Password">
                          <span class="input-group-text text-body">
                            <a type="button" data-bs-toggle="tooltip" data-bs-original-title="Show password" data-bs-placement="top" onclick="togglePassword()">
                              <i class="fa text-default px-2 fa-eye" aria-hidden="true"></i>
                            </a>
                          </span>
                        </div>
                        @error('password')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                        @error('g-recaptcha-response')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                      </div>
                      <div class="form-check form-switch">
                        <input class="form-check-input text-info" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                      </div>
                      <div class="text-center">
                        <button type="button" onclick="submitFormRecaptcha()" class="btn btn-md btn-info w-100 mt-3 mb-0" data-bs-toggle="tooltip" title="Signin">Sign in</button>
                      </div>
                      <div class="row text-center mt-3 mb-0">
                        <p class="text-sm font-weight-bold mb-0 text-secondary text-border d-inline z-index-2 text-center">or</p>
                      </div>
                      <div class="row text-center">
                        <div class="col-12 col-lg-12 mt-3">
                          <a href="{{ route('azure.login') }}" class="btn btn-md btn-light w-100 mb-0" data-bs-toggle="tooltip" title="Signin using Microsoft">Microsoft
                            <img class="ps-1" width="25px" height="20px" src="../../../assets/img/neda/microsoft.png">
                          </a>
                        </div>
                      </div>
                    </form>
                  </div>
                  <div class="card-footer text-center pt-0 px-lg-2 px-1">
                    <p class="mb-2 text-sm mx-auto">
                      Forgot your password?
                      <a href="{{ route('reset') }}" class="text-info text-gradient font-weight-bold">Reset here</a>
                    </p>
                    <p class="mb-2 text-sm mx-auto">
                      Have a question?
                      <a href="{{ route('guest.contactus.create') }}" class="text-primary text-gradient font-weight-bold">Message us here</a>
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                @include('layouts.side-cover')
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
    @include('layouts.footers.auth.desc-footer')
  </div>
@endsection

@push('css')
  <style>
    input::-webkit-password-toggle,
    input::-webkit-reveal {
      display: none;
      width: 0;
      height: 0;
    }

    input::-ms-reveal,
    input::-ms-clear {
      display: none;
    }

    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear,
    input[type="password"]::-webkit-password-toggle,
    input[type="password"]::-webkit-reveal {
      display: none;
    }
  </style>
@endpush

@push('js')
  @include('recaptchas.script', ['form_name' => 'loginForm', 'form_action' => 'login'])
  <script>
    function togglePassword() {
      let passwordField = $('#password');
      if (passwordField.attr('type') == 'password') {
        passwordField.attr('type', 'text');
        passwordField.next('.input-group-text').find('a').attr('data-bs-original-title', 'Hide password');
        passwordField.next('.input-group-text').find('i').removeClass('fa-eye').addClass('fa-eye-slash');
      } else {
        passwordField.attr('type', 'password');
        passwordField.next('.input-group-text').find('a').attr('data-bs-original-title', 'Show password');
        passwordField.next('.input-group-text').find('i').removeClass('fa-eye-slash').addClass('fa-eye');
      }
      refreshToolTip();
    }

    function submitViaEnterKey(e) {
      if (e.which == 13 || e.keyCode == 13) {
        if ($('#password').val().trim() != '' && $('#email').val().trim() != '') {
          e.preventDefault();
          submitFormRecaptcha();
        }
      }
    }
  </script>
@endpush
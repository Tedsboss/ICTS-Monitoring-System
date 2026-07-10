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
                      <h4 class="font-weight-bolder">Reset password</h4>
                      <p class="mb-0">Enter your email and preffered password</p>
                    </div>
                    <form role="form" method="POST" action="{{ route('change.perform') }}" id="frmChangePassword">
                      @csrf
                      <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Your e-mail" aria-label="Email">
                        @error('email') <p class='text-danger text-xs pt-1'> {{ $message }} </p> @enderror
                      </div>
                      <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" aria-label="Password">
                        @error('password') <p class='text-danger text-xs pt-1'> {{ $message }} </p>  @enderror
                      </div>
                      <div class="mb-3">
                        <input type="password" name="confirm-password" class="form-control" placeholder="Re-Type Password" aria-label="Password">
                        @error('confirm-password') <p class='text-danger text-xs pt-1'> {{ $message }} </p> @enderror
                        @error('g-recaptcha-response')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                      </div>
                      <div class="text-center">
                        <button type="button" onclick="submitFormRecaptcha()" class="btn btn-info w-100 mt-4 mb-0">Change password</button>
                      </div>
                    </form>
                  </div>
                  <div class="card-footer text-center pt-0 px-lg-2 px-1">
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

@push('js')
  @include('recaptchas.script', ['form_name' => 'frmChangePassword', 'form_action' => 'reset'])
@endpush
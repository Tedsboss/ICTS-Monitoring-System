@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  @auth
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-1 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Contact Us'])
        @include('layouts.navbars.auth.topnav-withdatetime')
      </div>
    </nav>
    <!-- End Navbar -->

    <div class="container-fluid">
      <div class="row mt-4">
        <div class="col-xl-8 ms-auto mt-xl-0 mt-4">
          <div class="card h-100 shadow-lg">
            <div class="card-header d-flex align-items-center border-bottom py-3">
              <div class="text-start ">
                <h5 class="mb-0">Message</h5>
              </div>
              <div class="text-end ms-auto">
                <i class="fa fa-commenting fa-lg text-info fa-2x"></i>
              </div>
            </div>
            <div class="card-body p-3">
              @include('inquiries.components.form', ['url' => route('auth.contactus.store')])
            </div>
          </div>
        </div>

        <div class="col-xl-4 ms-auto mt-xl-0 mt-4">
          <div class="row">
            <div class="col-12 col-lg-12 pb-3">
              <div class="card h-100 shadow-lg">
                <div class="card-header d-flex align-items-center border-bottom py-3">
                  <div class="text-start ">
                    <h5 class="mb-0">Telephone</h5>
                  </div>
                  <div class="text-end ms-auto">
                    <i class="fa fa-phone fa-lg text-info fa-2x"></i>
                  </div>
                </div>
                <div class="card-body">
                  <span class="text-sm">{{ $icc_telephone }}</span>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-12 pb-3">
              <div class="card h-100 shadow-lg">
                <div class="card-header d-flex align-items-center border-bottom py-3">
                  <div class="text-start ">
                    <h5 class="mb-0">Email Address</h5>
                  </div>
                  <div class="text-end ms-auto">
                    <i class="fa fa-envelope fa-lg text-info fa-2x"></i>
                  </div>
                </div>
                <div class="card-body">
                  <span class="text-sm"><a href="mailto:{{ $icc_email }}" target="_blank" rel="noopener noreferrer">{{ $icc_email }}</a></span>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-12 pb-3">
              <div class="card h-100 shadow-lg">
                <div class="card-header d-flex align-items-center border-bottom py-3">
                  <div class="text-start ">
                    <h5 class="mb-0">Office Hours</h5>
                  </div>
                  <div class="text-end ms-auto">
                    <i class="fa fa-clock-o fa-lg text-info fa-2x"></i>
                  </div>
                </div>
                <div class="card-body">
                  <span class="text-sm">Monday to Friday {{ $icc_open . ' - ' . $icc_close }}</span>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-12">
              <div class="card h-100 shadow-lg">
                <div class="card-header d-flex align-items-center border-bottom py-3">
                  <div class="text-start ">
                    <h5 class="mb-0">Location</h5>
                  </div>
                  <div class="text-end ms-auto">
                    <i class="fa fa-map-marker fa-lg text-info fa-2x"></i>
                  </div>
                </div>
                <div class="card-body">
                  <span class="text-sm">{{ $icc_address }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @else
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
            <div class="container" style="max-width: 1500px;">
              <div class="row">
                <div class="col-xl-6 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                  <div class="card fadeIn1 fadeInLeft shadow-lg">
                    <div class="card-body">
                      <div class="py-3">
                        <h4 class="font-weight-bolder">Send Message</h4>
                        <p class="mb-0">Enter your name and message here</p>
                      </div>
                      @include('inquiries.components.form', ['url' => route('guest.contactus.store')])
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
  @endauth
@endsection

@push('css')
  <style>
    .ql-editor {
      min-height: 100%; /* Ensure the editor fills the container */
    }

    @media (min-width: 1200px) {
      .col-xl-6 {
          flex: 0 0 auto;
          width: 40%;
      }
    }
  </style>
@endpush

@push('js')
@if (app()->environment('production'))
<script src="{{ config('recaptchav3.origin') }}/api.js?render={{ config('recaptchav3.sitekey') }}"></script>
@endif
<script>
  $(document).ready(function() {
    initQuillJs('quill_message');
    if ($('#html_message').val() == null || $('#html_message').val() == '') {
      quills['quill_message'].setContents([]);
    } else {
      // quill.setContents(JSON.parse($('#json_message').val()));
      quills['quill_message'].root.innerHTML = $('#html_message').val();
    }

  });

  function submitMessage() {
    // $("#loader").attr("hidden", false);
    $("#loader").fadeIn();
    $('#html_message').val(quills['quill_message'].root.innerHTML);
    @if (app()->environment('production'))
    grecaptcha.ready(function() {
      grecaptcha.execute("{{ config('recaptchav3.sitekey') }}", { action: 'inquiry' }).then(function(token) {
        $('#frmContactUs').prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
        $('#frmContactUs').unbind('submit').submit();
      });
    });
    @else
    $('#frmContactUs').unbind('submit').submit();
    @endif
  }
</script>
@endpush

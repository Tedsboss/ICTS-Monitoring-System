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
                    <div class="pb-3 pt-0">
                      <h4 class="font-weight-bolder">Two-Factor Authentication</h4>
                      <p class="mb-0">Enter the one-time password (OTP) sent to your email.</p>
                    </div>
                    <form id="frmOTPForm" role="form" method="POST" action="{{ route('2fa.verify') }}" class="text-start">
                      @csrf
                      {{-- <div class="mb-3">
                        <input type="otp_code" name="otp_code" class="form-control" placeholder="OTP" value="" aria-label="OTP">
                        @error('otp_code')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                      </div> --}}
                      <div class="row gx-2 gx-sm-3">
                        <div class="col">
                          <div class="form-group">
                            <input type="text" id="input1" name="input1" value="{{ old('input1') }}" class="otp-code-input form-control form-control-lg text-center" maxlength="1" autocomplete="off" autocapitalize="off" autofocus>
                          </div>
                        </div>
                        <div class="col">
                          <div class="form-group">
                            <input type="text" id="input2" name="input2" value="{{ old('input2') }}" class="otp-code-input form-control form-control-lg text-center" maxlength="1" autocomplete="off" autocapitalize="off">
                          </div>
                        </div>
                        <div class="col">
                          <div class="form-group">
                            <input type="text" id="input3" name="input3" value="{{ old('input3') }}" class="otp-code-input form-control form-control-lg text-center" maxlength="1" autocomplete="off" autocapitalize="off">
                          </div>
                        </div>
                        <div class="col">
                          <div class="form-group">
                            <input type="text" id="input4" name="input4" value="{{ old('input4') }}" class="otp-code-input form-control form-control-lg text-center" maxlength="1" autocomplete="off" autocapitalize="off">
                          </div>
                        </div>
                        <div class="col">
                          <div class="form-group">
                            <input type="text" id="input5" name="input5" value="{{ old('input5') }}" class="otp-code-input form-control form-control-lg text-center" maxlength="1" autocomplete="off" autocapitalize="off">
                          </div>
                        </div>
                        <div class="col">
                          <div class="form-group">
                            <input type="text" id="input6" name="input6" value="{{ old('input6') }}" class="otp-code-input form-control form-control-lg text-center" maxlength="1" autocomplete="off" autocapitalize="off">
                          </div>
                        </div>
                        <input type="otp_code" name="otp_code" id="otp_code" class="form-control" placeholder="OTP" value="" aria-label="OTP" hidden>
                        @error('otp_code')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                        @error('g-recaptcha-response')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                      </div>
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" value="1" name="remember_device" id="remember_device" checked>
                        <label class="form-check-label" for="remember_device">Remember this device</label>
                      </div>
                      <div class="text-center">
                        <button type="button" onclick="submitOTPForm()" class="btn btn-md btn-info w-100 mt-4 mb-0">Submit</button>
                      </div>
                    </form>
                  </div>
                  <div class="card-footer text-center py-0">
                    <form role="form" id="frmOTPResend" method="POST" action="{{ route('2fa.resend') }}">
                      @csrf
                      <p class="mb-4 text-sm mx-auto">
                        Haven't received it?
                        <a id="otp_resend" type="button" onclick="submitOTPResend()" class="font-weight-bold text-light pe-none" disabled>Resend OTP</a><br><span id="otp_timer"></span>
                      </p>
                    </form>
                  </div>
                  <div class="row p-3 text-left text-justify">
                    {{-- <p class="mb-0"><span class="text-danger">Note: </span><span class="text-sm">Do not refresh this page or force navigation to another page, as doing so may trigger a duplicate code. Delivery can take 2-3 minutes; if you don't see the email, check your spam or junk folder. The "Resend OTP" link activates after the timer expires.</span></p> --}}
                    <p class="mb-0"><span class="text-danger">Note: </span><span class="text-sm">Email delivery can take 2-3 minutes; if you don't see the email, check your spam or junk folder. The "Resend OTP" link activates after the timer expires.</span></p>
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
  @include('recaptchas.script', ['form_name' => 'frmOTPForm', 'form_action' => 'twofactor'])
  <script>
    $(document).ready(function() {
      let remaining = @php echo $remaining @endphp;
      const timer = setInterval(() => {
        if (remaining < 0) {
          clearInterval(timer);
          $('#otp_resend').prop('disabled', false).removeClass('text-light pe-none').addClass('text-info text-gradient');
          $('#otp_timer').addClass('text-light');
          return;
        }
        $('#otp_timer').text(`(${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')})`);
        remaining--;
      }, 1000);
    });

    function submitOTPResend() {
      $('#frmOTPResend').submit();
    }

    function submitOTPForm() {
      $('#otp_code').val($('#input1').val().trim() + $('#input2').val().trim() + $('#input3').val().trim() + $('#input4').val().trim() + $('#input5').val().trim() + $('#input6').val().trim());
      submitFormRecaptcha();
    }
    
    $('.otp-code-input').on('paste', function(event) {
      event.preventDefault();
      let digits = event.originalEvent.clipboardData.getData('text').split('');
      let current_loop = parseInt($(this).attr('id').slice(-1));

      if (digits.length == 6) {
        for (let i = 1; i <= 6; i++) {
          if (typeof digits[i - 1] !== 'undefined') {
            $('#input' + i).val(digits[i - 1]);
          }
        }
      } else {
        for (let i = current_loop; i <= 6; i++) {
          if (typeof digits[i - 1] !== 'undefined') {
            $('#input' + i).val(digits[i - 1]);
          }
        }
      }
    });

    $('.otp-code-input').on('keyup', function(event) {
      if (event.key === 'Enter') {
        submitOTPForm();
      } else if (event.key === 'Tab') {
      } else {
        let temp_val = $(this).val().trim();
        $(this).val(temp_val);
        if ($(this).val().length === 1) {
          let nextInputParent = $(this).parent().parent().next();
          if (nextInputParent.length) {
            let nextInput = nextInputParent.find('.otp-code-input');
            if (nextInput.length) {
              nextInput.focus();
            } else {
              $('#input1').focus();
            }
          }
        } else if ($(this).val().length === 0) {
          let prevInputParent = $(this).parent().parent().prev();
          if (prevInputParent.length) {
            let prevInput = prevInputParent.find('.otp-code-input');
            if (prevInput.length) {
              prevInput.focus();
            }
          } else {
            $('#input6').focus();
          }
        }
      }
    });

    $('.otp-code-input').on('focus', function() {
      $(this).select();
    });
  </script>
@endpush
<form role="form" method="POST" id="frmContactUs" action="{{ $url }}" class="text-start">
  @csrf

  @php
    $user = null;
    if(auth()->check()) {
      $user = auth()->user();
    }
  @endphp

  <div class="row">
    <div class="col-sm-6 mb-3">
      <input type="firstname" name="firstname" class="form-control" placeholder="First Name" aria-label="First Name" @auth value="{{ $user->firstname }}" disabled @else value="{{ old('firstname') }}" @endauth>
      @error('firstname')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
    </div>
    <div class="col-sm-6 mb-3">
      <input type="lastname" name="lastname" class="form-control" placeholder="Last Name" aria-label="Last Name" @auth value="{{ $user->lastname }}" disabled @else value="{{ old('lastname') }}" @endauth>
      @error('lastname')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
    </div>
  </div>
  <div class="row">
    <div class="col-sm-12 mb-3">
      <input type="email" name="email" class="form-control" placeholder="Email" aria-label="Email" @auth value="{{ $user->email }}" disabled @else value="{{ old('email') }}" @endauth>
      @error('email')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
    </div>
  </div>
  <div class="row">
    <div class="col-sm-12 mb-3">
      <input type="staff" name="staff" class="form-control" placeholder="Staff/Division" aria-label="staff" @auth value="{{ $user->staff->name . ' (' . $user->division->abbreviation . ')' }}" disabled @else value="{{ old('staff') }}" @endauth>
      @error('staff')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
    </div>
  </div>
  {{-- <div class="mb-3">
    <div id="quill_message" class="form-control overflow-auto" style="height: 200px;"></div>
    <textarea id="html_message" name="html_message" hidden>{{ old('html_message') }}</textarea>
    @error('html_message')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
  </div> --}}


  <div class="row">
    <div class="col-sm-12">
      <div id="quill_message" class="quill-box"></div>
      <textarea id="html_message" name="html_message" hidden>{{ old('html_message') }}</textarea>
      @error('html_message')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
      @error('g-recaptcha-response')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
    </div>
  </div>
  <div class="text-center">
    <button type="button" onclick="submitMessage()" class="btn btn-md btn-info w-100 mt-3 mb-0">Send</button>
  </div>


  {{-- <div class="row">
    <div class="col-sm-12 mb-6 min-height-200">
      <div id="quill_message" class="h-100"></div>
      <textarea id="html_message" name="html_message" hidden>{{ old('html_message') }}</textarea>
      @error('html_message')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
      @error('g-recaptcha-response')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
    </div>
  </div>
  <div class="text-center">
    <button type="button" onclick="submitMessage()" class="btn btn-md btn-info w-100 mt-3 mb-0">Send</button>
  </div> --}}
</form>
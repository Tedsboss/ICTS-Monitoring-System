<div class="d-flex">
  <p class="my-auto text-sm">Enable Dark Theme</p>
  <p class="text-sm text-secondary ms-auto me-3 my-auto"></p>
  <div class="form-check form-switch my-auto">
    <input class="form-check-input" type="checkbox" id="enabledark" name="enabledark" value="Y" @if(old('enabledark', $user->enabledark ?? null) == 'Y') checked @endif>
  </div>
</div>
@if($user == null || optional($user)->can('enableMyEmailNotification', [App\Models\User::class, $user]))
  <hr class="horizontal dark">
  <div class="d-flex">
    <p class="my-auto text-sm">Recieve Email Notification</p>
    <p class="text-sm text-secondary ms-auto me-3 my-auto"></p>
    <div class="form-check form-switch my-auto">
      <input class="form-check-input" type="checkbox" id="emailnotif" name="emailnotif" value="Y" @if(old('emailnotif', $user->emailnotif ?? null) == 'Y') checked @endif>
    </div>
  </div>
@endif
{{-- @can('enableMyEmailNotification', [App\Models\User::class, $user])
@endcan --}}
<hr class="horizontal dark">
<div class="d-flex">
  <p class="my-auto text-sm">Two-factor Authentication</p>
  <p class="text-sm text-secondary ms-auto me-3 my-auto"></p>
  <div class="form-check form-switch my-auto">
    <input class="form-check-input" type="checkbox" id="twofactor" name="twofactor" value="Y" onchange="ocTwoFactor()" @if(old('twofactor', $user->twofactor ?? null) == 'Y') checked @endif>
  </div>
</div>

<div class="ps-6" id="divTwoFactorType" @if(old('twofactor', $user->twofactor ?? null) == 'Y') @else hidden @endif>
  <hr class="horizontal dark">
  <div class="d-flex">
    <p class="my-auto text-sm">Email</p>
    <p class="text-secondary text-sm ms-auto my-auto me-3" id="pEmail">{{ old('email', $user->email ?? null) }}</p>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="twofactortype" id="twofactortype_email" value="Email" @if(old('twofactortype', $user->twofactortype ?? null) == 'Email') checked @endif>
    </div>
  </div>
  <hr class="horizontal dark">
  <div class="d-flex">
    <p class="my-auto text-sm">SMS <span class="text-xs text-danger"> (Not Yet Available)</span></p>
    <p class="text-secondary text-sm ms-auto my-auto me-3" id="pSMS">{{ old('phone', $user->phone ?? null) }}</p>

    <div class="form-check">
      <input class="form-check-input" type="radio" name="twofactortype" id="twofactortype_sms" value="SMS" @if(old('twofactortype', $user->twofactortype ?? null) == 'SMS') checked @endif disabled>
    </div>
  </div>
  <hr class="horizontal dark">
  <div class="d-flex">
    <p class="my-auto text-sm">Authenticator App <span class="text-xs text-danger"> (Not Yet Available)</span></p>
    <p class="text-secondary text-sm ms-auto my-auto me-3" id="pAuthApp">{{ 'Microsoft Authenticator' }}</p>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="twofactortype" id="twofactortype_auth_app" value="Authenticator App" @if(old('twofactortype', $user->twofactortype ?? null) == 'Authenticator App') checked @endif disabled>
    </div>
  </div>
</div>
{{-- DIREK Account Security & Settings --}}
@php
    $settingsDark = session('user_settings.class_theme', '') === 'dark';
    $settingsSurface = $settingsDark ? '!bg-[#1a293a] !border-[#34465a]' : '!bg-white !border-slate-200';
    $settingsHeading = $settingsDark ? '!text-slate-100' : '!text-[#203b55]';
    $settingsMuted = $settingsDark ? '!text-slate-400' : '!text-slate-500';
    $settingsIcon = $settingsDark ? '!bg-[#2b4056] !text-[#b7cee5]' : '!bg-[#edf3f9] !text-[#486a8b]';
    $settingsDivider = $settingsDark ? '!border-[#34465a]' : '!border-slate-200';
    $settingsInset = $settingsDark ? '!bg-[#142435]' : '!bg-slate-50';
    $settingsTrack = $settingsDark ? '!bg-slate-600' : '!bg-slate-300';
@endphp

<div class="!overflow-hidden !rounded-xl !border !border-solid {{ $settingsSurface }} [&_[hidden]]:!hidden">
    <div class="!flex !items-start !justify-between !gap-4 !px-4 !py-5  sm:!items-center sm:!px-5">
        <div class="!flex !min-w-0 !items-start !gap-3">
            <span class="!flex !h-10 !w-10 !shrink-0 !items-center !justify-center !rounded-xl {{ $settingsIcon }}"><i class="fa fa-moon-o" aria-hidden="true"></i></span>
            <div class="!min-w-0 !pt-0.5">
                <label for="enabledark" class="!m-0 !block !cursor-pointer !text-sm !font-semibold {{ $settingsHeading }}">Dark theme</label>
                <p id="enabledark-description" class="!m-0 !mt-1 !text-xs !leading-5 {{ $settingsMuted }}">Use a darker appearance for your DIREK account.</p>
                @error('enabledark')<p class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <label for="enabledark" class="!relative !m-0 !inline-flex !h-11 !w-12 !shrink-0 !cursor-pointer !items-center !justify-center">
            <input type="checkbox" id="enabledark" name="enabledark" value="Y" role="switch" class="peer !sr-only" aria-label="Dark theme" aria-describedby="enabledark-description"  @if (old('enabledark', $user->enabledark ?? null) == 'Y') checked @endif>
            <span aria-hidden="true" class="!relative !block !h-6 !w-11 !rounded-full {{ $settingsTrack }} !transition-colors after:!absolute after:!left-0.5 after:!top-0.5 after:!h-5 after:!w-5 after:!rounded-full after:!bg-white after:!shadow-sm after:!transition-transform after:!content-[''] peer-checked:!bg-[#365e85] peer-checked:after:!translate-x-5 peer-focus-visible:!ring-2 peer-focus-visible:!ring-[#809fbe] peer-focus-visible:!ring-offset-2 motion-reduce:!transition-none motion-reduce:after:!transition-none"></span>
        </label>
    </div>
    @if ($user == null || optional($user)->can('enableMyEmailNotification', [App\Models\User::class, $user]))
    <div class="!flex !items-start !justify-between !gap-4 !px-4 !py-5 !border-t !border-solid {{ $settingsDivider }} sm:!items-center sm:!px-5">
        <div class="!flex !min-w-0 !items-start !gap-3">
            <span class="!flex !h-10 !w-10 !shrink-0 !items-center !justify-center !rounded-xl {{ $settingsIcon }}"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
            <div class="!min-w-0 !pt-0.5">
                <label for="emailnotif" class="!m-0 !block !cursor-pointer !text-sm !font-semibold {{ $settingsHeading }}">Email notifications</label>
                <p id="emailnotif-description" class="!m-0 !mt-1 !text-xs !leading-5 {{ $settingsMuted }}">Receive DIREK account notifications through email.</p>
                @error('emailnotif')<p class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <label for="emailnotif" class="!relative !m-0 !inline-flex !h-11 !w-12 !shrink-0 !cursor-pointer !items-center !justify-center">
            <input type="checkbox" id="emailnotif" name="emailnotif" value="Y" role="switch" class="peer !sr-only" aria-label="Email notifications" aria-describedby="emailnotif-description"  @if (old('emailnotif', $user->emailnotif ?? null) == 'Y') checked @endif>
            <span aria-hidden="true" class="!relative !block !h-6 !w-11 !rounded-full {{ $settingsTrack }} !transition-colors after:!absolute after:!left-0.5 after:!top-0.5 after:!h-5 after:!w-5 after:!rounded-full after:!bg-white after:!shadow-sm after:!transition-transform after:!content-[''] peer-checked:!bg-[#365e85] peer-checked:after:!translate-x-5 peer-focus-visible:!ring-2 peer-focus-visible:!ring-[#809fbe] peer-focus-visible:!ring-offset-2 motion-reduce:!transition-none motion-reduce:after:!transition-none"></span>
        </label>
    </div>
    @endif
    <div class="!flex !items-start !justify-between !gap-4 !px-4 !py-5 !border-t !border-solid {{ $settingsDivider }} sm:!items-center sm:!px-5">
        <div class="!flex !min-w-0 !items-start !gap-3">
            <span class="!flex !h-10 !w-10 !shrink-0 !items-center !justify-center !rounded-xl {{ $settingsIcon }}"><i class="fa fa-shield" aria-hidden="true"></i></span>
            <div class="!min-w-0 !pt-0.5">
                <label for="twofactor" class="!m-0 !block !cursor-pointer !text-sm !font-semibold {{ $settingsHeading }}">Two-factor authentication</label>
                <p id="twofactor-description" class="!m-0 !mt-1 !text-xs !leading-5 {{ $settingsMuted }}">Add an extra verification step when signing in.</p>
                @error('twofactor')<p class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <label for="twofactor" class="!relative !m-0 !inline-flex !h-11 !w-12 !shrink-0 !cursor-pointer !items-center !justify-center">
            <input type="checkbox" id="twofactor" name="twofactor" value="Y" role="switch" class="peer !sr-only" aria-label="Two-factor authentication" aria-describedby="twofactor-description" onchange="ocTwoFactor()" @if (old('twofactor', $user->twofactor ?? null) == 'Y') checked @endif>
            <span aria-hidden="true" class="!relative !block !h-6 !w-11 !rounded-full {{ $settingsTrack }} !transition-colors after:!absolute after:!left-0.5 after:!top-0.5 after:!h-5 after:!w-5 after:!rounded-full after:!bg-white after:!shadow-sm after:!transition-transform after:!content-[''] peer-checked:!bg-[#365e85] peer-checked:after:!translate-x-5 peer-focus-visible:!ring-2 peer-focus-visible:!ring-[#809fbe] peer-focus-visible:!ring-offset-2 motion-reduce:!transition-none motion-reduce:after:!transition-none"></span>
        </label>
    </div>

    <fieldset id="divTwoFactorType" class="!m-0 !min-w-0 !border-0 !border-t !border-solid !px-4 !py-5 {{ $settingsDivider }} {{ $settingsInset }} sm:!px-5" @if (old('twofactor', $user->twofactor ?? null) != 'Y') hidden @endif>
        <legend class="!sr-only">Verification method</legend>
        <p class="!m-0 !text-xs !font-bold !uppercase !tracking-wider {{ $settingsHeading }}">Verification method</p>
        <p class="!m-0 !mt-1 !text-xs !leading-5 {{ $settingsMuted }}">Select how DIREK should send your verification code.</p>
        <div class="!mt-4 !space-y-3">
            <label for="twofactortype_email" class="!m-0 !flex !cursor-pointer !items-center !gap-3 !rounded-xl !border !border-solid !px-4 !py-4 {{ $settingsSurface }} !transition hover:!border-[#809fbe] focus-within:!ring-2 focus-within:!ring-[#809fbe]/50 motion-reduce:!transition-none">
                <span class="!flex !h-9 !w-9 !shrink-0 !items-center !justify-center !rounded-lg {{ $settingsIcon }}"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                <span class="!min-w-0 !flex-1">
                    <span class="!block !text-sm !font-semibold {{ $settingsHeading }}">Email</span>
                    <span id="pEmail" class="!mt-1 !block !break-words !text-xs {{ $settingsMuted }}">{{ old('email', $user->email ?? null) }}</span>
                </span>
                <input type="radio" name="twofactortype" id="twofactortype_email" value="Email" class="!m-0 !h-4 !w-4 !shrink-0 !accent-[#365e85]" aria-describedby="pEmail" @if (old('twofactortype', $user->twofactortype ?? null) == 'Email') checked @endif>
            </label>
            <div class="!flex !items-center !gap-3 !rounded-xl !border !border-solid !px-4 !py-4 {{ $settingsSurface }} !opacity-70">
                <span class="!flex !h-9 !w-9 !shrink-0 !items-center !justify-center !rounded-lg {{ $settingsIcon }}"><i class="fa fa-mobile" aria-hidden="true"></i></span>
                <div class="!min-w-0 !flex-1">
                    <div class="!flex !flex-wrap !items-center !gap-2"><label for="twofactortype_sms" class="!m-0 !text-sm !font-semibold {{ $settingsHeading }}">SMS</label><span class="!rounded-full !bg-amber-100 !px-2 !py-1 !text-[10px] !font-semibold !text-amber-800">Coming soon</span></div>
                    <p id="pSMS" class="!m-0 !mt-1 !break-words !text-xs {{ $settingsMuted }}">{{ old('phone', $user->phone ?? null) ?: 'No phone number' }}</p>
                </div>
                <input type="radio" name="twofactortype" id="twofactortype_sms" value="SMS" class="!m-0 !h-4 !w-4 !shrink-0" aria-describedby="pSMS" @if (old('twofactortype', $user->twofactortype ?? null) == 'SMS') checked @endif disabled>
            </div>
            <div class="!flex !items-center !gap-3 !rounded-xl !border !border-solid !px-4 !py-4 {{ $settingsSurface }} !opacity-70">
                <span class="!flex !h-9 !w-9 !shrink-0 !items-center !justify-center !rounded-lg {{ $settingsIcon }}"><i class="fa fa-key" aria-hidden="true"></i></span>
                <div class="!min-w-0 !flex-1">
                    <div class="!flex !flex-wrap !items-center !gap-2"><label for="twofactortype_auth_app" class="!m-0 !text-sm !font-semibold {{ $settingsHeading }}">Authenticator app</label><span class="!rounded-full !bg-amber-100 !px-2 !py-1 !text-[10px] !font-semibold !text-amber-800">Coming soon</span></div>
                    <p id="pAuthApp" class="!m-0 !mt-1 !break-words !text-xs {{ $settingsMuted }}">Microsoft Authenticator</p>
                </div>
                <input type="radio" name="twofactortype" id="twofactortype_auth_app" value="Authenticator App" class="!m-0 !h-4 !w-4 !shrink-0" aria-describedby="pAuthApp" @if (old('twofactortype', $user->twofactortype ?? null) == 'Authenticator App') checked @endif disabled>
            </div>
        </div>
        @error('twofactortype')<p class="!m-0 !mt-3 !text-xs !text-rose-600" role="alert">{{ $message }}</p>@enderror
    </fieldset>
</div>

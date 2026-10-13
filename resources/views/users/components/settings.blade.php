{{-- DIREK Account Security & Settings --}}
@php
    $settingsDark = session('user_settings.class_theme', '') === 'dark';
    $settingsSurface = $settingsDark ? '!bg-[#192130] !border-[#2c3546]' : '!bg-white !border-slate-200';
    $settingsHeading = $settingsDark ? '!text-slate-100' : '!text-[#172033]';
    $settingsMuted = $settingsDark ? '!text-slate-400' : '!text-slate-500';
    $settingsIcon = $settingsDark ? '!bg-[#353052] !text-[#d3ceff]' : '!bg-[#efedff] !text-[#635bff]';
    $settingsDivider = $settingsDark ? '!border-[#2c3546]' : '!border-slate-200';
    $settingsInset = $settingsDark ? '!bg-[#151c29]' : '!bg-slate-50';
    $settingsTrack = $settingsDark ? '!bg-slate-600' : '!bg-slate-300';
@endphp
<div data-theme="{{ $settingsDark ? 'dark' : 'light' }}" class="direk-settings !overflow-hidden !rounded-xl !border !border-solid {{ $settingsSurface }} [&_[hidden]]:!hidden">
    <div class="settings-intro"><div><p>MAKE IT YOURS</p><strong>Preferences & protection</strong></div><span><i class="fa fa-save" aria-hidden="true"></i>Save changes to apply</span></div>
    <div class="settings-row !flex !items-start !justify-between !gap-4 !px-4 !py-5  sm:!items-center sm:!px-5">
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
            <span aria-hidden="true" class="!relative !block !h-6 !w-11 !rounded-full {{ $settingsTrack }} !transition-colors after:!absolute after:!left-0.5 after:!top-0.5 after:!h-5 after:!w-5 after:!rounded-full after:!bg-white after:!shadow-sm after:!transition-transform after:!content-[''] peer-checked:!bg-[#635bff] peer-checked:after:!translate-x-5 peer-focus-visible:!ring-2 peer-focus-visible:!ring-[#8276ef] peer-focus-visible:!ring-offset-2 motion-reduce:!transition-none motion-reduce:after:!transition-none"></span>
        </label>
    </div>
    @if ($user == null || optional($user)->can('enableMyEmailNotification', [App\Models\User::class, $user]))
    <div class="settings-row !flex !items-start !justify-between !gap-4 !px-4 !py-5 !border-t !border-solid {{ $settingsDivider }} sm:!items-center sm:!px-5">
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
            <span aria-hidden="true" class="!relative !block !h-6 !w-11 !rounded-full {{ $settingsTrack }} !transition-colors after:!absolute after:!left-0.5 after:!top-0.5 after:!h-5 after:!w-5 after:!rounded-full after:!bg-white after:!shadow-sm after:!transition-transform after:!content-[''] peer-checked:!bg-[#635bff] peer-checked:after:!translate-x-5 peer-focus-visible:!ring-2 peer-focus-visible:!ring-[#8276ef] peer-focus-visible:!ring-offset-2 motion-reduce:!transition-none motion-reduce:after:!transition-none"></span>
        </label>
    </div>
    @endif
    <div class="settings-row !flex !items-start !justify-between !gap-4 !px-4 !py-5 !border-t !border-solid {{ $settingsDivider }} sm:!items-center sm:!px-5">
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
            <span aria-hidden="true" class="!relative !block !h-6 !w-11 !rounded-full {{ $settingsTrack }} !transition-colors after:!absolute after:!left-0.5 after:!top-0.5 after:!h-5 after:!w-5 after:!rounded-full after:!bg-white after:!shadow-sm after:!transition-transform after:!content-[''] peer-checked:!bg-[#635bff] peer-checked:after:!translate-x-5 peer-focus-visible:!ring-2 peer-focus-visible:!ring-[#8276ef] peer-focus-visible:!ring-offset-2 motion-reduce:!transition-none motion-reduce:after:!transition-none"></span>
        </label>
    </div>
    <fieldset id="divTwoFactorType" class="settings-methods !m-0 !min-w-0 !border-0 !border-t !border-solid !px-4 !py-5 {{ $settingsDivider }} {{ $settingsInset }} sm:!px-5" @if (old('twofactor', $user->twofactor ?? null) != 'Y') hidden @endif>
        <legend class="!sr-only">Verification method</legend>
        <p class="!m-0 !text-xs !font-bold !uppercase !tracking-wider {{ $settingsHeading }}">Verification method</p>
        <p class="!m-0 !mt-1 !text-xs !leading-5 {{ $settingsMuted }}">Select how DIREK should send your verification code.</p>
        <div class="!mt-4 !space-y-3">
            <label for="twofactortype_email" class="!m-0 !flex !cursor-pointer !items-center !gap-3 !rounded-xl !border !border-solid !px-4 !py-4 {{ $settingsSurface }} !transition hover:!border-[#8276ef] focus-within:!ring-2 focus-within:!ring-[#8276ef]/50 motion-reduce:!transition-none">
                <span class="!flex !h-9 !w-9 !shrink-0 !items-center !justify-center !rounded-lg {{ $settingsIcon }}"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                <span class="!min-w-0 !flex-1">
                    <span class="!block !text-sm !font-semibold {{ $settingsHeading }}">Email</span>
                    <span id="pEmail" class="!mt-1 !block !break-words !text-xs {{ $settingsMuted }}">{{ old('email', $user->email ?? null) }}</span>
                </span>
                <input type="radio" name="twofactortype" id="twofactortype_email" value="Email" class="!m-0 !h-4 !w-4 !shrink-0 !accent-[#635bff]" aria-describedby="pEmail" @if (old('twofactortype', $user->twofactortype ?? null) == 'Email') checked @endif>
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
@push('css')
<style>
.direk-settings{--security-bg:#fff;--security-subtle:#f6f7fb;--security-border:#e8ebf2;--security-text:#172033;--security-copy:#637086;--security-accent:#635bff;--security-accent-bg:#efedff;border-color:var(--security-border)!important;border-radius:14px!important}
.direk-settings[data-theme="dark"]{--security-bg:#192130;--security-subtle:#151c29;--security-border:#2c3546;--security-text:#eef2fb;--security-copy:#aab5c8;--security-accent:#a19cff;--security-accent-bg:#353052}
.direk-settings .settings-intro{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:17px 20px;background:var(--security-subtle);border-bottom:1px solid var(--security-border)}
.direk-settings .settings-intro p{margin:0;font-size:9px;letter-spacing:.12em;font-weight:600;color:var(--security-copy)}
.direk-settings .settings-intro strong{display:block;font-size:13px;font-weight:650;color:var(--security-text);margin-top:5px}
.direk-settings .settings-intro>span{display:inline-flex;align-items:center;gap:7px;font-size:9px;color:var(--security-copy);line-height:1.6;text-align:right}
.direk-settings .settings-intro>span i{color:var(--security-accent)}
.direk-settings .settings-row{padding:22px 20px!important;border-color:var(--security-border)!important;background:var(--security-bg);transition:background .15s}
.direk-settings .settings-row:hover{background:var(--security-subtle)}
.direk-settings .settings-row>div>span{width:38px!important;height:38px!important;border-radius:11px!important;background:var(--security-accent-bg)!important;color:var(--security-accent)!important;font-size:15px}
.direk-settings .settings-row>div>div>p{color:var(--security-copy)!important;font-size:11px!important;line-height:1.75!important;margin-top:6px!important}
.direk-settings .settings-row label,#direk-profile .direk-settings .settings-row label{font-size:12px!important;font-weight:600!important;color:var(--security-text)!important}
.direk-settings .settings-row input:focus-visible+span{box-shadow:0 0 0 3px rgba(99,91,255,.2)!important}
.direk-settings .settings-methods{border-color:var(--security-border)!important;background:var(--security-subtle)!important;padding:22px 20px!important}
.direk-settings .settings-methods>p:first-of-type{font-size:10px!important;letter-spacing:.08em!important;color:var(--security-text)!important}
.direk-settings .settings-methods>p:nth-of-type(2){font-size:11px!important;color:var(--security-copy)!important;line-height:1.75!important}
.direk-settings .settings-methods label[for="twofactortype_email"]{border-color:var(--security-border)!important;border-radius:12px!important;background:var(--security-bg)!important;padding:15px!important}
.direk-settings .settings-methods label[for="twofactortype_email"]:has(input:checked){border-color:var(--security-accent)!important;box-shadow:0 0 0 1px var(--security-accent)}
.direk-settings .settings-methods label[for="twofactortype_email"]>span:first-child{background:var(--security-accent-bg)!important;color:var(--security-accent)!important;border-radius:10px!important}
.direk-settings .settings-methods input[type="radio"]{accent-color:var(--security-accent)!important}
.direk-settings [hidden]{display:none!important}
@media(max-width:520px){.direk-settings .settings-intro{align-items:flex-start;flex-direction:column;gap:9px}.direk-settings .settings-row{padding:18px 15px!important}.direk-settings .settings-methods{padding:18px 15px!important}.direk-settings .settings-row>div{gap:10px!important}}
@media(prefers-reduced-motion:reduce){.direk-settings .settings-row{transition:none!important}}
</style>
@endpush

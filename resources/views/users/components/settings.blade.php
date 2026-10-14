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
    <div class="settings-intro"><span class="settings-intro-symbol" aria-hidden="true"><i class="fa fa-shield"></i></span><div><p>MAKE IT YOURS</p><strong>Your preferences. Your protection.</strong></div><span><i class="fa fa-save" aria-hidden="true"></i>Save changes to apply</span></div>
    <div class="settings-row !flex !items-start !justify-between !gap-4 !px-4 !py-5  sm:!items-center sm:!px-5">
        <div class="!flex !min-w-0 !items-start !gap-3">
            <span class="!flex !h-10 !w-10 !shrink-0 !items-center !justify-center !rounded-xl {{ $settingsIcon }}"><i class="fa fa-moon-o" aria-hidden="true"></i></span>
            <div class="!min-w-0 !pt-0.5">
                <label for="enabledark" class="!m-0 !block !cursor-pointer !text-sm !font-semibold {{ $settingsHeading }}">Dark theme</label>
                <p id="enabledark-description" class="!m-0 !mt-1 !text-xs !leading-5 {{ $settingsMuted }}">Use a darker appearance for your DIREK account.</p>
                @error('enabledark')<p class="settings-field-error !m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <label for="enabledark" class="settings-switch !relative !m-0 !inline-flex !h-11 !w-12 !shrink-0 !cursor-pointer !items-center !justify-center">
            <input type="checkbox" id="enabledark" name="enabledark" value="Y" role="switch" class="settings-switch-input peer !sr-only" aria-label="Dark theme" aria-describedby="enabledark-description"  @if (old('enabledark', $user->enabledark ?? null) == 'Y') checked @endif>
            <span aria-hidden="true" class="settings-switch-track !relative !block !h-6 !w-11 !rounded-full {{ $settingsTrack }} !transition-colors after:!absolute after:!left-0.5 after:!top-0.5 after:!h-5 after:!w-5 after:!rounded-full after:!bg-white after:!shadow-sm after:!transition-transform after:!content-[''] peer-checked:!bg-[#635bff] peer-checked:after:!translate-x-5 peer-focus-visible:!ring-2 peer-focus-visible:!ring-[#8276ef] peer-focus-visible:!ring-offset-2 motion-reduce:!transition-none motion-reduce:after:!transition-none"></span>
        </label>
    </div>
    @if ($user == null || optional($user)->can('enableMyEmailNotification', [App\Models\User::class, $user]))
    <div class="settings-row !flex !items-start !justify-between !gap-4 !px-4 !py-5 !border-t !border-solid {{ $settingsDivider }} sm:!items-center sm:!px-5">
        <div class="!flex !min-w-0 !items-start !gap-3">
            <span class="!flex !h-10 !w-10 !shrink-0 !items-center !justify-center !rounded-xl {{ $settingsIcon }}"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
            <div class="!min-w-0 !pt-0.5">
                <label for="emailnotif" class="!m-0 !block !cursor-pointer !text-sm !font-semibold {{ $settingsHeading }}">Email notifications</label>
                <p id="emailnotif-description" class="!m-0 !mt-1 !text-xs !leading-5 {{ $settingsMuted }}">Receive DIREK account notifications through email.</p>
                @error('emailnotif')<p class="settings-field-error !m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <label for="emailnotif" class="settings-switch !relative !m-0 !inline-flex !h-11 !w-12 !shrink-0 !cursor-pointer !items-center !justify-center">
            <input type="checkbox" id="emailnotif" name="emailnotif" value="Y" role="switch" class="settings-switch-input peer !sr-only" aria-label="Email notifications" aria-describedby="emailnotif-description"  @if (old('emailnotif', $user->emailnotif ?? null) == 'Y') checked @endif>
            <span aria-hidden="true" class="settings-switch-track !relative !block !h-6 !w-11 !rounded-full {{ $settingsTrack }} !transition-colors after:!absolute after:!left-0.5 after:!top-0.5 after:!h-5 after:!w-5 after:!rounded-full after:!bg-white after:!shadow-sm after:!transition-transform after:!content-[''] peer-checked:!bg-[#635bff] peer-checked:after:!translate-x-5 peer-focus-visible:!ring-2 peer-focus-visible:!ring-[#8276ef] peer-focus-visible:!ring-offset-2 motion-reduce:!transition-none motion-reduce:after:!transition-none"></span>
        </label>
    </div>
    @endif
    <div class="settings-row !flex !items-start !justify-between !gap-4 !px-4 !py-5 !border-t !border-solid {{ $settingsDivider }} sm:!items-center sm:!px-5">
        <div class="!flex !min-w-0 !items-start !gap-3">
            <span class="!flex !h-10 !w-10 !shrink-0 !items-center !justify-center !rounded-xl {{ $settingsIcon }}"><i class="fa fa-shield" aria-hidden="true"></i></span>
            <div class="!min-w-0 !pt-0.5">
                <label for="twofactor" class="!m-0 !block !cursor-pointer !text-sm !font-semibold {{ $settingsHeading }}">Two-factor authentication</label>
                <p id="twofactor-description" class="!m-0 !mt-1 !text-xs !leading-5 {{ $settingsMuted }}">Add an extra verification step when signing in.</p>
                @error('twofactor')<p class="settings-field-error !m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <label for="twofactor" class="settings-switch !relative !m-0 !inline-flex !h-11 !w-12 !shrink-0 !cursor-pointer !items-center !justify-center">
            <input type="checkbox" id="twofactor" name="twofactor" value="Y" role="switch" class="settings-switch-input peer !sr-only" aria-label="Two-factor authentication" aria-describedby="twofactor-description" onchange="ocTwoFactor()" @if (old('twofactor', $user->twofactor ?? null) == 'Y') checked @endif>
            <span aria-hidden="true" class="settings-switch-track !relative !block !h-6 !w-11 !rounded-full {{ $settingsTrack }} !transition-colors after:!absolute after:!left-0.5 after:!top-0.5 after:!h-5 after:!w-5 after:!rounded-full after:!bg-white after:!shadow-sm after:!transition-transform after:!content-[''] peer-checked:!bg-[#635bff] peer-checked:after:!translate-x-5 peer-focus-visible:!ring-2 peer-focus-visible:!ring-[#8276ef] peer-focus-visible:!ring-offset-2 motion-reduce:!transition-none motion-reduce:after:!transition-none"></span>
        </label>
    </div>
    <fieldset id="divTwoFactorType" class="settings-methods !m-0 !min-w-0 !border-0 !border-t !border-solid !px-4 !py-5 {{ $settingsDivider }} {{ $settingsInset }} sm:!px-5" @if (old('twofactor', $user->twofactor ?? null) != 'Y') hidden @endif>
        <legend class="!sr-only">Verification method</legend>
        <p class="!m-0 !text-xs !font-bold !uppercase !tracking-wider {{ $settingsHeading }}">Verification method</p>
        <p class="!m-0 !mt-1 !text-xs !leading-5 {{ $settingsMuted }}">Select how DIREK should send your verification code.</p>
        <div class="!mt-4 !space-y-3">
            <label for="twofactortype_email" class="settings-method-card !m-0 !flex !cursor-pointer !items-center !gap-3 !rounded-xl !border !border-solid !px-4 !py-4 {{ $settingsSurface }} !transition hover:!border-[#8276ef] focus-within:!ring-2 focus-within:!ring-[#8276ef]/50 motion-reduce:!transition-none">
                <span class="!flex !h-9 !w-9 !shrink-0 !items-center !justify-center !rounded-lg {{ $settingsIcon }}"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                <span class="!min-w-0 !flex-1">
                    <span class="!block !text-sm !font-semibold {{ $settingsHeading }}">Email</span>
                    <span id="pEmail" class="!mt-1 !block !break-words !text-xs {{ $settingsMuted }}">{{ old('email', $user->email ?? null) }}</span>
                </span>
                <input type="radio" name="twofactortype" id="twofactortype_email" value="Email" class="!m-0 !h-4 !w-4 !shrink-0 !accent-[#635bff]" aria-describedby="pEmail" @if (old('twofactortype', $user->twofactortype ?? null) == 'Email') checked @endif>
            </label>
            <div class="settings-method-card settings-method-unavailable !flex !items-center !gap-3 !rounded-xl !border !border-solid !px-4 !py-4 {{ $settingsSurface }} !opacity-70">
                <span class="!flex !h-9 !w-9 !shrink-0 !items-center !justify-center !rounded-lg {{ $settingsIcon }}"><i class="fa fa-mobile" aria-hidden="true"></i></span>
                <div class="!min-w-0 !flex-1">
                    <div class="!flex !flex-wrap !items-center !gap-2"><label for="twofactortype_sms" class="!m-0 !text-sm !font-semibold {{ $settingsHeading }}">SMS</label><span class="!rounded-full !bg-amber-100 !px-2 !py-1 !text-[10px] !font-semibold !text-amber-800">Coming soon</span></div>
                    <p id="pSMS" class="!m-0 !mt-1 !break-words !text-xs {{ $settingsMuted }}">{{ old('phone', $user->phone ?? null) ?: 'No phone number' }}</p>
                </div>
                <input type="radio" name="twofactortype" id="twofactortype_sms" value="SMS" class="!m-0 !h-4 !w-4 !shrink-0" aria-describedby="pSMS" @if (old('twofactortype', $user->twofactortype ?? null) == 'SMS') checked @endif disabled>
            </div>
            <div class="settings-method-card settings-method-unavailable !flex !items-center !gap-3 !rounded-xl !border !border-solid !px-4 !py-4 {{ $settingsSurface }} !opacity-70">
                <span class="!flex !h-9 !w-9 !shrink-0 !items-center !justify-center !rounded-lg {{ $settingsIcon }}"><i class="fa fa-key" aria-hidden="true"></i></span>
                <div class="!min-w-0 !flex-1">
                    <div class="!flex !flex-wrap !items-center !gap-2"><label for="twofactortype_auth_app" class="!m-0 !text-sm !font-semibold {{ $settingsHeading }}">Authenticator app</label><span class="!rounded-full !bg-amber-100 !px-2 !py-1 !text-[10px] !font-semibold !text-amber-800">Coming soon</span></div>
                    <p id="pAuthApp" class="!m-0 !mt-1 !break-words !text-xs {{ $settingsMuted }}">Microsoft Authenticator</p>
                </div>
                <input type="radio" name="twofactortype" id="twofactortype_auth_app" value="Authenticator App" class="!m-0 !h-4 !w-4 !shrink-0" aria-describedby="pAuthApp" @if (old('twofactortype', $user->twofactortype ?? null) == 'Authenticator App') checked @endif disabled>
            </div>
        </div>
        @error('twofactortype')<p class="settings-field-error !m-0 !mt-3 !text-xs !text-rose-600" role="alert">{{ $message }}</p>@enderror
    </fieldset>
</div>
@push('css')
<style>
.direk-settings{--security-bg:#fff;--security-subtle:#f6f4fc;--security-border:#e3def0;--security-text:#292940;--security-copy:#74758e;--security-accent:#8267d9;--security-accent-bg:#eee8fa;--security-edge:#d9d1e9;border:0!important;border-radius:0!important;background:transparent!important;overflow:visible!important;color:var(--security-text)}
.direk-settings[data-theme="dark"]{--security-bg:#192130;--security-subtle:#151c29;--security-border:#37384f;--security-text:#eeebfd;--security-copy:#abb0cd;--security-accent:#b7a0f5;--security-accent-bg:#302b45;--security-edge:#101523}
.direk-settings [hidden]{display:none!important}.direk-settings .settings-intro{display:flex;align-items:center;gap:13px;padding:0 0 22px;background:transparent;border:0}.direk-settings .settings-intro-symbol{display:grid;place-items:center;flex-shrink:0;width:40px;height:40px;border:1px solid var(--security-border);border-radius:12px;background:var(--security-accent-bg);color:var(--security-accent);font-size:19px;box-shadow:0 3px 0 var(--security-edge)}.direk-settings .settings-intro>div{flex:1;min-width:0}.direk-settings .settings-intro p{margin:0;font-size:9px;font-weight:650;letter-spacing:.12em;color:var(--security-copy)}.direk-settings .settings-intro strong{display:block;font-size:14px;line-height:1.5;font-weight:650;color:var(--security-text);margin-top:5px}.direk-settings .settings-intro>span:last-child{display:inline-flex;align-items:center;gap:6px;flex-shrink:0;font-size:9px;line-height:1.6;color:var(--security-copy);padding:7px 9px;border:1px solid var(--security-border);border-radius:8px;background:var(--security-subtle)}.direk-settings .settings-intro>span:last-child i{color:var(--security-accent)}
.direk-settings .settings-row{display:flex!important;align-items:center!important;justify-content:space-between;gap:15px!important;padding:20px!important;margin-bottom:13px;background:linear-gradient(135deg,var(--security-bg),var(--security-subtle));border:1px solid var(--security-border)!important;border-radius:14px;box-shadow:inset 0 1px 0 rgba(255,255,255,.12),0 3px 0 var(--security-edge);transition:border-color .15s ease}.direk-settings .settings-row:hover{border-color:var(--security-accent)!important}.direk-settings .settings-row>div{display:flex;align-items:flex-start;gap:13px;min-width:0}.direk-settings .settings-row>div>span{display:grid!important;place-items:center;flex-shrink:0;width:36px!important;height:36px!important;background:transparent!important;color:var(--security-accent)!important;border-radius:0!important;font-size:21px}.direk-settings .settings-row>div>div{min-width:0}.direk-settings .settings-row>div>div>p{font-size:11px!important;line-height:1.8!important;color:var(--security-copy)!important;margin-top:6px!important}.direk-settings .settings-row label,#direk-profile .direk-settings .settings-row label{font-size:13px!important;font-weight:600!important;color:var(--security-text)!important}.direk-settings .settings-row .settings-field-error{color:#d9496d!important}.direk-settings[data-theme="dark"] .settings-row .settings-field-error{color:#f79db3!important}
.direk-settings .settings-switch{position:relative;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;width:52px!important;height:44px!important;margin:0!important;cursor:pointer}.direk-settings .settings-switch-input{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}.direk-settings .settings-switch-track{position:relative;display:block;width:46px!important;height:26px!important;border:1px solid var(--security-border);border-radius:99px;background:var(--security-edge)!important;box-shadow:inset 0 2px 4px rgba(20,16,44,.08);transition:background .18s ease,border-color .18s ease}.direk-settings .settings-switch-track::after{content:"";position:absolute;top:3px!important;left:3px!important;width:18px!important;height:18px!important;border-radius:50%;background:#fff;box-shadow:0 2px 3px rgba(20,16,44,.16);transform:translateX(0);transition:transform .18s ease}.direk-settings .settings-switch-input:checked+.settings-switch-track{background:#886be0!important;border-color:#886be0}.direk-settings .settings-switch-input:checked+.settings-switch-track::after{transform:translateX(20px)!important}.direk-settings .settings-switch-input:focus-visible+.settings-switch-track{outline:2px solid var(--security-accent);outline-offset:4px}.direk-settings .settings-switch-input:disabled+.settings-switch-track{opacity:.5;cursor:not-allowed}
.direk-settings .settings-methods{padding:20px!important;margin:21px 0 0!important;border:1px solid var(--security-border)!important;border-radius:15px;background:var(--security-subtle)!important}.direk-settings .settings-methods legend{position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}.direk-settings .settings-methods>p:first-of-type{font-size:10px!important;letter-spacing:.1em!important;color:var(--security-text)!important;margin:0}.direk-settings .settings-methods>p:nth-of-type(2){font-size:11px!important;color:var(--security-copy)!important;line-height:1.8;margin-top:6px!important}.direk-settings .settings-methods>div{display:flex;flex-direction:column;gap:12px;margin-top:17px!important}
.direk-settings .settings-method-card{display:flex!important;align-items:center;gap:12px!important;min-width:0;margin:0!important;padding:16px!important;border:1px solid var(--security-border)!important;border-radius:12px!important;background:var(--security-bg)!important;box-shadow:0 2px 0 var(--security-edge);transition:border-color .15s ease,box-shadow .15s ease}.direk-settings .settings-method-card>span:first-child{display:grid;place-items:center;flex-shrink:0;width:33px!important;height:33px!important;background:transparent!important;color:var(--security-accent)!important;font-size:19px}.direk-settings .settings-method-card>span:nth-child(2),.direk-settings .settings-method-card>div{flex:1;min-width:0}.direk-settings .settings-method-card span,.direk-settings .settings-method-card p{overflow-wrap:anywhere}.direk-settings .settings-method-card:has(input:checked:not(:disabled)){border-color:var(--security-accent)!important;background:var(--security-accent-bg)!important;box-shadow:0 2px 0 var(--security-edge)}.direk-settings .settings-method-card:focus-within{outline:2px solid var(--security-accent);outline-offset:3px}.direk-settings .settings-method-card input[type="radio"]{width:17px!important;height:17px!important;flex-shrink:0;margin:0!important;accent-color:#886be0!important}.direk-settings .settings-method-unavailable{opacity:.65!important;box-shadow:none;cursor:default}
@media(max-width:600px){.direk-settings .settings-intro{flex-wrap:wrap;gap:11px}.direk-settings .settings-intro>span:last-child{margin-left:52px}.direk-settings .settings-row{padding:17px 14px!important;gap:10px!important}.direk-settings .settings-row>div{gap:10px}.direk-settings .settings-row>div>span{width:25px!important;font-size:19px}.direk-settings .settings-row label,#direk-profile .direk-settings .settings-row label{font-size:12px!important}.direk-settings .settings-methods{padding:17px 14px!important}.direk-settings .settings-method-card{padding:14px!important;gap:10px!important}}
@media(prefers-reduced-motion:reduce){.direk-settings .settings-row,.direk-settings .settings-switch-track,.direk-settings .settings-switch-track::after,.direk-settings .settings-method-card{transition:none!important}}

</style>
@endpush

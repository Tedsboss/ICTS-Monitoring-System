@extends('layouts.app')
@section('content')
@php
    $profileUser = auth()->user();
    $profileCurrentAgencyId = (string) $profileUser->agency_id;
    $profileAgencyId = old('agency_id', $profileUser->agency_id);
    $profileDepdevIds = array_map('strval', $depDevAgencyIds);
    $showStaffDivision = in_array((string) $profileAgencyId, $profileDepdevIds, true);
    $profileBirthday = old('birthday', $profileUser->birthday
        ? \Carbon\Carbon::parse($profileUser->birthday)->format('Y-m-d')
        : '');
    $profileRole = optional($profileUser->role)->name ?? 'User';
    $profileName = trim(($profileUser->firstname ?? '') . ' ' . ($profileUser->lastname ?? ''));
    $canEditAgency = $profileUser->isSuperAdmin() || ($profileUser->first_login === 'Y' && empty($profileUser->agency_id));
    $canEditAssignment = $profileUser->first_login === 'Y';
    $profileDark = session('user_settings.class_theme', '') === 'dark';
    $profileSurface = $profileDark ? '!bg-[#192130] !border-[#2c3546]' : '!bg-white !border-slate-200';
    $profileHeading = $profileDark ? '!text-slate-100' : '!text-[#172033]';
    $profileCopy = $profileDark ? '!text-slate-300' : '!text-slate-600';
    $profileMuted = $profileDark ? '!text-slate-400' : '!text-slate-500';
    $profileDivider = $profileDark ? '!border-[#2c3546]' : '!border-slate-200';
    $profileControlTheme = $profileDark
        ? '!bg-[#151c29] !border-[#39445b] !text-slate-100 placeholder:!text-slate-400 [&[readonly]]:!bg-[#252d40] disabled:!bg-[#252d40] disabled:!text-slate-400'
        : '!bg-white !border-slate-300 !text-slate-700 placeholder:!text-slate-400 [&[readonly]]:!bg-slate-50 disabled:!bg-slate-50 disabled:!text-slate-500';
    $profileControl = '!box-border !block !min-h-[46px] !w-full !rounded-lg !border !border-solid !px-3 !py-2.5 !font-sans !text-base !leading-5 !outline-none !transition focus:!border-[#8276ef] focus:!ring-2 focus:!ring-[#8276ef]/30 disabled:!cursor-not-allowed motion-reduce:!transition-none md:!text-sm';
    $profileLabel = '!mb-2 !block !text-xs !font-semibold';
    $profileEditorTheme = $profileDark
        ? '[&_.ts-control]:!bg-[#151c29] [&_.ts-control]:!border-[#39445b] [&_.ts-control]:!text-slate-100 [&_.ts-control_input]:!text-slate-100 [&_.ts-dropdown]:!bg-[#192130] [&_.ts-dropdown]:!border-[#39445b] [&_.ts-dropdown]:!text-slate-100 [&_.ts-dropdown_.active]:!bg-[#353052] [&_.ts-dropdown_.active]:!text-slate-100'
        : '[&_.ts-control]:!bg-white [&_.ts-control]:!border-slate-300 [&_.ts-control]:!text-slate-700 [&_.ts-control_input]:!text-slate-700 [&_.ts-dropdown]:!bg-white [&_.ts-dropdown]:!border-slate-200 [&_.ts-dropdown]:!text-slate-700 [&_.ts-dropdown_.active]:!bg-[#efedff] [&_.ts-dropdown_.active]:!text-[#172033]';
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'My Profile'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-profile" data-theme="{{ $profileDark ? 'dark' : 'light' }}" class="!px-4 !pb-8 !pt-4 [&_[hidden]]:!hidden [&_.ts-wrapper]:!w-full [&_.ts-control]:!min-h-[46px] [&_.ts-control]:!rounded-lg [&_.ts-control]:!border [&_.ts-control]:!border-solid [&_.ts-control]:!px-3 [&_.ts-control]:!py-2.5 [&_.ts-control]:!text-sm [&_.ts-control]:!shadow-none [&_.ts-wrapper.focus_.ts-control]:!border-[#8276ef] [&_.ts-wrapper.focus_.ts-control]:!ring-2 [&_.ts-wrapper.focus_.ts-control]:!ring-[#8276ef]/30 {{ $profileEditorTheme }} md:!px-6">
    <div class="!mx-auto !max-w-[1400px]">
        <div class="profile-page-intro">
            <div><p class="profile-eyebrow">ACCOUNT CENTER</p><h2>Your profile. Your preferences.</h2><p>Make this workspace yours, and keep your account details current.</p></div>
            <span class="profile-context-pill"><i class="fa fa-sliders" aria-hidden="true"></i>Personal settings</span>
        </div>
        @if ($errors->any())
            <div class="!mb-5 !rounded-xl !border !border-solid !border-rose-200 !bg-rose-50 !px-5 !py-4 !text-sm !text-rose-800" role="alert">
                <div class="!flex !items-start !gap-3">
                    <i class="fa fa-exclamation-circle !mt-1" aria-hidden="true"></i>
                    <div><p class="!m-0 !font-semibold">Please review the information below.</p><ul class="!m-0 !mt-2 !list-disc !space-y-1 !pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                </div>
            </div>
        @endif
        <form autocomplete="off" method="POST" action="{{ route('user-profile.perform') }}" enctype="multipart/form-data" id="frmUpdate">
            @csrf
            <div class="!grid !items-start !gap-6 xl:!grid-cols-[280px_minmax(0,1fr)]">
                {{-- Account summary and profile photo --}}
                <aside class="profile-summary !overflow-hidden !rounded-2xl !border !border-solid {{ $profileSurface }}" aria-label="Profile summary">
                    <div class="profile-summary-banner !px-6 !py-5"><p class="!m-0 !text-xs !font-semibold !uppercase !tracking-[0.12em] !text-white">Your DIREK account</p></div>
                    <div class="profile-summary-content !p-6">
                        <div class="profile-avatar-wrap !relative !mb-5 !h-24 !w-24">
                            <img id="avatar-preview" src="{{ $profileUser->avatarUrl() }}" width="96" height="96" alt="Your profile photo" class="!h-24 !w-24 !rounded-2xl !object-cover !shadow-sm">
                            <button type="button" onclick="document.getElementById('file-input').click()" aria-label="Change profile photo" title="Change profile photo" class="profile-avatar-button !absolute !-bottom-2 !-right-2 !grid !h-11 !w-11 !place-items-center !rounded-xl !border !border-solid !border-slate-200 !bg-white !text-[#172033] !shadow-sm hover:!bg-slate-50 focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#8276ef]"><i class="fa fa-camera" aria-hidden="true"></i></button>
                            <input type="file" name="avatar" id="file-input" accept="image/*" onchange="updateavatar()" hidden>
                        </div>
                        @error('avatar')<p class="!m-0 !mb-3 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                        <p id="avatar-client-error" class="!m-0 !mb-3 !text-xs !text-rose-600" role="alert" hidden></p>
                        <h2 class="!m-0 !break-words !text-xl !font-semibold !leading-7 {{ $profileHeading }}">{{ $profileName ?: 'My Profile' }}</h2>
                        <span class="!mt-3 !inline-flex !rounded-full !bg-[#efedff] !px-3 !py-1 !text-xs !font-semibold !text-[#635bff]">{{ $profileRole }}</span>
                        <p class="!m-0 !mt-5 !text-sm !font-medium !leading-6 {{ $profileCopy }}">{{ $profileUser->position_name() ?: 'No position assigned' }}</p>
                        <p class="!m-0 !mt-1 !text-xs !leading-6 {{ $profileMuted }}">{{ $profileUser->staff_name() ?: 'No staff/office assigned' }}</p>
                        <div class="!mt-5 !border-0 !border-t !border-solid !pt-5 {{ $profileDivider }}">
                            <p class="!m-0 !text-xs !font-semibold {{ $profileMuted }}">Account email</p><p class="!m-0 !mt-2 !break-all !text-sm {{ $profileCopy }}">{{ $profileUser->email }}</p>
                        </div>
                        <p class="!m-0 !mt-5 !text-xs !leading-6 {{ $profileMuted }}">Keep your contact details and account preferences up to date.</p>
                        <nav class="profile-section-nav" aria-label="Profile sections">
                            <a href="#profile-identity-title" aria-current="location"><i class="fa fa-user-o" aria-hidden="true"></i><span>Account information</span><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                            <a href="#profile-assignment-title"><i class="fa fa-building-o" aria-hidden="true"></i><span>Organization</span><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                            <a href="#profile-password-title"><i class="fa fa-lock" aria-hidden="true"></i><span>Password</span><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                            <a href="#profile-security-title"><i class="fa fa-shield" aria-hidden="true"></i><span>Security & settings</span><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                        </nav>
                    </div>
                </aside>
                <div class="profile-editor !min-w-0 !overflow-hidden !rounded-2xl !border !border-solid {{ $profileSurface }}">
                    <div class="profile-editor-heading !border-0 !border-b !border-solid !px-5 !py-5 md:!px-7 {{ $profileDivider }}">
                        <h2 class="!m-0 !text-xl !font-semibold {{ $profileHeading }}">Profile & account settings</h2><p class="!m-0 !mt-2 !text-sm !leading-6 {{ $profileMuted }}">Manage your contact details, organization, and account security.</p>
                    </div>
                    <section class="!border-0 !border-b !border-solid !p-5 md:!p-7 {{ $profileDivider }}" aria-labelledby="profile-identity-title">
                        <div class="!mb-5"><h3 id="profile-identity-title" class="!m-0 !text-base !font-semibold {{ $profileHeading }}">Account information</h3><p class="!m-0 !mt-2 !text-xs !leading-6 {{ $profileMuted }}">Your name and email are managed by your administrator.</p></div>
                        <div class="!grid !grid-cols-1 !gap-5 md:!grid-cols-3">
                            <div class=""><label for="firstname" class="{{ $profileLabel }} {{ $profileCopy }}">First name</label><input id="firstname" name="firstname" type="text" value="{{ old('firstname', $profileUser->firstname) }}" class="{{ $profileControl }} {{ $profileControlTheme }}" readonly @error('firstname') aria-invalid="true" aria-describedby="error-firstname" @enderror>@error('firstname')<p id="error-firstname" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                            <div class=""><label for="middlename" class="{{ $profileLabel }} {{ $profileCopy }}">Middle name</label><input id="middlename" name="middlename" type="text" value="{{ old('middlename', $profileUser->middlename) }}" class="{{ $profileControl }} {{ $profileControlTheme }}" readonly @error('middlename') aria-invalid="true" aria-describedby="error-middlename" @enderror>@error('middlename')<p id="error-middlename" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                            <div class=""><label for="lastname" class="{{ $profileLabel }} {{ $profileCopy }}">Last name</label><input id="lastname" name="lastname" type="text" value="{{ old('lastname', $profileUser->lastname) }}" class="{{ $profileControl }} {{ $profileControlTheme }}" readonly @error('lastname') aria-invalid="true" aria-describedby="error-lastname" @enderror>@error('lastname')<p id="error-lastname" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                            <div class="md:!col-span-2"><label for="email" class="{{ $profileLabel }} {{ $profileCopy }}">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $profileUser->email) }}" class="{{ $profileControl }} {{ $profileControlTheme }}" readonly @error('email') aria-invalid="true" aria-describedby="error-email" @enderror>@error('email')<p id="error-email" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                            <div class=""><label for="phone" class="{{ $profileLabel }} {{ $profileCopy }}">Phone number</label><input id="phone" name="phone" type="tel" value="{{ old('phone', $profileUser->phone) }}" class="{{ $profileControl }} {{ $profileControlTheme }}" placeholder="+63 9XX XXX XXXX" @error('phone') aria-invalid="true" aria-describedby="error-phone" @enderror>@error('phone')<p id="error-phone" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                            <div><label for="gender" class="{{ $profileLabel }} {{ $profileCopy }}">Gender</label><select name="gender" id="gender" class="hide-search {{ $profileControl }} {{ $profileControlTheme }}" autocomplete="off" @error('gender') aria-invalid="true" aria-describedby="error-gender" @enderror><option value="">Select Gender</option><option value="Male" {{ old('gender', $profileUser->gender) == 'Male' ? 'selected' : '' }}>Male</option><option value="Female" {{ old('gender', $profileUser->gender) == 'Female' ? 'selected' : '' }}>Female</option></select>@error('gender')<p id="error-gender" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                            <div><label for="birthday" class="{{ $profileLabel }} {{ $profileCopy }}">Birth date</label><input id="birthday" name="birthday" type="date" value="{{ $profileBirthday }}" class="{{ $profileControl }} {{ $profileControlTheme }}" @error('birthday') aria-invalid="true" aria-describedby="error-birthday" @enderror>@error('birthday')<p id="error-birthday" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                            <div><label for="position_id" class="{{ $profileLabel }} {{ $profileCopy }}">Position</label><select name="position_id" id="position_id" class="{{ $profileControl }} {{ $profileControlTheme }}" autocomplete="off" @error('position_id') aria-invalid="true" aria-describedby="error-position" @enderror><option value="">Select Position</option>@foreach ($positions as $position)<option value="{{ $position->id }}" {{ old('position_id', $profileUser->position_id) == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>@endforeach</select>@error('position_id')<p id="error-position" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                        </div>
                    </section>
                    <section class="!border-0 !border-b !border-solid !p-5 md:!p-7 {{ $profileDivider }}" aria-labelledby="profile-assignment-title">
                        <div class="!mb-5"><h3 id="profile-assignment-title" class="!m-0 !text-base !font-semibold {{ $profileHeading }}">Organizational assignment</h3><p class="!m-0 !mt-2 !text-xs !leading-6 {{ $profileMuted }}">Your agency, staff/office, division, and office location.</p></div>
                        <div class="!grid !grid-cols-1 !gap-5 md:!grid-cols-2">
                            <div class="md:!col-span-2">
                                <label for="{{ $canEditAgency ? 'agency_id' : 'agency' }}" class="{{ $profileLabel }} {{ $profileCopy }}">Agency</label>
                                @if ($canEditAgency)
                                    <select name="agency_id" id="agency_id" class="{{ $profileControl }} {{ $profileControlTheme }}" autocomplete="off" onchange="updateStaffDivisionFields()" @error('agency_id') aria-invalid="true" aria-describedby="error-agency" @enderror><option value="">Select Agency</option>@foreach ($agencies as $agency)<option value="{{ $agency->id }}" {{ old('agency_id', $profileUser->agency_id) == $agency->id ? 'selected' : '' }}>{{ $agency->display_name }}</option>@endforeach</select>
                                @else
                                    <input id="agency" name="agency" type="text" disabled value="{{ old('agency', optional($profileUser->agency)->display_name) }}" class="{{ $profileControl }} {{ $profileControlTheme }}">
                                @endif
                                @error('agency_id')<p id="error-agency" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div id="staffField" @if (!$showStaffDivision) hidden @endif>
                                <label for="{{ $canEditAssignment ? 'staff_id' : 'staff' }}" class="{{ $profileLabel }} {{ $profileCopy }}">Staff / Office</label>
                                @if ($canEditAssignment)
                                    <select name="staff_id" id="staff_id" class="{{ $profileControl }} {{ $profileControlTheme }}" autocomplete="off" onchange="ocStaff()" @error('staff_id') aria-invalid="true" aria-describedby="error-staff" @enderror><option value="">Select Staff / Office</option>@foreach ($staffs as $staff)<option value="{{ $staff->id }}" {{ old('staff_id', $profileUser->staff_id) == $staff->id ? 'selected' : '' }}>{{ $staff->name }}@if ($staff->abbreviation) ({{ $staff->abbreviation }}) @endif</option>@endforeach</select>
                                @else
                                    <input id="staff" name="staff" type="text" disabled value="{{ old('staff', optional($profileUser->staff)->name) }}" class="{{ $profileControl }} {{ $profileControlTheme }}">
                                @endif
                                @error('staff_id')<p id="error-staff" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div id="divisionField" @if (!$showStaffDivision) hidden @endif>
                                <label for="{{ $canEditAssignment ? 'division_id' : 'division' }}" class="{{ $profileLabel }} {{ $profileCopy }}">Division</label>
                                @if ($canEditAssignment)
                                    <select name="division_id" id="division_id" class="{{ $profileControl }} {{ $profileControlTheme }}" autocomplete="off" @error('division_id') aria-invalid="true" aria-describedby="error-division" @enderror><option value="">Select Division</option>@foreach ($divisions as $division)<option value="{{ $division->id }}" data-name="{{ $division->name }}" data-abbreviation="{{ $division->abbreviation }}" {{ old('division_id', $profileUser->division_id) == $division->id ? 'selected' : '' }}>{{ $division->name }}@if ($division->abbreviation) ({{ $division->abbreviation }}) @endif</option>@endforeach</select>
                                @else
                                    <input id="division" name="division" type="text" disabled value="{{ old('division', optional($profileUser->division)->name) }}" class="{{ $profileControl }} {{ $profileControlTheme }}">
                                @endif
                                @error('division_id')<p id="error-division" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:!col-span-2"><label for="location" class="{{ $profileLabel }} {{ $profileCopy }}">Office location</label><input id="location" name="location" type="text" value="{{ old('location', $profileUser->location) }}" placeholder="Office location" class="{{ $profileControl }} {{ $profileControlTheme }}" @error('location') aria-invalid="true" aria-describedby="error-location" @enderror>@error('location')<p id="error-location" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                        </div>
                    </section>
                    <section class="!border-0 !border-b !border-solid !p-5 md:!p-7 {{ $profileDivider }}" aria-labelledby="profile-password-title">
                        <div class="!mb-5"><h3 id="profile-password-title" class="!m-0 !text-base !font-semibold {{ $profileHeading }}">Change password</h3><p class="!m-0 !mt-2 !text-xs !leading-6 {{ $profileMuted }}">Leave these fields blank to keep your current password.</p></div>
                        <div class="!grid !grid-cols-1 !gap-5 lg:!grid-cols-3">
                            <div><label for="old-password" class="{{ $profileLabel }} {{ $profileCopy }}">Current password</label><input id="old-password" name="old-password" type="password" autocomplete="current-password" placeholder="Current password" class="{{ $profileControl }} {{ $profileControlTheme }}" @error('old-password') aria-invalid="true" aria-describedby="error-old-password" @enderror>@error('old-password')<p id="error-old-password" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                            <div><label for="new-password" class="{{ $profileLabel }} {{ $profileCopy }}">New password</label><input id="new-password" name="new-password" type="password" autocomplete="new-password" placeholder="New password" class="{{ $profileControl }} {{ $profileControlTheme }}" @error('new-password') aria-invalid="true" aria-describedby="error-new-password" @enderror>@error('new-password')<p id="error-new-password" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                            <div><label for="confirm-password" class="{{ $profileLabel }} {{ $profileCopy }}">Confirm password</label><input id="confirm-password" name="confirm-password" type="password" autocomplete="new-password" placeholder="Confirm password" class="{{ $profileControl }} {{ $profileControlTheme }}" @error('confirm-password') aria-invalid="true" aria-describedby="error-confirm-password" @enderror>@error('confirm-password')<p id="error-confirm-password" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror</div>
                        </div>
                    </section>
                    <section class="!border-0 !border-b !border-solid !p-5 md:!p-7 {{ $profileDivider }}" aria-labelledby="profile-security-title">
                        <div class="!mb-5"><h3 id="profile-security-title" class="!m-0 !text-base !font-semibold {{ $profileHeading }}">Security & settings</h3><p class="!m-0 !mt-2 !text-xs !leading-6 {{ $profileMuted }}">Manage your account security preferences.</p></div>
                        @include('users.components.settings', ['user' => $profileUser])
                    </section>
                    <section id="divTrustedDevices" class="!border-0 !border-b !border-solid !p-5 md:!p-7 {{ $profileDivider }}" @if ($profileUser->twofactor != 'Y') hidden @endif aria-labelledby="profile-devices-title">
                        <div class="!mb-5"><h3 id="profile-devices-title" class="!m-0 !text-base !font-semibold {{ $profileHeading }}">Trusted devices</h3><p class="!m-0 !mt-2 !text-xs !leading-6 {{ $profileMuted }}">Review devices currently trusted for your account.</p></div>
                        @include('users.components.sessions', ['user' => $profileUser])
                    </section>
                    <div class="profile-actions !flex !flex-wrap !items-center !justify-between !gap-4 !p-5 md:!px-7">
                        <p id="profile-submit-error" class="!m-0 !text-xs !text-rose-600" role="alert" hidden></p>
                        <span class="!text-xs {{ $profileMuted }}">Review your changes before saving.</span>
                        <div class="!flex !flex-wrap !items-center !gap-3">
                            <button type="button" id="btnCancel" onclick="occancel()" class="!inline-flex !min-h-[44px] !items-center !justify-center !rounded-lg !border !border-solid !px-5 !py-2.5 !font-sans !text-sm !font-semibold focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#8276ef] disabled:!opacity-60 {{ $profileSurface }} {{ $profileCopy }}">Cancel</button>
                            <button type="button" id="btnSave" onclick="ocSubmit()" class="!inline-flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !border-0 !bg-[#635bff] !px-5 !py-2.5 !font-sans !text-sm !font-semibold !text-white hover:!bg-[#5146d7] focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#8276ef] disabled:!opacity-60"><i class="fa fa-save" aria-hidden="true"></i><span>Save changes</span></button>
                            <button type="button" id="btnSaveDisabled" disabled hidden class="!inline-flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !border-0 !bg-[#635bff] !px-5 !py-2.5 !font-sans !text-sm !font-semibold !text-white !opacity-70"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span role="status">Saving…</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@include('layouts.footers.auth.footer')
@endsection
@push('css')
<style>
#direk-profile{--profile-bg:#fff;--profile-subtle:#f6f7fb;--profile-border:#e8ebf2;--profile-heading:#172033;--profile-copy:#637086;--profile-accent:#635bff;--profile-accent-bg:#efedff;color:var(--profile-heading)}
#direk-profile[data-theme="dark"]{--profile-bg:#192130;--profile-subtle:#151c29;--profile-border:#2c3546;--profile-heading:#eef2fb;--profile-copy:#aab5c8;--profile-accent:#a19cff;--profile-accent-bg:#353052}
#direk-profile [hidden]{display:none!important}
#direk-profile .profile-page-intro{display:flex;align-items:center;justify-content:space-between;gap:20px;margin:3px 0 24px}
#direk-profile .profile-eyebrow{margin:0;color:var(--profile-copy);font-size:10px;font-weight:650;letter-spacing:.13em}
#direk-profile .profile-page-intro h2{margin:7px 0 0;color:var(--profile-heading);font-size:clamp(24px,2.5vw,32px);line-height:1.25;letter-spacing:-.9px;font-weight:650}
#direk-profile .profile-page-intro p:last-child{margin:9px 0 0;color:var(--profile-copy);font-size:12px;line-height:1.8}
#direk-profile .profile-context-pill{display:inline-flex;align-items:center;gap:8px;flex-shrink:0;padding:10px 13px;border:1px solid var(--profile-border);border-radius:11px;background:var(--profile-bg);color:var(--profile-copy);font-size:11px}
#direk-profile .profile-context-pill i{color:var(--profile-accent)}
#direk-profile .profile-summary,#direk-profile .profile-editor{border-color:var(--profile-border)!important;border-radius:18px!important;box-shadow:0 7px 25px rgba(28,32,70,.035)}
#direk-profile .profile-summary-banner{min-height:108px;background:linear-gradient(120deg,#262441,#6356bf)!important;position:relative;overflow:hidden;padding-top:21px!important}
#direk-profile .profile-summary-banner::after{content:"";position:absolute;width:150px;height:150px;border:1px solid rgba(255,255,255,.16);border-radius:50%;top:-80px;right:-20px}
#direk-profile .profile-summary-banner p{font-size:9px!important;letter-spacing:.12em!important}
#direk-profile .profile-summary-content{position:relative}
#direk-profile .profile-avatar-wrap{margin-top:-59px;z-index:1}
#direk-profile #avatar-preview{border:5px solid var(--profile-bg);border-radius:24px!important;background:var(--profile-bg);box-shadow:0 8px 22px rgba(23,25,55,.12)!important}
#direk-profile .profile-avatar-button{width:34px!important;height:34px!important;bottom:-1px!important;right:-1px!important;border:3px solid var(--profile-bg)!important;border-radius:11px!important;background:var(--profile-accent)!important;color:#fff!important;box-shadow:0 4px 12px rgba(44,33,105,.15)!important;font-size:12px}
#direk-profile[data-theme="dark"] .profile-avatar-button{color:#192130!important}
#direk-profile .profile-summary-content>h2{font-size:20px!important;font-weight:650!important;letter-spacing:-.5px}
#direk-profile .profile-summary-content>span{background:var(--profile-accent-bg)!important;color:var(--profile-accent)!important;font-size:10px!important;padding:5px 10px!important}
#direk-profile .profile-section-nav{display:flex;flex-direction:column;gap:5px;margin-top:22px;padding-top:18px;border-top:1px solid var(--profile-border)}
#direk-profile .profile-section-nav a{display:flex;align-items:center;gap:10px;padding:11px 10px;min-height:40px;border-radius:10px;text-decoration:none;color:var(--profile-copy);font-size:11px;line-height:1.5;transition:background .15s,color .15s}
#direk-profile .profile-section-nav a>i:first-child{width:16px;text-align:center;font-size:14px}
#direk-profile .profile-section-nav a>.fa-angle-right{margin-left:auto;font-size:10px}
#direk-profile .profile-section-nav a:hover{background:var(--profile-subtle);color:var(--profile-heading)}
#direk-profile .profile-section-nav a[aria-current="location"]{background:var(--profile-accent-bg);color:var(--profile-accent);font-weight:600}
#direk-profile .profile-section-nav a:focus-visible{outline:2px solid var(--profile-accent);outline-offset:2px}
#direk-profile .profile-editor-heading{background:var(--profile-subtle);padding-top:25px!important;padding-bottom:25px!important;border-color:var(--profile-border)!important}
#direk-profile .profile-editor-heading h2{font-size:21px!important;letter-spacing:-.6px;font-weight:650!important}
#direk-profile .profile-editor-heading p{font-size:12px!important}
#direk-profile .profile-editor>section{border-color:var(--profile-border)!important;padding-top:28px!important;padding-bottom:28px!important}
#direk-profile .profile-editor h3{display:flex;align-items:center;gap:10px;scroll-margin-top:28px;font-size:15px!important;font-weight:650!important;letter-spacing:-.2px}
#direk-profile .profile-editor h3::before{display:grid;place-items:center;width:26px;height:26px;border-radius:8px;background:var(--profile-accent-bg);color:var(--profile-accent);font-size:10px;letter-spacing:0;flex-shrink:0}
#direk-profile #profile-identity-title::before{content:"01"}
#direk-profile #profile-assignment-title::before{content:"02"}
#direk-profile #profile-password-title::before{content:"03"}
#direk-profile #profile-security-title::before{content:"04"}
#direk-profile #profile-devices-title::before{content:"05"}
#direk-profile .profile-editor label{font-weight:550!important;font-size:11px!important}
#direk-profile .profile-editor input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),#direk-profile .profile-editor select{border-radius:10px!important;border-color:var(--profile-border)!important;min-height:44px!important;font-size:13px!important}
#direk-profile .profile-editor input:focus,#direk-profile .profile-editor select:focus{border-color:var(--profile-accent)!important;box-shadow:0 0 0 3px rgba(99,91,255,.12)!important}
#direk-profile .profile-editor input[readonly],#direk-profile .profile-editor input:disabled{background:var(--profile-subtle)!important;color:var(--profile-copy)!important}
#direk-profile .ts-wrapper .ts-control{min-height:44px!important;border-radius:10px!important;border-color:var(--profile-border)!important;font-size:13px!important;box-shadow:none!important}
#direk-profile .ts-wrapper.focus .ts-control{border-color:var(--profile-accent)!important;box-shadow:0 0 0 3px rgba(99,91,255,.12)!important}
#direk-profile .ts-dropdown{border-radius:10px!important;box-shadow:0 12px 28px rgba(28,32,70,.1)!important}
#direk-profile .ts-dropdown .active{background:var(--profile-accent-bg)!important;color:var(--profile-heading)!important}
#direk-profile .profile-actions{background:var(--profile-subtle);border-top:1px solid var(--profile-border);padding-top:19px!important;padding-bottom:19px!important}
#direk-profile .profile-actions #btnSave{background:#635bff!important;border-radius:10px!important;box-shadow:0 5px 12px rgba(99,91,255,.16);font-size:12px!important}
#direk-profile .profile-actions #btnSave:hover{background:#5146d7!important}
#direk-profile .profile-actions #btnCancel{border-color:var(--profile-border)!important;border-radius:10px!important;font-size:12px!important}
#direk-profile .profile-actions #btnCancel:hover{background:var(--profile-accent-bg)!important}
#direk-profile .profile-actions #btnSaveDisabled{background:#635bff!important;border-radius:10px!important;font-size:12px!important}
@media(min-width:1200px){#direk-profile .profile-summary{position:sticky;top:24px}}
@media(max-width:760px){#direk-profile .profile-page-intro{align-items:flex-start;flex-direction:column;gap:14px}#direk-profile .profile-section-nav{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}#direk-profile .profile-context-pill{font-size:10px}#direk-profile .profile-actions>div{width:100%}#direk-profile .profile-actions #btnSave,#direk-profile .profile-actions #btnCancel,#direk-profile .profile-actions #btnSaveDisabled{flex:1}}
@media(max-width:400px){#direk-profile .profile-section-nav{grid-template-columns:minmax(0,1fr)}}
@media(prefers-reduced-motion:reduce){#direk-profile .profile-section-nav a{transition:none!important}}
</style>
@endpush
@push('js')
@include('users.components.scripts')
<script>
    const divisions = {{ \Illuminate\Support\Js::from($divisions) }};
    const depDevAgencyIds = {{ \Illuminate\Support\Js::from($profileDepdevIds) }};
    const currentAgencyId = {{ \Illuminate\Support\Js::from($profileCurrentAgencyId) }};
    let profileSaving = false;
    initTomSelect('gender');
    initTomSelect('position_id', true);
    @if ($canEditAgency)
        initTomSelect('agency_id', true);
    @endif
    @if ($canEditAssignment)
        initTomSelect('staff_id', false);
        const customRender = {
            option: function (data, escape) {
                return `<div>${escape(data.name || data.text || '')}${data.abbreviation ? ' (' + escape(data.abbreviation) + ')' : ''}</div>`;
            },
            item: function (data, escape) {
                return `<div>${escape(data.name || data.text || '')}${data.abbreviation ? ' (' + escape(data.abbreviation) + ')' : ''}</div>`;
            }
        };
        initTomSelect('division_id', true, false, false, null, null, customRender);
    @endif
    updateStaffDivisionFields();
    function resetProfileSaveState() {
        profileSaving = false;
        document.getElementById('btnSave').hidden = false;
        document.getElementById('btnSave').disabled = false;
        document.getElementById('btnCancel').disabled = false;
        document.getElementById('btnSaveDisabled').hidden = true;
        document.getElementById('frmUpdate').removeAttribute('aria-busy');
    }
    function ocSubmit() {
        const form = document.getElementById('frmUpdate');
        if (profileSaving || !form.reportValidity()) return;
        document.getElementById('profile-submit-error').hidden = true;
        profileSaving = true;
        form.setAttribute('aria-busy', 'true');
        document.getElementById('btnSave').hidden = true;
        document.getElementById('btnSave').disabled = true;
        document.getElementById('btnCancel').disabled = true;
        document.getElementById('btnSaveDisabled').hidden = false;
        try {
            // Native submission bypasses this page's submit listener, avoiding recursion.
            HTMLFormElement.prototype.submit.call(form);
        } catch (error) {
            resetProfileSaveState();
            const message = document.getElementById('profile-submit-error');
            message.textContent = 'We could not submit your changes. Please try again.';
            message.hidden = false;
        }
    }
    document.getElementById('frmUpdate').addEventListener('submit', function (event) {
        event.preventDefault();
        ocSubmit();
    });
    window.addEventListener('pageshow', resetProfileSaveState);
    function updateavatar() {
        const fileInput = document.getElementById('file-input');
        const message = document.getElementById('avatar-client-error');
        const file = fileInput.files && fileInput.files[0];
        message.hidden = true;
        if (!file) return;
        if (file.type && !file.type.startsWith('image/')) {
            fileInput.value = '';
            message.textContent = 'Please choose an image for your profile photo.';
            message.hidden = false;
            return;
        }
        const reader = new FileReader();
        reader.onload = function (event) { document.getElementById('avatar-preview').src = event.target.result; };
        reader.onerror = function () {
            message.textContent = 'We could not preview this image. Please choose it again.';
            message.hidden = false;
        };
        reader.readAsDataURL(file);
    }
    function occancel() {
        if (!profileSaving && confirm('Discard unsaved changes?')) location.reload();
    }
    function ocStaff() {
        if (typeof tomSelects === 'undefined' || !tomSelects['staff_id'] || !tomSelects['division_id']) return;
        const staffControl = tomSelects['staff_id'];
        const divisionControl = tomSelects['division_id'];
        const staffId = String(staffControl.getValue() || '');
        const oldDivisionId = String(divisionControl.getValue() || '');
        divisionControl.clear(true);
        divisionControl.clearOptions();
        const options = divisions.filter(function (item) { return staffId && String(item.staff_id) === staffId; }).map(function (item) {
            return { value: String(item.id), text: item.name + (item.abbreviation ? ' (' + item.abbreviation + ')' : ''), name: item.name, abbreviation: item.abbreviation || '' };
        });
        divisionControl.addOptions(options);
        if (options.some(function (option) { return option.value === oldDivisionId; })) divisionControl.setValue(oldDivisionId, true);
    }
    function updateStaffDivisionFields() {
        const agencyElement = document.getElementById('agency_id');
        const agencyId = agencyElement ? String(agencyElement.value || '') : currentAgencyId;
        const show = depDevAgencyIds.includes(String(agencyId));
        document.getElementById('staffField').hidden = !show;
        document.getElementById('divisionField').hidden = !show;
        if (typeof tomSelects === 'undefined') return;
        if (!show) {
            if (tomSelects['staff_id']) tomSelects['staff_id'].clear(true);
            if (tomSelects['division_id']) {
                tomSelects['division_id'].clear(true);
                tomSelects['division_id'].clearOptions();
            }
        } else {
            ocStaff();
        }
    }
</script>
<script>
(function () {
    const root = document.getElementById('direk-profile');
    if (!root) return;
    const links = Array.from(root.querySelectorAll('.profile-section-nav a'));
    links.forEach(function (link) {
        link.addEventListener('click', function () {
            links.forEach(function (item) { item.removeAttribute('aria-current'); });
            link.setAttribute('aria-current', 'location');
        });
    });
})();
</script>
@endpush

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
            <div>
                <p class="profile-eyebrow">ACCOUNT CENTER</p>
                <h2>Your identity. <br> Your workspace.</h2>
                <p>Keep your details current, personalize your workspace, and manage your account in one place.</p>
            </div>
            <div class="profile-cover-art" aria-hidden="true">
                <span class="profile-cover-orbit"></span>
                <span class="profile-cover-orbit profile-cover-orbit-two"></span>
                <span class="profile-cover-symbol"><i class="fa fa-user-o"></i></span>
                <span class="profile-cover-tag"><i class="fa fa-sliders"></i> Personal settings</span>
            </div>
        </div>
        @if ($errors->any())
            <div class="!mb-5 !rounded-xl !border !border-solid !border-rose-200 !bg-rose-50 !px-5 !py-4 !text-sm !text-rose-800" role="alert">
                <div class="!flex !items-start !gap-3">
                    <i class="fa fa-exclamation-circle !mt-1" aria-hidden="true"></i>
                    <div>
                        <p class="!m-0 !font-semibold">Please review the information below.</p>
                        <ul class="!m-0 !mt-2 !list-disc !space-y-1 !pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        <form autocomplete="off" method="POST" action="{{ route('user-profile.perform') }}" enctype="multipart/form-data" id="frmUpdate">
            @csrf
            <div class="profile-layout !grid !items-start !gap-6 xl:!grid-cols-[280px_minmax(0,1fr)]">
                {{-- Account summary and profile photo --}}
                <aside class="profile-summary !overflow-hidden !rounded-2xl !border !border-solid {{ $profileSurface }}" aria-label="Profile summary">
                    <div class="profile-summary-banner !px-6 !py-5">
                        <p class="!m-0 !text-xs !font-semibold !uppercase !tracking-[0.12em] !text-white">Your DIREK account</p>
                    </div>
                    <div class="profile-summary-content !p-6">
                        <div class="profile-avatar-wrap !relative !mb-5 !h-24 !w-24">
                            <img id="avatar-preview" src="{{ $profileUser->avatarUrl() }}" width="96" height="96" alt="Your profile photo" class="!h-24 !w-24 !rounded-2xl !object-cover !shadow-sm">
                            <button type="button" onclick="document.getElementById('file-input').click()" aria-label="Change profile photo" title="Change profile photo" class="profile-avatar-button !absolute !-bottom-2 !-right-2 !grid !h-11 !w-11 !place-items-center !rounded-xl !border !border-solid !border-slate-200 !bg-white !text-[#172033] !shadow-sm hover:!bg-slate-50 focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#8276ef]"><i class="fa fa-camera" aria-hidden="true"></i></button>
                            <input type="file" name="avatar" id="file-input" accept="image/*" onchange="updateavatar()" hidden>
                        </div>
                        @error('avatar')
                            <p class="!m-0 !mb-3 !text-xs !text-rose-600">{{ $message }}</p>
                        @enderror
                        <p id="avatar-client-error" class="!m-0 !mb-3 !text-xs !text-rose-600" role="alert" hidden></p>
                        <h2 class="!m-0 !break-words !text-xl !font-semibold !leading-7 {{ $profileHeading }}">{{ $profileName ?: 'My Profile' }}</h2>
                        <span class="!mt-3 !inline-flex !rounded-full !bg-[#efedff] !px-3 !py-1 !text-xs !font-semibold !text-[#635bff]">{{ $profileRole }}</span>
                        <p class="!m-0 !mt-5 !text-sm !font-medium !leading-6 {{ $profileCopy }}">{{ $profileUser->position_name() ?: 'No position assigned' }}</p>
                        <p class="!m-0 !mt-1 !text-xs !leading-6 {{ $profileMuted }}">{{ $profileUser->staff_name() ?: 'No staff/office assigned' }}</p>
                        <div class="!mt-5 !border-0 !border-t !border-solid !pt-5 {{ $profileDivider }}">
                            <p class="!m-0 !text-xs !font-semibold {{ $profileMuted }}">Account email</p>
                            <p class="!m-0 !mt-2 !break-all !text-sm {{ $profileCopy }}">{{ $profileUser->email }}</p>
                        </div>
                        <p class="!m-0 !mt-5 !text-xs !leading-6 {{ $profileMuted }}">Keep your contact details and account preferences up to date.</p>
                        <nav class="profile-section-nav" aria-label="Profile sections">
                            <a href="#profile-identity-title" aria-current="location"><i class="fa fa-user-o" aria-hidden="true"></i> <span> Account information </span> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                            <a href="#profile-assignment-title"><i class="fa fa-building-o" aria-hidden="true"></i> <span> Organization </span> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                            <a href="#profile-password-title"><i class="fa fa-lock" aria-hidden="true"></i> <span> Password </span> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                            <a href="#profile-security-title"><i class="fa fa-shield" aria-hidden="true"></i> <span> Security & settings </span> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                        </nav>
                    </div>
                </aside>
                <div class="profile-editor !min-w-0 !overflow-hidden !rounded-2xl !border !border-solid {{ $profileSurface }}">
                    <div class="profile-editor-heading !border-0 !border-b !border-solid !px-5 !py-5 md:!px-7 {{ $profileDivider }}">
                        <h2 class="!m-0 !text-xl !font-semibold {{ $profileHeading }}">Profile & account settings</h2>
                        <p class="!m-0 !mt-2 !text-sm !leading-6 {{ $profileMuted }}">Manage your contact details, organization, and account security.</p>
                    </div>
                    <section class="!border-0 !border-b !border-solid !p-5 md:!p-7 {{ $profileDivider }}" aria-labelledby="profile-identity-title">
                        <div class="!mb-5">
                            <h3 id="profile-identity-title" class="!m-0 !text-base !font-semibold {{ $profileHeading }}">Account information</h3>
                            <p class="!m-0 !mt-2 !text-xs !leading-6 {{ $profileMuted }}">Your name and email are managed by your administrator.</p>
                        </div>
                        <div class="profile-fields-three !grid !grid-cols-1 !gap-5 md:!grid-cols-3">
                            <div class="">
                                <label for="firstname" class="{{ $profileLabel }} {{ $profileCopy }}">First name</label>
                                <input id="firstname" name="firstname" type="text" value="{{ old('firstname', $profileUser->firstname) }}" class="{{ $profileControl }} {{ $profileControlTheme }}" readonly @error('firstname') aria-invalid="true" aria-describedby="error-firstname" @enderror>
                                @error('firstname')
                                    <p id="error-firstname" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="">
                                <label for="middlename" class="{{ $profileLabel }} {{ $profileCopy }}">Middle name</label>
                                <input id="middlename" name="middlename" type="text" value="{{ old('middlename', $profileUser->middlename) }}" class="{{ $profileControl }} {{ $profileControlTheme }}" readonly @error('middlename') aria-invalid="true" aria-describedby="error-middlename" @enderror>
                                @error('middlename')
                                    <p id="error-middlename" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="">
                                <label for="lastname" class="{{ $profileLabel }} {{ $profileCopy }}">Last name</label>
                                <input id="lastname" name="lastname" type="text" value="{{ old('lastname', $profileUser->lastname) }}" class="{{ $profileControl }} {{ $profileControlTheme }}" readonly @error('lastname') aria-invalid="true" aria-describedby="error-lastname" @enderror>
                                @error('lastname')
                                    <p id="error-lastname" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="md:!col-span-2">
                                <label for="email" class="{{ $profileLabel }} {{ $profileCopy }}">Email address</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $profileUser->email) }}" class="{{ $profileControl }} {{ $profileControlTheme }}" readonly @error('email') aria-invalid="true" aria-describedby="error-email" @enderror>
                                @error('email')
                                    <p id="error-email" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="">
                                <label for="phone" class="{{ $profileLabel }} {{ $profileCopy }}">Phone number</label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone', $profileUser->phone) }}" class="{{ $profileControl }} {{ $profileControlTheme }}" placeholder="+63 9XX XXX XXXX" @error('phone') aria-invalid="true" aria-describedby="error-phone" @enderror>
                                @error('phone')
                                    <p id="error-phone" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="gender" class="{{ $profileLabel }} {{ $profileCopy }}">Gender</label>
                                <select name="gender" id="gender" class="hide-search {{ $profileControl }} {{ $profileControlTheme }}" autocomplete="off" @error('gender') aria-invalid="true" aria-describedby="error-gender" @enderror>
                                    <option value="">
                                        Select Gender
                                    </option>
                                    <option value="Male" {{ old('gender', $profileUser->
                                        gender) == 'Male' ? 'selected' : '' }}>Male
                                    </option>
                                    <option value="Female" {{ old('gender', $profileUser->
                                        gender) == 'Female' ? 'selected' : '' }}>Female
                                    </option>
                                </select>
                                @error('gender')
                                    <p id="error-gender" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="birthday" class="{{ $profileLabel }} {{ $profileCopy }}">Birth date</label>
                                <input id="birthday" name="birthday" type="date" value="{{ $profileBirthday }}" class="{{ $profileControl }} {{ $profileControlTheme }}" @error('birthday') aria-invalid="true" aria-describedby="error-birthday" @enderror>
                                @error('birthday')
                                    <p id="error-birthday" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="position_id" class="{{ $profileLabel }} {{ $profileCopy }}">Position</label>
                                <select name="position_id" id="position_id" class="{{ $profileControl }} {{ $profileControlTheme }}" autocomplete="off" @error('position_id') aria-invalid="true" aria-describedby="error-position" @enderror>
                                    <option value="">
                                        Select Position
                                    </option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}" {{ old('position_id', $profileUser->
                                            position_id) == $position->id ? 'selected' : '' }}>{{ $position->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id')
                                    <p id="error-position" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                    <section class="!border-0 !border-b !border-solid !p-5 md:!p-7 {{ $profileDivider }}" aria-labelledby="profile-assignment-title">
                        <div class="!mb-5">
                            <h3 id="profile-assignment-title" class="!m-0 !text-base !font-semibold {{ $profileHeading }}">Organizational assignment</h3>
                            <p class="!m-0 !mt-2 !text-xs !leading-6 {{ $profileMuted }}">Your agency, staff/office, division, and office location.</p>
                        </div>
                        <div class="profile-fields-two !grid !grid-cols-1 !gap-5 md:!grid-cols-2">
                            <div class="md:!col-span-2">
                                <label for="{{ $canEditAgency ? 'agency_id' : 'agency' }}" class="{{ $profileLabel }} {{ $profileCopy }}">Agency</label>
                                @if ($canEditAgency)
                                    <select name="agency_id" id="agency_id" class="{{ $profileControl }} {{ $profileControlTheme }}" autocomplete="off" onchange="updateStaffDivisionFields()" @error('agency_id') aria-invalid="true" aria-describedby="error-agency" @enderror>
                                        <option value="">
                                            Select Agency
                                        </option>
                                        @foreach ($agencies as $agency)
                                            <option value="{{ $agency->id }}" {{ old('agency_id', $profileUser->
                                                agency_id) == $agency->id ? 'selected' : '' }}>{{ $agency->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input id="agency" name="agency" type="text" disabled value="{{ old('agency', optional($profileUser->agency)->display_name) }}" class="{{ $profileControl }} {{ $profileControlTheme }}">
                                @endif
                                @error('agency_id')
                                    <p id="error-agency" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div id="staffField" @if (!$showStaffDivision) hidden @endif>
                                <label for="{{ $canEditAssignment ? 'staff_id' : 'staff' }}" class="{{ $profileLabel }} {{ $profileCopy }}">Staff / Office</label>
                                @if ($canEditAssignment)
                                    <select name="staff_id" id="staff_id" class="{{ $profileControl }} {{ $profileControlTheme }}" autocomplete="off" onchange="ocStaff()" @error('staff_id') aria-invalid="true" aria-describedby="error-staff" @enderror>
                                        <option value="">
                                            Select Staff / Office
                                        </option>
                                        @foreach ($staffs as $staff)
                                            <option value="{{ $staff->id }}" {{ old('staff_id', $profileUser->
                                                staff_id) == $staff->id ? 'selected' : '' }}>{{ $staff->name }}
                                                @if ($staff->abbreviation)
                                                    ({{ $staff->abbreviation }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input id="staff" name="staff" type="text" disabled value="{{ old('staff', optional($profileUser->staff)->name) }}" class="{{ $profileControl }} {{ $profileControlTheme }}">
                                @endif
                                @error('staff_id')
                                    <p id="error-staff" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div id="divisionField" @if (!$showStaffDivision) hidden @endif>
                                <label for="{{ $canEditAssignment ? 'division_id' : 'division' }}" class="{{ $profileLabel }} {{ $profileCopy }}">Division</label>
                                @if ($canEditAssignment)
                                    <select name="division_id" id="division_id" class="{{ $profileControl }} {{ $profileControlTheme }}" autocomplete="off" @error('division_id') aria-invalid="true" aria-describedby="error-division" @enderror>
                                        <option value="">
                                            Select Division
                                        </option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}" data-name="{{ $division->name }}" data-abbreviation="{{ $division->abbreviation }}" {{ old('division_id', $profileUser->
                                                division_id) == $division->id ? 'selected' : '' }}>{{ $division->name }}
                                                @if ($division->abbreviation)
                                                    ({{ $division->abbreviation }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input id="division" name="division" type="text" disabled value="{{ old('division', optional($profileUser->division)->name) }}" class="{{ $profileControl }} {{ $profileControlTheme }}">
                                @endif
                                @error('division_id')
                                    <p id="error-division" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="md:!col-span-2">
                                <label for="location" class="{{ $profileLabel }} {{ $profileCopy }}">Office location</label>
                                <input id="location" name="location" type="text" value="{{ old('location', $profileUser->location) }}" placeholder="Office location" class="{{ $profileControl }} {{ $profileControlTheme }}" @error('location') aria-invalid="true" aria-describedby="error-location" @enderror>
                                @error('location')
                                    <p id="error-location" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                    <section class="!border-0 !border-b !border-solid !p-5 md:!p-7 {{ $profileDivider }}" aria-labelledby="profile-password-title">
                        <div class="!mb-5">
                            <h3 id="profile-password-title" class="!m-0 !text-base !font-semibold {{ $profileHeading }}">Change password</h3>
                            <p class="!m-0 !mt-2 !text-xs !leading-6 {{ $profileMuted }}">Leave these fields blank to keep your current password.</p>
                        </div>
                        <div class="profile-fields-password !grid !grid-cols-1 !gap-5 lg:!grid-cols-3">
                            <div>
                                <label for="old-password" class="{{ $profileLabel }} {{ $profileCopy }}">Current password</label>
                                <input id="old-password" name="old-password" type="password" autocomplete="current-password" placeholder="Current password" class="{{ $profileControl }} {{ $profileControlTheme }}" @error('old-password') aria-invalid="true" aria-describedby="error-old-password" @enderror>
                                @error('old-password')
                                    <p id="error-old-password" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="new-password" class="{{ $profileLabel }} {{ $profileCopy }}">New password</label>
                                <input id="new-password" name="new-password" type="password" autocomplete="new-password" placeholder="New password" class="{{ $profileControl }} {{ $profileControlTheme }}" @error('new-password') aria-invalid="true" aria-describedby="error-new-password" @enderror>
                                @error('new-password')
                                    <p id="error-new-password" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="confirm-password" class="{{ $profileLabel }} {{ $profileCopy }}">Confirm password</label>
                                <input id="confirm-password" name="confirm-password" type="password" autocomplete="new-password" placeholder="Confirm password" class="{{ $profileControl }} {{ $profileControlTheme }}" @error('confirm-password') aria-invalid="true" aria-describedby="error-confirm-password" @enderror>
                                @error('confirm-password')
                                    <p id="error-confirm-password" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                    <section class="!border-0 !border-b !border-solid !p-5 md:!p-7 {{ $profileDivider }}" aria-labelledby="profile-security-title">
                        <div class="!mb-5">
                            <h3 id="profile-security-title" class="!m-0 !text-base !font-semibold {{ $profileHeading }}">Security & settings</h3>
                            <p class="!m-0 !mt-2 !text-xs !leading-6 {{ $profileMuted }}">Manage your account security preferences.</p>
                        </div>
                        @include('users.components.settings', ['user' => $profileUser])
                    </section>
                    <section id="divTrustedDevices" class="!border-0 !border-b !border-solid !p-5 md:!p-7 {{ $profileDivider }}" @if ($profileUser->
                        twofactor != 'Y') hidden
                    @endif
                    aria-labelledby="profile-devices-title">
                    <div class="!mb-5">
                        <h3 id="profile-devices-title" class="!m-0 !text-base !font-semibold {{ $profileHeading }}">Trusted devices</h3>
                        <p class="!m-0 !mt-2 !text-xs !leading-6 {{ $profileMuted }}">Review devices currently trusted for your account.</p>
                    </div>
                    @include('users.components.sessions', ['user' => $profileUser])
                </section>
                <div class="profile-actions !flex !flex-wrap !items-center !justify-between !gap-4 !p-5 md:!px-7">
                    <p id="profile-submit-error" class="!m-0 !text-xs !text-rose-600" role="alert" hidden></p>
                    <span class="!text-xs {{ $profileMuted }}">Review your changes before saving.</span>
                    <div class="!flex !flex-wrap !items-center !gap-3">
                        <button type="button" id="btnCancel" onclick="occancel()" class="!inline-flex !min-h-[44px] !items-center !justify-center !rounded-lg !border !border-solid !px-5 !py-2.5 !font-sans !text-sm !font-semibold focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#8276ef] disabled:!opacity-60 {{ $profileSurface }} {{ $profileCopy }}">Cancel</button>
                        <button type="button" id="btnSave" onclick="ocSubmit()" class="!inline-flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !border-0 !bg-[#635bff] !px-5 !py-2.5 !font-sans !text-sm !font-semibold !text-white hover:!bg-[#5146d7] focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#8276ef] disabled:!opacity-60"><i class="fa fa-save" aria-hidden="true"></i> <span> Save changes </span></button>
                        <button type="button" id="btnSaveDisabled" disabled hidden class="!inline-flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !border-0 !bg-[#635bff] !px-5 !py-2.5 !font-sans !text-sm !font-semibold !text-white !opacity-70"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span> <span role="status"> Saving… </span></button>
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
#direk-profile{--profile-bg:#fff;--profile-subtle:#f6f5fc;--profile-border:#e1e0ef;--profile-heading:#262a43;--profile-copy:#6f7590;--profile-accent:#7761e8;--profile-accent-bg:#efeafa;--profile-edge:#d8d4eb;color:var(--profile-heading);padding:22px 24px 35px!important}
#direk-profile[data-theme="dark"]{--profile-bg:#192130;--profile-subtle:#151c29;--profile-border:#35384f;--profile-heading:#f0edff;--profile-copy:#aab1cd;--profile-accent:#b6a0ff;--profile-accent-bg:#302c48;--profile-edge:#101523}
#direk-profile *{box-sizing:border-box}#direk-profile [hidden]{display:none!important}#direk-profile .profile-page-intro{position:relative;isolation:isolate;display:flex;align-items:center;justify-content:space-between;gap:24px;margin:0 0 30px;min-height:200px;padding:30px 36px;border:1px solid #68518f;border-radius:24px;background:radial-gradient(ellipse at 85% 50%,rgba(168,120,238,.25),transparent 45%),linear-gradient(120deg,#25253f,#342650);box-shadow:inset 0 1px 0 rgba(237,211,255,.18),0 5px 0 #241c36,0 16px 30px rgba(40,29,72,.13);overflow:hidden}
#direk-profile .profile-page-intro>div:first-child{position:relative;z-index:2;max-width:590px}#direk-profile .profile-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;margin:0;color:#c7acee}#direk-profile .profile-page-intro h2{font-size:clamp(28px,3vw,40px);font-weight:650;letter-spacing:-1.1px;line-height:1.15;color:#f5efff;margin:13px 0}#direk-profile .profile-page-intro p:last-child{font-size:12px;line-height:1.8;color:#c9bddc;margin:0;max-width:470px}
#direk-profile .profile-cover-art{position:relative;width:225px;height:155px;flex-shrink:0;pointer-events:none}#direk-profile .profile-cover-orbit{position:absolute;inset:12px 17px 12px;border:1px solid rgba(196,160,255,.27);border-radius:50%;transform:rotate(-25deg) scaleY(.65)}#direk-profile .profile-cover-orbit-two{transform:rotate(32deg) scaleY(.7);inset:0 4px}#direk-profile .profile-cover-symbol{position:absolute;left:50%;top:43%;display:grid;place-items:center;width:78px;height:78px;margin:-39px;border:1px solid #b99ae8;border-radius:23px;background:linear-gradient(145deg,rgba(181,138,239,.5),rgba(115,84,175,.35));color:#e9d9ff;font-size:31px;box-shadow:inset 0 1px 0 rgba(255,255,255,.3),0 6px 0 #39234f,0 16px 30px rgba(11,6,24,.25);transform:rotate(-7deg)}#direk-profile .profile-cover-tag{position:absolute;left:50%;bottom:0;transform:translateX(-50%);display:inline-flex;align-items:center;gap:8px;white-space:nowrap;color:#e2cffa;border:1px solid #6e548e;background:#392a51;border-radius:9px;padding:8px 11px;font-size:10px;box-shadow:0 4px 0 rgba(11,6,24,.3)}
#direk-profile .profile-layout{display:grid!important;grid-template-columns:300px minmax(0,1fr);gap:26px!important;align-items:start}
#direk-profile .profile-summary{background:var(--profile-bg)!important;border:1px solid var(--profile-border)!important;border-radius:21px!important;overflow:hidden;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 5px 0 var(--profile-edge),0 16px 30px rgba(36,28,70,.05)}#direk-profile .profile-summary-banner{position:relative;min-height:117px;padding:22px 25px!important;background:linear-gradient(125deg,#3b305c,#8061b8)!important;overflow:hidden}#direk-profile .profile-summary-banner::after{content:"";position:absolute;width:155px;height:155px;right:-29px;top:-87px;border:1px solid rgba(255,255,255,.2);border-radius:50%;box-shadow:0 0 0 24px rgba(219,187,255,.04)}#direk-profile .profile-summary-banner p{margin:0;font-size:9px!important;letter-spacing:.13em!important;color:#eadcfb!important}
#direk-profile .profile-summary-content{position:relative;padding:26px!important}#direk-profile .profile-avatar-wrap{position:relative;margin-top:-65px!important;margin-bottom:21px!important;width:100px!important;height:100px!important;z-index:1}#direk-profile #avatar-preview{width:100px!important;height:100px!important;border:5px solid var(--profile-bg);border-radius:25px!important;background:var(--profile-bg);object-fit:cover;box-shadow:0 4px 0 var(--profile-edge),0 12px 20px rgba(30,22,56,.14)!important}#direk-profile .profile-avatar-button{position:absolute;width:35px!important;height:35px!important;bottom:-2px!important;right:-2px!important;border:3px solid var(--profile-bg)!important;border-radius:11px!important;background:#8067d9!important;color:#fff!important;font-size:12px;box-shadow:0 4px 9px rgba(53,32,99,.15)!important}#direk-profile .profile-summary-content>h2{font-size:21px!important;line-height:1.35;font-weight:650!important;letter-spacing:-.4px;color:var(--profile-heading)!important}#direk-profile .profile-summary-content>span{background:var(--profile-accent-bg)!important;color:var(--profile-accent)!important;font-size:10px!important;padding:6px 11px!important}
#direk-profile .profile-section-nav{display:flex;flex-direction:column;gap:7px;margin-top:23px;padding-top:20px;border-top:1px solid var(--profile-border)}#direk-profile .profile-section-nav a{display:flex;align-items:center;gap:10px;padding:11px 12px;min-height:43px;border:1px solid transparent;border-radius:10px;color:var(--profile-copy);font-size:11px;font-weight:550;line-height:1.5;text-decoration:none;transition:background .15s,color .15s}#direk-profile .profile-section-nav a>i:first-child{width:17px;text-align:center;font-size:15px}#direk-profile .profile-section-nav a>.fa-angle-right{margin-left:auto;font-size:11px}#direk-profile .profile-section-nav a:hover{background:var(--profile-subtle);color:var(--profile-heading)}#direk-profile .profile-section-nav a[aria-current="location"]{border-color:var(--profile-border);background:var(--profile-accent-bg);color:var(--profile-accent);box-shadow:0 2px 0 var(--profile-edge)}
#direk-profile .profile-editor{min-width:0;overflow:visible!important;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none}#direk-profile .profile-editor-heading{padding:4px 3px 22px!important;border:0!important;background:transparent}#direk-profile .profile-editor-heading h2{font-size:21px!important;font-weight:650;letter-spacing:-.5px;color:var(--profile-heading)!important}#direk-profile .profile-editor-heading p{font-size:12px!important;color:var(--profile-copy)!important;line-height:1.8}
#direk-profile .profile-editor>section{position:relative;background:var(--profile-bg)!important;padding:25px 27px!important;margin:0 0 23px;border:1px solid var(--profile-border)!important;border-radius:18px;box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 4px 0 var(--profile-edge),0 12px 20px rgba(36,28,70,.035);overflow:visible}#direk-profile .profile-editor h3{display:flex;align-items:center;gap:11px;scroll-margin-top:105px;font-size:16px!important;font-weight:650!important;letter-spacing:-.25px;color:var(--profile-heading)!important}#direk-profile .profile-editor h3::before{display:grid;place-items:center;flex-shrink:0;width:31px;height:31px;border:1px solid var(--profile-border);border-radius:10px;background:var(--profile-accent-bg);color:var(--profile-accent);font-size:10px;box-shadow:0 2px 0 var(--profile-edge)}#direk-profile #profile-identity-title::before{content:"01"}#direk-profile #profile-assignment-title::before{content:"02"}#direk-profile #profile-password-title::before{content:"03"}#direk-profile #profile-security-title::before{content:"04"}#direk-profile #profile-devices-title::before{content:"05"}
#direk-profile .profile-editor label{font-size:11px!important;font-weight:600!important;margin-bottom:9px!important}#direk-profile .profile-editor input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),#direk-profile .profile-editor select{width:100%;min-height:46px!important;border:1px solid var(--profile-border)!important;border-radius:10px!important;font-size:13px!important;line-height:1.5;padding:11px 12px!important;color:var(--profile-heading)!important;background:var(--profile-bg)!important;box-shadow:inset 0 1px 2px rgba(39,27,70,.025)}#direk-profile .profile-editor input[readonly],#direk-profile .profile-editor input:disabled{background:var(--profile-subtle)!important;color:var(--profile-copy)!important}#direk-profile .profile-editor input:focus,#direk-profile .profile-editor select:focus{border-color:var(--profile-accent)!important;box-shadow:0 0 0 3px rgba(138,108,226,.13)!important;outline:none}
#direk-profile .profile-fields-three,#direk-profile .profile-fields-two,#direk-profile .profile-fields-password{display:grid!important;gap:20px!important;grid-template-columns:repeat(3,minmax(0,1fr))}#direk-profile .profile-fields-two{grid-template-columns:repeat(2,minmax(0,1fr))}#direk-profile .profile-fields-three>div,#direk-profile .profile-fields-two>div,#direk-profile .profile-fields-password>div{min-width:0}
#direk-profile .ts-wrapper{width:100%}#direk-profile .ts-wrapper .ts-control{min-height:46px!important;border:1px solid var(--profile-border)!important;border-radius:10px!important;background:var(--profile-bg)!important;color:var(--profile-heading)!important;font-size:13px!important;padding:11px 12px!important;box-shadow:none!important}#direk-profile .ts-wrapper.focus .ts-control{border-color:var(--profile-accent)!important;box-shadow:0 0 0 3px rgba(138,108,226,.13)!important}#direk-profile .ts-control input{min-height:0!important;padding:0!important;border:0!important;color:var(--profile-heading)!important;box-shadow:none!important}#direk-profile .ts-dropdown{border-color:var(--profile-border)!important;border-radius:10px;background:var(--profile-bg)!important;color:var(--profile-heading)!important;box-shadow:0 14px 28px rgba(30,22,60,.14);z-index:30}#direk-profile .ts-dropdown .active{background:var(--profile-accent-bg)!important;color:var(--profile-heading)!important}
#direk-profile .profile-actions{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;padding:20px 24px!important;border:1px solid var(--profile-border);border-radius:15px;background:var(--profile-bg);box-shadow:0 4px 0 var(--profile-edge)}#direk-profile .profile-actions #btnSave,#direk-profile .profile-actions #btnSaveDisabled{min-height:44px;border-radius:10px!important;background:linear-gradient(135deg,#9375e8,#7255c4)!important;color:#fff!important;font-size:12px!important;box-shadow:0 3px 0 #564094,0 8px 15px rgba(96,60,167,.15)}#direk-profile .profile-actions #btnSave:hover{background:linear-gradient(135deg,#8566d9,#6549b7)!important}#direk-profile .profile-actions #btnCancel{min-height:44px;border-color:var(--profile-border)!important;border-radius:10px!important;font-size:12px!important;color:var(--profile-copy)!important;background:var(--profile-bg)!important}#direk-profile .profile-actions #btnCancel:hover{background:var(--profile-subtle)!important}
#direk-profile a:focus-visible,#direk-profile button:focus-visible{outline:2px solid var(--profile-accent)!important;outline-offset:3px}
@media(min-width:1200px){#direk-profile .profile-summary{position:sticky;top:94px}}
@media(max-width:1100px){#direk-profile .profile-layout{grid-template-columns:260px minmax(0,1fr)}#direk-profile .profile-fields-three,#direk-profile .profile-fields-password{grid-template-columns:repeat(2,minmax(0,1fr))}#direk-profile .profile-summary-content{padding:22px!important}}
@media(max-width:850px){#direk-profile .profile-layout{grid-template-columns:1fr}#direk-profile .profile-section-nav{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}#direk-profile .profile-summary-banner{min-height:105px}#direk-profile .profile-summary-content{padding:25px!important}#direk-profile .profile-cover-art{width:150px}#direk-profile .profile-page-intro{padding:27px}#direk-profile .profile-editor-heading{padding-top:7px!important}}
@media(max-width:600px){#direk-profile{padding:18px 15px 28px!important}#direk-profile .profile-page-intro{min-height:0;padding:26px 23px;gap:15px}#direk-profile .profile-page-intro h2{font-size:31px}#direk-profile .profile-cover-art{display:none}#direk-profile .profile-editor>section{padding:23px 20px!important}#direk-profile .profile-fields-three,#direk-profile .profile-fields-two,#direk-profile .profile-fields-password{grid-template-columns:1fr}#direk-profile .profile-fields-three>div,#direk-profile .profile-fields-two>div,#direk-profile .profile-fields-password>div{grid-column:auto!important}#direk-profile .profile-actions{padding:19px!important}#direk-profile .profile-actions>div{display:flex;width:100%;gap:12px}#direk-profile .profile-actions #btnSave,#direk-profile .profile-actions #btnCancel,#direk-profile .profile-actions #btnSaveDisabled{flex:1}#direk-profile .profile-editor input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),#direk-profile .profile-editor select{font-size:16px!important}}
@media(max-width:400px){#direk-profile .profile-section-nav{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){#direk-profile *{transition:none!important;scroll-behavior:auto!important}}
*/* Coordinated violet surfaces for the selected DIREK combination. */*
#direk-profile{--profile-bg:#fff;--profile-subtle:#f5f1fa;--profile-border:#ded7ec;--profile-heading:#302842;--profile-copy:#706482;--profile-accent:#7160ca;--profile-accent-bg:#eee7fa;--profile-edge:#d9d0e8;--profile-highlight:rgba(255,255,255,.9);--profile-shadow:rgba(54,39,89,.08)}
#direk-profile[data-theme="dark"]{--profile-bg:#26213b;--profile-subtle:#211d34;--profile-border:#514465;--profile-heading:#f4f0ff;--profile-copy:#c1b6d7;--profile-accent:#c2acff;--profile-accent-bg:#39314f;--profile-edge:#161126;--profile-highlight:rgba(239,221,255,.1);--profile-shadow:rgba(0,0,0,.2);color-scheme:dark}
#direk-profile .profile-page-intro{background:radial-gradient(ellipse at 85% 45%,rgba(168,120,238,.28),transparent 48%),linear-gradient(120deg,#25253f,#342650);border-color:#756093;box-shadow:inset 0 1px 0 rgba(237,211,255,.18),0 5px 0 #241c36,0 18px 35px rgba(40,29,72,.15)}
#direk-profile .profile-cover-symbol{background:linear-gradient(145deg,#9775c8,#61458e);border-color:#c2a5eb;box-shadow:inset 0 1px 0 rgba(255,255,255,.25),0 6px 0 #39234f,0 18px 30px rgba(11,6,24,.25)}
#direk-profile .profile-summary,#direk-profile .profile-editor>section{background:linear-gradient(145deg,var(--profile-bg),var(--profile-subtle))!important;border-color:var(--profile-border)!important;box-shadow:inset 0 1px 0 var(--profile-highlight),0 4px 0 var(--profile-edge),0 14px 28px var(--profile-shadow)}
#direk-profile .profile-summary-banner{background:radial-gradient(ellipse at 100% 0%,rgba(203,172,255,.22),transparent 65%),linear-gradient(125deg,#3b305c,#8061b8)!important}
#direk-profile .profile-avatar-button{background:linear-gradient(145deg,#957be5,#7158bf)!important;color:#fff!important}
#direk-profile .profile-avatar-button:hover{background:linear-gradient(145deg,#866bd6,#634bac)!important}
#direk-profile .profile-section-nav a{border:1px solid var(--profile-border);background:linear-gradient(145deg,var(--profile-bg),var(--profile-subtle));color:var(--profile-copy);box-shadow:inset 0 1px 0 var(--profile-highlight),0 2px 0 var(--profile-edge);transition:background .18s ease,border-color .18s ease}
#direk-profile .profile-section-nav a>i{background:transparent;border:0;box-shadow:none}
#direk-profile .profile-section-nav a:hover{border-color:var(--profile-accent);color:var(--profile-heading)}
#direk-profile .profile-section-nav a[aria-current="location"]{background:var(--profile-accent-bg);border-color:var(--profile-accent);color:var(--profile-accent)}
#direk-profile .profile-fields-three label,#direk-profile .profile-fields-two label,#direk-profile .profile-fields-password label{color:var(--profile-heading)!important}
#direk-profile .profile-editor>section>div:first-child>p{color:var(--profile-copy)!important}
#direk-profile .profile-editor input::placeholder{color:var(--profile-copy)!important;opacity:.8}
#direk-profile .profile-editor input[readonly],#direk-profile .profile-editor input:disabled{background:var(--profile-subtle)!important;color:var(--profile-copy)!important}
#direk-profile .profile-editor input[aria-invalid="true"],#direk-profile .profile-editor select[aria-invalid="true"]{border-color:#dc526c!important}
#direk-profile .ts-wrapper:has(select[aria-invalid="true"]) .ts-control{border-color:#dc526c!important}
#direk-profile .profile-actions{background:linear-gradient(145deg,var(--profile-bg),var(--profile-subtle));border-color:var(--profile-border);box-shadow:inset 0 1px 0 var(--profile-highlight),0 4px 0 var(--profile-edge),0 12px 25px var(--profile-shadow)}
#direk-profile .profile-actions>span{color:var(--profile-copy)!important}
#direk-profile .profile-actions #btnSave,#direk-profile .profile-actions #btnSaveDisabled{background:linear-gradient(145deg,#957be5,#7158bf)!important;color:#fff!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.2),0 3px 0 #4d378b,0 9px 18px rgba(96,60,167,.16)}
#direk-profile .profile-actions #btnSave:hover{background:linear-gradient(145deg,#866bd6,#634bac)!important}
#direk-profile .profile-actions #btnCancel{background:var(--profile-bg)!important;color:var(--profile-copy)!important;border-color:var(--profile-border)!important;box-shadow:0 2px 0 var(--profile-edge)}
#direk-profile .profile-editor,#direk-profile .profile-editor>section{overflow:visible!important}
#direk-profile .ts-dropdown{background:var(--profile-bg)!important;border-color:var(--profile-border)!important;color:var(--profile-heading)!important}
#direk-profile .ts-dropdown .active{background:var(--profile-accent-bg)!important;color:var(--profile-heading)!important}
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

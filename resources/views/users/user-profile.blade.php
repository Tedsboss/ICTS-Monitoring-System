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
    $profileSurface = $profileDark ? '!bg-[#1a293a] !border-[#34465a]' : '!bg-white !border-slate-200';
    $profileHeading = $profileDark ? '!text-slate-100' : '!text-[#203b55]';
    $profileCopy = $profileDark ? '!text-slate-300' : '!text-slate-600';
    $profileMuted = $profileDark ? '!text-slate-400' : '!text-slate-500';
    $profileDivider = $profileDark ? '!border-[#34465a]' : '!border-slate-200';
    $profileControlTheme = $profileDark
        ? '!bg-[#142435] !border-[#41566d] !text-slate-100 placeholder:!text-slate-400 [&[readonly]]:!bg-[#243548] disabled:!bg-[#243548] disabled:!text-slate-400'
        : '!bg-white !border-slate-300 !text-slate-700 placeholder:!text-slate-400 [&[readonly]]:!bg-slate-50 disabled:!bg-slate-50 disabled:!text-slate-500';
    $profileControl = '!box-border !block !min-h-[46px] !w-full !rounded-lg !border !border-solid !px-3 !py-2.5 !font-sans !text-base !leading-5 !outline-none !transition focus:!border-[#80939c] focus:!ring-2 focus:!ring-[#8eb3dc]/30 disabled:!cursor-not-allowed motion-reduce:!transition-none md:!text-sm';
    $profileLabel = '!mb-2 !block !text-xs !font-semibold';
    $profileEditorTheme = $profileDark
        ? '[&_.ts-control]:!bg-[#142435] [&_.ts-control]:!border-[#41566d] [&_.ts-control]:!text-slate-100 [&_.ts-control_input]:!text-slate-100 [&_.ts-dropdown]:!bg-[#1a293a] [&_.ts-dropdown]:!border-[#41566d] [&_.ts-dropdown]:!text-slate-100 [&_.ts-dropdown_.active]:!bg-[#2b4660] [&_.ts-dropdown_.active]:!text-slate-100'
        : '[&_.ts-control]:!bg-white [&_.ts-control]:!border-slate-300 [&_.ts-control]:!text-slate-700 [&_.ts-control_input]:!text-slate-700 [&_.ts-dropdown]:!bg-white [&_.ts-dropdown]:!border-slate-200 [&_.ts-dropdown]:!text-slate-700 [&_.ts-dropdown_.active]:!bg-[#eaf1f8] [&_.ts-dropdown_.active]:!text-[#203b55]';
@endphp

<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'My Profile'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div id="direk-profile" class="!px-4 !pb-8 !pt-4 [&_[hidden]]:!hidden [&_.ts-wrapper]:!w-full [&_.ts-control]:!min-h-[46px] [&_.ts-control]:!rounded-lg [&_.ts-control]:!border [&_.ts-control]:!border-solid [&_.ts-control]:!px-3 [&_.ts-control]:!py-2.5 [&_.ts-control]:!text-sm [&_.ts-control]:!shadow-none [&_.ts-wrapper.focus_.ts-control]:!border-[#80939c] [&_.ts-wrapper.focus_.ts-control]:!ring-2 [&_.ts-wrapper.focus_.ts-control]:!ring-[#8eb3dc]/30 {{ $profileEditorTheme }} md:!px-6">
    <div class="!mx-auto !max-w-6xl">
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
                <aside class="!overflow-hidden !rounded-2xl !border !border-solid {{ $profileSurface }}" aria-label="Profile summary">
                    <div class="!bg-[#142d45] !px-6 !py-5"><p class="!m-0 !text-xs !font-semibold !uppercase !tracking-[0.12em] !text-[#e4cf9d]">Your DIREK account</p></div>
                    <div class="!p-6">
                        <div class="!relative !mb-5 !h-24 !w-24">
                            <img id="avatar-preview" src="{{ $profileUser->avatarUrl() }}" width="96" height="96" alt="Your profile photo" class="!h-24 !w-24 !rounded-2xl !object-cover !shadow-sm">
                            <button type="button" onclick="document.getElementById('file-input').click()" aria-label="Change profile photo" title="Change profile photo" class="!absolute !-bottom-2 !-right-2 !grid !h-11 !w-11 !place-items-center !rounded-xl !border !border-solid !border-slate-200 !bg-white !text-[#203b55] !shadow-sm hover:!bg-slate-50 focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e]"><i class="fa fa-camera" aria-hidden="true"></i></button>
                            <input type="file" name="avatar" id="file-input" accept="image/*" onchange="updateavatar()" hidden>
                        </div>
                        @error('avatar')<p class="!m-0 !mb-3 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                        <p id="avatar-client-error" class="!m-0 !mb-3 !text-xs !text-rose-600" role="alert" hidden></p>
                        <h2 class="!m-0 !break-words !text-xl !font-semibold !leading-7 {{ $profileHeading }}">{{ $profileName ?: 'My Profile' }}</h2>
                        <span class="!mt-3 !inline-flex !rounded-full !bg-[#eaf1f8] !px-3 !py-1 !text-xs !font-semibold !text-[#315e87]">{{ $profileRole }}</span>
                        <p class="!m-0 !mt-5 !text-sm !font-medium !leading-6 {{ $profileCopy }}">{{ $profileUser->position_name() ?: 'No position assigned' }}</p>
                        <p class="!m-0 !mt-1 !text-xs !leading-6 {{ $profileMuted }}">{{ $profileUser->staff_name() ?: 'No staff/office assigned' }}</p>
                        <div class="!mt-5 !border-0 !border-t !border-solid !pt-5 {{ $profileDivider }}">
                            <p class="!m-0 !text-xs !font-semibold {{ $profileMuted }}">Account email</p><p class="!m-0 !mt-2 !break-all !text-sm {{ $profileCopy }}">{{ $profileUser->email }}</p>
                        </div>
                        <p class="!m-0 !mt-5 !text-xs !leading-6 {{ $profileMuted }}">Keep your contact details and account preferences up to date.</p>
                    </div>
                </aside>

                <div class="!min-w-0 !overflow-hidden !rounded-2xl !border !border-solid {{ $profileSurface }}">
                    <div class="!border-0 !border-b !border-solid !px-5 !py-5 md:!px-7 {{ $profileDivider }}">
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
                    <div class="!flex !flex-wrap !items-center !justify-between !gap-4 !p-5 md:!px-7">
                        <p id="profile-submit-error" class="!m-0 !text-xs !text-rose-600" role="alert" hidden></p>
                        <span class="!text-xs {{ $profileMuted }}">Review your changes before saving.</span>
                        <div class="!flex !flex-wrap !items-center !gap-3">
                            <button type="button" id="btnCancel" onclick="occancel()" class="!inline-flex !min-h-[44px] !items-center !justify-center !rounded-lg !border !border-solid !px-5 !py-2.5 !font-sans !text-sm !font-semibold focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] disabled:!opacity-60 {{ $profileSurface }} {{ $profileCopy }}">Cancel</button>
                            <button type="button" id="btnSave" onclick="ocSubmit()" class="!inline-flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !border-0 !bg-[#203b55] !px-5 !py-2.5 !font-sans !text-sm !font-semibold !text-white hover:!bg-[#315e87] focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] disabled:!opacity-60"><i class="fa fa-save" aria-hidden="true"></i><span>Save changes</span></button>
                            <button type="button" id="btnSaveDisabled" disabled hidden class="!inline-flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !border-0 !bg-[#315e87] !px-5 !py-2.5 !font-sans !text-sm !font-semibold !text-white !opacity-70"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span role="status">Saving…</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@include('layouts.footers.auth.footer')
@endsection

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
@endpush

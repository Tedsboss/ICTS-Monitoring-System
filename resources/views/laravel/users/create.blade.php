@extends('layouts.app')
@section('content')
@php
    $createDark = session('user_settings.class_theme', '') === 'dark';
    $createSurface = $createDark ? '!bg-[#192130]' : '!bg-white';
    $createInset = $createDark ? '!bg-[#151c29]' : '!bg-slate-50';
    $createBorder = $createDark ? '!border-[#2c3546]' : '!border-slate-200';
    $createHeading = $createDark ? '!text-slate-100' : '!text-[#172033]';
    $createText = $createDark ? '!text-slate-300' : '!text-slate-700';
    $createMuted = $createDark ? '!text-slate-400' : '!text-slate-500';
    $createInput = '!box-border !block !min-h-[46px] !w-full !rounded-lg !border !border-solid !px-3 !py-2.5 !text-sm !outline-none !transition focus:!border-[#635bff] focus:!ring-2 focus:!ring-[#635bff]/20 motion-reduce:!transition-none';
    $createBirthdayParts = isset($birthdayArray) && is_array($birthdayArray) ? $birthdayArray : [];
    $createBirthdayConfig = [
        'year' => (string) old('choices-year', $createBirthdayParts['year'] ?? ''),
        'month' => (string) old('choices-month', $createBirthdayParts['month'] ?? ''),
        'day' => (string) old('choices-day', $createBirthdayParts['day'] ?? ''),
        'currentYear' => now()->year,
        'today' => now()->format('Y-m-d'),
    ];
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'New User'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-create" data-theme="{{ $createDark ? 'dark' : 'light' }}" class="!px-4 !pb-8 !pt-4 [&_[hidden]]:!hidden">
    <div class="account-workspace !mx-auto !max-w-4xl">
        <a href="{{ route('user-management') }}" class="!mb-5 !inline-flex !min-h-[44px] !items-center !gap-2 !rounded-lg !border !border-solid !px-4 !py-2 !text-sm !font-semibold !no-underline {{ $createBorder }} {{ $createSurface }} {{ $createText }} focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#635bff]"><i class="fa fa-arrow-left" aria-hidden="true"></i>Back to User Management</a>
        <div class="!mb-5">
            <span class="account-eyebrow">PEOPLE &amp; ACCESS</span>
            <h2 class="account-page-title !m-0 !text-xl !font-bold {{ $createHeading }}">Create user account</h2>
            <p class="!m-0 !mt-1 !text-sm {{ $createMuted }}">Enter the user's details and assign an access role.</p>
        </div>
        @if ($errors->any())
            <div role="alert" class="!mb-5 !rounded-xl !border !border-solid !border-rose-200 !bg-rose-50 !px-4 !py-3 !text-sm !text-rose-800">
                <p class="!m-0 !font-semibold">Please review the information below.</p>
                <ul class="!m-0 !mt-2 !list-disc !space-y-1 !pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <div class="account-grid">
            <aside class="account-sidebar" aria-label="Account setup guide">
                <span class="account-sidebar-icon" aria-hidden="true"><i class="fa fa-user-plus"></i></span>
                <h3>Welcome someone new.</h3>
                <p>Give your team a clear identity and the access they need to get started.</p>
                <div class="account-sidebar-list">
                    <div><i class="fa fa-user" aria-hidden="true"></i>Account identity</div>
                    <div><i class="fa fa-shield" aria-hidden="true"></i>Role and access</div>
                    <div><i class="fa fa-lock" aria-hidden="true"></i>Sign-in credentials</div>
                    <div><i class="fa fa-camera" aria-hidden="true"></i>Profile photo</div>
                </div>
            </aside>
            <form class="account-form" id="createUserForm" method="POST" action="{{ route('user-new.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="birthday" id="birthday" value="{{ old('birthday') }}">
            <section class="!mb-5 !rounded-2xl !border !border-solid {{ $createBorder }} {{ $createSurface }} !shadow-sm">
                <div class="!rounded-t-2xl !border-b !border-solid !px-5 !py-4 {{ $createBorder }} {{ $createInset }}">
                    <h3 class="account-section-heading !m-0 !text-xs !font-bold !uppercase !tracking-wide {{ $createHeading }}"><span class="account-section-number">01</span>Account information</h3>
                    <p class="!m-0 !mt-1 !text-xs !leading-5 {{ $createMuted }}">Basic identity and email details for this account.</p>
                </div>
                <div class="!grid !grid-cols-1 !gap-5 !p-5 md:!grid-cols-2">
                    <div>
                        <label for="firstname" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">First name <span class="!text-rose-500" aria-hidden="true">*</span></label>
                        <input type="text" id="firstname" name="firstname" value="{{ old('firstname') }}" required maxlength="255" autocomplete="given-name" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('firstname') aria-invalid="true" aria-describedby="error-firstname" @enderror>
                        @error('firstname')<p id="error-firstname" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="lastname" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Last name <span class="!text-rose-500" aria-hidden="true">*</span></label>
                        <input type="text" id="lastname" name="lastname" value="{{ old('lastname') }}" required maxlength="255" autocomplete="family-name" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('lastname') aria-invalid="true" aria-describedby="error-lastname" @enderror>
                        @error('lastname')<p id="error-lastname" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Email address <span class="!text-rose-500" aria-hidden="true">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('email') aria-invalid="true" aria-describedby="error-email" @enderror>
                        @error('email')<p id="error-email" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="confirmation" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Confirm email address <span class="!text-rose-500" aria-hidden="true">*</span></label>
                        <input type="email" id="confirmation" name="confirmation" value="{{ old('confirmation') }}" required maxlength="255" autocomplete="off" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('confirmation') aria-invalid="true" aria-describedby="error-confirmation" @enderror>
                        @error('confirmation')<p id="error-confirmation" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:!col-span-2">
                        <label for="choices-role" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Access role</label>
                        <select name="role" id="choices-role" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('role') aria-invalid="true" aria-describedby="error-role" @enderror>
                            <option value="">Select role</option>
                            @foreach ($roles as $role)<option value="{{ $role->id }}" {{ (string) old('role') === (string) $role->id ? 'selected' : '' }}>{{ $role->name }}</option>@endforeach
                        </select>
                        @error('role')<p id="error-role" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                        <p id="role-client-error" role="alert" class="!m-0 !mt-2 !text-xs !text-rose-600" hidden></p>
                    </div>
                </div>
            </section>
            <section class="!mb-5 !rounded-2xl !border !border-solid {{ $createBorder }} {{ $createSurface }} !shadow-sm">
                <div class="!rounded-t-2xl !border-b !border-solid !px-5 !py-4 {{ $createBorder }} {{ $createInset }}">
                    <h3 class="account-section-heading !m-0 !text-xs !font-bold !uppercase !tracking-wide {{ $createHeading }}"><span class="account-section-number">02</span>Personal and contact details</h3>
                    <p class="!m-0 !mt-1 !text-xs !leading-5 {{ $createMuted }}">Profile information and contact preferences.</p>
                </div>
                <div class="!grid !grid-cols-1 !gap-5 !p-5 md:!grid-cols-2">
                    <div>
                        <label for="choices-gender" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Gender</label>
                        <select name="gender" id="choices-gender" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}">
                            <option value="">Select gender</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')<p class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <fieldset class="!m-0 !min-w-0 !border-0 !p-0">
                        <legend class="!mb-2 !block !w-auto !p-0 !text-xs !font-semibold {{ $createText }}">Birth date</legend>
                        <div class="!grid !grid-cols-3 !gap-2">
                            <div><label for="choices-month" class="!sr-only">Birth month</label><select name="choices-month" id="choices-month" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}"><option value="">Month</option></select></div>
                            <div><label for="choices-day" class="!sr-only">Birth day</label><select name="choices-day" id="choices-day" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}"><option value="">Day</option></select></div>
                            <div><label for="choices-year" class="!sr-only">Birth year</label><select name="choices-year" id="choices-year" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}"><option value="">Year</option></select></div>
                        </div>
                        <p id="birthday-client-error" role="alert" class="!m-0 !mt-2 !text-xs !text-rose-600" hidden></p>
                        @foreach (['birthday', 'choices-month', 'choices-day', 'choices-year'] as $birthdayField)
                            @error($birthdayField)<p class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                        @endforeach
                    </fieldset>
                    <div>
                        <label for="location" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Location</label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}" maxlength="255" autocomplete="street-address" placeholder="Office or city" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('location') aria-invalid="true" aria-describedby="error-location" @enderror>
                        @error('location')<p id="error-location" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Phone number</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="+63 9XX XXX XXXX" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('phone') aria-invalid="true" aria-describedby="error-phone" @enderror>
                        @error('phone')<p id="error-phone" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="choices-language" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Language</label>
                        <select name="language" id="choices-language" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}">
                            <option value="">Select language</option>
                            @foreach (['English', 'French', 'Spanish'] as $language)<option value="{{ $language }}" {{ old('language') === $language ? 'selected' : '' }}>{{ $language }}</option>@endforeach
                        </select>
                        @error('language')<p class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="skills" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Skills</label>
                        <input type="text" id="skills" name="skills" value="{{ old('skills') }}" placeholder="Enter skills" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('skills') aria-invalid="true" aria-describedby="error-skills" @enderror>
                        @error('skills')<p id="error-skills" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>
            <section class="!mb-5 !rounded-2xl !border !border-solid {{ $createBorder }} {{ $createSurface }} !shadow-sm">
                <div class="!rounded-t-2xl !border-b !border-solid !px-5 !py-4 {{ $createBorder }} {{ $createInset }}">
                    <h3 class="account-section-heading !m-0 !text-xs !font-bold !uppercase !tracking-wide {{ $createHeading }}"><span class="account-section-number">03</span>Password</h3>
                    <p class="!m-0 !mt-1 !text-xs !leading-5 {{ $createMuted }}">Set the initial password for this account.</p>
                </div>
                <div class="!grid !grid-cols-1 !gap-5 !p-5 md:!grid-cols-2">
                    <div>
                        <label for="password" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Password <span class="!text-rose-500" aria-hidden="true">*</span></label>
                        <input type="password" id="password" name="password" required autocomplete="new-password" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('password') aria-invalid="true" aria-describedby="error-password" @enderror>
                        @error('password')<p id="error-password" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="confirm-password" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Confirm password <span class="!text-rose-500" aria-hidden="true">*</span></label>
                        <input type="password" id="confirm-password" name="confirm-password" required autocomplete="new-password" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('confirm-password') aria-invalid="true" aria-describedby="error-confirm-password" @enderror>
                        @error('confirm-password')<p id="error-confirm-password" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>
            <section class="!mb-5 !rounded-2xl !border !border-solid {{ $createBorder }} {{ $createSurface }} !shadow-sm">
                <div class="!rounded-t-2xl !border-b !border-solid !px-5 !py-4 {{ $createBorder }} {{ $createInset }}">
                    <h3 class="account-section-heading !m-0 !text-xs !font-bold !uppercase !tracking-wide {{ $createHeading }}"><span class="account-section-number">04</span>Profile photo</h3>
                    <p class="!m-0 !mt-1 !text-xs !leading-5 {{ $createMuted }}">Choose an image to identify this account.</p>
                </div>
                <div class="!grid !grid-cols-1 !gap-5 !p-5 md:!grid-cols-2">
                    <div class="md:!col-span-2">
                        <label for="avatar" class="!mb-2 !block !text-xs !font-semibold {{ $createText }}">Upload photo</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="{{ $createInput }} {{ $createBorder }} {{ $createSurface }} {{ $createText }}" @error('avatar') aria-invalid="true" aria-describedby="error-avatar" @enderror>
                        @error('avatar')<p id="error-avatar" class="!m-0 !mt-2 !text-xs !text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div id="imageChange" class="!flex !items-center !gap-3 md:!col-span-2" hidden>
                        <img id="avatar-preview" alt="Selected profile photo preview" class="!h-20 !w-20 !rounded-xl !object-cover !shadow-sm">
                        <span class="!text-xs {{ $createMuted }}">Selected photo preview</span>
                    </div>
                    <p id="avatar-client-error" role="alert" class="!m-0 !text-xs !text-rose-600 md:!col-span-2" hidden></p>
                </div>
            </section>
            <div class="!flex !flex-col-reverse !gap-3 !rounded-xl !border !border-solid !p-4 {{ $createBorder }} {{ $createInset }} sm:!flex-row sm:!justify-end">
                <a href="{{ route('user-management') }}" class="!inline-flex !min-h-[44px] !items-center !justify-center !rounded-lg !border !border-solid !px-5 !py-2.5 !text-sm !font-semibold !no-underline {{ $createBorder }} {{ $createSurface }} {{ $createText }}">Cancel</a>
                <button type="submit" id="btnCreateUser" class="account-primary !inline-flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !border-0 !bg-[#635bff] !px-5 !py-2.5 !text-sm !font-semibold !text-white hover:!bg-[#534be5] focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#635bff] disabled:!cursor-wait disabled:!opacity-60"><i class="fa fa-save" aria-hidden="true"></i><span id="createUserButtonText">Create user</span></button>
            </div>
        </form>
        </div>
    </div>
    <div class="!mt-6">@include('layouts.footers.auth.footer')</div>
</div>
@endsection
@push('css')
@endpush
@push('js')
<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const config = {{ \Illuminate\Support\Js::from($createBirthdayConfig) }};
    const form = document.getElementById('createUserForm');
    const field = id => document.getElementById(id);
    const year = field('choices-year');
    const month = field('choices-month');
    const day = field('choices-day');
    const choices = Object.create(null);
    let updatingDays = false;
    let saving = false;
    let previewReadId = 0;
    function numericValue(value) {
        return /^\d+$/.test(String(value || '')) ? Number(value) : 0;
    }
    function fillNativeSelect(element, placeholder, options, selectedValue = '') {
        const selected = String(selectedValue || '');
        element.replaceChildren(new Option(placeholder, ''));
        options.forEach(option => element.add(new Option(option.label, option.value, false, String(option.value) === selected)));
    }
    function initChoice(id, options = {}) {
        const element = field(id);
        if (!element || typeof window.Choices !== 'function') return;
        try {
            choices[id] = new window.Choices(element, { shouldSort:false, allowHTML:false, itemSelectText:'', ...options });
        } catch (error) {
            // Native controls continue working when enhancement is unavailable.
        }
    }
    function rebuildDays(selectedDay = day.value) {
        if (updatingDays) return;
        updatingDays = true;
        try {
            const selectedMonth = numericValue(month.value);
            const selectedYear = numericValue(year.value) || 2000;
            const daysInMonth = selectedMonth ? new Date(Date.UTC(selectedYear, selectedMonth, 0)).getUTCDate() : 31;
            const previousDay = numericValue(selectedDay);
            const selected = previousDay >= 1 && previousDay <= daysInMonth ? String(previousDay) : '';
            const options = Array.from({length:daysInMonth}, (_, index) => ({ value:String(index + 1), label:String(index + 1), selected:String(index + 1) === selected }));
            if (choices['choices-day']) {
                choices['choices-day'].clearStore();
                choices['choices-day'].setChoices([{value:'', label:'Day', selected:!selected}, ...options], 'value', 'label', true);
            } else {
                fillNativeSelect(day, 'Day', options, selected);
            }
        } finally {
            updatingDays = false;
        }
        syncBirthday();
    }
    function syncBirthday(showError = false) {
        const values = [year.value, month.value, day.value];
        const birthday = field('birthday');
        const error = field('birthday-client-error');
        birthday.value = '';
        let message = '';
        if (values.some(Boolean)) {
            if (!values.every(Boolean)) {
                message = 'Please select the complete birth date.';
            } else {
                const y = numericValue(year.value), m = numericValue(month.value), d = numericValue(day.value);
                const date = new Date(Date.UTC(y, m - 1, d));
                const formatted = String(y).padStart(4, '0') + '-' + String(m).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                if (date.getUTCFullYear() !== y || date.getUTCMonth() !== m - 1 || date.getUTCDate() !== d) {
                    message = 'Please select a valid birth date.';
                } else if (formatted > config.today) {
                    message = 'The birth date cannot be in the future.';
                } else {
                    birthday.value = formatted;
                }
            }
        }
        error.textContent = message;
        error.hidden = !showError || !message;
        return !message;
    }
    function focusControl(element) {
        const wrapper = element.closest('.choices');
        const visible = wrapper && wrapper.querySelector('input:not([type="hidden"]), [tabindex="0"]');
        (visible || element).focus();
    }
    const years = Array.from({length:config.currentYear - 1900 + 1}, (_, index) => ({value:String(config.currentYear - index), label:String(config.currentYear - index)}));
    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    fillNativeSelect(year, 'Year', years, numericValue(config.year));
    fillNativeSelect(month, 'Month', monthNames.map((label,index) => ({value:String(index + 1), label})), numericValue(config.month));
    rebuildDays(config.day);
    initChoice('choices-role');
    initChoice('choices-gender', {searchEnabled:false});
    initChoice('choices-language');
    initChoice('skills', {removeItemButton:true});
    initChoice('choices-year');
    initChoice('choices-month', {searchEnabled:false});
    initChoice('choices-day', {searchEnabled:false});
    year.addEventListener('change', () => rebuildDays());
    month.addEventListener('change', () => rebuildDays());
    day.addEventListener('change', () => { if (!updatingDays) syncBirthday(); });
    field('choices-role').addEventListener('change', () => { field('role-client-error').hidden = true; });
    function validateConfirmations() {
        const emailConfirmation = field('confirmation');
        emailConfirmation.setCustomValidity(emailConfirmation.value && emailConfirmation.value !== field('email').value ? 'The email addresses do not match.' : '');
        const passwordConfirmation = field('confirm-password');
        passwordConfirmation.setCustomValidity(passwordConfirmation.value && passwordConfirmation.value !== field('password').value ? 'The passwords do not match.' : '');
    }
    ['email', 'confirmation', 'password', 'confirm-password'].forEach(id => field(id).addEventListener('input', validateConfirmations));
    validateConfirmations();
    field('avatar').addEventListener('change', function () {
        const file = this.files && this.files[0];
        const error = field('avatar-client-error');
        const preview = field('imageChange');
        const readId = ++previewReadId;
        error.hidden = true;
        preview.hidden = true;
        if (!file) return;
        if (file.type && !file.type.startsWith('image/')) {
            this.value = '';
            error.textContent = 'Please choose an image for the profile photo.';
            error.hidden = false;
            return;
        }
        const reader = new FileReader();
        reader.onload = event => {
            if (readId !== previewReadId) return;
            field('avatar-preview').src = event.target.result;
            preview.hidden = false;
        };
        reader.onerror = () => {
            if (readId !== previewReadId) return;
            error.textContent = 'We could not preview this image. Please choose it again.';
            error.hidden = false;
        };
        reader.readAsDataURL(file);
    });
    form.addEventListener('submit', event => {
        if (saving) {
            event.preventDefault();
            return;
        }
        validateConfirmations();
        const birthdayValid = syncBirthday(true);
        if (!birthdayValid) {
            event.preventDefault();
            focusControl(!year.value ? year : !month.value ? month : day);
            return;
        }
        if (!field('choices-role').value) {
            event.preventDefault();
            field('role-client-error').textContent = 'Please select an access role.';
            field('role-client-error').hidden = false;
            focusControl(field('choices-role'));
            return;
        }
        if (!form.reportValidity()) {
            event.preventDefault();
            return;
        }
        saving = true;
        field('btnCreateUser').disabled = true;
        field('createUserButtonText').textContent = 'Creating…';
        form.setAttribute('aria-busy', 'true');
    });
    window.addEventListener('pageshow', () => {
        saving = false;
        field('btnCreateUser').disabled = false;
        field('createUserButtonText').textContent = 'Create user';
        form.removeAttribute('aria-busy');
    });
});
</script>
@endpush
@push('css')
<style>
    #direk-create {
        --account-surface: #fff;
        --account-inset: #f6f7fb;
        --account-border: #e6e9f0;
        --account-heading: #172033;
        --account-copy: #637086;
        --account-accent: #635bff;
        --account-soft: #eeedff;
        --account-accent-copy: #5850df;
    }
    #direk-create[data-theme="dark"] {
        --account-surface: #192130;
        --account-inset: #151c29;
        --account-border: #2c3546;
        --account-heading: #eef0f8;
        --account-copy: #95a0b5;
        --account-soft: #2b2850;
        --account-accent-copy: #a19cff;
    }
    #direk-create [hidden] { display: none !important; }
    #direk-create .account-workspace { max-width: 1400px !important; margin: 0 auto; }
    #direk-create .account-page-title { font-size: clamp(26px, 3vw, 34px) !important; font-weight: 700 !important; letter-spacing: -.04em; line-height: 1.2; }
    #direk-create .account-eyebrow { display: block; margin-bottom: 10px; color: var(--account-accent-copy); font-size: 10px; font-weight: 700; letter-spacing: .14em; }
    #direk-create .account-grid { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 24px; align-items: start; }
    #direk-create .account-sidebar { position: sticky; top: 28px; padding: 24px; border: 1px solid var(--account-border); border-radius: 20px; background: var(--account-surface); }
    #direk-create .account-sidebar-icon { display: grid; width: 52px; height: 52px; place-items: center; margin-bottom: 20px; border-radius: 16px; background: var(--account-soft); color: var(--account-accent-copy); font-size: 20px; }
    #direk-create .account-sidebar h3 { margin: 0; color: var(--account-heading); font-size: 16px; font-weight: 650; line-height: 1.5; }
    #direk-create .account-sidebar p { margin: 8px 0 0; color: var(--account-copy); font-size: 12px; line-height: 1.8; overflow-wrap: anywhere; }
    #direk-create .account-sidebar-list { display: grid; gap: 16px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--account-border); }
    #direk-create .account-sidebar-list div { display: flex; align-items: center; gap: 10px; color: var(--account-copy); font-size: 12px; }
    #direk-create .account-sidebar-list i { width: 18px; color: var(--account-accent-copy); text-align: center; }
    #direk-create .account-form { min-width: 0; }
    #direk-create .account-form > section { border-radius: 18px !important; border-color: var(--account-border) !important; background: var(--account-surface) !important; box-shadow: 0 4px 20px rgba(20,25,60,.025) !important; overflow: hidden; }
    #direk-create .account-form > section > div:first-child { padding: 20px 24px !important; background: var(--account-surface) !important; border-color: var(--account-border) !important; }
    #direk-create .account-form > section > div:nth-child(2) { padding: 24px !important; }
    #direk-create .account-section-heading { display: flex; align-items: center; gap: 10px; color: var(--account-heading) !important; font-size: 13px !important; font-weight: 650 !important; text-transform: none !important; letter-spacing: -.01em !important; }
    #direk-create .account-section-number { display: grid; flex: 0 0 28px; width: 28px; height: 28px; place-items: center; border-radius: 9px; background: var(--account-soft); color: var(--account-accent-copy); font-size: 10px; }
    #direk-create .account-form label { font-size: 12px !important; }
    #direk-create .account-form input:not([type="hidden"]), #direk-create .account-form select { min-height: 44px !important; border-radius: 10px !important; box-shadow: none !important; font-size: 13px !important; }
    #direk-create .account-form input:focus, #direk-create .account-form select:focus { border-color: var(--account-accent) !important; box-shadow: 0 0 0 3px rgba(99,91,255,.12) !important; }
    #direk-create .account-form input[type="file"]::file-selector-button { border: 0; border-radius: 6px; background: var(--account-soft); color: var(--account-accent-copy); padding: 5px 9px; margin-right: 10px; font-size: 12px; }
    #direk-create .account-form [aria-invalid="true"] { border-color: #e55378 !important; }
    #direk-create .account-primary { background: #635bff !important; border-radius: 10px !important; box-shadow: 0 3px 8px rgba(99,91,255,.15) !important; }
    #direk-create .account-primary:hover { background: #534be5 !important; }
    #direk-create .account-form .choices { margin: 0; }
    #direk-create .account-form .choices__inner { min-height: 44px; border: 1px solid var(--account-border) !important; border-radius: 10px !important; background: var(--account-surface) !important; color: var(--account-heading) !important; padding: 8px 10px !important; font-size: 13px; }
    #direk-create .account-form .choices__input { background: var(--account-surface) !important; color: var(--account-heading) !important; }
    #direk-create .account-form .choices__list--dropdown, #direk-create .account-form .choices__list[aria-expanded] { border-color: var(--account-border) !important; background: var(--account-surface) !important; color: var(--account-heading) !important; z-index: 20; }
    #direk-create .account-form .choices__list--dropdown .is-highlighted { background: var(--account-soft) !important; }
    #direk-create .account-form .choices.is-focused .choices__inner { border-color: var(--account-accent) !important; box-shadow: 0 0 0 3px rgba(99,91,255,.12); }
    #direk-create .account-form .choices[data-type*="select-one"]:after { border-color: var(--account-copy) transparent transparent !important; }
    #direk-create .account-form .choices.is-open[data-type*="select-one"]:after { border-color: transparent transparent var(--account-copy) !important; }
    #direk-create .account-form .choices__list--multiple .choices__item { border-color: #635bff; background: #635bff; }
    @media (max-width: 991px) { #direk-create .account-grid { grid-template-columns: 1fr; } #direk-create .account-sidebar { display: none; } }
    @media (max-width: 639px) { #direk-create .account-form > section > div:first-child, #direk-create .account-form > section > div:nth-child(2) { padding: 18px !important; } }
    @media (prefers-reduced-motion: reduce) { #direk-create .account-form * { transition: none !important; } }
</style>
@endpush

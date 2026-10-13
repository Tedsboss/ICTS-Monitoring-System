@extends('layouts.app')
@section('content')
@php
    $editDark = session('user_settings.class_theme', '') === 'dark';
    $editSurface = $editDark ? '!bg-[#192130]' : '!bg-white';
    $editInset = $editDark ? '!bg-[#151c29]' : '!bg-slate-50';
    $editBorder = $editDark ? '!border-[#2c3546]' : '!border-slate-200';
    $editHeading = $editDark ? '!text-slate-100' : '!text-[#172033]';
    $editText = $editDark ? '!text-slate-300' : '!text-slate-700';
    $editMuted = $editDark ? '!text-slate-400' : '!text-slate-500';
    $editError = $editDark ? '!text-rose-300' : '!text-rose-600';
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Edit User'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-edit" data-theme="{{ $editDark ? 'dark' : 'light' }}" class="!px-4 !pb-8 !pt-4">
    <div class="account-workspace !mx-auto !max-w-4xl">
        {{-- Back --}}
        <div class="!mb-4">
            <a href="{{ route('user-management') }}" class="!no-underline !inline-flex !min-h-[44px] !items-center !gap-2 !rounded-lg !border {{ $editBorder }} {{ $editSurface }} !px-4 !py-2 !text-sm !font-semibold {{ $editText }} !shadow-sm !transition focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#635bff] motion-reduce:!transition-none {{ $editDark ? 'hover:!bg-[#20283b]' : 'hover:!bg-slate-50' }} !border-solid">
                <i aria-hidden="true" class="fa fa-arrow-left"></i>
                <span>Back to User Management</span>
            </a>
        </div>
        {{-- Page Header --}}
        <div class="!mb-5">
            <span class="account-eyebrow">PEOPLE &amp; ACCESS</span>
            <h2 class="account-page-title !m-0 !text-xl !font-bold {{ $editHeading }}">Edit User</h2>
            <p class="!m-0 !mt-1 !text-sm {{ $editMuted }}">Update the user's DIREK account and access role.</p>
        </div>
        {{-- Validation Summary --}}
        @if ($errors->any())
            <div role="alert" class="!mb-5 !rounded-xl !border !border-rose-200 !bg-rose-50 !px-4 !py-3 !text-sm !text-rose-800 !border-solid">
                <div class="!flex !items-start !gap-3">
                    <i aria-hidden="true" class="fa fa-exclamation-circle mt-0.5"></i>
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
        <div class="account-grid">
            <aside class="account-sidebar" aria-label="Account being edited">
                <span class="account-sidebar-icon" aria-hidden="true"><i class="fa fa-user"></i></span>
                <h3>{{ trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: 'User account' }}</h3>
                <p>{{ $user->email }}</p>
                <div class="account-sidebar-list">
                    <div><i class="fa fa-user" aria-hidden="true"></i>Account information</div>
                    <div><i class="fa fa-shield" aria-hidden="true"></i>Access role</div>
                    <div><i class="fa fa-lock" aria-hidden="true"></i>Password</div>
                </div>
                <p>Leave the password fields blank to keep the current password.</p>
            </aside>
            <form class="account-form" id="editUserForm" method="POST" action="{{ route('user-edit.update', $user->id) }}">
            @csrf
            {{-- Account Information --}}
            <section class="!mb-5 !overflow-hidden !rounded-2xl !border {{ $editBorder }} {{ $editSurface }} !shadow-sm !border-solid">
                <div class="!border-b {{ $editBorder }} !px-5 !py-4 !border-solid {{ $editInset }}">
                    <h2 class="account-section-heading !m-0 !text-sm !font-bold !uppercase !tracking-wide {{ $editHeading }}"><span class="account-section-number">01</span>Account Information</h2>
                    <p class="!m-0 !mt-1 !text-xs {{ $editMuted }}">Basic information used to identify and access the DIREK system.</p>
                </div>
                <div class="!p-5">
                    <div class="!grid !grid-cols-1 !gap-5 md:!grid-cols-2">
                        {{-- First Name --}}
                        <div>
                            <label for="firstname" class="!mb-1.5 !block !text-sm !font-semibold {{ $editText }}">
                                First Name
                                <span class="!text-rose-500">*</span>
                            </label>
                            <input type="text" id="firstname" name="firstname" value="{{ old('firstname', $user->firstname) }}" required maxlength="150" autocomplete="given-name" class="!box-border !block !min-h-[46px] !w-full !rounded-lg !border {{ $editBorder }} {{ $editSurface }} !px-3 !py-2.5 !text-sm {{ $editText }} !shadow-sm !outline-none !transition motion-reduce:!transition-none focus:!border-[#635bff] focus:!ring-2 focus:!ring-[#635bff]/20 !border-solid" @error('firstname') aria-invalid="true" aria-describedby="error-firstname" @enderror>
                            @error('firstname')
                                <p id="error-firstname" class="!m-0 !mt-1 !text-xs !font-medium {{ $editError }}">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Last Name --}}
                        <div>
                            <label for="lastname" class="!mb-1.5 !block !text-sm !font-semibold {{ $editText }}">
                                Last Name
                                <span class="!text-rose-500">*</span>
                            </label>
                            <input type="text" id="lastname" name="lastname" value="{{ old('lastname', $user->lastname) }}" required maxlength="150" autocomplete="family-name" class="!box-border !block !min-h-[46px] !w-full !rounded-lg !border {{ $editBorder }} {{ $editSurface }} !px-3 !py-2.5 !text-sm {{ $editText }} !shadow-sm !outline-none !transition motion-reduce:!transition-none focus:!border-[#635bff] focus:!ring-2 focus:!ring-[#635bff]/20 !border-solid" @error('lastname') aria-invalid="true" aria-describedby="error-lastname" @enderror>
                            @error('lastname')
                                <p id="error-lastname" class="!m-0 !mt-1 !text-xs !font-medium {{ $editError }}">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Email --}}
                        <div class="md:!col-span-2">
                            <label for="email" class="!mb-1.5 !block !text-sm !font-semibold {{ $editText }}">
                                Email Address
                                <span class="!text-rose-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" class="!box-border !block !min-h-[46px] !w-full !rounded-lg !border {{ $editBorder }} {{ $editSurface }} !px-3 !py-2.5 !text-sm {{ $editText }} !shadow-sm !outline-none !transition motion-reduce:!transition-none focus:!border-[#635bff] focus:!ring-2 focus:!ring-[#635bff]/20 !border-solid" @error('email') aria-invalid="true" aria-describedby="error-email" @enderror>
                            @error('email')
                                <p id="error-email" class="!m-0 !mt-1 !text-xs !font-medium {{ $editError }}">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>
            {{-- DIREK Access --}}
            <section class="!mb-5 !overflow-hidden !rounded-2xl !border {{ $editBorder }} {{ $editSurface }} !shadow-sm !border-solid">
                <div class="!border-b {{ $editBorder }} !px-5 !py-4 !border-solid {{ $editInset }}">
                    <h2 class="account-section-heading !m-0 !text-sm !font-bold !uppercase !tracking-wide {{ $editHeading }}"><span class="account-section-number">02</span>DIREK Access</h2>
                    <p class="!m-0 !mt-1 !text-xs {{ $editMuted }}">Select what level of authority this account has in DIREK.</p>
                </div>
                <div class="!p-5">
                    <div>
                        <label for="role" class="!mb-1.5 !block !text-sm !font-semibold {{ $editText }}">
                            Role
                            <span class="!text-rose-500">*</span>
                        </label>
                        <select name="role" id="role" required class="!box-border !block !min-h-[46px] !w-full !rounded-lg !border {{ $editBorder }} {{ $editSurface }} !px-3 !py-2.5 !text-sm {{ $editText }} !shadow-sm !outline-none !transition motion-reduce:!transition-none focus:!border-[#635bff] focus:!ring-2 focus:!ring-[#635bff]/20 !border-solid" @error('role') aria-invalid="true" aria-describedby="error-role" @enderror>
                            <option value="">Select Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ (string) old('role', $user->role_id) === (string) $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p id="error-role" class="!m-0 !mt-1 !text-xs !font-medium {{ $editError }}">{{ $message }}</p>
                        @enderror
                        <p class="!m-0 !mt-2 !text-xs {{ $editMuted }}">
                            Role determines what actions the user is allowed to perform.
                            Staff/Office assignment will determine which office records
                            the user can access.
                        </p>
                    </div>
                </div>
            </section>
            {{-- Password --}}
            <section class="!mb-5 !overflow-hidden !rounded-2xl !border {{ $editBorder }} {{ $editSurface }} !shadow-sm !border-solid">
                <div class="!border-b {{ $editBorder }} !px-5 !py-4 !border-solid {{ $editInset }}">
                    <h2 class="account-section-heading !m-0 !text-sm !font-bold !uppercase !tracking-wide {{ $editHeading }}"><span class="account-section-number">03</span>Change Password</h2>
                    <p class="!m-0 !mt-1 !text-xs {{ $editMuted }}">Leave both fields blank to keep the user's current password.</p>
                </div>
                <div class="!p-5">
                    <div class="!grid !grid-cols-1 !gap-5 md:!grid-cols-2">
                        <div>
                            <label for="password" class="!mb-1.5 !block !text-sm !font-semibold {{ $editText }}">
                                New Password
                            </label>
                            <input type="password" id="password" name="password" autocomplete="new-password" class="!box-border !block !min-h-[46px] !w-full !rounded-lg !border {{ $editBorder }} {{ $editSurface }} !px-3 !py-2.5 !text-sm {{ $editText }} !shadow-sm !outline-none !transition motion-reduce:!transition-none focus:!border-[#635bff] focus:!ring-2 focus:!ring-[#635bff]/20 !border-solid" @error('password') aria-invalid="true" aria-describedby="error-password" @enderror>
                            @error('password')
                                <p id="error-password" class="!m-0 !mt-1 !text-xs !font-medium {{ $editError }}">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="confirm-password" class="!mb-1.5 !block !text-sm !font-semibold {{ $editText }}">
                                Confirm New Password
                            </label>
                            <input type="password" id="confirm-password" name="confirm-password" autocomplete="new-password" class="!box-border !block !min-h-[46px] !w-full !rounded-lg !border {{ $editBorder }} {{ $editSurface }} !px-3 !py-2.5 !text-sm {{ $editText }} !shadow-sm !outline-none !transition motion-reduce:!transition-none focus:!border-[#635bff] focus:!ring-2 focus:!ring-[#635bff]/20 !border-solid" @error('confirm-password') aria-invalid="true" aria-describedby="error-confirm-password" @enderror>
                            @error('confirm-password')
                                <p id="error-confirm-password" class="!m-0 !mt-1 !text-xs !font-medium {{ $editError }}">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>
            {{-- Actions --}}
            <div class="!flex !flex-col-reverse !gap-3 !rounded-xl !border !border-solid {{ $editBorder }} {{ $editInset }} !p-4 sm:!flex-row sm:!justify-end">
                <a href="{{ route('user-management') }}" class="!no-underline !inline-flex !min-h-[44px] !items-center !justify-center !rounded-lg !border {{ $editBorder }} {{ $editSurface }} !px-5 !py-2.5 !text-sm !font-semibold {{ $editText }} !shadow-sm !transition focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#635bff] motion-reduce:!transition-none {{ $editDark ? 'hover:!bg-[#20283b]' : 'hover:!bg-slate-50' }} !border-solid">
                    Cancel
                </a>
                <button type="submit" id="btnSaveUserEdit" class="account-primary !border-0 disabled:!cursor-wait disabled:!opacity-60 !inline-flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !bg-[#635bff] !px-5 !py-2.5 !text-sm !font-semibold !text-white !shadow-sm !transition focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#635bff] motion-reduce:!transition-none hover:!bg-[#534be5]">
                    <i aria-hidden="true" class="fa fa-save"></i>
                    <span id="saveUserEditText">Save Changes</span>
                </button>
            </div>
        </form>
        </div>
    </div>
    <div class="!mt-6">
        @include('layouts.footers.auth.footer')
    </div>
</div>
@endsection
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editUserForm');
    const button = document.getElementById('btnSaveUserEdit');
    const text = document.getElementById('saveUserEditText');
    let saving = false;
    const password = document.getElementById('password');
    const confirmation = document.getElementById('confirm-password');
    function validatePasswordConfirmation() {
        confirmation.required = password.value !== '';
        confirmation.setCustomValidity(
            confirmation.value !== password.value ? 'The passwords do not match.' : ''
        );
    }
    password.addEventListener('input', validatePasswordConfirmation);
    confirmation.addEventListener('input', validatePasswordConfirmation);
    form.addEventListener('submit', function (event) {
        validatePasswordConfirmation();
        if (saving || !form.reportValidity()) {
            event.preventDefault();
            return;
        }
        saving = true;
        button.disabled = true;
        text.textContent = 'Saving…';
        form.setAttribute('aria-busy', 'true');
    });
    window.addEventListener('pageshow', function () {
        saving = false;
        button.disabled = false;
        text.textContent = 'Save Changes';
        form.removeAttribute('aria-busy');
    });
});
</script>
@endpush
@push('css')
<style>
    #direk-edit {
        --account-surface: #fff;
        --account-inset: #f6f7fb;
        --account-border: #e6e9f0;
        --account-heading: #172033;
        --account-copy: #637086;
        --account-accent: #635bff;
        --account-soft: #eeedff;
        --account-accent-copy: #5850df;
    }
    #direk-edit[data-theme="dark"] {
        --account-surface: #192130;
        --account-inset: #151c29;
        --account-border: #2c3546;
        --account-heading: #eef0f8;
        --account-copy: #95a0b5;
        --account-soft: #2b2850;
        --account-accent-copy: #a19cff;
    }
    #direk-edit [hidden] { display: none !important; }
    #direk-edit .account-workspace { max-width: 1400px !important; margin: 0 auto; }
    #direk-edit .account-page-title { font-size: clamp(26px, 3vw, 34px) !important; font-weight: 700 !important; letter-spacing: -.04em; line-height: 1.2; }
    #direk-edit .account-eyebrow { display: block; margin-bottom: 10px; color: var(--account-accent-copy); font-size: 10px; font-weight: 700; letter-spacing: .14em; }
    #direk-edit .account-grid { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 24px; align-items: start; }
    #direk-edit .account-sidebar { position: sticky; top: 28px; padding: 24px; border: 1px solid var(--account-border); border-radius: 20px; background: var(--account-surface); }
    #direk-edit .account-sidebar-icon { display: grid; width: 52px; height: 52px; place-items: center; margin-bottom: 20px; border-radius: 16px; background: var(--account-soft); color: var(--account-accent-copy); font-size: 20px; }
    #direk-edit .account-sidebar h3 { margin: 0; color: var(--account-heading); font-size: 16px; font-weight: 650; line-height: 1.5; }
    #direk-edit .account-sidebar p { margin: 8px 0 0; color: var(--account-copy); font-size: 12px; line-height: 1.8; overflow-wrap: anywhere; }
    #direk-edit .account-sidebar-list { display: grid; gap: 16px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--account-border); }
    #direk-edit .account-sidebar-list div { display: flex; align-items: center; gap: 10px; color: var(--account-copy); font-size: 12px; }
    #direk-edit .account-sidebar-list i { width: 18px; color: var(--account-accent-copy); text-align: center; }
    #direk-edit .account-form { min-width: 0; }
    #direk-edit .account-form > section { border-radius: 18px !important; border-color: var(--account-border) !important; background: var(--account-surface) !important; box-shadow: 0 4px 20px rgba(20,25,60,.025) !important; overflow: hidden; }
    #direk-edit .account-form > section > div:first-child { padding: 20px 24px !important; background: var(--account-surface) !important; border-color: var(--account-border) !important; }
    #direk-edit .account-form > section > div:nth-child(2) { padding: 24px !important; }
    #direk-edit .account-section-heading { display: flex; align-items: center; gap: 10px; color: var(--account-heading) !important; font-size: 13px !important; font-weight: 650 !important; text-transform: none !important; letter-spacing: -.01em !important; }
    #direk-edit .account-section-number { display: grid; flex: 0 0 28px; width: 28px; height: 28px; place-items: center; border-radius: 9px; background: var(--account-soft); color: var(--account-accent-copy); font-size: 10px; }
    #direk-edit .account-form label { font-size: 12px !important; }
    #direk-edit .account-form input:not([type="hidden"]), #direk-edit .account-form select { min-height: 44px !important; border-radius: 10px !important; box-shadow: none !important; font-size: 13px !important; }
    #direk-edit .account-form input:focus, #direk-edit .account-form select:focus { border-color: var(--account-accent) !important; box-shadow: 0 0 0 3px rgba(99,91,255,.12) !important; }
    #direk-edit .account-form input[type="file"]::file-selector-button { border: 0; border-radius: 6px; background: var(--account-soft); color: var(--account-accent-copy); padding: 5px 9px; margin-right: 10px; font-size: 12px; }
    #direk-edit .account-form [aria-invalid="true"] { border-color: #e55378 !important; }
    #direk-edit .account-primary { background: #635bff !important; border-radius: 10px !important; box-shadow: 0 3px 8px rgba(99,91,255,.15) !important; }
    #direk-edit .account-primary:hover { background: #534be5 !important; }
    #direk-edit .account-form .choices { margin: 0; }
    #direk-edit .account-form .choices__inner { min-height: 44px; border: 1px solid var(--account-border) !important; border-radius: 10px !important; background: var(--account-surface) !important; color: var(--account-heading) !important; padding: 8px 10px !important; font-size: 13px; }
    #direk-edit .account-form .choices__input { background: var(--account-surface) !important; color: var(--account-heading) !important; }
    #direk-edit .account-form .choices__list--dropdown, #direk-edit .account-form .choices__list[aria-expanded] { border-color: var(--account-border) !important; background: var(--account-surface) !important; color: var(--account-heading) !important; z-index: 20; }
    #direk-edit .account-form .choices__list--dropdown .is-highlighted { background: var(--account-soft) !important; }
    #direk-edit .account-form .choices.is-focused .choices__inner { border-color: var(--account-accent) !important; box-shadow: 0 0 0 3px rgba(99,91,255,.12); }
    #direk-edit .account-form .choices[data-type*="select-one"]:after { border-color: var(--account-copy) transparent transparent !important; }
    #direk-edit .account-form .choices.is-open[data-type*="select-one"]:after { border-color: transparent transparent var(--account-copy) !important; }
    #direk-edit .account-form .choices__list--multiple .choices__item { border-color: #635bff; background: #635bff; }
    @media (max-width: 991px) { #direk-edit .account-grid { grid-template-columns: 1fr; } #direk-edit .account-sidebar { display: none; } }
    @media (max-width: 639px) { #direk-edit .account-form > section > div:first-child, #direk-edit .account-form > section > div:nth-child(2) { padding: 18px !important; } }
    @media (prefers-reduced-motion: reduce) { #direk-edit .account-form * { transition: none !important; } }
</style>
@endpush

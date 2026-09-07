@php
    $profileAgencyId = old('agency_id', auth()->user()->agency_id);
    $showStaffDivision = in_array((string) $profileAgencyId, $depDevAgencyIds, true);
    $profileRole = optional(auth()->user()->role)->name ?? 'User';
@endphp

@extends('layouts.app')

@section('content')
<style>
    /* DIREK User Profile - aligned with User Management V2 */
    .direk-profile-shell {
        margin-top: 0.5rem;
    }

    .direk-profile-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .direk-profile-toolbar {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .direk-profile-section {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .direk-profile-section:last-child {
        border-bottom: 0;
    }

    .direk-profile-label {
        display: block;
        margin-bottom: 0.375rem;
        color: #334155;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .direk-profile-input,
    .direk-profile-select {
        display: block;
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 0.625rem;
        background: #ffffff;
        padding: 0.625rem 0.75rem;
        color: #334155;
        font-size: 0.875rem;
        outline: none;
        transition: 0.15s ease;
    }

    .direk-profile-input:focus,
    .direk-profile-select:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
    }

    .direk-profile-input[readonly],
    .direk-profile-input:disabled {
        background: #f8fafc;
        color: #64748b;
        cursor: not-allowed;
    }

    .direk-profile-role {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.3rem 0.6rem;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .direk-avatar-wrap {
        position: relative;
        width: 82px;
        height: 82px;
        flex: 0 0 82px;
    }

    .direk-avatar-wrap img {
        width: 82px;
        height: 82px;
        border-radius: 18px;
        object-fit: cover;
        border: 3px solid #ffffff;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.14);
    }

    .direk-avatar-edit {
        position: absolute;
        right: -5px;
        bottom: -5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #0369a1;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
        transition: 0.15s ease;
    }

    .direk-avatar-edit:hover {
        background: #f0f9ff;
        transform: translateY(-1px);
    }

    .direk-profile-error {
        margin-top: 0.35rem;
        color: #e11d48;
        font-size: 0.75rem;
    }

    .direk-profile-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    /* Keep Tom Select visually aligned with the DIREK form */
    .ts-wrapper .ts-control {
        min-height: 42px;
        border: 1px solid #cbd5e1 !important;
        border-radius: 0.625rem !important;
        padding: 0.55rem 0.75rem !important;
        box-shadow: none !important;
        font-size: 0.875rem;
    }

    .ts-wrapper.focus .ts-control {
        border-color: #0ea5e9 !important;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12) !important;
    }

    @media (max-width: 767px) {
        .direk-profile-toolbar,
        .direk-profile-section,
        .direk-profile-actions {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
</style>

<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'My Profile'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="px-4 pb-8 pt-4">
    <div class="direk-profile-shell mx-auto max-w-6xl">

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <div class="flex items-start gap-3">
                    <i class="fa fa-exclamation-circle mt-0.5"></i>
                    <div>
                        <p class="font-semibold">Please review the information below.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form
            autocomplete="off"
            method="POST"
            action="{{ route('user-profile.perform') }}"
            enctype="multipart/form-data"
            id="frmUpdate"
        >
            @csrf

            <div class="direk-profile-card">
                <div class="direk-profile-toolbar">
                    <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="direk-avatar-wrap">
                                <img
                                    id="avatar-preview"
                                    src="{{ auth()->user()->avatarUrl() }}"
                                    alt="Profile photo"
                                >

                                <label
                                    for="file-input"
                                    class="direk-avatar-edit"
                                    title="Change profile photo"
                                >
                                    <i class="fa fa-pencil"></i>
                                    <span class="sr-only">Change profile photo</span>
                                </label>

                                <input
                                    type="file"
                                    name="avatar"
                                    id="file-input"
                                    accept="image/*"
                                    class="hidden"
                                    onchange="updateavatar()"
                                >
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h1 class="text-xl font-bold text-slate-900">
                                        {{ trim((auth()->user()->firstname ?? '') . ' ' . (auth()->user()->lastname ?? '')) }}
                                    </h1>
                                    <span class="direk-profile-role">{{ $profileRole }}</span>
                                </div>

                                <p class="mt-1 text-sm font-medium text-slate-600">
                                    {{ auth()->user()->position_name() ?: 'No position assigned' }}
                                </p>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ auth()->user()->staff_name() ?: 'No staff/office assigned' }}
                                </p>
                            </div>
                        </div>

                        <div class="text-left md:text-right">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Account
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                {{ auth()->user()->email }}
                            </p>
                        </div>
                    </div>
                </div>

                <section class="direk-profile-section">
                    <div class="mb-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-800">
                            Account Information
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Your official DIREK identity and contact details.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="firstname" class="direk-profile-label">
                                First Name
                            </label>
                            <input
                                id="firstname"
                                name="firstname"
                                class="direk-profile-input"
                                type="text"
                                value="{{ old('firstname', auth()->user()->firstname) }}"
                                readonly
                            >
                            @error('firstname')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="middlename" class="direk-profile-label">
                                Middle Name
                            </label>
                            <input
                                id="middlename"
                                name="middlename"
                                class="direk-profile-input"
                                type="text"
                                value="{{ old('middlename', auth()->user()->middlename) }}"
                                readonly
                            >
                            @error('middlename')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="lastname" class="direk-profile-label">
                                Last Name
                            </label>
                            <input
                                id="lastname"
                                name="lastname"
                                class="direk-profile-input"
                                type="text"
                                value="{{ old('lastname', auth()->user()->lastname) }}"
                                readonly
                            >
                            @error('lastname')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="email" class="direk-profile-label">
                                Email Address
                            </label>
                            <input
                                id="email"
                                name="email"
                                class="direk-profile-input"
                                type="email"
                                value="{{ old('email', auth()->user()->email) }}"
                                readonly
                            >
                            @error('email')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="direk-profile-label">
                                Phone Number
                            </label>
                            <input
                                id="phone"
                                name="phone"
                                class="direk-profile-input"
                                type="text"
                                value="{{ old('phone', auth()->user()->phone) }}"
                                placeholder="+63 9XX XXX XXXX"
                            >
                            @error('phone')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="gender" class="direk-profile-label">
                                Gender
                            </label>
                            <select
                                name="gender"
                                id="gender"
                                class="direk-profile-select hide-search"
                                autocomplete="off"
                            >
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', auth()->user()->gender) == 'Male' ? 'selected' : '' }}>
                                    Male
                                </option>
                                <option value="Female" {{ old('gender', auth()->user()->gender) == 'Female' ? 'selected' : '' }}>
                                    Female
                                </option>
                            </select>
                            @error('gender')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="birthday" class="direk-profile-label">
                                Birth Date
                            </label>
                            <input
                                id="birthday"
                                name="birthday"
                                class="direk-profile-input"
                                type="date"
                                value="{{ old('birthday', auth()->user()->birthday) }}"
                            >
                            @error('birthday')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="position_id" class="direk-profile-label">
                                Position
                            </label>
                            <select
                                name="position_id"
                                id="position_id"
                                class="direk-profile-select"
                                autocomplete="off"
                            >
                                <option value="">Select Position</option>
                                @foreach ($positions as $position)
                                    <option
                                        value="{{ $position->id }}"
                                        {{ old('position_id', auth()->user()->position_id) == $position->id ? 'selected' : '' }}
                                    >
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position_id')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="direk-profile-section">
                    <div class="mb-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-800">
                            Organizational Assignment
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Your agency, staff/office, division, and office location.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="direk-profile-label" for="agency_id">
                                Agency
                            </label>

                            @if (auth()->user()->isSuperAdmin() || (auth()->user()->first_login == 'Y' && empty(auth()->user()->agency_id)))
                                <select
                                    name="agency_id"
                                    id="agency_id"
                                    class="direk-profile-select"
                                    autocomplete="off"
                                    onchange="updateStaffDivisionFields()"
                                >
                                    <option value="">Select Agency</option>
                                    @foreach ($agencies as $agency)
                                        <option
                                            value="{{ $agency->id }}"
                                            {{ old('agency_id', auth()->user()->agency_id) == $agency->id ? 'selected' : '' }}
                                        >
                                            {{ $agency->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input
                                    id="agency"
                                    name="agency"
                                    class="direk-profile-input"
                                    type="text"
                                    disabled
                                    value="{{ old('agency', optional(auth()->user()->agency)->display_name) }}"
                                >
                            @endif

                            @error('agency_id')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div
                            id="staffField"
                            @if(!$showStaffDivision) hidden @endif
                        >
                            <label class="direk-profile-label" for="staff_id">
                                Staff / Office
                            </label>

                            @if (auth()->user()->first_login == 'Y')
                                <select
                                    name="staff_id"
                                    id="staff_id"
                                    class="direk-profile-select"
                                    autocomplete="off"
                                    onchange="ocStaff()"
                                >
                                    <option value="">Select Staff / Office</option>
                                    @foreach ($staffs as $staff)
                                        <option
                                            value="{{ $staff->id }}"
                                            {{ old('staff_id', auth()->user()->staff_id) == $staff->id ? 'selected' : '' }}
                                        >
                                            {{ $staff->name }}
                                            @if($staff->abbreviation)
                                                ({{ $staff->abbreviation }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input
                                    id="staff"
                                    name="staff"
                                    class="direk-profile-input"
                                    type="text"
                                    disabled
                                    value="{{ old('staff', optional(auth()->user()->staff)->name) }}"
                                >
                            @endif

                            @error('staff_id')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div
                            id="divisionField"
                            @if(!$showStaffDivision) hidden @endif
                        >
                            <label class="direk-profile-label" for="division_id">
                                Division
                            </label>

                            @if (auth()->user()->first_login == 'Y')
                                <select
                                    name="division_id"
                                    id="division_id"
                                    class="direk-profile-select"
                                    autocomplete="off"
                                >
                                    <option value="">Select Division</option>
                                    @foreach ($divisions as $division)
                                        <option
                                            value="{{ $division->id }}"
                                            data-name="{{ $division->name }}"
                                            data-abbreviation="{{ $division->abbreviation }}"
                                            {{ old('division_id', auth()->user()->division_id) == $division->id ? 'selected' : '' }}
                                        >
                                            {{ $division->name }}
                                            @if($division->abbreviation)
                                                ({{ $division->abbreviation }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input
                                    id="division"
                                    name="division"
                                    class="direk-profile-input"
                                    type="text"
                                    disabled
                                    value="{{ old('division', optional(auth()->user()->division)->name) }}"
                                >
                            @endif

                            @error('division_id')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="location" class="direk-profile-label">
                                Office Location
                            </label>
                            <input
                                id="location"
                                name="location"
                                class="direk-profile-input"
                                type="text"
                                value="{{ old('location', auth()->user()->location) }}"
                                placeholder="Office location"
                            >
                            @error('location')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="direk-profile-section">
                    <div class="mb-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-800">
                            Change Password
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Leave these fields blank if you do not want to change your password.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="old-password" class="direk-profile-label">
                                Current Password
                            </label>
                            <input
                                id="old-password"
                                name="old-password"
                                class="direk-profile-input"
                                type="password"
                                value=""
                                autocomplete="current-password"
                                placeholder="Current password"
                            >
                            @error('old-password')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new-password" class="direk-profile-label">
                                New Password
                            </label>
                            <input
                                id="new-password"
                                name="new-password"
                                class="direk-profile-input"
                                type="password"
                                autocomplete="new-password"
                                placeholder="New password"
                            >
                            @error('new-password')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="confirm-password" class="direk-profile-label">
                                Confirm Password
                            </label>
                            <input
                                id="confirm-password"
                                name="confirm-password"
                                class="direk-profile-input"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Confirm new password"
                            >
                            @error('confirm-password')
                                <p class="direk-profile-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="direk-profile-section">
                    <div class="mb-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-800">
                            Security & Settings
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Manage your DIREK account security preferences.
                        </p>
                    </div>

                    @include('users.components.settings', ['user' => auth()->user()])
                </section>

                <section
                    id="divTrustedDevices"
                    class="direk-profile-section"
                    @if(auth()->user()->twofactor != 'Y') hidden @endif
                >
                    <div class="mb-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-800">
                            Trusted Devices
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Review devices currently trusted for your DIREK account.
                        </p>
                    </div>

                    @include('users.components.sessions', ['user' => auth()->user()])
                </section>

                <div class="direk-profile-actions">
                    <button
                        type="button"
                        id="btnCancel"
                        onclick="occancel()"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        id="btnSave"
                        onclick="ocSubmit()"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700"
                    >
                        <i class="fa fa-save"></i>
                        <span>Save Changes</span>
                    </button>

                    <button
                        type="button"
                        id="btnSaveDisabled"
                        disabled
                        hidden
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-400 px-5 py-2.5 text-sm font-semibold text-white"
                    >
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span>Saving...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>
</div>
@endsection

@push('js')
    @include('users.components.scripts')

    <script>
        const divisions = @json($divisions);
        const depDevAgencyIds = @json($depDevAgencyIds);
        const currentAgencyId = @json((string) auth()->user()->agency_id);

        initTomSelect('gender');
        initTomSelect('position_id', true);

        @if(auth()->user()->isSuperAdmin() || (auth()->user()->first_login == 'Y' && empty(auth()->user()->agency_id)))
            initTomSelect('agency_id', true);
        @endif

        @if(auth()->user()->first_login == 'Y')
            initTomSelect('staff_id', false);

            const customRender = {
                option: function(data, escape) {
                    return `<div>
                        <span class="title">${escape(data.name)}</span>
                        <span class="title"> (${escape(data.abbreviation || '')})</span>
                    </div>`;
                },
                item: function(data, escape) {
                    return `<div>${escape(data.name)} (${escape(data.abbreviation || '')})</div>`;
                }
            };

            initTomSelect(
                'division_id',
                true,
                false,
                false,
                null,
                null,
                customRender
            );
        @endif

        updateStaffDivisionFields();

        function ocSubmit() {
            document.getElementById('btnSave').hidden = true;
            document.getElementById('btnCancel').disabled = true;
            document.getElementById('btnSaveDisabled').hidden = false;
            document.getElementById('frmUpdate').submit();
        }

        function updateavatar() {
            const fileInput = document.getElementById('file-input');

            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function(event) {
                document.getElementById('avatar-preview').src = event.target.result;
            };

            reader.readAsDataURL(fileInput.files[0]);
        }

        function occancel() {
            if (confirm('Discard unsaved changes?')) {
                location.reload();
            }
        }

        function ocStaff() {
            if (
                typeof tomSelects === 'undefined' ||
                !tomSelects['staff_id'] ||
                !tomSelects['division_id']
            ) {
                return;
            }

            const staffId = $('#staff_id').val();
            const oldDivisionId = $('#division_id').val();

            tomSelects['division_id'].clear();
            tomSelects['division_id'].clearOptions();

            if (!staffId) {
                return;
            }

            const newOptions = [];
            let selectedDivisionStillValid = false;

            const filteredDivisions = divisions.filter(function(item) {
                return String(item.staff_id) === String(staffId);
            });

            $.each(filteredDivisions, function(key, value) {
                newOptions.push({
                    value: String(value.id),
                    text: value.name + (value.abbreviation ? ' (' + value.abbreviation + ')' : ''),
                    name: value.name,
                    abbreviation: value.abbreviation || ''
                });

                if (String(value.id) === String(oldDivisionId)) {
                    selectedDivisionStillValid = true;
                }
            });

            tomSelects['division_id'].addOptions(newOptions);

            if (selectedDivisionStillValid) {
                tomSelects['division_id'].setValue(String(oldDivisionId));
            }
        }

        function updateStaffDivisionFields() {
            const agencyElement = document.getElementById('agency_id');
            const agencyId = agencyElement ? String($('#agency_id').val() || '') : currentAgencyId;
            const showStaffDivision = depDevAgencyIds.map(String).includes(String(agencyId));

            const staffField = document.getElementById('staffField');
            const divisionField = document.getElementById('divisionField');

            if (staffField) {
                staffField.hidden = !showStaffDivision;
            }

            if (divisionField) {
                divisionField.hidden = !showStaffDivision;
            }

            if (
                !showStaffDivision &&
                typeof tomSelects !== 'undefined' &&
                tomSelects['staff_id'] &&
                tomSelects['division_id']
            ) {
                tomSelects['staff_id'].setValue('');
                tomSelects['division_id'].setValue('');
                tomSelects['division_id'].clearOptions();
            }
        }
    </script>
@endpush

@extends('layouts.app')

@section('content')

<style>

    */ User Management V2 */

    .direk-users-shell { margin-top: 0.5rem; }

    .direk-users-card { border: 1px solid #e2e8f0; border-radius: 18px; background: #fff; box-shadow: 0 10px 30px rgba(15,23,42,.08); overflow: hidden; }

    .direk-users-toolbar { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: linear-gradient(180deg,#fff 0%,#f8fafc 100%); }

    .direk-user-count { display:inline-flex; align-items:center; min-height:26px; padding:0 10px; border-radius:999px; background:#e0f2fe; color:#0369a1; font-size:12px; font-weight:700; }

    .direk-users-table-wrap { padding: 0.5rem 1.25rem 1.25rem; overflow-x:auto; }

    #usersTable { border-collapse: separate !important; border-spacing: 0 !important; }

    #usersTable thead th { background:#f8fafc; border-bottom:1px solid #e2e8f0 !important; color:#64748b; font-size:11px; font-weight:800; letter-spacing:.06em; padding:13px 12px !important; white-space:nowrap; }

    #usersTable tbody td { border-bottom:1px solid #f1f5f9 !important; color:#475569; font-size:13px; padding:14px 12px !important; vertical-align:middle; }

    #usersTable tbody tr:hover td { background:#f8fafc; }

    #usersTable tbody tr:last-child td { border-bottom:0 !important; }

    .direk-user-cell { display:flex; align-items:center; gap:10px; min-width:190px; }

    .direk-user-initials { width:36px; height:36px; flex:0 0 36px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; background:#e0f2fe; color:#0369a1; font-size:12px; font-weight:800; letter-spacing:.02em; }

    .direk-user-name { color:#0f172a; font-weight:700; line-height:1.25; white-space:nowrap; }

    .direk-role-badge { display:inline-flex; align-items:center; border-radius:999px; padding:5px 9px; background:#f1f5f9; color:#334155; font-size:11px; font-weight:700; line-height:1.2; }

    .direk-role-badge.is-admin { background:#ede9fe; color:#6d28d9; }

    .direk-role-badge.is-director { background:#dbeafe; color:#1d4ed8; }

    .direk-role-badge.is-staff { background:#dcfce7; color:#15803d; }

    #usersTable td:last-child { white-space:nowrap; min-width:86px; }

    #usersTable td:last-child a, #usersTable td:last-child button { display:inline-flex !important; align-items:center; justify-content:center; width:32px; height:32px; margin:0 2px; border-radius:8px; transition:.15s ease; }

    #usersTable td:last-child a:hover, #usersTable td:last-child button:hover { background:#f1f5f9; transform:translateY(-1px); }

    #usersTable_wrapper .dataTables_length, #usersTable_wrapper .dataTables_filter { margin: 0.75rem 0; color:#64748b; font-size:12px; }

    #usersTable_wrapper .dataTables_filter input { min-width:230px; border:1px solid #cbd5e1; border-radius:10px; padding:8px 11px; outline:none; background:#fff; }

    #usersTable_wrapper .dataTables_filter input:focus { border-color:#0ea5e9; box-shadow:0 0 0 3px rgba(14,165,233,.12); }

    #usersTable_wrapper .dataTables_length select { border:1px solid #cbd5e1; border-radius:9px; padding:6px 28px 6px 9px; background:#fff; }

    #usersTable_wrapper .dataTables_info { color:#64748b; font-size:12px; padding-top:1rem; }

    #usersTable_wrapper .dataTables_paginate { padding-top:.75rem; }

    @media (max-width: 900px) { .direk-users-table-wrap { padding-left:.75rem; padding-right:.75rem; } #usersTable_wrapper .dataTables_filter input { min-width:170px; } }

</style>

<nav

    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"

    id="navbarBlur"

    data-scroll="false"

>

    <div class="container-fluid py-2 px-3">

        @include('layouts.navbars.auth.topnav', ['title' => 'User Management'])

        @include('layouts.navbars.auth.topnav-withdatetime')

    </div>

</nav>

<div class="px-4 pb-8 pt-4">

    <div class="direk-users-shell">

        {{-- Validation Errors --}}

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

        <div class="direk-users-card">

            <div class="direk-users-toolbar">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <div class="flex flex-wrap items-center gap-2">

                            <h1 class="text-xl font-bold text-slate-900">User Management</h1>

                            <span id="userCountBadge" class="direk-user-count">Users</span>

                        </div>

                        <p class="mt-1 text-sm text-slate-500">

                            Manage DIREK accounts, office assignments, and access roles.

                        </p>

                    </div>

                    <button type="button" id="btnAddUser"

                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">

                        <i class="fa fa-plus"></i>

                        <span>Add User</span>

                    </button>

                </div>

            </div>

            <div class="direk-users-table-wrap">

                <table id="usersTable" class="min-w-full" style="width: 100%;">

                    <thead>

                        <tr>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Staff / Office</th>

                            <th>Division</th>

                            <th>Position</th>

                            <th>Role</th>

                            <th class="text-center">Actions</th>

                        </tr>

                    </thead>

                    <tbody></tbody>

                </table>

            </div>

        </div>

    </div>

    <div class="mt-6">

        @include('layouts.footers.auth.footer')

    </div>

</div>

{{-- User Modal --}}

<div

    id="userModal"

    class="fixed inset-0 z-[1055] hidden overflow-y-auto bg-slate-900/50 p-4"

    aria-hidden="true"

>

    <div class="flex min-h-full items-start justify-center py-8">

        <div

            class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl"

            role="dialog"

            aria-modal="true"

            aria-labelledby="userModalTitle"

        >

            {{-- Modal Header --}}

            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">

                <div>

                    <h2

                        id="userModalTitle"

                        class="text-lg font-bold text-slate-900"

                    >

                        Add User

                    </h2>

                    <p

                        id="userModalSubtitle"

                        class="mt-1 text-xs text-slate-500"

                    >

                        Create a new DIREK user account.

                    </p>

                </div>

                <button

                    type="button"

                    id="btnCloseUserModal"

                    class="inline-flex h-9 w-9 items-center justify-center

                           rounded-lg text-slate-500 transition hover:bg-slate-100

                           hover:text-slate-800"

                    aria-label="Close"

                >

                    <i class="fa fa-times"></i>

                </button>

            </div>

            {{-- User Form --}}

            <form

                id="userForm"

                method="POST"

                action="{{ route('users.store') }}"

            >

                @csrf

                <input

                    type="hidden"

                    name="_method"

                    id="userFormMethod"

                    value="POST"

                >

                <input
                    type="hidden"
                    name="_editing_user_id"
                    id="editingUserId"
                    value=""
                >

                <div class="max-h-[72vh] overflow-y-auto p-5">

                    {{-- Account Information --}}

                    <section class="mb-6">

                        <div class="mb-4">

                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-800">

                                Account Information

                            </h3>

                            <p class="mt-1 text-xs text-slate-500">

                                Basic information used to identify the DIREK user.

                            </p>

                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                            <div>

                                <label

                                    for="firstname"

                                    class="mb-1.5 block text-sm font-semibold text-slate-700"

                                >

                                    First Name

                                    <span class="text-rose-500">*</span>

                                </label>

                                <input

                                    type="text"

                                    id="firstname"

                                    name="firstname"

                                    required

                                    maxlength="255"

                                    autocomplete="given-name"

                                    class="block w-full rounded-lg border border-slate-300

                                           bg-white px-3 py-2.5 text-sm text-slate-700

                                           shadow-sm outline-none transition

                                           focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                                >

                            </div>

                            <div>

                                <label

                                    for="middlename"

                                    class="mb-1.5 block text-sm font-semibold text-slate-700"

                                >

                                    Middle Name

                                </label>

                                <input

                                    type="text"

                                    id="middlename"

                                    name="middlename"

                                    maxlength="255"

                                    autocomplete="additional-name"

                                    class="block w-full rounded-lg border border-slate-300

                                           bg-white px-3 py-2.5 text-sm text-slate-700

                                           shadow-sm outline-none transition

                                           focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                                >

                            </div>

                            <div>

                                <label

                                    for="lastname"

                                    class="mb-1.5 block text-sm font-semibold text-slate-700"

                                >

                                    Last Name

                                    <span class="text-rose-500">*</span>

                                </label>

                                <input

                                    type="text"

                                    id="lastname"

                                    name="lastname"

                                    required

                                    maxlength="255"

                                    autocomplete="family-name"

                                    class="block w-full rounded-lg border border-slate-300

                                           bg-white px-3 py-2.5 text-sm text-slate-700

                                           shadow-sm outline-none transition

                                           focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                                >

                            </div>

                            <div>

                                <label

                                    for="email"

                                    class="mb-1.5 block text-sm font-semibold text-slate-700"

                                >

                                    Email Address

                                    <span class="text-rose-500">*</span>

                                </label>

                                <input

                                    type="email"

                                    id="email"

                                    name="email"

                                    required

                                    maxlength="255"

                                    autocomplete="email"

                                    class="block w-full rounded-lg border border-slate-300

                                           bg-white px-3 py-2.5 text-sm text-slate-700

                                           shadow-sm outline-none transition

                                           focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                                >

                            </div>

                        </div>

                    </section>

                    <hr class="mb-6 border-slate-200">

                    {{-- Organizational Assignment --}}

                    <section class="mb-6">

                        <div class="mb-4">

                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-800">

                                Organizational Assignment

                            </h3>

                            <p class="mt-1 text-xs text-slate-500">

                                Assign the user's agency, staff/office, division, and position.

                            </p>

                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                            <div>

                                <label

                                    for="agency_id"

                                    class="mb-1.5 block text-sm font-semibold text-slate-700"

                                >

                                    Agency

                                    <span class="text-rose-500">*</span>

                                </label>

                                <select

                                    id="agency_id"

                                    name="agency_id"

                                    required

                                    class="block w-full rounded-lg border border-slate-300

                                           bg-white px-3 py-2.5 text-sm text-slate-700

                                           shadow-sm outline-none transition

                                           focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                                >

                                    <option value="">

                                        Select Agency

                                    </option>

                                    @foreach ($agencies as $agency)

                                        <option value="{{ $agency->id }}">

                                            {{ $agency->display_name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            <div id="staffField">

                                <label

                                    for="staff_id"

                                    class="mb-1.5 block text-sm font-semibold text-slate-700"

                                >

                                    Staff / Office

                                    <span class="text-rose-500">*</span>

                                </label>

                                <select

                                    id="staff_id"

                                    name="staff_id"

                                    class="block w-full rounded-lg border border-slate-300

                                           bg-white px-3 py-2.5 text-sm text-slate-700

                                           shadow-sm outline-none transition

                                           focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                                >

                                    <option value="">

                                        Select Staff / Office

                                    </option>

                                    @foreach ($staffs as $staff)

                                        <option value="{{ $staff->id }}">

                                            {{ $staff->name }}

                                            @if ($staff->abbreviation)

                                                ({{ $staff->abbreviation }})

                                            @endif

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            <div id="divisionField">

                                <label

                                    for="division_id"

                                    class="mb-1.5 block text-sm font-semibold text-slate-700"

                                >

                                    Division

                                    <span class="text-rose-500">*</span>

                                </label>

                                <select

                                    id="division_id"

                                    name="division_id"

                                    class="block w-full rounded-lg border border-slate-300

                                           bg-white px-3 py-2.5 text-sm text-slate-700

                                           shadow-sm outline-none transition

                                           focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                                >

                                    <option value="">

                                        Select Division

                                    </option>

                                    @foreach ($divisions as $division)

                                        <option

                                            value="{{ $division->id }}"

                                            data-staff-id="{{ $division->staff_id }}"

                                        >

                                            {{ $division->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            <div>

                                <label

                                    for="position_id"

                                    class="mb-1.5 block text-sm font-semibold text-slate-700"

                                >

                                    Position

                                </label>

                                <select

                                    id="position_id"

                                    name="position_id"

                                    class="block w-full rounded-lg border border-slate-300

                                           bg-white px-3 py-2.5 text-sm text-slate-700

                                           shadow-sm outline-none transition

                                           focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                                >

                                    <option value="">

                                        Select Position

                                    </option>

                                    @foreach ($positions as $position)

                                        <option value="{{ $position->id }}">

                                            {{ $position->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                        </div>

                    </section>

                    <hr class="mb-6 border-slate-200">

                    {{-- DIREK Access --}}

                    <section class="mb-6">

                        <div class="mb-4">

                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-800">

                                DIREK Access

                            </h3>

                            <p class="mt-1 text-xs text-slate-500">

                                Role determines what actions the user is authorized to perform.

                            </p>

                        </div>

                        <div>

                            <label

                                for="role_id"

                                class="mb-1.5 block text-sm font-semibold text-slate-700"

                            >

                                Role

                                <span class="text-rose-500">*</span>

                            </label>

                            <select

                                id="role_id"

                                name="role_id"

                                required

                                class="block w-full rounded-lg border border-slate-300

                                       bg-white px-3 py-2.5 text-sm text-slate-700

                                       shadow-sm outline-none transition

                                       focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                            >

                                <option value="">

                                    Select DIREK Role

                                </option>

                                @foreach ($roles as $role)

                                    <option value="{{ $role->id }}">

                                        {{ $role->name }}

                                    </option>

                                @endforeach

                            </select>

                            <p class="mt-2 text-xs text-slate-500">

                                Staff/Office controls which records the user can access.

                                Role controls what the user can do with those records.

                            </p>

                        </div>

                    </section>

                    <hr class="mb-6 border-slate-200">

                    {{-- Password --}}

                    <section>

                        <div class="mb-4">

                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-800">

                                Password

                            </h3>

                            <p

                                id="passwordHelp"

                                class="mt-1 text-xs text-slate-500"

                            >

                                Set the initial password for this account.

                            </p>

                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                            <div>

                                <label

                                    for="new-password"

                                    class="mb-1.5 block text-sm font-semibold text-slate-700"

                                >

                                    New Password

                                    <span

                                        id="passwordRequired"

                                        class="text-rose-500"

                                    >*</span>

                                </label>

                                <input

                                    type="password"

                                    id="new-password"

                                    name="new-password"

                                    minlength="6"

                                    autocomplete="new-password"

                                    class="block w-full rounded-lg border border-slate-300

                                           bg-white px-3 py-2.5 text-sm text-slate-700

                                           shadow-sm outline-none transition

                                           focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                                >

                            </div>

                            <div>

                                <label

                                    for="confirm-password"

                                    class="mb-1.5 block text-sm font-semibold text-slate-700"

                                >

                                    Confirm Password

                                    <span

                                        id="confirmPasswordRequired"

                                        class="text-rose-500"

                                    >*</span>

                                </label>

                                <input

                                    type="password"

                                    id="confirm-password"

                                    name="confirm-password"

                                    minlength="6"

                                    autocomplete="new-password"

                                    class="block w-full rounded-lg border border-slate-300

                                           bg-white px-3 py-2.5 text-sm text-slate-700

                                           shadow-sm outline-none transition

                                           focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                                >

                            </div>

                        </div>

                    </section>

                </div>

                {{-- Modal Footer --}}

                <div

                    class="flex flex-col-reverse gap-3 border-t border-slate-200

                           bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end"

                >

                    <button

                        type="button"

                        id="btnCancelUser"

                        class="inline-flex items-center justify-center rounded-lg

                               border border-slate-300 bg-white px-5 py-2.5

                               text-sm font-semibold text-slate-700 shadow-sm

                               transition hover:bg-slate-50"

                    >

                        Cancel

                    </button>

                    <button

                        type="submit"

                        class="inline-flex items-center justify-center gap-2 rounded-lg

                               bg-sky-600 px-5 py-2.5 text-sm font-semibold

                               text-white shadow-sm transition hover:bg-sky-700"

                    >

                        <i class="fa fa-save"></i>

                        <span id="btnSaveUserText">

                            Save User

                        </span>

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection

@push('js')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const storeUrl = @json(route('users.store'));

    const getUsersUrl = @json(route('getusers'));

    const csrfToken = @json(csrf_token());

    const depDevAgencyIds = @json(collect($depDevAgencyIds)->values()->all());

    const modal = document.getElementById('userModal');

    const form = document.getElementById('userForm');

    const formMethod = document.getElementById('userFormMethod');
    const editingUserId = document.getElementById('editingUserId');

    const modalTitle = document.getElementById('userModalTitle');

    const modalSubtitle = document.getElementById('userModalSubtitle');

    const passwordHelp = document.getElementById('passwordHelp');

    const passwordRequired = document.getElementById('passwordRequired');

    const confirmPasswordRequired = document.getElementById('confirmPasswordRequired');

    const newPassword = document.getElementById('new-password');

    const confirmPassword = document.getElementById('confirm-password');

    const agencySelect = document.getElementById('agency_id');

    const staffSelect = document.getElementById('staff_id');

    const divisionSelect = document.getElementById('division_id');

    const staffField = document.getElementById('staffField');

    const divisionField = document.getElementById('divisionField');

    // Keep original Division options for Staff/Office filtering.

    const divisionOptions = Array.from(divisionSelect.options)

        .slice(1)

        .map(function (option) {

            return {

                value: option.value,

                text: option.textContent.trim(),

                staffId: option.dataset.staffId || ''

            };

        });

    function isDepDevAgency(agencyId) {

        return depDevAgencyIds

            .map(String)

            .includes(String(agencyId || ''));

    }

    function openModal() {

        modal.classList.remove('hidden');

        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');

    }

    function closeModal() {

        modal.classList.add('hidden');

        modal.setAttribute('aria-hidden', 'true');

        document.body.classList.remove('overflow-hidden');

    }

    function resetForm() {

        form.reset();

        form.action = storeUrl;

        formMethod.value = 'POST';
        editingUserId.value = '';

        modalTitle.textContent = 'Add User';

        modalSubtitle.textContent =

            'Create a new DIREK user account.';

        document.getElementById('btnSaveUserText').textContent =

            'Save User';

        passwordHelp.textContent =

            'Set the initial password for this account.';

        passwordRequired.classList.remove('hidden');

        confirmPasswordRequired.classList.remove('hidden');

        newPassword.required = true;

        confirmPassword.required = true;

        updateAgencyFields();

        filterDivisions('');

    }

    function updateAgencyFields() {

        const requiresOrganization =

            isDepDevAgency(agencySelect.value);

        staffField.classList.toggle(

            'hidden',

            !requiresOrganization

        );

        divisionField.classList.toggle(

            'hidden',

            !requiresOrganization

        );

        staffSelect.required = requiresOrganization;

        divisionSelect.required = requiresOrganization;

        if (!requiresOrganization) {

            staffSelect.value = '';

            divisionSelect.value = '';

            filterDivisions('');

        }

    }

    function filterDivisions(selectedDivisionId = '') {

        const selectedStaffId =

            String(staffSelect.value || '');

        divisionSelect.innerHTML =

            '<option value="">Select Division</option>';

        divisionOptions.forEach(function (option) {

            if (

                selectedStaffId !== '' &&

                String(option.staffId) === selectedStaffId

            ) {

                const newOption =

                    document.createElement('option');

                newOption.value = option.value;

                newOption.textContent = option.text;

                if (

                    selectedDivisionId !== '' &&

                    String(option.value) ===

                        String(selectedDivisionId)

                ) {

                    newOption.selected = true;

                }

                divisionSelect.appendChild(newOption);

            }

        });

    }

    function setFieldValue(id, value) {

        const field = document.getElementById(id);

        if (!field) {

            return;

        }

        field.value =

            value === null || value === undefined

                ? ''

                : String(value);

    }

    // Add User.

    document

        .getElementById('btnAddUser')

        .addEventListener('click', function () {

            resetForm();

            openModal();

        });

    // Close User modal.

    document

        .getElementById('btnCloseUserModal')

        .addEventListener('click', closeModal);

    document

        .getElementById('btnCancelUser')

        .addEventListener('click', closeModal);

    // Close when clicking outside modal card.

    modal.addEventListener('click', function (event) {

        if (event.target === modal) {

            closeModal();

        }

    });

    // Close modal with Escape.

    document.addEventListener('keydown', function (event) {

        if (

            event.key === 'Escape' &&

            !modal.classList.contains('hidden')

        ) {

            closeModal();

        }

    });

    agencySelect.addEventListener('change', function () {

        updateAgencyFields();

        filterDivisions('');

    });

    staffSelect.addEventListener('change', function () {

        filterDivisions('');

    });

    // Called by the Edit button returned by UserController@getusers.

    window.showUser = function (user, updateUrl) {

        form.reset();

        form.action = updateUrl;

        formMethod.value = 'PUT';
        editingUserId.value = String(user.id || '');

        modalTitle.textContent = 'Edit User';

        modalSubtitle.textContent =

            'Update the DIREK account and organizational assignment.';

        document.getElementById('btnSaveUserText').textContent =

            'Save Changes';

        passwordHelp.textContent =

            'Leave both password fields blank to keep the current password.';

        passwordRequired.classList.add('hidden');

        confirmPasswordRequired.classList.add('hidden');

        newPassword.required = false;

        confirmPassword.required = false;

        setFieldValue(

            'firstname',

            user.firstname

        );

        setFieldValue(

            'middlename',

            user.middlename

        );

        setFieldValue(

            'lastname',

            user.lastname

        );

        setFieldValue(

            'email',

            user.email

        );

        setFieldValue(

            'agency_id',

            user.agency_id

        );

        updateAgencyFields();

        setFieldValue(

            'staff_id',

            user.staff_id

        );

        filterDivisions(

            user.division_id === null ||

            user.division_id === undefined

                ? ''

                : String(user.division_id)

        );

        setFieldValue(

            'position_id',

            user.position_id

        );

        setFieldValue(

            'role_id',

            user.role_id

        );

        openModal();

    };

    function escapeHtml(value) {

        return String(value ?? '')

            .replace(/&/g, '&amp;')

            .replace(/</g, '&lt;')

            .replace(/>/g, '&gt;')

            .replace(/"/g, '&quot;')

            .replace(/'/g, '&#039;');

    }

    function userInitials(name) {

        const parts = String(name || '').trim().split(/\s+/).filter(Boolean);

        if (!parts.length) return 'U';

        return ((parts[0][0] || '') + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();

    }

    function roleBadge(data) {

        const wrapper = document.createElement('div');

        wrapper.innerHTML = data || '';

        const text = (wrapper.textContent || '').trim();

        const normalized = text.toLowerCase();

        let modifier = '';

        if (normalized === 'super admin') modifier = ' is-admin';

        else if (normalized === 'director') modifier = ' is-director';

        else if (normalized === 'planning and finance staff') modifier = ' is-staff';

        return '<span class="direk-role-badge' + modifier + '">' + escapeHtml(text || '—') + '</span>';

    }

    // DIREK User Management DataTable.

    const table = $('#usersTable').DataTable({

        processing: true,

        serverSide: true,

        responsive: true,

        autoWidth: false,

        ajax: {

            url: getUsersUrl,

            type: 'POST',

            headers: {

                'X-CSRF-TOKEN': csrfToken

            }

        },

        order: [

            [1, 'asc']

        ],

        columns: [

            {

                data: 'fullname',

                name: 'fullname',

                orderable: false

            },

            {

                data: 'email',

                name: 'email'

            },

            {

                data: 'staff',

                name: 'staff.name',

                defaultContent: ''

            },

            {

                data: 'division',

                name: 'division.name',

                defaultContent: ''

            },

            {

                data: 'designation',

                name: 'position.name',

                defaultContent: ''

            },

            {

                data: 'role',

                name: 'role.name',

                defaultContent: ''

            },

            {

                data: 'actions',

                name: 'actions',

                orderable: false,

                searchable: false,

                className: 'text-center'

            }

        ]

    });

    table.on('draw', function () {

        const info = table.page.info();

        const badge = document.getElementById('userCountBadge');

        if (badge) {

            badge.textContent = info.recordsDisplay + (info.recordsDisplay === 1 ? ' User' : ' Users');

        }

    });

    table.draw(false);

    // Re-open the correct modal after validation failure.
    @if ($errors->any())
        @php
            $editingUserId = old('_editing_user_id');
        @endphp

        @if ($editingUserId)
            form.reset();
            form.action = @json(url('/administrator/users')) + '/' + @json($editingUserId);
            formMethod.value = 'PUT';
            editingUserId.value = @json($editingUserId);

            modalTitle.textContent = 'Edit User';
            modalSubtitle.textContent =
                'Update the DIREK account and organizational assignment.';
            document.getElementById('btnSaveUserText').textContent =
                'Save Changes';

            passwordHelp.textContent =
                'Leave both password fields blank to keep the current password.';
            passwordRequired.classList.add('hidden');
            confirmPasswordRequired.classList.add('hidden');
            newPassword.required = false;
            confirmPassword.required = false;
        @else
            resetForm();
        @endif

        setFieldValue('firstname', @json(old('firstname')));
        setFieldValue('middlename', @json(old('middlename')));
        setFieldValue('lastname', @json(old('lastname')));
        setFieldValue('email', @json(old('email')));
        setFieldValue('agency_id', @json(old('agency_id')));

        updateAgencyFields();

        setFieldValue('staff_id', @json(old('staff_id')));

        filterDivisions(
            @json(old('division_id'))
                ? String(@json(old('division_id')))
                : ''
        );

        setFieldValue('position_id', @json(old('position_id')));
        setFieldValue('role_id', @json(old('role_id')));

        openModal();
    @endif

});

</script>

@endpush

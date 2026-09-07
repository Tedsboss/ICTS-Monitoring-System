@extends('layouts.app')

@section('content')

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

    {{-- Page Header --}}

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h1 class="text-xl font-bold text-slate-900">

                User Management

            </h1>

            <p class="mt-1 text-sm text-slate-500">

                Manage DIREK user accounts, organizational assignments, and access roles.

            </p>

        </div>

        <button

            type="button"

            id="btnAddUser"

            class="inline-flex items-center justify-center gap-2 rounded-lg

                   bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white

                   shadow-sm transition hover:bg-sky-700"

        >

            <i class="fa fa-plus"></i>

            <span>Add User</span>

        </button>

    </div>

    {{-- Validation Errors --}}

    @if ($errors->any())

        <div

            class="mb-5 rounded-xl border border-rose-200 bg-rose-50

                   px-4 py-3 text-sm text-rose-800"

        >

            <div class="flex items-start gap-3">

                <i class="fa fa-exclamation-circle mt-0.5"></i>

                <div>

                    <p class="font-semibold">

                        Please review the information below.

                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            </div>

        </div>

    @endif

    {{-- Users Table --}}

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-5 py-4">

            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-800">

                DIREK Users

            </h2>

            <p class="mt-1 text-xs text-slate-500">

                Role determines authority. Staff/Office determines which office records the user can access.

            </p>

        </div>

        <div class="overflow-x-auto p-4">

            <table

                id="usersTable"

                class="min-w-full divide-y divide-slate-200"

                style="width: 100%;"

            >

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-600">

                            Name

                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-600">

                            Email

                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-600">

                            Staff / Office

                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-600">

                            Division

                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-600">

                            Position

                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-600">

                            Role

                        </th>

                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-600">

                            Actions

                        </th>

                    </tr>

                </thead>

                <tbody></tbody>

            </table>

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

                                    <span class="text-rose-500">\*</span>

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

                                    <span class="text-rose-500">\*</span>

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

                                    <span class="text-rose-500">\*</span>

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

                                    <span class="text-rose-500">\*</span>

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

                                    <span class="text-rose-500">\*</span>

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

                                    <span class="text-rose-500">\*</span>

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

                                <span class="text-rose-500">\*</span>

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

                                    >\*</span>

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

                                    >\*</span>

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

    function filterDivisions(*selectedDivisionId* = '') {

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

    function setFieldValue(*id*, *value*) {

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

    // Called by the Edit button returned by UserController\@getusers.
    window.showUser = function (*user*, *updateUrl*) {

        form.reset();

        form.action = updateUrl;

        formMethod.value = 'PUT';

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

            [0, 'asc']

        ],

        columns: [

            {

                data: 'fullname',

                name: 'fullname'

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

    // Re-open Add form after validation failure.
    @if ($errors->any())

        resetForm();

        setFieldValue(

            'firstname',

            @json(old('firstname'))

        );

        setFieldValue(

            'middlename',

            @json(old('middlename'))

        );

        setFieldValue(

            'lastname',

            @json(old('lastname'))

        );

        setFieldValue(

            'email',

            @json(old('email'))

        );

        setFieldValue(

            'agency_id',

            @json(old('agency_id'))

        );

        updateAgencyFields();

        setFieldValue(

            'staff_id',

            @json(old('staff_id'))

        );

        filterDivisions(

            @json(old('division_id'))

                ? String(@json(old('division_id')))

                : ''

        );

        setFieldValue(

            'position_id',

            @json(old('position_id'))

        );

        setFieldValue(

            'role_id',

            @json(old('role_id'))

        );

        openModal();

    @endif

});

</script>

@endpush

@extends('layouts.app')
@section('content')
@php
    $usersUiConfig = [
        'storeUrl' => route('users.store'),
        'getUsersUrl' => route('getusers'),
        'editBaseUrl' => url('/administrator/users'),
        'csrfToken' => csrf_token(),
        'depDevAgencyIds' => collect($depDevAgencyIds)->values()->all(),
        'hasErrors' => $errors->any(),
        'old' => [
            '_editing_user_id' => old('_editing_user_id'),
            'firstname' => old('firstname'),
            'middlename' => old('middlename'),
            'lastname' => old('lastname'),
            'email' => old('email'),
            'agency_id' => old('agency_id'),
            'staff_id' => old('staff_id'),
            'division_id' => old('division_id'),
            'position_id' => old('position_id'),
            'role_id' => old('role_id'),
        ],
    ];
@endphp
@push('css')
<style>
    #direk-users, #userModal {
        --users-surface: #ffffff;
        --users-inset: #f6f7fb;
        --users-border: #e6e9f0;
        --users-heading: #172033;
        --users-text: #465267;
        --users-muted: #637086;
        --users-icon: #eeedff;
        --users-hover: #fafaff;
        --users-accent: #635bff;
        --users-accent-text: #5850df;
        color: var(--users-text);
    }
    #direk-users[data-theme="dark"], #userModal[data-theme="dark"] {
        --users-surface: #192130;
        --users-inset: #151c29;
        --users-border: #2c3546;
        --users-heading: #eef0f8;
        --users-text: #ccd2e0;
        --users-muted: #95a0b5;
        --users-icon: #2b2850;
        --users-hover: #20283b;
        --users-accent-text: #a19cff;
    }
    #direk-users [hidden], #userModal [hidden], #direk-users .hidden, #userModal .hidden { display: none !important; }
    #direk-users .direk-users-shell { max-width: 1600px; margin: 0 auto; }
    #direk-users .users-page-heading { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 8px 0 28px; }
    #direk-users .users-eyebrow, #userModal .users-eyebrow { color: var(--users-accent-text); font-size: 10px; font-weight: 700; letter-spacing: .14em; }
    #direk-users .users-page-heading h2 { margin: 10px 0 8px; color: var(--users-heading); font-size: clamp(27px, 3vw, 36px); font-weight: 700; letter-spacing: -.04em; line-height: 1.2; }
    #direk-users .users-heading-dot { color: var(--users-accent-text); }
    #direk-users .users-page-heading p { margin: 0; color: var(--users-muted); font-size: 13px; line-height: 1.7; }
    #direk-users .users-heading-icon { display: grid; width: 62px; height: 62px; flex-shrink: 0; place-items: center; border: 1px solid var(--users-border); border-radius: 20px; background: var(--users-surface); color: var(--users-accent-text); font-size: 23px; box-shadow: 0 4px 14px rgba(20,25,60,.03); }
    #direk-users .direk-users-card { border: 1px solid var(--users-border); border-radius: 20px; background: var(--users-surface); box-shadow: 0 5px 24px rgba(20,25,60,.035); overflow: hidden; }
    #direk-users .direk-users-toolbar { padding: 24px; border-bottom: 1px solid var(--users-border); }
    #direk-users .direk-users-toolbar h2 { margin: 0; color: var(--users-heading) !important; font-size: 16px; font-weight: 650; letter-spacing: -.02em; }
    #direk-users .direk-users-toolbar p { margin-bottom: 0; color: var(--users-muted) !important; font-size: 12px; line-height: 1.7; }
    #direk-users .direk-user-count { display: inline-flex; align-items: center; padding: 4px 9px; border-radius: 7px; background: var(--users-icon); color: var(--users-accent-text); font-size: 10px; font-weight: 650; }
    #direk-users .users-toolbar-actions { display: flex; flex-shrink: 0; align-items: center; gap: 10px; }
    #direk-users .users-refresh { display: grid; width: 44px; height: 44px; place-items: center; border: 1px solid var(--users-border); border-radius: 10px; background: var(--users-surface); color: var(--users-muted); }
    #direk-users .users-refresh:hover { background: var(--users-inset); color: var(--users-accent-text); }
    #direk-users #btnAddUser, #userModal #btnSaveUser { min-height: 44px; margin: 0; border: 0; border-radius: 10px; background: #635bff !important; color: #fff !important; box-shadow: 0 3px 8px rgba(99,91,255,.15); }
    #direk-users #btnAddUser:hover, #userModal #btnSaveUser:hover { background: #534be5 !important; }
    #direk-users .direk-users-table-wrap { padding: 0 22px 18px; }
    #direk-users .direk-table-controls, #direk-users .direk-table-bottom { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 2px; }
    #direk-users .direk-table-bottom { padding-bottom: 0; }
    #direk-users .direk-table-scroll { overflow-x: auto; width: 100%; }
    #usersTable { width: 100% !important; margin: 0 !important; border-collapse: separate !important; border-spacing: 0 !important; border: 0; }
    #usersTable thead th { position: static; padding: 13px 12px !important; border: 0 !important; border-bottom: 1px solid var(--users-border) !important; background: var(--users-inset); color: var(--users-muted); font-size: 10px; font-weight: 650; letter-spacing: .06em; text-align: left; text-transform: uppercase; vertical-align: middle; white-space: nowrap; }
    #usersTable thead th:first-child { border-radius: 9px 0 0 0; }
    #usersTable thead th:last-child { border-radius: 0 9px 0 0; }
    #usersTable tbody td { padding: 17px 12px !important; border: 0 !important; border-bottom: 1px solid var(--users-border) !important; background: var(--users-surface); color: var(--users-text); font-size: 12px; line-height: 1.6; vertical-align: middle; overflow-wrap: anywhere; }
    #usersTable tbody tr:hover > td { background: var(--users-hover); }
    #usersTable tbody tr:last-child > td { border-bottom: 0 !important; }
    #usersTable th.users-actions, #usersTable td.users-actions { text-align: right !important; white-space: nowrap; }
    #usersTable td.users-actions a, #usersTable td.users-actions button { display: inline-flex !important; align-items: center; justify-content: center; min-width: 40px; min-height: 40px; margin: 2px; padding: 8px; border-radius: 9px; text-decoration: none; transition: background-color .15s; }
    #usersTable td.users-actions a:hover, #usersTable td.users-actions button:hover { background: var(--users-icon); }
    #usersTable .dataTables_empty { padding: 52px 16px !important; text-align: center; color: var(--users-muted); }
    #usersTable td.child ul.dtr-details { width: 100%; }
    #usersTable td.child .dtr-title { color: var(--users-heading); }
    #usersTable td.child li { padding: 8px 0; border-color: var(--users-border); }
    #direk-users .direk-user-cell { display: flex; align-items: center; gap: 12px; min-width: 170px; }
    #direk-users .direk-user-initials { display: inline-flex; flex: 0 0 40px; width: 40px; height: 40px; align-items: center; justify-content: center; border: 1px solid var(--users-border); border-radius: 13px; background: var(--users-icon); color: var(--users-accent-text); font-size: 11px; font-weight: 700; }
    #direk-users .direk-user-actions { display: inline-flex; align-items: center; justify-content: center; gap: 4px; }
    #direk-users .direk-user-actions form { display: inline-flex; margin: 0; }
    #direk-users .direk-role-link { text-decoration: none; }
    #direk-users .direk-user-name { color: var(--users-heading); font-weight: 600; line-height: 1.5; }
    #direk-users .direk-role-badge { display: inline-flex; align-items: center; border: 1px solid var(--users-border); border-radius: 7px; padding: 4px 8px; background: var(--users-inset); color: var(--users-text); font-size: 10px; font-weight: 600; line-height: 1.5; }
    #direk-users .direk-role-badge.is-admin { border-color: transparent; background: var(--users-icon); color: var(--users-accent-text); }
    #direk-users .direk-role-badge.is-director { border-color: transparent; background: #e8f0ff; color: #3463ae; }
    #direk-users .direk-role-badge.is-staff { border-color: transparent; background: #e8f7f0; color: #287452; }
    #direk-users[data-theme="dark"] .direk-role-badge.is-director { background: #22364f; color: #aacbff; }
    #direk-users[data-theme="dark"] .direk-role-badge.is-staff { background: #203c35; color: #9fe0bb; }
    #usersTable_wrapper .dataTables_length, #usersTable_wrapper .dataTables_filter, #usersTable_wrapper .dataTables_info, #usersTable_wrapper .dataTables_paginate { float: none !important; margin: 0 !important; padding: 0 !important; color: var(--users-muted) !important; font-size: 12px; }
    #usersTable_wrapper .dataTables_length label, #usersTable_wrapper .dataTables_filter label { display: flex; align-items: center; gap: 9px; margin: 0; color: var(--users-muted); font-weight: 400; }
    #usersTable_wrapper .dataTables_filter input, #usersTable_wrapper .dataTables_length select { box-sizing: border-box; min-height: 40px; margin: 0; border: 1px solid var(--users-border); border-radius: 9px; background: var(--users-surface); color: var(--users-text); font-size: 12px; outline: none; }
    #usersTable_wrapper .dataTables_filter input { width: 260px; padding: 9px 12px; }
    #usersTable_wrapper .dataTables_length select { width: auto; padding: 8px 28px 8px 10px; }
    #usersTable_wrapper input:focus, #usersTable_wrapper select:focus { border-color: var(--users-accent); box-shadow: 0 0 0 3px rgba(99,91,255,.12); }
    #usersTable_wrapper .pagination { display: flex; flex-wrap: wrap; gap: 4px; margin: 0; }
    #usersTable_wrapper .page-link, #usersTable_wrapper .paginate_button { border: 1px solid var(--users-border) !important; border-radius: 8px !important; background: var(--users-surface) !important; color: var(--users-text) !important; font-size: 12px; }
    #usersTable_wrapper .paginate_button { margin: 2px !important; }
    #usersTable_wrapper li.paginate_button { padding: 0 !important; border: 0 !important; background: none !important; }
    #usersTable_wrapper .page-item.active .page-link, #usersTable_wrapper .paginate_button.current { background: var(--users-accent) !important; border-color: var(--users-accent) !important; color: #fff !important; }
    #userModal { position: fixed !important; inset: 0; z-index: 1060; padding: 16px; background: rgba(15,20,40,.55); backdrop-filter: blur(5px); }
    #userModal [role="dialog"] { display: flex; flex-direction: column; max-width: 820px; max-height: calc(100vh - 64px); max-height: calc(100dvh - 64px); border: 1px solid var(--users-border); border-radius: 20px; background: var(--users-surface) !important; outline: none; }
    #userModal .users-modal-header { flex-shrink: 0; padding: 23px 28px; border-bottom: 1px solid var(--users-border); background: linear-gradient(125deg, var(--users-icon), var(--users-surface) 75%); }
    #userModal #userModalTitle { margin: 7px 0 0; color: var(--users-heading) !important; font-size: 22px; font-weight: 650; letter-spacing: -.035em; }
    #userModal #userModalSubtitle { margin: 6px 0 0; color: var(--users-muted) !important; font-size: 12px; line-height: 1.6; }
    #userModal #btnCloseUserModal { width: 40px; height: 40px; margin: 0; flex-shrink: 0; border: 1px solid var(--users-border); color: var(--users-muted) !important; background: var(--users-surface); }
    #userModal #btnCloseUserModal:hover { background: var(--users-icon); color: var(--users-accent-text) !important; }
    #userForm { display: flex; flex-direction: column; min-height: 0; margin: 0; }
    #userModal .direk-user-modal-body { flex: 1; min-height: 0; padding: 26px 28px; overflow-y: auto; overscroll-behavior: contain; }
    #userModal .users-section-heading { display: flex; align-items: center; gap: 10px; margin: 0; color: var(--users-heading) !important; font-size: 13px; font-weight: 650; letter-spacing: -.01em; }
    #userModal .users-section-number { display: grid; width: 26px; height: 26px; place-items: center; border-radius: 8px; background: var(--users-icon); color: var(--users-accent-text); font-size: 10px; }
    #userModal p { color: var(--users-muted) !important; line-height: 1.6; }
    #userModal label { margin-bottom: 7px; color: var(--users-text) !important; font-size: 12px; font-weight: 600; }
    #userModal input:not([type="hidden"]), #userModal select { box-sizing: border-box; display: block; width: 100%; min-height: 44px; border: 1px solid var(--users-border) !important; border-radius: 10px; padding: 10px 12px; background: var(--users-surface) !important; color: var(--users-heading) !important; font: inherit; font-size: 13px; line-height: 1.5; box-shadow: none; outline: none; }
    #userModal input:focus, #userModal select:focus { border-color: var(--users-accent) !important; box-shadow: 0 0 0 3px rgba(99,91,255,.12); }
    #userModal [aria-invalid="true"] { border-color: #e55378 !important; }
    #userModal .users-field-error { margin: 5px 0 0; color: #e55378 !important; font-size: 11px; }
    #userModal hr { margin: 24px 0; border: 0; border-top: 1px solid var(--users-border); opacity: 1; }
    #userForm > div:last-child { flex-shrink: 0; padding: 16px 28px; border-top: 1px solid var(--users-border); background: var(--users-inset) !important; }
    #userModal #btnCancelUser { min-height: 44px; margin: 0; border: 1px solid var(--users-border); border-radius: 10px; background: var(--users-surface) !important; color: var(--users-text) !important; box-shadow: none; }
    #userModal button:focus-visible, #direk-users button:focus-visible, #direk-users a:focus-visible { outline: 2px solid var(--users-accent-text); outline-offset: 3px; }
    #userModal button:disabled { cursor: wait; opacity: .65; }
    #direk-users .users-notice, #userModal .users-notice { border: 1px solid #f4b8c7; border-radius: 12px; padding: 14px 16px; font-size: 12px; line-height: 1.7; }
    #direk-users #usersLoadError { margin: 18px 24px 0; }
    #direk-users .users-notice-error, #userModal .users-notice-error { background: #fff1f4; color: #aa2b4d; }
    #userModal #userValidationSummary { margin-bottom: 24px; }
    #userModal #userValidationSummary ul { margin: 8px 0 0; padding-left: 18px; }
    #direk-users[data-theme="dark"] .users-notice-error, #userModal[data-theme="dark"] .users-notice-error { border-color: #663449; background: #352332; color: #ffb3c5; }
    @media (max-width: 639px) {
        #direk-users .users-page-heading { padding-bottom: 20px; }
        #direk-users .users-heading-icon { display: none; }
        #direk-users .direk-users-toolbar { padding: 20px 16px; }
        #direk-users .direk-users-table-wrap { padding: 0 12px 12px; }
        #direk-users .direk-table-controls, #direk-users .direk-table-bottom { align-items: flex-start; gap: 12px; }
        #usersTable_wrapper .dataTables_filter { width: 100%; }
        #usersTable_wrapper .dataTables_filter label { width: 100%; }
        #usersTable_wrapper .dataTables_filter input { flex: 1; width: 0; min-width: 0; }
        #userModal { padding: 8px; }
        #userModal [role="dialog"] { max-height: calc(100vh - 32px); max-height: calc(100dvh - 32px); }
        #userModal .direk-user-modal-body, #userModal .users-modal-header, #userForm > div:last-child { padding: 18px; }
        #direk-users #usersLoadError { margin: 16px 12px 0; }
    }
    @media (prefers-reduced-motion: reduce) { #userModal *, #direk-users * { transition: none !important; } }

</style>
@endpush
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'User Management'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-users" data-theme="{{ session('user_settings.class_theme', '') === 'dark' ? 'dark' : 'light' }}" class="px-4 pb-8 pt-4">
    <div class="direk-users-shell">
        <header class="users-page-heading">
            <div>
                <span class="users-eyebrow">WORKSPACE ADMINISTRATION</span>
                <h2>People &amp; access<span class="users-heading-dot">.</span></h2>
                <p>A place for every person. The right access for every role.</p>
            </div>
            <span class="users-heading-icon" aria-hidden="true"><i class="fa fa-users"></i></span>
        </header>
        {{-- Validation Errors --}}
        @if ($errors->any())
            <div role="alert" class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <div class="flex items-start gap-3">
                    <i aria-hidden="true" class="fa fa-exclamation-circle mt-0.5"></i>
                    <div>
                        <p class="m-0 font-semibold">Please review the information below.</p>
                        <ul class="m-0 mt-2 list-disc space-y-1 pl-5">
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
                            <h2 class="m-0 text-xl font-bold text-slate-900">Team directory</h2>
                            <span id="userCountBadge" aria-live="polite" class="direk-user-count">Loading…</span>
                        </div>
                        <p class="m-0 mt-1 text-sm text-slate-500">Manage accounts, organizational assignments, and permissions.</p>
                    </div>
                    <div class="users-toolbar-actions">
                        <button type="button" id="btnRefreshUsers" class="users-refresh" title="Refresh directory" aria-label="Refresh directory"><i class="fa fa-refresh" aria-hidden="true"></i></button>
                        @can('create', App\Models\User::class)
                        <button type="button" id="btnAddUser" aria-haspopup="dialog" aria-controls="userModal" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                            <i aria-hidden="true" class="fa fa-plus"></i>
                            <span>Add User</span>
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
            <div id="usersLoadError" class="users-notice users-notice-error" role="alert" hidden></div>
            <div class="direk-users-table-wrap">
                <table aria-label="DIREK user accounts" id="usersTable" class="min-w-full" style="width: 100%;">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Staff / Office</th>
                            <th scope="col">Division</th>
                            <th scope="col">Position</th>
                            <th scope="col">Role</th>
                            <th scope="col" class="users-actions text-center">Actions</th>
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
<div id="userModal" data-theme="{{ session('user_settings.class_theme', '') === 'dark' ? 'dark' : 'light' }}" class="fixed inset-0 z-[1055] hidden overflow-y-auto bg-slate-900/50 p-4" aria-hidden="true">
    <div id="userModalBackdrop" class="flex min-h-full items-center justify-center py-4">
        <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="userModalTitle" aria-describedby="userModalSubtitle" tabindex="-1">
            {{-- Modal Header --}}
            <div class="users-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <span class="users-eyebrow">PEOPLE &amp; ACCESS</span>
                    <h2 id="userModalTitle" class="text-lg font-bold text-slate-900">Add User</h2>
                    <p id="userModalSubtitle" class="m-0 mt-1 text-xs text-slate-500">Create a new DIREK user account.</p>
                </div>
                <button type="button" id="btnCloseUserModal" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800" aria-label="Close">
                    <i aria-hidden="true" class="fa fa-times"></i>
                </button>
            </div>
            {{-- User Form --}}
            <form id="userForm" method="POST" action="{{ route('users.store') }}">
                @csrf
                <input type="hidden" name="_method" id="userFormMethod" value="POST">
                <input type="hidden" name="_editing_user_id" id="editingUserId" value="">
                <div class="direk-user-modal-body overflow-y-auto p-5">
                    @if ($errors->any())
                        <div id="userValidationSummary" class="users-notice users-notice-error" role="alert" tabindex="-1">
                            <strong>Please review these fields.</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {{-- Account Information --}}
                    <section class="mb-6">
                        <div class="mb-4">
                            <h3 class="users-section-heading"><span class="users-section-number">01</span>Account Information</h3>
                            <p class="m-0 mt-1 text-xs text-slate-500">Basic information used to identify the DIREK user.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="firstname" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    First Name
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="firstname" name="firstname" required maxlength="255" autocomplete="given-name" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('firstname') aria-invalid="true" aria-describedby="error-firstname" @enderror>
                                @error('firstname')
                                    <p id="error-firstname" class="users-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="middlename" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    Middle Name
                                </label>
                                <input type="text" id="middlename" name="middlename" maxlength="255" autocomplete="additional-name" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('middlename') aria-invalid="true" aria-describedby="error-middlename" @enderror>
                                @error('middlename')
                                    <p id="error-middlename" class="users-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="lastname" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    Last Name
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="lastname" name="lastname" required maxlength="255" autocomplete="family-name" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('lastname') aria-invalid="true" aria-describedby="error-lastname" @enderror>
                                @error('lastname')
                                    <p id="error-lastname" class="users-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    Email Address
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input type="email" id="email" name="email" required maxlength="255" autocomplete="email" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('email') aria-invalid="true" aria-describedby="error-email" @enderror>
                                @error('email')
                                    <p id="error-email" class="users-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                    <hr class="mb-6 border-slate-200">
                    {{-- Organizational Assignment --}}
                    <section class="mb-6">
                        <div class="mb-4">
                            <h3 class="users-section-heading"><span class="users-section-number">02</span>Organizational Assignment</h3>
                            <p class="m-0 mt-1 text-xs text-slate-500">Assign the user's agency, staff/office, division, and position.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="agency_id" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    Agency
                                    <span class="text-rose-500">*</span>
                                </label>
                                <select id="agency_id" name="agency_id" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('agency_id') aria-invalid="true" aria-describedby="error-agency_id" @enderror>
                                    <option value="">Select Agency</option>
                                    @foreach ($agencies as $agency)
                                        <option value="{{ $agency->id }}">
                                            {{ $agency->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('agency_id')
                                    <p id="error-agency_id" class="users-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div id="staffField">
                                <label for="staff_id" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    Staff / Office
                                    <span class="text-rose-500">*</span>
                                </label>
                                <select id="staff_id" name="staff_id" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('staff_id') aria-invalid="true" aria-describedby="error-staff_id" @enderror>
                                    <option value="">Select Staff / Office</option>
                                    @foreach ($staffs as $staff)
                                        <option value="{{ $staff->id }}">
                                            {{ $staff->name }}
                                            @if ($staff->abbreviation)
                                                ({{ $staff->abbreviation }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('staff_id')
                                    <p id="error-staff_id" class="users-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div id="divisionField">
                                <label for="division_id" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    Division
                                    <span class="text-rose-500">*</span>
                                </label>
                                <select id="division_id" name="division_id" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('division_id') aria-invalid="true" aria-describedby="error-division_id" @enderror>
                                    <option value="">Select Division</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}" data-staff-id="{{ $division->staff_id }}">
                                            {{ $division->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('division_id')
                                    <p id="error-division_id" class="users-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="position_id" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    Position
                                </label>
                                <select id="position_id" name="position_id" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('position_id') aria-invalid="true" aria-describedby="error-position_id" @enderror>
                                    <option value="">Select Position</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}">
                                            {{ $position->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id')
                                    <p id="error-position_id" class="users-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                    <hr class="mb-6 border-slate-200">
                    {{-- DIREK Access --}}
                    <section class="mb-6">
                        <div class="mb-4">
                            <h3 class="users-section-heading"><span class="users-section-number">03</span>DIREK Access</h3>
                            <p class="m-0 mt-1 text-xs text-slate-500">Role determines what actions the user is authorized to perform.</p>
                        </div>
                        <div>
                            <label for="role_id" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Role
                                <span class="text-rose-500">*</span>
                            </label>
                            <select id="role_id" name="role_id" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('role_id') aria-invalid="true" aria-describedby="error-role_id" @enderror>
                                <option value="">Select DIREK Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                                @error('role_id')
                                    <p id="error-role_id" class="users-field-error">{{ $message }}</p>
                                @enderror
                            <p class="m-0 mt-2 text-xs text-slate-500">
                                Staff/Office controls which records the user can access.
                                Role controls what the user can do with those records.
                            </p>
                        </div>
                    </section>
                    <hr class="mb-6 border-slate-200">
                    {{-- Password --}}
                    <section>
                        <div class="mb-4">
                            <h3 class="users-section-heading"><span class="users-section-number">04</span>Password</h3>
                            <p id="passwordHelp" class="m-0 mt-1 text-xs text-slate-500">Set the initial password for this account.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="new-password" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    New Password
                                    <span id="passwordRequired" class="text-rose-500">*</span>
                                </label>
                                <input type="password" id="new-password" name="new-password" minlength="6" autocomplete="new-password" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('new-password') aria-invalid="true" aria-describedby="error-new-password" @enderror>
                                @error('new-password')
                                    <p id="error-new-password" class="users-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="confirm-password" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    Confirm Password
                                    <span id="confirmPasswordRequired" class="text-rose-500">*</span>
                                </label>
                                <input type="password" id="confirm-password" name="confirm-password" minlength="6" autocomplete="new-password" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" @error('confirm-password') aria-invalid="true" aria-describedby="error-confirm-password" @enderror>
                                @error('confirm-password')
                                    <p id="error-confirm-password" class="users-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                </div>
                {{-- Modal Footer --}}
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" id="btnCancelUser" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" id="btnSaveUser" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                        <i aria-hidden="true" class="fa fa-save"></i>
                        <span id="btnSaveUserText">Save User</span>
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
    const config = {{ \Illuminate\Support\Js::from($usersUiConfig) }};
    const modal = document.getElementById('userModal');
    const dialog = modal.querySelector('[role="dialog"]');
    const form = document.getElementById('userForm');
    const field = id => document.getElementById(id);
    const agencySelect = field('agency_id');
    const staffSelect = field('staff_id');
    const divisionSelect = field('division_id');
    const divisionOptions = Array.from(divisionSelect.options).slice(1).map(option => ({ value:option.value, text:option.textContent.trim(), staffId:String(option.dataset.staffId || '') }));
    const agencyIds = config.depDevAgencyIds.map(String);
    let previousFocus = null;
    let previousOverflow = '';
    let modalOpen = false;
    function isDepDevAgency(value) {
        return agencyIds.includes(String(value || ''));
    }
    function setFieldValue(id, value) {
        const element = field(id);
        if (element) element.value = value == null ? '' : String(value);
    }
    function openModal() {
        if (!modalOpen) {
            previousFocus = document.activeElement;
            previousOverflow = document.body.style.overflow;
        }
        modalOpen = true;
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        dialog.focus();
    }
    function closeModal() {
        if (!modalOpen || form.getAttribute('aria-busy') === 'true') return;
        modalOpen = false;
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = previousOverflow;
        if (previousFocus && previousFocus.isConnected) previousFocus.focus();
    }
    function resetSaveState() {
        field('btnSaveUser').disabled = false;
        field('btnCancelUser').disabled = false;
        field('btnCloseUserModal').disabled = false;
        form.removeAttribute('aria-busy');
    }
    function configureForm(editing, updateUrl = '', userId = '') {
        form.reset();
        field('confirm-password').setCustomValidity('');
        form.querySelectorAll('[aria-invalid]').forEach(element => element.removeAttribute('aria-invalid'));
        form.querySelectorAll('.users-field-error, #userValidationSummary').forEach(element => { element.hidden = true; });
        resetSaveState();
        form.action = editing ? updateUrl : config.storeUrl;
        field('userFormMethod').value = editing ? 'PUT' : 'POST';
        field('editingUserId').value = String(userId || '');
        field('userModalTitle').textContent = editing ? 'Edit User' : 'Add User';
        field('userModalSubtitle').textContent = editing ? 'Update the DIREK account and organizational assignment.' : 'Create a new DIREK user account.';
        field('btnSaveUserText').textContent = editing ? 'Save Changes' : 'Save User';
        field('passwordHelp').textContent = editing ? 'Leave both password fields blank to keep the current password.' : 'Set the initial password for this account.';
        field('passwordRequired').classList.toggle('hidden', editing);
        field('confirmPasswordRequired').classList.toggle('hidden', editing);
        field('new-password').required = !editing;
        field('confirm-password').required = !editing;
        updateAgencyFields();
        filterDivisions();
    }
    function filterDivisions(selectedDivisionId = '') {
        const staffId = String(staffSelect.value || '');
        divisionSelect.replaceChildren(new Option('Select Division', ''));
        divisionOptions.forEach(option => {
            if (staffId && option.staffId === staffId) {
                const selected = String(option.value) === String(selectedDivisionId || '');
                divisionSelect.add(new Option(option.text, option.value, false, selected));
            }
        });
    }
    function updateAgencyFields() {
        const applies = isDepDevAgency(agencySelect.value);
        field('staffField').classList.toggle('hidden', !applies);
        field('divisionField').classList.toggle('hidden', !applies);
        staffSelect.required = applies;
        divisionSelect.required = applies;
        staffSelect.disabled = !applies;
        divisionSelect.disabled = !applies;
        if (!applies) {
            staffSelect.value = '';
            filterDivisions();
        }
    }
    function populateForm(user) {
        ['firstname', 'middlename', 'lastname', 'email', 'agency_id', 'position_id', 'role_id'].forEach(id => setFieldValue(id, user[id]));
        updateAgencyFields();
        if (isDepDevAgency(agencySelect.value)) {
            setFieldValue('staff_id', user.staff_id);
            filterDivisions(user.division_id);
        }
    }
    field('btnAddUser')?.addEventListener('click', () => {
        configureForm(false);
        openModal();
    });
    field('btnCloseUserModal').addEventListener('click', closeModal);
    field('btnCancelUser').addEventListener('click', closeModal);
    modal.addEventListener('click', event => {
        if (event.target === modal || event.target === field('userModalBackdrop')) closeModal();
    });
    document.addEventListener('keydown', event => {
        if (!modalOpen) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
        } else if (event.key === 'Tab') {
            const focusable = Array.from(dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), [tabindex="0"]')).filter(element => element.getClientRects().length);
            if (!focusable.length) {
                event.preventDefault();
                dialog.focus();
                return;
            }
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && (document.activeElement === first || document.activeElement === dialog)) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && (document.activeElement === last || document.activeElement === dialog)) {
                event.preventDefault();
                first.focus();
            }
        }
    });
    agencySelect.addEventListener('change', () => {
        updateAgencyFields();
        filterDivisions();
    });
    staffSelect.addEventListener('change', () => filterDivisions());
    window.showUser = function (user, updateUrl) {
        configureForm(true, updateUrl, user.id);
        populateForm(user);
        openModal();
    };
    function validatePasswordConfirmation() {
        const password = field('new-password');
        const confirmation = field('confirm-password');
        confirmation.required = field('userFormMethod').value !== 'PUT' || password.value !== '';
        confirmation.setCustomValidity(
            confirmation.value !== password.value ? 'The password confirmation does not match the new password.' : ''
        );
    }
    ['new-password', 'confirm-password'].forEach(id => {
        field(id).addEventListener('input', validatePasswordConfirmation);
    });
    form.addEventListener('submit', event => {
        validatePasswordConfirmation();
        if (field('btnSaveUser').disabled) {
            event.preventDefault();
            return;
        }
        if (!form.reportValidity()) {
            event.preventDefault();
            return;
        }
        field('btnSaveUser').disabled = true;
        field('btnCancelUser').disabled = true;
        field('btnCloseUserModal').disabled = true;
        field('btnSaveUserText').textContent = 'Saving…';
        form.setAttribute('aria-busy', 'true');
    });
    window.addEventListener('pageshow', () => {
        resetSaveState();
        field('btnSaveUserText').textContent = field('userFormMethod').value === 'PUT' ? 'Save Changes' : 'Save User';
    });
    function escapeHtml(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
    function plainText(value) {
        const parsed = new DOMParser().parseFromString(String(value ?? ''), 'text/html');
        return (parsed.body.textContent || '').trim();
    }
    function userInitials(name) {
        const parts = name.split(/\s+/).filter(Boolean);
        return parts.length ? ((parts[0][0] || '') + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase() : 'U';
    }
    function roleBadge(data, type) {
        const text = plainText(data);
        if (type !== 'display') return text;
        const role = text.toLowerCase();
        const modifier = role === 'super admin' ? ' is-admin' : role === 'director' ? ' is-director' : role === 'planning and finance staff' ? ' is-staff' : '';
        const badge = '<span class="direk-role-badge' + modifier + '">' + escapeHtml(text || '—') + '</span>';
        const parsed = new DOMParser().parseFromString(String(data ?? ''), 'text/html');
        const link = parsed.querySelector('a[href]');
        if (link) {
            const href = link.getAttribute('href');
            try {
                const url = new URL(href, window.location.origin);
                if (url.origin === window.location.origin && ['http:', 'https:'].includes(url.protocol)) {
                    return '<a class="direk-role-link" title="Update Role" href="' + escapeHtml(url.href) + '">' + badge + '</a>';
                }
            } catch (error) {
                // Keep the badge readable if the returned URL is malformed.
            }
        }
        return badge;
    }
    function showDirectoryError(message) {
        field('usersLoadError').textContent = message;
        field('usersLoadError').hidden = false;
        field('userCountBadge').textContent = 'Unavailable';
    }
    if (window.jQuery && $.fn.DataTable) {
        $('#usersTable').on('xhr.dt', function (event, settings, response, xhr) {
            if (response && !response.error) {
                field('usersLoadError').hidden = true;
                return;
            }
            const message = xhr?.status === 419
                ? 'Your session has expired. Refresh this page and sign in again.'
                : xhr?.status === 403
                    ? 'You do not have permission to load this directory.'
                    : 'The directory could not be loaded. Use Refresh to try again.';
            showDirectoryError(message);
            return true;
        });
        const table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: Boolean($.fn.dataTable.Responsive),
        autoWidth: false,
        dom: '<"direk-table-controls"lf><"direk-table-scroll"t><"direk-table-bottom"ip>',
        ajax: {
            url: config.getUsersUrl,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': config.csrfToken }
        },
        order: [[0, 'asc']],
        language: { search:'Search', searchPlaceholder:'Name or email', lengthMenu:'Show _MENU_ users', emptyTable:'No users available.', zeroRecords:'No matching users found.', info:'Showing _START_–_END_ of _TOTAL_ users', infoEmpty:'No users to show' },
        columns: [
            { data:'fullname', name:'fullname', defaultContent:'', orderable:true, responsivePriority:1, render:function(data, type) {
                const name = plainText(data);
                if (type !== 'display') return name;
                return '<div class="direk-user-cell"><span class="direk-user-initials" aria-hidden="true">' + escapeHtml(userInitials(name)) + '</span><span class="direk-user-name">' + escapeHtml(name || 'Unnamed user') + '</span></div>';
            } },
            { data:'email', name:'email', defaultContent:'', responsivePriority:3, render:$.fn.dataTable.render.text() },
            { data:'staff', name:'staff.name', defaultContent:'—' },
            { data:'division', name:'division.name', defaultContent:'—' },
            { data:'designation', name:'position.name', defaultContent:'—' },
            { data:'role', name:'role.name', defaultContent:'', responsivePriority:4, render:roleBadge },
            { data:'actions', name:'actions', defaultContent:'', orderable:false, searchable:false, className:'users-actions', responsivePriority:2 }
        ],
        drawCallback:function () {
            const info = this.api().page.info();
            field('userCountBadge').textContent = info.recordsDisplay + (info.recordsDisplay === 1 ? ' user' : ' users');
        }
    });
        field('btnRefreshUsers').addEventListener('click', () => table.ajax.reload(null, false));
    } else {
        showDirectoryError('The directory component could not initialize. Refresh the page to reload its scripts.');
        field('btnRefreshUsers').addEventListener('click', () => window.location.reload());
    }
    if (config.hasErrors) {
        const old = config.old;
        const editing = Boolean(old._editing_user_id);
        const updateUrl = config.editBaseUrl + '/' + encodeURIComponent(old._editing_user_id || '');
        configureForm(editing, updateUrl, old._editing_user_id);
        populateForm(old);
        form.querySelectorAll('.users-field-error, #userValidationSummary').forEach(element => { element.hidden = false; });
        form.querySelectorAll('[aria-describedby^="error-"]').forEach(element => element.setAttribute('aria-invalid', 'true'));
        openModal();
    }
});
</script>
@endpush

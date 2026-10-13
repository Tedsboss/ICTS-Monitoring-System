@extends('layouts.app')
@section('content')
@php
    $rolesDark = session('user_settings.class_theme', '') === 'dark';
    $roleUiConfig = [
        'storeUrl' => route('roles.store'),
        'baseUrl' => url('/administrator/roles'),
        'dataUrl' => route('getroles'),
        'csrf' => csrf_token(),
        'hasErrors' => $errors->any(),
        'old' => [
            'name' => old('name', ''),
            'description' => old('description', ''),
            'roleTitle' => old('roleTitle', 'New Role'),
            'roleAction' => old('roleAction', route('roles.store')),
            'roleMethod' => old('roleMethod', 'post'),
        ],
    ];
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Roles and Permissions'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-roles" class="direk-role-scope px-4 pb-8 pt-4" data-theme="{{ $rolesDark ? 'dark' : 'light' }}">
    <div class="roles-shell">
        <header class="roles-page-heading">
            <div><span class="roles-eyebrow">WORKSPACE ADMINISTRATION</span><h2>Access, thoughtfully assigned.</h2><p>Define responsibilities. Give each role the tools it needs.</p></div>
            <span class="roles-feature-icon" aria-hidden="true"><i class="fa fa-shield"></i></span>
        </header>
        <section class="roles-card" aria-labelledby="rolesDirectoryTitle">
            <div class="roles-card-header">
                <div><h3 id="rolesDirectoryTitle">Role directory <span id="roleCountBadge" class="roles-badge" aria-live="polite">Loading…</span></h3><p>Manage role identities and their access permissions.</p></div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btnRefreshRoles" class="roles-button" aria-label="Refresh role directory"><i class="fa fa-refresh" aria-hidden="true"></i></button>
                    @can('create', App\Models\Role::class)
                        <button type="button" class="roles-button roles-primary" onclick="showRole()" aria-haspopup="dialog" aria-controls="role-modal"><i class="fa fa-plus" aria-hidden="true"></i>Add Role</button>
                    @endcan
                </div>
            </div>
            <div class="roles-info"><i class="fa fa-info-circle" aria-hidden="true"></i><span>Open a role to review its module permissions and allowed actions.</span></div>
            <div id="rolesLoadError" class="roles-alert" role="alert" hidden></div>
            <div class="roles-table-wrap">
                <table id="datatable-roles" aria-label="DIREK roles" style="width:100%">
                    <thead><tr><th scope="col">Role</th><th scope="col">Description</th><th scope="col">Created by</th><th scope="col">Created date</th><th scope="col" class="roles-actions">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="mt-6">@include('layouts.footers.auth.footer')</div>
</div>
<form method="POST" id="frmRole" action="{{ route('roles.store') }}" autocomplete="off">
    @csrf
    @method('post')
    <input type="hidden" name="roleTitle" id="roleTitle" value="{{ old('roleTitle', 'New Role') }}">
    <input type="hidden" name="roleAction" id="roleAction" value="{{ old('roleAction', route('roles.store')) }}">
    <input type="hidden" name="roleMethod" id="roleMethod" value="{{ old('roleMethod', 'post') }}">
    <div class="modal fade direk-role-scope" id="role-modal" data-theme="{{ $rolesDark ? 'dark' : 'light' }}" tabindex="-1" aria-labelledby="h5roleTitle" aria-hidden="true" hidden>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div><span class="roles-eyebrow">PEOPLE &amp; ACCESS</span><h2 class="modal-title" id="h5roleTitle">New Role</h2></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())
                        <div id="roleValidationSummary" class="roles-alert" role="alert"><strong>Please review these fields.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    <label for="name" class="roles-label">Role name</label>
                    <input name="name" id="name" type="text" class="roles-field" required value="{{ old('name') }}" placeholder="e.g. Planning Officer">
                    @error('name')<p class="roles-error" data-role-error>{{ $message }}</p>@enderror
                    <div class="mt-4">
                        <label for="description" class="roles-label">Description</label>
                        <textarea name="description" id="description" rows="4" class="roles-field" required placeholder="Describe the responsibilities of this role.">{{ old('description') }}</textarea>
                        @error('description')<p class="roles-error" data-role-error>{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="roles-button" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="roles-button roles-primary" id="btnSave"><i class="fa fa-check" aria-hidden="true"></i>Save Role</button>
                    <button type="button" class="roles-button roles-primary" id="btnSaveDisabled" disabled hidden><span class="spinner-border spinner-border-sm" aria-hidden="true"></span>Saving…</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const config = {{ \Illuminate\Support\Js::from($roleUiConfig) }};
    const field = id => document.getElementById(id);
    const form = field('frmRole');
    const modalElement = field('role-modal');
    const method = form.querySelector('input[name="_method"]');
    let saving = false;
    let modal = null;
    function resetSave() {
        saving = false;
        field('btnSave').hidden = false;
        field('btnSave').disabled = false;
        field('btnSaveDisabled').hidden = true;
        form.removeAttribute('aria-busy');
    }
    function openModal() {
        modalElement.hidden = false;
        if (window.bootstrap?.Modal) {
            modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        } else if (window.jQuery && typeof $.fn.modal === 'function') {
            $('#role-modal').modal('show');
        } else {
            showLoadError('The role dialog could not initialize. Refresh this page to reload its scripts.');
        }
    }
    function validAction(action, editing) {
        if (!editing) return config.storeUrl;
        try {
            const url = new URL(action, window.location.origin);
            const base = new URL(config.baseUrl, window.location.origin);
            if (url.origin === base.origin && url.pathname.startsWith(base.pathname + '/')) return url.href;
        } catch (error) {}
        return config.storeUrl;
    }
    window.showRole = function (data = [], action = '') {
        const editing = data && !Array.isArray(data) && data.id;
        resetSave();
        form.action = validAction(action, editing);
        method.value = editing ? 'put' : 'post';
        field('roleTitle').value = editing ? 'Edit Role: ' + data.name : 'New Role';
        field('roleMethod').value = method.value;
        field('roleAction').value = form.action;
        field('h5roleTitle').textContent = field('roleTitle').value;
        field('name').value = editing ? data.name || '' : '';
        field('description').value = editing ? data.description || '' : '';
        form.querySelectorAll('[data-role-error], #roleValidationSummary').forEach(element => { element.hidden = true; });
        openModal();
    };
    form.addEventListener('submit', function (event) {
        if (saving || !form.reportValidity()) { event.preventDefault(); return; }
        saving = true;
        field('btnSave').disabled = true;
        field('btnSave').hidden = true;
        field('btnSaveDisabled').hidden = false;
        form.setAttribute('aria-busy', 'true');
    });
    window.ocSubmit = () => form.requestSubmit();
    modalElement.addEventListener('hidden.bs.modal', resetSave);
    window.addEventListener('pageshow', resetSave);
    function showLoadError(message) {
        field('rolesLoadError').textContent = message;
        field('rolesLoadError').hidden = false;
        field('roleCountBadge').textContent = 'Unavailable';
    }
    function escapeHtml(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
    function renderText(data, type) {
        const parsed = new DOMParser().parseFromString(String(data ?? ''), 'text/html');
        const text = parsed.body.textContent || '';
        return type === 'display' ? escapeHtml(text) : text;
    }
    if (window.jQuery && $.fn.DataTable) {
        $('#datatable-roles').on('xhr.dt', function (event, settings, response, xhr) {
            if (response && !response.error) { field('rolesLoadError').hidden = true; return; }
            showLoadError(xhr?.status === 419 ? 'Your session has expired. Refresh the page and sign in again.' : 'The role directory could not be loaded. Use Refresh to try again.');
            return true;
        });
        const table = $('#datatable-roles').DataTable({
            ajax: { url:config.dataUrl, type:'POST', headers:{'X-CSRF-TOKEN':config.csrf} },
            stateSave:true, searchDelay:500, serverSide:true, processing:true, autoWidth:false,
            dom:'<"roles-table-controls"lf><"roles-table-scroll"t><"roles-table-bottom"ip>',
            responsive:Boolean($.fn.dataTable.Responsive),
            columns:[
                {data:'name', name:'name', responsivePriority:1, render:function (data, type) {
                    const parsed = new DOMParser().parseFromString(String(data ?? ''), 'text/html');
                    const name = parsed.body.textContent || '';
                    if (type !== 'display') return name;
                    return '<div class="roles-name"><span class="roles-name-icon" aria-hidden="true"><i class="fa fa-key"></i></span><span>' + escapeHtml(name) + '</span></div>';
                }},
                {data:'description', name:'description', defaultContent:'—', render:renderText},
                {data:'creator', name:'creator', defaultContent:'—', orderable:false, render:renderText},
                {data:'created_at', name:'created_at', defaultContent:'—', render:renderText},
                {data:'actions', orderable:false, searchable:false, className:'roles-actions', responsivePriority:2}
            ],
            order:[[0,'asc']], lengthMenu:[[10,25,50,-1],[10,25,50,'All']],
            language:{search:'Search',searchPlaceholder:'Role or description',lengthMenu:'Show _MENU_ roles',emptyTable:'No roles available.',zeroRecords:'No matching roles found.',info:'Showing _START_–_END_ of _TOTAL_ roles',infoEmpty:'No roles to show'},
            drawCallback:function () {
                const info = this.api().page.info();
                field('roleCountBadge').textContent = info.recordsDisplay + (info.recordsDisplay === 1 ? ' role' : ' roles');
            }
        });
        field('btnRefreshRoles').addEventListener('click', () => table.ajax.reload(null, false));
    } else {
        showLoadError('The directory could not initialize. Refresh the page to reload its scripts.');
        field('btnRefreshRoles').addEventListener('click', () => window.location.reload());
    }
    if (config.hasErrors) {
        const old = config.old;
        resetSave();
        const editing = String(old.roleMethod).toLowerCase() === 'put';
        form.action = validAction(old.roleAction, editing);
        method.value = editing ? 'put' : 'post';
        field('h5roleTitle').textContent = old.roleTitle || 'New Role';
        field('name').value = old.name || '';
        field('description').value = old.description || '';
        openModal();
    }
});
</script>
@endpush

@push('css')
<style>
    #direk-roles, #direk-role-editor, #role-modal { --role-bg:#fff; --role-inset:#f6f7fb; --role-border:#e6e9f0; --role-title:#172033; --role-copy:#637086; --role-accent:#635bff; --role-accent-text:#5850df; --role-soft:#eeedff; color:var(--role-copy); }
    #direk-roles[data-theme="dark"], #direk-role-editor[data-theme="dark"], #role-modal[data-theme="dark"] { --role-bg:#192130; --role-inset:#151c29; --role-border:#2c3546; --role-title:#eef0f8; --role-copy:#95a0b5; --role-accent-text:#a19cff; --role-soft:#2b2850; }
    #direk-roles [hidden], #direk-role-editor [hidden], #role-modal [hidden] { display:none !important; }
    .direk-role-scope .roles-shell { max-width:1600px; margin:0 auto; }
    .direk-role-scope .roles-eyebrow { display:block; color:var(--role-accent-text); font-size:10px; font-weight:700; letter-spacing:.14em; }
    .direk-role-scope .roles-page-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; margin:8px 0 26px; }
    .direk-role-scope .roles-page-heading h2 { margin:9px 0 8px; color:var(--role-title); font-size:clamp(27px,3vw,36px); font-weight:700; line-height:1.2; letter-spacing:-.04em; }
    .direk-role-scope .roles-page-heading p { margin:0; color:var(--role-copy); font-size:13px; line-height:1.7; }
    .direk-role-scope .roles-feature-icon { display:grid; place-items:center; width:60px; height:60px; flex-shrink:0; border:1px solid var(--role-border); border-radius:20px; background:var(--role-bg); color:var(--role-accent-text); font-size:23px; }
    .direk-role-scope .roles-card { border:1px solid var(--role-border); border-radius:20px; background:var(--role-bg); overflow:hidden; box-shadow:0 5px 24px rgba(20,25,60,.035); }
    .direk-role-scope .roles-card-header { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; padding:23px 24px; border-bottom:1px solid var(--role-border); }
    .direk-role-scope .roles-card-header h3 { margin:0; color:var(--role-title); font-size:16px; font-weight:650; letter-spacing:-.02em; }
    .direk-role-scope .roles-card-header p { margin:5px 0 0; color:var(--role-copy); font-size:12px; line-height:1.6; }
    .direk-role-scope .roles-badge { display:inline-flex; align-items:center; border-radius:7px; background:var(--role-soft); color:var(--role-accent-text); padding:5px 9px; font-size:10px; font-weight:650; }
    .direk-role-scope .roles-button { display:inline-flex; min-height:44px; align-items:center; justify-content:center; gap:8px; border:1px solid var(--role-border); border-radius:10px; background:var(--role-bg); padding:10px 15px; color:var(--role-title); font-size:12px; font-weight:600; text-decoration:none; margin:0; }
    .direk-role-scope .roles-primary { border-color:#635bff; background:#635bff; color:#fff; box-shadow:0 3px 8px rgba(99,91,255,.15); }
    .direk-role-scope .roles-primary:hover { background:#534be5; color:#fff; }
    .direk-role-scope .roles-button:disabled { opacity:.6; cursor:wait; }
    .direk-role-scope button:focus-visible, .direk-role-scope a:focus-visible { outline:2px solid var(--role-accent-text); outline-offset:3px; }
    .direk-role-scope .roles-info { display:flex; align-items:flex-start; gap:9px; padding:13px 24px; background:var(--role-inset); color:var(--role-copy); font-size:12px; line-height:1.7; }
    .direk-role-scope .roles-table-wrap { padding:0 22px 18px; }
    .direk-role-scope .roles-table-controls, .direk-role-scope .roles-table-bottom { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; padding:18px 2px; }
    .direk-role-scope .roles-table-bottom { padding-bottom:0; }
    .direk-role-scope .roles-table-scroll { overflow-x:auto; }
    #datatable-roles { width:100% !important; margin:0; border-collapse:separate !important; border-spacing:0; }
    #datatable-roles thead th { position:static; border:0; border-bottom:1px solid var(--role-border); background:var(--role-inset); color:var(--role-copy); padding:13px 12px; font-size:10px; font-weight:650; text-transform:uppercase; letter-spacing:.06em; }
    #datatable-roles tbody td { padding:17px 12px; border:0; border-bottom:1px solid var(--role-border); background:var(--role-bg); color:var(--role-copy); vertical-align:middle; font-size:12px; line-height:1.7; overflow-wrap:anywhere; }
    #datatable-roles tbody tr:hover td { background:var(--role-inset); }
    #datatable-roles .roles-name { display:flex; align-items:center; gap:12px; min-width:160px; color:var(--role-title); font-weight:600; }
    #datatable-roles .roles-name-icon { display:grid; place-items:center; width:38px; height:38px; flex-shrink:0; border:1px solid var(--role-border); border-radius:12px; background:var(--role-soft); color:var(--role-accent-text); }
    #datatable-roles .roles-actions { white-space:nowrap; text-align:right; }
    #datatable-roles .roles-actions .btn-group { display:inline-flex; align-items:center; gap:6px; }
    #datatable-roles .roles-actions form { display:inline-flex; margin:0; }
    #datatable-roles .roles-actions button, #datatable-roles .roles-actions a { display:inline-flex; align-items:center; justify-content:center; min-width:40px; min-height:40px; border-radius:9px; text-decoration:none; }
    #datatable-roles .roles-actions button:hover, #datatable-roles .roles-actions a:hover { background:var(--role-soft); }
    #datatable-roles .dataTables_empty { padding:48px 16px; text-align:center; }
    #datatable-roles_wrapper .dataTables_length, #datatable-roles_wrapper .dataTables_filter, #datatable-roles_wrapper .dataTables_info, #datatable-roles_wrapper .dataTables_paginate { float:none !important; margin:0; padding:0; color:var(--role-copy); font-size:12px; }
    #datatable-roles_wrapper label { display:flex; align-items:center; gap:8px; margin:0; font-weight:400; color:var(--role-copy); }
    #datatable-roles_wrapper input, #datatable-roles_wrapper select { min-height:40px; margin:0; border:1px solid var(--role-border); border-radius:9px; background:var(--role-bg); color:var(--role-title); padding:8px 10px; outline:none; }
    #datatable-roles_wrapper .dataTables_filter input { width:250px; }
    #datatable-roles_wrapper .pagination { display:flex; gap:4px; flex-wrap:wrap; margin:0; }
    #datatable-roles_wrapper .page-link, #datatable-roles_wrapper .paginate_button { border:1px solid var(--role-border) !important; border-radius:8px !important; background:var(--role-bg) !important; color:var(--role-copy) !important; font-size:12px; }
    #datatable-roles_wrapper li.paginate_button { padding:0 !important; border:0 !important; background:none !important; }
    #datatable-roles_wrapper .active .page-link, #datatable-roles_wrapper .paginate_button.current { border-color:#635bff !important; background:#635bff !important; color:#fff !important; }
    #role-modal .modal-content { border:1px solid var(--role-border); border-radius:20px; background:var(--role-bg) !important; color:var(--role-copy); overflow:hidden; }
    #role-modal .modal-header { padding:23px 24px; border-color:var(--role-border); background:linear-gradient(125deg,var(--role-soft),var(--role-bg) 75%); }
    #role-modal .modal-title { color:var(--role-title); margin:7px 0 0; font-size:22px; font-weight:650; letter-spacing:-.03em; }
    #role-modal .modal-body { padding:24px; }
    #role-modal .modal-footer { border-color:var(--role-border); background:var(--role-inset); padding:16px 24px; }
    #role-modal[data-theme="dark"] .btn-close { filter:invert(1); }
    .direk-role-scope .roles-field { display:block; box-sizing:border-box; width:100%; min-height:44px; border:1px solid var(--role-border); border-radius:10px; padding:10px 12px; background:var(--role-bg); color:var(--role-title); font-size:13px; line-height:1.6; outline:none; }
    .direk-role-scope textarea.roles-field { resize:vertical; }
    .direk-role-scope .roles-label { display:block; margin-bottom:7px; color:var(--role-title); font-size:12px; font-weight:600; }
    .direk-role-scope .roles-field:focus, #datatable-roles_wrapper input:focus { border-color:#635bff; box-shadow:0 0 0 3px rgba(99,91,255,.12); }
    .direk-role-scope .roles-error { margin:6px 0 0; color:#e55378; font-size:11px; line-height:1.6; }
    .direk-role-scope .roles-alert { margin-bottom:18px; padding:14px 16px; border:1px solid #f4b8c7; border-radius:12px; background:#fff1f4; color:#aa2b4d; font-size:12px; line-height:1.8; }
    .direk-role-scope[data-theme="dark"] .roles-alert { border-color:#663449; background:#352332; color:#ffb3c5; }
    .direk-role-scope .roles-alert ul { margin:6px 0 0; padding-left:18px; }
    .direk-role-scope .roles-editor-grid { display:grid; grid-template-columns:300px minmax(0,1fr); gap:24px; align-items:start; }
    .direk-role-scope .roles-details { position:sticky; top:24px; padding:24px; }
    .direk-role-scope .roles-details h3 { margin:0 0 20px; color:var(--role-title); font-size:16px; font-weight:650; }
    .direk-role-scope .roles-details .roles-field { margin-bottom:18px; }
    .direk-role-scope .roles-metadata { border-top:1px solid var(--role-border); padding-top:16px; margin-top:6px; font-size:11px; line-height:1.8; overflow-wrap:anywhere; }
    .direk-role-scope .roles-permission-tools { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; padding:18px 24px; border-bottom:1px solid var(--role-border); }
    .direk-role-scope .roles-permission-search { width:280px; max-width:100%; }
    .direk-role-scope .role-permission-category + .role-permission-category { border-top:1px solid var(--role-border); }
    .direk-role-scope .role-permission-toggle { display:flex; width:100%; align-items:center; gap:12px; border:0; margin:0; padding:20px 24px; text-align:left; background:var(--role-bg); color:var(--role-title); }
    .direk-role-scope .role-permission-toggle strong { display:block; font-size:13px; font-weight:650; }
    .direk-role-scope .role-permission-toggle small { display:block; margin-top:5px; color:var(--role-copy); font-size:11px; line-height:1.7; }
    .direk-role-scope .role-permission-toggle .roles-category-copy { flex:1; min-width:0; }
    .direk-role-scope .role-permission-toggle-icon { color:var(--role-copy); font-size:11px; }
    .direk-role-scope .role-permission-panel { padding:0 20px 20px; background:var(--role-bg); }
    .direk-role-scope .role-module { border:1px solid var(--role-border); border-radius:13px; overflow:hidden; margin-top:12px; }
    .direk-role-scope .role-module-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; background:var(--role-inset); }
    .direk-role-scope .role-module-header h4 { margin:0; color:var(--role-title); font-size:12px; font-weight:650; }
    .direk-role-scope .role-permission-row { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:14px 16px; border-top:1px solid var(--role-border); }
    .direk-role-scope .role-permission-row strong { display:block; color:var(--role-title); font-size:12px; font-weight:600; }
    .direk-role-scope .role-permission-row p { margin:4px 0 0; color:var(--role-copy); font-size:11px; line-height:1.7; }
    .direk-role-scope .role-switch { display:flex; flex-shrink:0; position:relative; cursor:pointer; padding:4px; }
    .direk-role-scope .role-switch input { position:absolute; width:1px; height:1px; opacity:0; }
    .direk-role-scope .role-switch-track { display:block; position:relative; width:38px; height:22px; border-radius:999px; background:#cbd1dd; transition:background .15s; }
    .direk-role-scope .role-switch-track::after { content:''; position:absolute; top:3px; left:3px; width:16px; height:16px; border-radius:50%; background:white; box-shadow:0 1px 3px rgba(0,0,0,.12); transition:transform .15s; }
    .direk-role-scope .role-switch input:checked + span { background:#635bff; }
    .direk-role-scope .role-switch input:checked + span::after { transform:translateX(16px); }
    .direk-role-scope .role-switch input:focus-visible + span { outline:2px solid var(--role-accent-text); outline-offset:3px; }
    .direk-role-scope .roles-editor-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:10px; margin-top:20px; padding:16px 20px; border:1px solid var(--role-border); border-radius:14px; background:var(--role-bg); }
    .direk-role-scope .roles-empty { text-align:center; padding:34px 20px; color:var(--role-copy); font-size:12px; line-height:1.8; }
    @media(max-width:991px) { .direk-role-scope .roles-editor-grid { grid-template-columns:1fr; } .direk-role-scope .roles-details { position:static; } }
    @media(max-width:639px) { .direk-role-scope .roles-feature-icon { display:none; } .direk-role-scope .roles-card-header { padding:20px 16px; } .direk-role-scope .roles-table-wrap { padding:0 12px 12px; } #datatable-roles_wrapper .dataTables_filter { width:100%; } #datatable-roles_wrapper .dataTables_filter label { width:100%; } #datatable-roles_wrapper .dataTables_filter input { min-width:0; width:0; flex:1; } .direk-role-scope .roles-details, .direk-role-scope .roles-permission-tools { padding:18px; } .direk-role-scope .role-permission-toggle { padding:18px; } .direk-role-scope .role-permission-panel { padding:0 12px 14px; } .direk-role-scope .roles-permission-search { width:100%; } }
    @media(prefers-reduced-motion:reduce) { .direk-role-scope * { transition:none !important; } }

</style>
@endpush

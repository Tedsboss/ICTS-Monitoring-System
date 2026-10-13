@php
    $class_theme = session('user_settings.class_theme', '');
    $selectedPermissions = collect(
        session()->hasOldInput() ? old('permissions', []) : ($role_permissions ?? [])
    )
        ->map(function ($permissionId) {
            return (int) $permissionId;
        })
        ->values()
        ->toArray();
    // DIREK Administration modules
    $administrationModuleNames = [
        'User Management',
        'Roles and Permissions',
        'Staff Management',
        'Division Management',
        'System Parameters',
        'System Logs',
    ];
    // DIREK Financial Management modules
    $financialManagementModuleNames = [
        'Financial Plan',
        'Work Plan',
        'Allocation Management',
        'Allocation Type Management',
        'Procurement',
        'SAEB',
    ];
    // Match Administration modules
    $administrationModules = $modules->filter(function ($module) use ($administrationModuleNames) {
        return collect($administrationModuleNames)->contains(function ($name) use ($module) {
            return strcasecmp(
                trim((string) $module->name),
                trim((string) $name)
            ) === 0;
        });
    });
    // Match Financial Management modules
    $financialManagementModules = $modules->filter(function ($module) use ($financialManagementModuleNames) {
        return collect($financialManagementModuleNames)->contains(function ($name) use ($module) {
            return strcasecmp(
                trim((string) $module->name),
                trim((string) $name)
            ) === 0;
        });
    });
    // DIREK permission categories
    $direkCategories = collect([
        [
            'name' => 'Administration',
            'key' => 'administration',
            'description' => 'Manage DIREK users, roles, organizational structure, parameters, and system logs.',
            'icon' => 'fa fa-cog',
            'modules' => $administrationModules,
        ],
        [
            'name' => 'Financial Management',
            'key' => 'financial-management',
            'description' => 'Manage access to Financial Plan, Work Plan, Allocation Management, Allocation Type Management, Procurement, and SAEB functions.',
            'icon' => 'fa fa-money',
            'modules' => $financialManagementModules,
        ],
    ])->filter(function ($category) {
        return $category['modules']->isNotEmpty();
    });
@endphp

@extends('layouts.app')
@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Edit Role', 'subtitle' => $role->name, 'links' => [['name' => 'Roles & Permissions', 'url' => route('roles.index')]]])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-role-editor" class="direk-role-scope px-4 pb-8 pt-4" data-theme="{{ $class_theme === 'dark' ? 'dark' : 'light' }}">
    <div class="roles-shell">
        <a class="roles-button mb-4" href="{{ route('roles.index') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i>Back to roles</a>
        <header class="roles-page-heading">
            <div><span class="roles-eyebrow">PEOPLE &amp; ACCESS</span><h2>Shape the role.</h2><p>Review responsibilities and choose the actions this role can perform.</p></div>
            <span class="roles-feature-icon" aria-hidden="true"><i class="fa fa-key"></i></span>
        </header>
        @if ($errors->any())
            <div class="roles-alert" role="alert"><strong>Please review these fields.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('roles.update', $role->id) }}" id="frmUpdate">
            @csrf
            @method('put')
            <div class="roles-editor-grid">
                <section class="roles-card roles-details" aria-labelledby="roleDetailsTitle">
                    <span class="roles-eyebrow mb-2">ROLE IDENTITY</span>
                    <h3 id="roleDetailsTitle">Role details</h3>
                    <label for="name" class="roles-label">Role name</label>
                    <input class="roles-field" type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required>
                    @error('name')<p class="roles-error">{{ $message }}</p>@enderror
                    <label for="description" class="roles-label">Description</label>
                    <textarea class="roles-field" name="description" id="description" rows="5" required>{{ old('description', $role->description) }}</textarea>
                    @error('description')<p class="roles-error">{{ $message }}</p>@enderror
                    <div class="roles-metadata">
                        <div>Created by <strong>{{ optional($role->creator)->full_name ?: '—' }}</strong></div>
                        <div>Last updated {{ $role->updated_at ? $role->updated_at->format('M j, Y g:i A') : '—' }}</div>
                    </div>
                </section>
                <section class="roles-card" aria-labelledby="rolePermissionsTitle">
                    <div class="roles-card-header">
                        <div><h3 id="rolePermissionsTitle">Module permissions</h3><p>Allow the functions needed for this role's responsibilities.</p></div>
                        <span id="rolePermissionTotal" class="roles-badge" aria-live="polite">Review permissions</span>
                    </div>
                    <div class="roles-permission-tools">
                        <div class="roles-permission-search"><label for="permissionSearch" class="sr-only">Search modules</label><input type="search" id="permissionSearch" class="roles-field" placeholder="Search modules…"></div>
                        <button type="button" id="btnExpandPermissions" class="roles-button" aria-controls="role-permission-accordion">Expand all</button>
                    </div>
                    <div id="role-permission-accordion">
                        @forelse ($direkCategories as $direkCategory)
                            @php
                                $categoryKey = $direkCategory['key'];
                                $categoryModules = $direkCategory['modules'];
                                $categoryPermissions = $categoryModules->flatMap(fn ($module) => $module->permissions);
                                $categoryCheckedCount = $categoryPermissions->filter(fn ($permission) => in_array((int) $permission->id, $selectedPermissions, true))->count();
                            @endphp
                            <div class="role-permission-category" data-permission-category="{{ $categoryKey }}">
                                <button type="button" class="role-permission-toggle" data-target="rolePermissionPanel{{ $categoryKey }}" aria-controls="rolePermissionPanel{{ $categoryKey }}" aria-expanded="{{ $categoryCheckedCount > 0 ? 'true' : 'false' }}">
                                    <span class="roles-badge" aria-hidden="true"><i class="{{ $direkCategory['icon'] }}"></i></span>
                                    <span class="roles-category-copy"><strong>{{ $direkCategory['name'] }}</strong><small>{{ $direkCategory['description'] }}</small></span>
                                    <span class="roles-badge role-category-count">{{ $categoryCheckedCount }} / {{ $categoryPermissions->count() }}</span>
                                    <i class="fa {{ $categoryCheckedCount > 0 ? 'fa-minus' : 'fa-plus' }} role-permission-toggle-icon" aria-hidden="true"></i>
                                </button>
                                <div id="rolePermissionPanel{{ $categoryKey }}" class="role-permission-panel" @if ($categoryCheckedCount === 0) hidden @endif>
                                    @foreach ($categoryModules as $module)
                                        <section class="role-module" data-module-name="{{ $module->name }}" aria-label="{{ $module->name }} permissions">
                                            <div class="role-module-header"><h4>{{ $module->name }}</h4><span class="roles-badge role-module-count"></span></div>
                                            @foreach ($module->permissions->sortBy([['order', 'asc'], ['id', 'asc']]) as $permission)
                                                <div class="role-permission-row" @if ((int) $permission->id === 48) id="tr_permission_48" @if (!in_array(7, $selectedPermissions, true)) hidden @endif @endif>
                                                    <div><strong id="permission_label_{{ $permission->id }}">{{ $permission->name }}</strong><p id="permission_description_{{ $permission->id }}">{{ $permission->description ?: '—' }}</p></div>
                                                    <label class="role-switch" for="permission_{{ $permission->id }}">
                                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}" aria-labelledby="permission_label_{{ $permission->id }}" aria-describedby="permission_description_{{ $permission->id }}" @if (in_array((int) $permission->id, $selectedPermissions, true)) checked @endif>
                                                        <span class="role-switch-track" aria-hidden="true"></span>
                                                    </label>
                                                </div>
                                            @endforeach
                                            @if ($module->permissions->isEmpty())<p class="roles-empty">No permissions configured for this module.</p>@endif
                                        </section>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="roles-empty">No DIREK permission modules are configured.</p>
                        @endforelse
                    </div>
                    <p id="permissionSearchEmpty" class="roles-empty" role="status" hidden>No modules match your search.</p>
                </section>
            </div>
            <div class="roles-editor-actions">
                <button type="button" id="btnCancel" class="roles-button" onclick="occancel()">Cancel</button>
                <button type="submit" id="btnSave" class="roles-button roles-primary"><i class="fa fa-check" aria-hidden="true"></i>Save Changes</button>
                <button type="button" id="btnSaveDisabled" class="roles-button roles-primary" disabled hidden><span class="spinner-border spinner-border-sm" aria-hidden="true"></span>Saving…</button>
            </div>
        </form>
    </div>
    <div class="mt-6">@include('layouts.footers.auth.footer')</div>
</div>
@endsection
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('direk-role-editor');
    const form = document.getElementById('frmUpdate');
    const field = id => document.getElementById(id);
    const categories = Array.from(root.querySelectorAll('.role-permission-category'));
    const modules = Array.from(root.querySelectorAll('.role-module'));
    const permissions = Array.from(form.querySelectorAll('input[name="permissions[]"]'));
    let saving = false;
    let expandedBeforeSearch = null;
    function setExpanded(category, expanded) {
        const toggle = category.querySelector('.role-permission-toggle');
        field(toggle.dataset.target).hidden = !expanded;
        toggle.setAttribute('aria-expanded', String(expanded));
        const icon = toggle.querySelector('.role-permission-toggle-icon');
        icon.classList.toggle('fa-plus', !expanded);
        icon.classList.toggle('fa-minus', expanded);
    }
    function updateExpandLabel() {
        const visible = categories.filter(category => !category.hidden);
        field('btnExpandPermissions').textContent = visible.length && visible.every(category => category.querySelector('.role-permission-toggle').getAttribute('aria-expanded') === 'true') ? 'Collapse all' : 'Expand all';
    }
    function countPermissions(container) {
        const inputs = Array.from(container.querySelectorAll('input[name="permissions[]"]'));
        return inputs.filter(input => input.checked).length + ' / ' + inputs.length;
    }
    function refreshCounts() {
        field('rolePermissionTotal').textContent = permissions.filter(input => input.checked).length + ' of ' + permissions.length + ' allowed';
        categories.forEach(category => { category.querySelector('.role-category-count').textContent = countPermissions(category); });
        modules.forEach(module => { module.querySelector('.role-module-count').textContent = countPermissions(module) + ' allowed'; });
    }
    function updateDependentPermission() {
        const row = field('tr_permission_48');
        const parent = field('permission_7');
        if (row && parent) row.hidden = !parent.checked;
    }
    categories.forEach(category => {
        const toggle = category.querySelector('.role-permission-toggle');
        toggle.addEventListener('click', () => {
            setExpanded(category, toggle.getAttribute('aria-expanded') !== 'true');
            updateExpandLabel();
        });
    });
    permissions.forEach(input => input.addEventListener('change', () => {
        if ([7,25,29,41,42].includes(Number(input.value)) && typeof window.showSubItems === 'function') window.showSubItems(Number(input.value));
        updateDependentPermission();
        refreshCounts();
    }));
    field('btnExpandPermissions').addEventListener('click', () => {
        const visible = categories.filter(category => !category.hidden);
        const expand = !visible.every(category => category.querySelector('.role-permission-toggle').getAttribute('aria-expanded') === 'true');
        visible.forEach(category => setExpanded(category, expand));
        updateExpandLabel();
    });
    field('permissionSearch').addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        if (term && expandedBeforeSearch === null) expandedBeforeSearch = categories.map(category => category.querySelector('.role-permission-toggle').getAttribute('aria-expanded') === 'true');
        modules.forEach(module => { module.hidden = term !== '' && !module.dataset.moduleName.toLowerCase().includes(term); });
        categories.forEach((category, index) => {
            category.hidden = Array.from(category.querySelectorAll('.role-module')).every(module => module.hidden);
            if (term && !category.hidden) setExpanded(category, true);
            else if (!term && expandedBeforeSearch !== null) setExpanded(category, expandedBeforeSearch[index]);
        });
        if (!term) expandedBeforeSearch = null;
        field('permissionSearchEmpty').hidden = !term || modules.some(module => !module.hidden);
        updateExpandLabel();
    });
    function resetSave() {
        saving = false;
        field('btnSave').hidden = false;
        field('btnSave').disabled = false;
        field('btnSaveDisabled').hidden = true;
        field('btnCancel').disabled = false;
        form.removeAttribute('aria-busy');
    }
    form.addEventListener('submit', event => {
        if (saving || !form.reportValidity()) { event.preventDefault(); return; }
        saving = true;
        field('btnSave').disabled = true;
        field('btnSave').hidden = true;
        field('btnSaveDisabled').hidden = false;
        field('btnCancel').disabled = true;
        form.setAttribute('aria-busy', 'true');
    });
    window.ocSubmit = () => form.requestSubmit();
    window.occancel = () => { if (!saving && confirm('Discard unsaved role changes?')) window.location.href = {{ \Illuminate\Support\Js::from(route('roles.index')) }}; };
    window.addEventListener('pageshow', resetSave);
    refreshCounts();
    updateDependentPermission();
    updateExpandLabel();
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

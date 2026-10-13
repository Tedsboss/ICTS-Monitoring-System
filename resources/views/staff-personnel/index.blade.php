@extends('layouts.app')
@section('content')
@php
    $personnelDark = session('user_settings.class_theme', '') === 'dark';
    $personnelConfig = [
        'storeUrl' => route('staff-personnel.store'),
        'baseUrl' => url('/administrator/staff-personnel'),
        'staffId' => $staffId,
        'hasErrors' => $errors->any(),
        'old' => [
            'id' => old('_editing_personnel_id', ''),
            'staff_id' => old('staff_id', $staffId),
            'name' => old('name', ''),
            'position' => old('position', ''),
        ],
    ];
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Staff Personnel'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-personnel" class="direk-staff-scope px-4 pb-8 pt-4" data-theme="{{ $personnelDark ? 'dark' : 'light' }}">
    <div class="staffs-shell">
        <header class="staffs-page-heading">
            <div><span class="staffs-eyebrow">TEAM DIRECTORY</span><h2>The people behind the plan.</h2><p>Keep your personnel list ready for Financial Plan assignments.</p></div>
            <span class="staffs-feature-icon" aria-hidden="true"><i class="fa fa-users"></i></span>
        </header>
        @if (session()->has('succes'))<div class="personnel-success" role="status"><i class="fa fa-check-circle" aria-hidden="true"></i>{{ session('succes') }}</div>@endif
        <section class="staffs-card" aria-labelledby="personnelDirectoryTitle">
            <div class="staffs-card-header">
                <div><h3 id="personnelDirectoryTitle">Personnel directory <span id="personnelCountBadge" class="staffs-badge" aria-live="polite">{{ $personnel->count() }} {{ $personnel->count() === 1 ? 'person' : 'people' }}</span></h3><p>People listed here do not need a DIREK user account.</p></div>
                <button type="button" id="btnAddPersonnel" class="staffs-button staffs-primary" aria-haspopup="dialog" aria-controls="personnelModal"><i class="fa fa-plus" aria-hidden="true"></i>Add Personnel</button>
            </div>
            <div class="personnel-tools">
                <form method="GET" action="{{ route('staff-personnel.index') }}" class="personnel-filter">
                    <div><label for="staffFilter" class="staffs-label">Staff / Office</label><select id="staffFilter" name="staff_id" class="staffs-field"><option value="">All Staff / Offices</option>@foreach ($staffs as $staff)<option value="{{ $staff->id }}" {{ (string) $staffId === (string) $staff->id ? 'selected' : '' }}>{{ $staff->name }}{{ filled($staff->abbreviation) ? ' (' . $staff->abbreviation . ')' : '' }}</option>@endforeach</select></div>
                    <button type="submit" class="staffs-button"><i class="fa fa-filter" aria-hidden="true"></i>Filter</button>
                    @if ($staffId !== null)<a href="{{ route('staff-personnel.index') }}" class="staffs-button">Reset</a>@endif
                </form>
                <div class="personnel-search"><label for="personnelSearch" class="staffs-label">Search personnel</label><input type="search" id="personnelSearch" class="staffs-field" placeholder="Name, position, or office…"></div>
            </div>
            <div class="staffs-info"><i class="fa fa-info-circle" aria-hidden="true"></i><span>Inactive personnel are excluded from new Financial Plan Assigned Personnel selections.</span></div>
            <div class="staffs-table-wrap">
                <div class="staffs-table-scroll">
                    <table id="personnelTable" aria-label="Staff personnel directory" style="width:100%">
                        <thead><tr><th scope="col">Personnel</th><th scope="col">Position</th><th scope="col">Staff / Office</th><th scope="col">Status</th><th scope="col" class="staffs-actions">Actions</th></tr></thead>
                        <tbody>
                            @forelse ($personnel as $person)
                                <tr data-personnel-row data-search="{{ mb_strtolower(trim($person->name . ' ' . $person->position . ' ' . optional($person->staff)->name . ' ' . optional($person->staff)->abbreviation)) }}">
                                    <td><div class="staffs-name"><span class="staffs-name-icon" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim($person->name), 0, 1)) }}</span><span>{{ $person->name }}</span></div></td>
                                    <td>{{ filled($person->position) ? $person->position : '—' }}</td>
                                    <td>@if ($person->staff)<span>{{ $person->staff->name }}</span>@if (filled($person->staff->abbreviation))<small class="personnel-office-code">{{ $person->staff->abbreviation }}</small>@endif @else<span class="staffs-error">Staff unavailable</span>@endif</td>
                                    <td><span class="staffs-badge {{ $person->is_active ? 'staff-checking-enabled' : 'staff-checking-disabled' }}">{{ $person->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td class="staffs-actions">
                                        <div class="btn-group" role="group" aria-label="Actions for {{ $person->name }}">
                                            <button type="button" class="btn-edit-personnel staff-action-edit" data-id="{{ $person->id }}" data-name="{{ $person->name }}" data-position="{{ $person->position }}" data-staff-id="{{ $person->staff_id }}" data-update-url="{{ route('staff-personnel.update', $person->id) }}" aria-label="Edit {{ $person->name }}" title="Edit personnel"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                            <a href="{{ route('staff-personnel.switchstatus', $person->id) }}" class="staff-action-status" data-confirm="{{ ($person->is_active ? 'Deactivate ' : 'Activate ') . $person->name . '?' }}" onclick="return confirm(this.dataset.confirm);" aria-label="{{ ($person->is_active ? 'Deactivate ' : 'Activate ') . $person->name }}" title="{{ $person->is_active ? 'Deactivate personnel' : 'Activate personnel' }}"><i class="fa {{ $person->is_active ? 'fa-lock' : 'fa-unlock' }}" aria-hidden="true"></i></a>
                                            <form method="POST" action="{{ route('staff-personnel.destroy', $person->id) }}" data-confirm="{{ 'Delete ' . $person->name . '? Use Deactivate instead if this person should only be removed from future selections.' }}" onsubmit="return confirm(this.dataset.confirm);">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="personnel-delete" title="Delete personnel" aria-label="Delete {{ $person->name }}"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="staffs-empty">No staff personnel found. Add personnel to make them available for Financial Plan assignments.</td></tr>
                            @endforelse
                            <tr id="personnelSearchEmpty" hidden><td colspan="5" class="staffs-empty">No personnel match your search.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
    <div class="mt-6">@include('layouts.footers.auth.footer')</div>
</div>
<div id="personnelModal" class="direk-staff-scope hidden" data-theme="{{ $personnelDark ? 'dark' : 'light' }}" aria-hidden="true">
    <div id="personnelModalBackdrop"></div>
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="personnelModalTitle" aria-describedby="personnelModalSubtitle" tabindex="-1">
        <div class="modal-header">
            <div><span class="staffs-eyebrow">TEAM DIRECTORY</span><h2 id="personnelModalTitle" class="modal-title">Add Personnel</h2><p id="personnelModalSubtitle" class="staff-modal-subtitle">Personnel do not need a DIREK user account.</p></div>
            <button type="button" id="btnClosePersonnelModal" class="personnel-close" aria-label="Close"><i class="fa fa-times" aria-hidden="true"></i></button>
        </div>
        <form id="personnelForm" method="POST" action="{{ route('staff-personnel.store') }}">
            @csrf
            <input type="hidden" name="_editing_personnel_id" id="editingPersonnelId" value="">
            <div id="personnelMethodContainer"></div>
            <div class="modal-body">
                @if ($errors->any())<div id="personnelValidationSummary" class="staffs-alert" role="alert"><strong>Please review these fields.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <label for="personnelStaffId" class="staffs-label">Staff / Office</label>
                <select name="staff_id" id="personnelStaffId" class="staffs-field" required><option value="">Select Staff / Office</option>@foreach ($staffs as $staff)<option value="{{ $staff->id }}">{{ $staff->name }}{{ filled($staff->abbreviation) ? ' (' . $staff->abbreviation . ')' : '' }}</option>@endforeach</select>
                @error('staff_id')<p class="staffs-error" data-personnel-error>{{ $message }}</p>@enderror
                <div class="mt-4"><label for="personnelName" class="staffs-label">Personnel name</label><input type="text" name="name" id="personnelName" class="staffs-field" maxlength="150" required placeholder="e.g. Juan Dela Cruz">@error('name')<p class="staffs-error" data-personnel-error>{{ $message }}</p>@enderror</div>
                <div class="mt-4"><label for="personnelPosition" class="staffs-label">Position</label><input type="text" name="position" id="personnelPosition" class="staffs-field" maxlength="150" placeholder="e.g. Information Systems Analyst">@error('position')<p class="staffs-error" data-personnel-error>{{ $message }}</p>@enderror</div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnCancelPersonnel" class="staffs-button">Cancel</button>
                <button type="submit" id="btnSavePersonnel" class="staffs-button staffs-primary"><i class="fa fa-check" aria-hidden="true"></i><span id="savePersonnelText">Save Personnel</span></button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const config = {{ \Illuminate\Support\Js::from($personnelConfig) }};
    const field = id => document.getElementById(id);
    const modal = field('personnelModal');
    const dialog = modal.querySelector('[role="dialog"]');
    const form = field('personnelForm');
    let saving = false;
    let previousFocus = null;
    let previousOverflow = '';
    function resetSave() {
        saving = false;
        field('btnSavePersonnel').disabled = false;
        field('btnCancelPersonnel').disabled = false;
        field('btnClosePersonnelModal').disabled = false;
        field('savePersonnelText').textContent = field('editingPersonnelId').value ? 'Update Personnel' : 'Save Personnel';
        form.removeAttribute('aria-busy');
    }
    function configure(editingId = '', updateUrl = '') {
        form.reset();
        field('personnelMethodContainer').replaceChildren();
        field('editingPersonnelId').value = String(editingId || '');
        form.action = config.storeUrl;
        if (editingId) {
            const url = new URL(updateUrl || config.baseUrl + '/' + encodeURIComponent(editingId), window.location.origin);
            const base = new URL(config.baseUrl, window.location.origin);
            if (url.origin !== base.origin || !url.pathname.startsWith(base.pathname + '/') || !/^\d+$/.test(url.pathname.slice(base.pathname.length + 1))) throw new Error('Invalid personnel update URL');
            form.action = url.href;
            const method = document.createElement('input');
            method.type = 'hidden'; method.name = '_method'; method.value = 'PUT';
            field('personnelMethodContainer').append(method);
        }
        field('personnelModalTitle').textContent = editingId ? 'Edit Personnel' : 'Add Personnel';
        field('personnelStaffId').value = String(config.staffId || '');
        form.querySelectorAll('[data-personnel-error], #personnelValidationSummary').forEach(element => { element.hidden = true; });
        resetSave();
    }
    function openModal() {
        previousFocus = document.activeElement;
        previousOverflow = document.body.style.overflow;
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        dialog.focus();
    }
    function closeModal() {
        if (saving) return;
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = previousOverflow;
        if (previousFocus?.isConnected) previousFocus.focus();
    }
    field('btnAddPersonnel').addEventListener('click', () => { configure(); openModal(); });
    document.querySelectorAll('.btn-edit-personnel').forEach(button => button.addEventListener('click', () => {
        configure(button.dataset.id, button.dataset.updateUrl);
        field('personnelStaffId').value = String(button.dataset.staffId || '');
        field('personnelName').value = button.dataset.name || '';
        field('personnelPosition').value = button.dataset.position || '';
        openModal();
    }));
    ['btnClosePersonnelModal','btnCancelPersonnel','personnelModalBackdrop'].forEach(id => field(id).addEventListener('click', closeModal));
    document.addEventListener('keydown', event => {
        if (modal.classList.contains('hidden')) return;
        if (event.key === 'Escape') { event.preventDefault(); closeModal(); }
        if (event.key === 'Tab') {
            const items = Array.from(dialog.querySelectorAll('button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled])')).filter(element => element.getClientRects().length);
            if (!items.length) { event.preventDefault(); dialog.focus(); return; }
            const first = items[0], last = items[items.length - 1];
            if (event.shiftKey && (document.activeElement === first || document.activeElement === dialog)) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && (document.activeElement === last || document.activeElement === dialog)) { event.preventDefault(); first.focus(); }
        }
    });
    form.addEventListener('submit', event => {
        if (saving || !form.reportValidity()) { event.preventDefault(); return; }
        saving = true;
        field('btnSavePersonnel').disabled = true;
        field('btnCancelPersonnel').disabled = true;
        field('btnClosePersonnelModal').disabled = true;
        field('savePersonnelText').textContent = 'Saving…';
        form.setAttribute('aria-busy', 'true');
    });
    const rows = Array.from(document.querySelectorAll('[data-personnel-row]'));
    field('personnelSearch').addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach(row => { row.hidden = term !== '' && !row.dataset.search.toLowerCase().includes(term); if (!row.hidden) visible++; });
        field('personnelCountBadge').textContent = visible + (visible === 1 ? ' person' : ' people');
        field('personnelSearchEmpty').hidden = !term || visible > 0;
    });
    window.addEventListener('pageshow', resetSave);
    if (config.hasErrors) {
        configure(config.old.id);
        field('personnelStaffId').value = String(config.old.staff_id || '');
        field('personnelName').value = config.old.name || '';
        field('personnelPosition').value = config.old.position || '';
        form.querySelectorAll('[data-personnel-error], #personnelValidationSummary').forEach(element => { element.hidden = false; });
        openModal();
    }
});
</script>
@endpush

@push('css')
<style>

    #direk-personnel, #direk-staff-editor, #personnelModal { --staff-bg:#fff; --staff-inset:#f6f7fb; --staff-border:#e6e9f0; --staff-title:#172033; --staff-copy:#637086; --staff-accent:#635bff; --staff-accent-text:#5850df; --staff-soft:#eeedff; color:var(--staff-copy); }
    #direk-personnel[data-theme="dark"], #direk-staff-editor[data-theme="dark"], #personnelModal[data-theme="dark"] { --staff-bg:#192130; --staff-inset:#151c29; --staff-border:#2c3546; --staff-title:#eef0f8; --staff-copy:#95a0b5; --staff-accent-text:#a19cff; --staff-soft:#2b2850; }
    #direk-personnel [hidden], #direk-staff-editor [hidden], #personnelModal [hidden] { display:none !important; }
    .direk-staff-scope .staffs-shell { max-width:1600px; margin:0 auto; }
    .direk-staff-scope .staffs-eyebrow { display:block; color:var(--staff-accent-text); font-size:10px; font-weight:700; letter-spacing:.14em; }
    .direk-staff-scope .staffs-page-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; margin:8px 0 26px; }
    .direk-staff-scope .staffs-page-heading h2 { margin:9px 0 8px; color:var(--staff-title); font-size:clamp(27px,3vw,36px); font-weight:700; line-height:1.2; letter-spacing:-.04em; }
    .direk-staff-scope .staffs-page-heading p { margin:0; color:var(--staff-copy); font-size:13px; line-height:1.7; }
    .direk-staff-scope .staffs-feature-icon { display:grid; place-items:center; width:60px; height:60px; flex-shrink:0; border:1px solid var(--staff-border); border-radius:20px; background:var(--staff-bg); color:var(--staff-accent-text); font-size:23px; }
    .direk-staff-scope .staffs-card { border:1px solid var(--staff-border); border-radius:20px; background:var(--staff-bg); overflow:hidden; box-shadow:0 5px 24px rgba(20,25,60,.035); }
    .direk-staff-scope .staffs-card-header { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; padding:23px 24px; border-bottom:1px solid var(--staff-border); }
    .direk-staff-scope .staffs-card-header h3 { margin:0; color:var(--staff-title); font-size:16px; font-weight:650; letter-spacing:-.02em; }
    .direk-staff-scope .staffs-card-header p { margin:5px 0 0; color:var(--staff-copy); font-size:12px; line-height:1.6; }
    .direk-staff-scope .staffs-badge { display:inline-flex; align-items:center; border-radius:7px; background:var(--staff-soft); color:var(--staff-accent-text); padding:5px 9px; font-size:10px; font-weight:650; }
    .direk-staff-scope .staffs-button { display:inline-flex; min-height:44px; align-items:center; justify-content:center; gap:8px; border:1px solid var(--staff-border); border-radius:10px; background:var(--staff-bg); padding:10px 15px; color:var(--staff-title); font-size:12px; font-weight:600; text-decoration:none; margin:0; }
    .direk-staff-scope .staffs-primary { border-color:#635bff; background:#635bff; color:#fff; box-shadow:0 3px 8px rgba(99,91,255,.15); }
    .direk-staff-scope .staffs-primary:hover { background:#534be5; color:#fff; }
    .direk-staff-scope .staffs-button:disabled { opacity:.6; cursor:wait; }
    .direk-staff-scope button:focus-visible, .direk-staff-scope a:focus-visible { outline:2px solid var(--staff-accent-text); outline-offset:3px; }
    .direk-staff-scope .staffs-info { display:flex; align-items:flex-start; gap:9px; padding:13px 24px; background:var(--staff-inset); color:var(--staff-copy); font-size:12px; line-height:1.7; }
    .direk-staff-scope .staffs-table-wrap { padding:0 22px 18px; }
    .direk-staff-scope .staffs-table-controls, .direk-staff-scope .staffs-table-bottom { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; padding:18px 2px; }
    .direk-staff-scope .staffs-table-bottom { padding-bottom:0; }
    .direk-staff-scope .staffs-table-scroll { overflow-x:auto; }
    #personnelTable { width:100% !important; margin:0; border-collapse:separate !important; border-spacing:0; }
    #personnelTable thead th { position:static; border:0; border-bottom:1px solid var(--staff-border); background:var(--staff-inset); color:var(--staff-copy); padding:13px 12px; font-size:10px; font-weight:650; text-transform:uppercase; letter-spacing:.06em; }
    #personnelTable tbody td { padding:17px 12px; border:0; border-bottom:1px solid var(--staff-border); background:var(--staff-bg); color:var(--staff-copy); vertical-align:middle; font-size:12px; line-height:1.7; overflow-wrap:anywhere; }
    #personnelTable tbody tr:hover td { background:var(--staff-inset); }
    #personnelTable .staffs-name { display:flex; align-items:center; gap:12px; min-width:160px; color:var(--staff-title); font-weight:600; }
    #personnelTable .staffs-name-icon { display:grid; place-items:center; width:38px; height:38px; flex-shrink:0; border:1px solid var(--staff-border); border-radius:12px; background:var(--staff-soft); color:var(--staff-accent-text); }
    #personnelTable .staffs-actions { white-space:nowrap; text-align:right; }
    #personnelTable .staffs-actions .btn-group { display:inline-flex; align-items:center; gap:6px; }
    #personnelTable .staffs-actions form { display:inline-flex; margin:0; }
    #personnelTable .staffs-actions button, #personnelTable .staffs-actions a { display:inline-flex; align-items:center; justify-content:center; min-width:40px; min-height:40px; border-radius:9px; text-decoration:none; }
    #personnelTable .staffs-actions button:hover, #personnelTable .staffs-actions a:hover { background:var(--staff-soft); }
    #personnelTable .dataTables_empty { padding:48px 16px; text-align:center; }
    #personnelTable_wrapper .dataTables_length, #personnelTable_wrapper .dataTables_filter, #personnelTable_wrapper .dataTables_info, #personnelTable_wrapper .dataTables_paginate { float:none !important; margin:0; padding:0; color:var(--staff-copy); font-size:12px; }
    #personnelTable_wrapper label { display:flex; align-items:center; gap:8px; margin:0; font-weight:400; color:var(--staff-copy); }
    #personnelTable_wrapper input, #personnelTable_wrapper select { min-height:40px; margin:0; border:1px solid var(--staff-border); border-radius:9px; background:var(--staff-bg); color:var(--staff-title); padding:8px 10px; outline:none; }
    #personnelTable_wrapper .dataTables_filter input { width:250px; }
    #personnelTable_wrapper .pagination { display:flex; gap:4px; flex-wrap:wrap; margin:0; }
    #personnelTable_wrapper .page-link, #personnelTable_wrapper .paginate_button { border:1px solid var(--staff-border) !important; border-radius:8px !important; background:var(--staff-bg) !important; color:var(--staff-copy) !important; font-size:12px; }
    #personnelTable_wrapper li.paginate_button { padding:0 !important; border:0 !important; background:none !important; }
    #personnelTable_wrapper .active .page-link, #personnelTable_wrapper .paginate_button.current { border-color:#635bff !important; background:#635bff !important; color:#fff !important; }
    #personnelModal .modal-content { border:1px solid var(--staff-border); border-radius:20px; background:var(--staff-bg) !important; color:var(--staff-copy); overflow:hidden; }
    #personnelModal .modal-header { padding:23px 24px; border-color:var(--staff-border); background:linear-gradient(125deg,var(--staff-soft),var(--staff-bg) 75%); }
    #personnelModal .modal-title { color:var(--staff-title); margin:7px 0 0; font-size:22px; font-weight:650; letter-spacing:-.03em; }
    #personnelModal .modal-body { padding:24px; }
    #personnelModal .modal-footer { border-color:var(--staff-border); background:var(--staff-inset); padding:16px 24px; }
    #personnelModal[data-theme="dark"] .btn-close { filter:invert(1); }
    .direk-staff-scope .staffs-field { display:block; box-sizing:border-box; width:100%; min-height:44px; border:1px solid var(--staff-border); border-radius:10px; padding:10px 12px; background:var(--staff-bg); color:var(--staff-title); font-size:13px; line-height:1.6; outline:none; }
    .direk-staff-scope textarea.staffs-field { resize:vertical; }
    .direk-staff-scope .staffs-label { display:block; margin-bottom:7px; color:var(--staff-title); font-size:12px; font-weight:600; }
    .direk-staff-scope .staffs-field:focus, #personnelTable_wrapper input:focus { border-color:#635bff; box-shadow:0 0 0 3px rgba(99,91,255,.12); }
    .direk-staff-scope .staffs-error { margin:6px 0 0; color:#e55378; font-size:11px; line-height:1.6; }
    .direk-staff-scope .staffs-alert { margin-bottom:18px; padding:14px 16px; border:1px solid #f4b8c7; border-radius:12px; background:#fff1f4; color:#aa2b4d; font-size:12px; line-height:1.8; }
    .direk-staff-scope[data-theme="dark"] .staffs-alert { border-color:#663449; background:#352332; color:#ffb3c5; }
    .direk-staff-scope .staffs-alert ul { margin:6px 0 0; padding-left:18px; }
    @media(max-width:639px) { .direk-staff-scope .staffs-feature-icon { display:none; } .direk-staff-scope .staffs-card-header { padding:20px 16px; } .direk-staff-scope .staffs-table-wrap { padding:0 12px 12px; } #personnelTable_wrapper .dataTables_filter { width:100%; } #personnelTable_wrapper .dataTables_filter label { width:100%; } #personnelTable_wrapper .dataTables_filter input { min-width:0; width:0; flex:1; } .direk-staff-scope .staffs-details, .direk-staff-scope .staffs-permission-tools { padding:18px; } .direk-staff-scope .role-permission-toggle { padding:18px; } .direk-staff-scope .role-permission-panel { padding:0 12px 14px; } .direk-staff-scope .staffs-permission-search { width:100%; } }
    @media(prefers-reduced-motion:reduce) { .direk-staff-scope * { transition:none !important; } }

    #personnelModal .staff-modal-subtitle { margin:7px 0 0; color:var(--staff-copy); font-size:12px; line-height:1.7; }
    #personnelModal .staff-section-title { display:flex; align-items:center; gap:10px; margin-bottom:18px; }
    #personnelModal .staff-section-title h3 { margin:0; color:var(--staff-title); font-size:13px; font-weight:650; }
    #personnelModal .staff-section-divider { border-top:1px solid var(--staff-border); padding-top:22px; margin-top:22px; }
    #personnelTable .staff-action-edit, #personnelTable .staff-action-status { border:0; background:transparent; color:var(--staff-accent-text); }
    #direk-personnel .staff-checking-enabled { background:#e8f7f0; color:#287452; }
    #direk-personnel .staff-checking-disabled { background:var(--staff-inset); color:var(--staff-copy); }
    #direk-personnel[data-theme="dark"] .staff-checking-enabled { background:#203c35; color:#9fe0bb; }
    #direk-personnel #staffsLoadError { margin:18px 22px 0; }


    #direk-personnel .personnel-tools { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-end; gap:18px; padding:20px 24px; border-bottom:1px solid var(--staff-border); }
    #direk-personnel .personnel-filter { display:flex; flex-wrap:wrap; align-items:flex-end; gap:10px; margin:0; }
    #direk-personnel .personnel-filter > div { width:340px; max-width:100%; }
    #direk-personnel .personnel-search { width:250px; max-width:100%; }
    #direk-personnel .personnel-office-code { display:block; margin-top:3px; color:var(--staff-copy); font-size:10px; }
    #personnelTable { min-width:850px; }
    #personnelTable .personnel-delete { background:transparent; border:0; color:#e55378; }
    #direk-personnel .personnel-success { display:flex; gap:9px; padding:14px 16px; margin-bottom:18px; border:1px solid var(--staff-border); border-radius:12px; background:var(--staff-soft); color:var(--staff-accent-text); font-size:12px; }
    #personnelModal { display:flex; position:fixed; inset:0; z-index:1060; align-items:center; justify-content:center; padding:16px; }
    #personnelModal.hidden { display:none !important; }
    #personnelModalBackdrop { position:absolute; inset:0; background:rgba(15,20,40,.55); backdrop-filter:blur(5px); }
    #personnelModal .modal-content { position:relative; z-index:1; display:flex; flex-direction:column; width:100%; max-width:600px; max-height:calc(100vh - 32px); max-height:calc(100dvh - 32px); outline:none; }
    #personnelForm { display:flex; flex-direction:column; min-height:0; margin:0; }
    #personnelModal .modal-header, #personnelModal .modal-footer { flex-shrink:0; }
    #personnelModal .modal-body { min-height:0; overflow-y:auto; }
    #personnelModal .personnel-close { display:grid; width:40px; height:40px; place-items:center; border:1px solid var(--staff-border); border-radius:10px; background:var(--staff-bg); color:var(--staff-copy); }
    #personnelModal #personnelStaffId { width:100%; }
    @media(max-width:639px) { #direk-personnel .personnel-tools { padding:18px; } #direk-personnel .personnel-search, #direk-personnel .personnel-filter { width:100%; } }

</style>
@endpush

@extends('layouts.app')
@section('content')
@php
    $staffsDark = session('user_settings.class_theme', '') === 'dark';
    $staffUiConfig = [
        'baseUrl' => url('/administrator/staffs'),
        'dataUrl' => route('getstaffs'),
        'csrf' => csrf_token(),
        'hasErrors' => $errors->any(),
        'old' => [
            'name' => old('name', ''),
            'abbreviation' => old('abbreviation', ''),
            'head_name' => old('head_name', ''),
            'head_position' => old('head_position', ''),
            'head_email' => old('head_email', ''),
            'staffTitle' => old('staffTitle', 'Edit Staff'),
            'staffAction' => old('staffAction', ''),
        ],
    ];
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Staff Management'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-staffs" class="direk-staff-scope px-4 pb-8 pt-4" data-theme="{{ $staffsDark ? 'dark' : 'light' }}">
    <div class="staffs-shell">
        <header class="staffs-page-heading">
            <div><span class="staffs-eyebrow">WORKSPACE ADMINISTRATION</span><h2>Structure that connects.</h2><p>Your offices, their leaders, and the details that keep work moving.</p></div>
            <span class="staffs-feature-icon" aria-hidden="true"><i class="fa fa-building"></i></span>
        </header>
        <section class="staffs-card" aria-labelledby="staffsDirectoryTitle">
            <div class="staffs-card-header">
                <div><h3 id="staffsDirectoryTitle">Staff &amp; office directory <span id="staffCountBadge" class="staffs-badge" aria-live="polite">Loading…</span></h3><p>Review office details and update designated heads.</p></div>
                <button type="button" id="btnRefreshStaffs" class="staffs-button" aria-label="Refresh staff directory"><i class="fa fa-refresh" aria-hidden="true"></i>Refresh</button>
            </div>
            <div class="staffs-info"><i class="fa fa-info-circle" aria-hidden="true"></i><span>Checking eligibility controls staff checking access. Disabling it also disables review eligibility for the staff's divisions.</span></div>
            <div id="staffsLoadError" class="staffs-alert" role="alert" hidden></div>
            <div class="staffs-table-wrap">
                <table id="datatable-staffs" aria-label="DIREK staff and offices" style="width:100%">
                    <thead><tr><th scope="col">Staff / Office</th><th scope="col">Abbreviation</th><th scope="col">Designated head</th><th scope="col">Head position</th><th scope="col">Head email</th><th scope="col">Checking</th><th scope="col" class="staffs-actions">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="mt-6">@include('layouts.footers.auth.footer')</div>
</div>
<form method="POST" id="frmStaff" autocomplete="off">
    @csrf
    @method('put')
    <input type="hidden" name="staffTitle" id="staffTitle" value="{{ old('staffTitle') }}">
    <input type="hidden" name="staffAction" id="staffAction" value="{{ old('staffAction') }}">
    <input type="hidden" name="staffMethod" id="staffMethod" value="put">
    <div class="modal fade direk-staff-scope" id="staff-modal" data-theme="{{ $staffsDark ? 'dark' : 'light' }}" tabindex="-1" aria-labelledby="h5staffTitle" aria-describedby="staffModalSubtitle" aria-hidden="true" hidden>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div><span class="staffs-eyebrow">OFFICE DETAILS</span><h2 class="modal-title" id="h5staffTitle">Edit Staff</h2><p id="staffModalSubtitle" class="staff-modal-subtitle">Keep the office identity and leadership information up to date.</p></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())
                        <div id="staffValidationSummary" class="staffs-alert" role="alert"><strong>Please review these fields.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    <div id="staffActionError" class="staffs-alert" role="alert" hidden></div>
                    <div class="staff-section-title"><span class="staffs-badge">01</span><h3>Office identity</h3></div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="staffs-label">Staff / Office name</label>
                            <input name="name" id="name" type="text" class="staffs-field" minlength="2" maxlength="50" required value="{{ old('name') }}">
                            @error('name')<p class="staffs-error" data-staff-error>{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="abbreviation" class="staffs-label">Abbreviation</label>
                            <input name="abbreviation" id="abbreviation" type="text" class="staffs-field" minlength="2" maxlength="50" required value="{{ old('abbreviation') }}">
                            @error('abbreviation')<p class="staffs-error" data-staff-error>{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="staff-section-title staff-section-divider"><span class="staffs-badge">02</span><h3>Designated head</h3></div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="head_name" class="staffs-label">Head name</label>
                            <input name="head_name" id="head_name" type="text" class="staffs-field" minlength="2" maxlength="50" required value="{{ old('head_name') }}">
                            @error('head_name')<p class="staffs-error" data-staff-error>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="head_position" class="staffs-label">Head position</label>
                            <input name="head_position" id="head_position" type="text" class="staffs-field" minlength="2" maxlength="50" required value="{{ old('head_position') }}">
                            @error('head_position')<p class="staffs-error" data-staff-error>{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="head_email" class="staffs-label">Head email</label>
                            <input name="head_email" id="head_email" type="email" class="staffs-field" required value="{{ old('head_email') }}">
                            @error('head_email')<p class="staffs-error" data-staff-error>{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="staffs-button" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="staffs-button staffs-primary" id="btnSave"><i class="fa fa-check" aria-hidden="true"></i>Save Changes</button>
                    <button type="button" class="staffs-button staffs-primary" id="btnSaveDisabled" disabled hidden><span class="spinner-border spinner-border-sm" aria-hidden="true"></span>Saving…</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const config = {{ \Illuminate\Support\Js::from($staffUiConfig) }};
    const field = id => document.getElementById(id);
    const form = field('frmStaff');
    const modalElement = field('staff-modal');
    const editableFields = ['name','abbreviation','head_name','head_position','head_email'];
    let saving = false;
    function resetSave() {
        saving = false;
        field('btnSave').hidden = false;
        field('btnSave').disabled = false;
        field('btnSaveDisabled').hidden = true;
        form.removeAttribute('aria-busy');
    }
    function validAction(action) {
        try {
            const url = new URL(action, window.location.origin);
            const base = new URL(config.baseUrl, window.location.origin);
            const suffix = url.pathname.slice(base.pathname.length + 1);
            if (url.origin === base.origin && url.pathname.startsWith(base.pathname + '/') && /^\d+$/.test(suffix)) return url.href;
        } catch (error) {}
        return '';
    }
    function openModal() {
        modalElement.hidden = false;
        if (window.bootstrap?.Modal) window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
        else if (window.jQuery && typeof $.fn.modal === 'function') $('#staff-modal').modal('show');
        else showLoadError('The edit dialog could not initialize. Refresh this page to reload its scripts.');
    }
    window.showStaff = function (data, action) {
        if (!data) return;
        resetSave();
        field('staffActionError').hidden = true;
        field('staffTitle').value = 'Edit: ' + (data.abbreviation || data.name || 'Staff');
        field('h5staffTitle').textContent = field('staffTitle').value;
        field('staffMethod').value = 'put';
        form.querySelector('input[name="_method"]').value = 'put';
        const updateUrl = validAction(action);
        field('staffAction').value = updateUrl;
        if (updateUrl) form.action = updateUrl;
        else form.removeAttribute('action');
        editableFields.forEach(id => { field(id).value = data[id] == null ? '' : String(data[id]); });
        form.querySelectorAll('[data-staff-error], #staffValidationSummary').forEach(element => { element.hidden = true; });
        openModal();
    };
    form.addEventListener('submit', function (event) {
        if (saving) { event.preventDefault(); return; }
        const updateUrl = validAction(field('staffAction').value);
        if (!updateUrl) {
            event.preventDefault();
            field('staffActionError').textContent = 'The update link is unavailable. Close this dialog and reopen the staff record from the directory.';
            field('staffActionError').hidden = false;
            return;
        }
        if (!form.reportValidity()) { event.preventDefault(); return; }
        form.action = updateUrl;
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
        field('staffsLoadError').textContent = message;
        field('staffsLoadError').hidden = false;
        field('staffCountBadge').textContent = 'Unavailable';
    }
    function plainText(value) {
        return new DOMParser().parseFromString(String(value ?? ''), 'text/html').body.textContent || '';
    }
    function escapeHtml(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
    function renderText(data, type) {
        const text = plainText(data);
        return type === 'display' ? escapeHtml(text || '—') : text;
    }
    if (window.jQuery && $.fn.DataTable) {
        $('#datatable-staffs').on('xhr.dt', function (event, settings, response, xhr) {
            if (response && !response.error) { field('staffsLoadError').hidden = true; return; }
            showLoadError(xhr?.status === 419 ? 'Your session has expired. Refresh this page and sign in again.' : 'The staff directory could not be loaded. Use Refresh to try again.');
            return true;
        });
        const table = $('#datatable-staffs').DataTable({
            ajax:{url:config.dataUrl, type:'POST', headers:{'X-CSRF-TOKEN':config.csrf}},
            stateSave:true, searchDelay:500, serverSide:true, processing:true, autoWidth:false,
            dom:'<"staffs-table-controls"lf><"staffs-table-scroll"t><"staffs-table-bottom"ip>',
            responsive:Boolean($.fn.dataTable.Responsive),
            columns:[
                {data:'name', name:'name', responsivePriority:1, render:function (data, type) {
                    const name = plainText(data);
                    if (type !== 'display') return name;
                    return '<div class="staffs-name"><span class="staffs-name-icon" aria-hidden="true"><i class="fa fa-building"></i></span><span>' + escapeHtml(name || 'Unnamed staff') + '</span></div>';
                }},
                {data:'abbreviation', name:'abbreviation', defaultContent:'—', render:renderText},
                {data:'head_name', name:'head_name', defaultContent:'—', responsivePriority:3, render:renderText},
                {data:'head_position', name:'head_position', defaultContent:'—', render:renderText},
                {data:'head_email', name:'head_email', defaultContent:'—', render:renderText},
                {data:'can_check', name:'can_check', responsivePriority:4, render:function (data, type) {
                    const enabled = Number(data) === 1;
                    if (type !== 'display') return enabled ? 1 : 0;
                    return '<span class="staffs-badge staff-checking-' + (enabled ? 'enabled' : 'disabled') + '">' + (enabled ? 'Enabled' : 'Disabled') + '</span>';
                }},
                {data:'actions', orderable:false, searchable:false, className:'staffs-actions', responsivePriority:2}
            ],
            order:[[0,'asc']], lengthMenu:[[10,25,50,-1],[10,25,50,'All']],
            language:{search:'Search',searchPlaceholder:'Office or head name',lengthMenu:'Show _MENU_ offices',emptyTable:'No staff or offices available.',zeroRecords:'No matching offices found.',info:'Showing _START_–_END_ of _TOTAL_ offices',infoEmpty:'No offices to show'},
            drawCallback:function () {
                const info = this.api().page.info();
                field('staffCountBadge').textContent = info.recordsDisplay + (info.recordsDisplay === 1 ? ' office' : ' offices');
            }
        });
        field('btnRefreshStaffs').addEventListener('click', () => table.ajax.reload(null, false));
    } else {
        showLoadError('The directory could not initialize. Refresh this page to reload its scripts.');
        field('btnRefreshStaffs').addEventListener('click', () => window.location.reload());
    }
    if (config.hasErrors) {
        const old = config.old;
        resetSave();
        const action = validAction(old.staffAction);
        field('staffAction').value = action;
        if (action) form.action = action;
        field('h5staffTitle').textContent = old.staffTitle || 'Edit Staff';
        editableFields.forEach(id => { field(id).value = old[id] == null ? '' : String(old[id]); });
        openModal();
    }
});
</script>
@endpush

@push('css')
<style>
    #direk-staffs, #direk-staff-editor, #staff-modal { --staff-bg:#fff; --staff-inset:#f6f7fb; --staff-border:#e6e9f0; --staff-title:#172033; --staff-copy:#637086; --staff-accent:#635bff; --staff-accent-text:#5850df; --staff-soft:#eeedff; color:var(--staff-copy); }
    #direk-staffs[data-theme="dark"], #direk-staff-editor[data-theme="dark"], #staff-modal[data-theme="dark"] { --staff-bg:#192130; --staff-inset:#151c29; --staff-border:#2c3546; --staff-title:#eef0f8; --staff-copy:#95a0b5; --staff-accent-text:#a19cff; --staff-soft:#2b2850; }
    #direk-staffs [hidden], #direk-staff-editor [hidden], #staff-modal [hidden] { display:none !important; }
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
    #datatable-staffs { width:100% !important; margin:0; border-collapse:separate !important; border-spacing:0; }
    #datatable-staffs thead th { position:static; border:0; border-bottom:1px solid var(--staff-border); background:var(--staff-inset); color:var(--staff-copy); padding:13px 12px; font-size:10px; font-weight:650; text-transform:uppercase; letter-spacing:.06em; }
    #datatable-staffs tbody td { padding:17px 12px; border:0; border-bottom:1px solid var(--staff-border); background:var(--staff-bg); color:var(--staff-copy); vertical-align:middle; font-size:12px; line-height:1.7; overflow-wrap:anywhere; }
    #datatable-staffs tbody tr:hover td { background:var(--staff-inset); }
    #datatable-staffs .staffs-name { display:flex; align-items:center; gap:12px; min-width:160px; color:var(--staff-title); font-weight:600; }
    #datatable-staffs .staffs-name-icon { display:grid; place-items:center; width:38px; height:38px; flex-shrink:0; border:1px solid var(--staff-border); border-radius:12px; background:var(--staff-soft); color:var(--staff-accent-text); }
    #datatable-staffs .staffs-actions { white-space:nowrap; text-align:right; }
    #datatable-staffs .staffs-actions .btn-group { display:inline-flex; align-items:center; gap:6px; }
    #datatable-staffs .staffs-actions form { display:inline-flex; margin:0; }
    #datatable-staffs .staffs-actions button, #datatable-staffs .staffs-actions a { display:inline-flex; align-items:center; justify-content:center; min-width:40px; min-height:40px; border-radius:9px; text-decoration:none; }
    #datatable-staffs .staffs-actions button:hover, #datatable-staffs .staffs-actions a:hover { background:var(--staff-soft); }
    #datatable-staffs .dataTables_empty { padding:48px 16px; text-align:center; }
    #datatable-staffs_wrapper .dataTables_length, #datatable-staffs_wrapper .dataTables_filter, #datatable-staffs_wrapper .dataTables_info, #datatable-staffs_wrapper .dataTables_paginate { float:none !important; margin:0; padding:0; color:var(--staff-copy); font-size:12px; }
    #datatable-staffs_wrapper label { display:flex; align-items:center; gap:8px; margin:0; font-weight:400; color:var(--staff-copy); }
    #datatable-staffs_wrapper input, #datatable-staffs_wrapper select { min-height:40px; margin:0; border:1px solid var(--staff-border); border-radius:9px; background:var(--staff-bg); color:var(--staff-title); padding:8px 10px; outline:none; }
    #datatable-staffs_wrapper .dataTables_filter input { width:250px; }
    #datatable-staffs_wrapper .pagination { display:flex; gap:4px; flex-wrap:wrap; margin:0; }
    #datatable-staffs_wrapper .page-link, #datatable-staffs_wrapper .paginate_button { border:1px solid var(--staff-border) !important; border-radius:8px !important; background:var(--staff-bg) !important; color:var(--staff-copy) !important; font-size:12px; }
    #datatable-staffs_wrapper li.paginate_button { padding:0 !important; border:0 !important; background:none !important; }
    #datatable-staffs_wrapper .active .page-link, #datatable-staffs_wrapper .paginate_button.current { border-color:#635bff !important; background:#635bff !important; color:#fff !important; }
    #staff-modal .modal-content { border:1px solid var(--staff-border); border-radius:20px; background:var(--staff-bg) !important; color:var(--staff-copy); overflow:hidden; }
    #staff-modal .modal-header { padding:23px 24px; border-color:var(--staff-border); background:linear-gradient(125deg,var(--staff-soft),var(--staff-bg) 75%); }
    #staff-modal .modal-title { color:var(--staff-title); margin:7px 0 0; font-size:22px; font-weight:650; letter-spacing:-.03em; }
    #staff-modal .modal-body { padding:24px; }
    #staff-modal .modal-footer { border-color:var(--staff-border); background:var(--staff-inset); padding:16px 24px; }
    #staff-modal[data-theme="dark"] .btn-close { filter:invert(1); }
    .direk-staff-scope .staffs-field { display:block; box-sizing:border-box; width:100%; min-height:44px; border:1px solid var(--staff-border); border-radius:10px; padding:10px 12px; background:var(--staff-bg); color:var(--staff-title); font-size:13px; line-height:1.6; outline:none; }
    .direk-staff-scope textarea.staffs-field { resize:vertical; }
    .direk-staff-scope .staffs-label { display:block; margin-bottom:7px; color:var(--staff-title); font-size:12px; font-weight:600; }
    .direk-staff-scope .staffs-field:focus, #datatable-staffs_wrapper input:focus { border-color:#635bff; box-shadow:0 0 0 3px rgba(99,91,255,.12); }
    .direk-staff-scope .staffs-error { margin:6px 0 0; color:#e55378; font-size:11px; line-height:1.6; }
    .direk-staff-scope .staffs-alert { margin-bottom:18px; padding:14px 16px; border:1px solid #f4b8c7; border-radius:12px; background:#fff1f4; color:#aa2b4d; font-size:12px; line-height:1.8; }
    .direk-staff-scope[data-theme="dark"] .staffs-alert { border-color:#663449; background:#352332; color:#ffb3c5; }
    .direk-staff-scope .staffs-alert ul { margin:6px 0 0; padding-left:18px; }
    @media(max-width:639px) { .direk-staff-scope .staffs-feature-icon { display:none; } .direk-staff-scope .staffs-card-header { padding:20px 16px; } .direk-staff-scope .staffs-table-wrap { padding:0 12px 12px; } #datatable-staffs_wrapper .dataTables_filter { width:100%; } #datatable-staffs_wrapper .dataTables_filter label { width:100%; } #datatable-staffs_wrapper .dataTables_filter input { min-width:0; width:0; flex:1; } .direk-staff-scope .staffs-details, .direk-staff-scope .staffs-permission-tools { padding:18px; } .direk-staff-scope .role-permission-toggle { padding:18px; } .direk-staff-scope .role-permission-panel { padding:0 12px 14px; } .direk-staff-scope .staffs-permission-search { width:100%; } }
    @media(prefers-reduced-motion:reduce) { .direk-staff-scope * { transition:none !important; } }

    #staff-modal .staff-modal-subtitle { margin:7px 0 0; color:var(--staff-copy); font-size:12px; line-height:1.7; }
    #staff-modal .staff-section-title { display:flex; align-items:center; gap:10px; margin-bottom:18px; }
    #staff-modal .staff-section-title h3 { margin:0; color:var(--staff-title); font-size:13px; font-weight:650; }
    #staff-modal .staff-section-divider { border-top:1px solid var(--staff-border); padding-top:22px; margin-top:22px; }
    #datatable-staffs .staff-action-edit, #datatable-staffs .staff-action-status { border:0; background:transparent; color:var(--staff-accent-text); }
    #direk-staffs .staff-checking-enabled { background:#e8f7f0; color:#287452; }
    #direk-staffs .staff-checking-disabled { background:var(--staff-inset); color:var(--staff-copy); }
    #direk-staffs[data-theme="dark"] .staff-checking-enabled { background:#203c35; color:#9fe0bb; }
    #direk-staffs #staffsLoadError { margin:18px 22px 0; }

</style>
@endpush

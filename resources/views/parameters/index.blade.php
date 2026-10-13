@extends('layouts.app')
@section('content')
@php
    $parametersDark = session('user_settings.class_theme', '') === 'dark';
    $parameterUiConfig = [
        'baseUrl' => url('/administrator/parameters'),
        'dataUrl' => route('getparameters'),
        'csrf' => csrf_token(),
        'hasErrors' => $errors->any(),
        'old' => [
            'name' => old('name', ''), 'title' => old('title', ''),
            'type' => old('type', ''), 'category' => old('category', ''),
            'description' => old('description', ''), 'value' => old('value', ''),
            'start_date' => old('start_date', ''), 'end_date' => old('end_date', ''),
            'with_duration' => old('parameterWithDuration', 'N'),
            'modalTitle' => old('parameterTitle', 'Edit Parameter'),
            'action' => old('parameterAction', ''),
        ],
    ];
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'System Parameters'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-parameters" class="direk-parameter-scope px-4 pb-8 pt-4" data-theme="{{ $parametersDark ? 'dark' : 'light' }}">
    <div class="parameters-shell">
        <header class="parameters-page-heading">
            <div><span class="parameters-eyebrow">WORKSPACE ADMINISTRATION</span><h2>Fine-tune your workspace.</h2><p>Manage the configuration and messages used across DIREK.</p></div>
            <span class="parameters-feature-icon" aria-hidden="true"><i class="fa fa-sliders"></i></span>
        </header>
        <section class="parameters-card" aria-labelledby="parametersDirectoryTitle">
            <div class="parameters-card-header">
                <div><h3 id="parametersDirectoryTitle">System configuration <span id="parameterCountBadge" class="parameters-badge" aria-live="polite">Loading…</span></h3><p>Review settings and open a parameter to update its value.</p></div>
                <button type="button" id="btnRefreshParameters" class="parameters-button" aria-label="Refresh parameter directory"><i class="fa fa-refresh" aria-hidden="true"></i>Refresh</button>
            </div>
            <div id="parametersLoadError" class="parameters-alert" role="alert" hidden></div>
            <div class="parameters-table-wrap">
                <table id="datatable-parameters" aria-label="System parameters" style="width:100%">
                    <thead><tr><th scope="col">Parameter</th><th scope="col">Description</th><th scope="col">Category</th><th scope="col">Updated by</th><th scope="col">Updated date</th><th scope="col" class="parameters-actions">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="mt-6">@include('layouts.footers.auth.footer')</div>
</div>
<form method="POST" id="frmParameter" autocomplete="off">
    @csrf
    @method('put')
    <input type="hidden" name="parameterTitle" id="parameterTitle" value="{{ old('parameterTitle') }}">
    <input type="hidden" name="parameterAction" id="parameterAction" value="{{ old('parameterAction') }}">
    <input type="hidden" name="parameterMethod" id="parameterMethod" value="put">
    <input type="hidden" name="parameterWithDuration" id="parameterWithDuration" value="{{ old('parameterWithDuration') }}">
    <div class="modal fade direk-parameter-scope" id="parameter-modal" data-theme="{{ $parametersDark ? 'dark' : 'light' }}" tabindex="-1" aria-labelledby="h5parameterTitle" aria-hidden="true" hidden>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div><span class="parameters-eyebrow">SYSTEM CONFIGURATION</span><h2 class="modal-title" id="h5parameterTitle">Edit Parameter</h2></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())<div id="parameterValidationSummary" class="parameters-alert" role="alert"><strong>Please review these fields.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                    <div id="parameterActionError" class="parameters-alert" role="alert" hidden></div>
                    <div class="parameter-editor-grid">
                        <section class="parameter-details" aria-labelledby="parameterDetailsTitle">
                            <h3 id="parameterDetailsTitle">Parameter details</h3>
                            <label for="name" class="parameters-label">Name</label><input name="name" id="name" type="text" class="parameters-field" minlength="2" maxlength="255" required value="{{ old('name') }}">
                            @error('name')<p class="parameters-error" data-parameter-error>{{ $message }}</p>@enderror
                            <div class="mt-4"><label for="title" class="parameters-label">Title</label><input name="title" id="title" type="text" class="parameters-field" minlength="2" maxlength="255" required value="{{ old('title') }}">@error('title')<p class="parameters-error" data-parameter-error>{{ $message }}</p>@enderror</div>
                            <div class="mt-4"><label for="type" class="parameters-label">Value type</label><select name="type" id="type" class="parameters-field" required><option value="">Select type</option><option value="html">HTML</option><option value="string">Text</option><option value="time">Time</option><option value="integer">Integer</option><option value="boolean">Boolean (yes, no)</option></select>@error('type')<p class="parameters-error" data-parameter-error>{{ $message }}</p>@enderror</div>
                            <div class="mt-4"><label for="category" class="parameters-label">Category</label><input name="category" id="category" type="text" class="parameters-field" readonly value="{{ old('category') }}">@error('category')<p class="parameters-error" data-parameter-error>{{ $message }}</p>@enderror</div>
                            <div class="mt-4"><label for="description" class="parameters-label">Description</label><textarea name="description" id="description" rows="3" class="parameters-field" minlength="2" maxlength="10000">{{ old('description') }}</textarea>@error('description')<p class="parameters-error" data-parameter-error>{{ $message }}</p>@enderror</div>
                        </section>
                        <section class="parameter-value-area" aria-labelledby="parameterValueTitle">
                            <div class="parameter-value-heading"><h3 id="parameterValueTitle">Value editor</h3><span id="parameterTypeBadge" class="parameters-badge">Text</span></div>
                            <p id="parameterValueHint" class="parameter-value-hint">Enter the value used by this parameter.</p>
                            <label for="value" class="sr-only">Parameter value</label>
                            <div id="divQuill" hidden><div id="quill_value"></div></div>
                            <div id="divValue"><textarea name="value" id="value" rows="12" class="parameters-field" spellcheck="false">{{ old('value') }}</textarea></div>
                            <p id="parameterEditorNote" class="parameters-error" role="status" hidden>Rich-text editing is unavailable. You can edit the HTML source in the value field.</p>
                            @error('value')<p class="parameters-error" data-parameter-error>{{ $message }}</p>@enderror
                            <div id="divDuration" class="parameter-duration" hidden>
                                <h3>Active period</h3><p class="parameter-value-hint">Set when this parameter applies.</p>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div><label for="start_date" class="parameters-label">Start date</label><input name="start_date" id="start_date" type="datetime-local" step="1" class="parameters-field" value="{{ old('start_date') }}">@error('start_date')<p class="parameters-error" data-parameter-error>{{ $message }}</p>@enderror</div>
                                    <div><label for="end_date" class="parameters-label">End date</label><input name="end_date" id="end_date" type="datetime-local" step="1" class="parameters-field" value="{{ old('end_date') }}">@error('end_date')<p class="parameters-error" data-parameter-error>{{ $message }}</p>@enderror</div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="parameters-button" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="parameters-button parameters-primary" id="btnSave"><i class="fa fa-check" aria-hidden="true"></i>Save Changes</button>
                    <button type="button" class="parameters-button parameters-primary" id="btnSaveDisabled" disabled hidden><span class="spinner-border spinner-border-sm" aria-hidden="true"></span>Saving…</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const config = {{ \Illuminate\Support\Js::from($parameterUiConfig) }};
    const field = id => document.getElementById(id);
    const form = field('frmParameter');
    const modalElement = field('parameter-modal');
    let saving = false;
    let editor = null;
    let activeType = '';
    function ensureEditor() {
        if (editor) return editor;
        try {
            if (typeof initQuillJs === 'function') initQuillJs('quill_value');
            if (typeof quills !== 'undefined') editor = quills['quill_value'] || null;
        } catch (error) { editor = null; }
        return editor;
    }
    function copyEditorValue() {
        if (activeType === 'html' && editor) field('value').value = editor.root.innerHTML;
    }
    function loadValue() {
        const type = field('type').value;
        const richText = type === 'html' && ensureEditor();
        field('divQuill').hidden = !richText;
        field('divValue').hidden = Boolean(richText);
        field('parameterEditorNote').hidden = type !== 'html' || Boolean(richText);
        if (richText) {
            editor.setText('');
            editor.clipboard.dangerouslyPasteHTML(field('value').value || '');
        }
        const labels = {html:'HTML',string:'Text',time:'Time',integer:'Integer',boolean:'Boolean'};
        const hints = {html:'Format the content that will be displayed by this parameter.',string:'Enter the text used by this parameter.',time:'Use 24-hour time in HH:MM format, for example 08:30.',integer:'Enter an integer value.',boolean:'Enter yes or no.'};
        field('parameterTypeBadge').textContent = labels[type] || 'Value';
        field('parameterValueHint').textContent = hints[type] || 'Enter the value used by this parameter.';
        activeType = type;
        const duration = field('parameterWithDuration').value === 'Y';
        field('divDuration').hidden = !duration;
        ['start_date','end_date'].forEach(id => { field(id).required = duration; field(id).disabled = !duration; });
        updateDateOrder();
    }
    function updateDateOrder() {
        const start = field('start_date'), end = field('end_date');
        end.min = start.value || '';
        end.setCustomValidity(!end.disabled && start.value && end.value && end.value <= start.value ? 'The end date must be later than the start date.' : '');
    }
    function dateInputValue(value) {
        return value ? String(value).replace(' ', 'T').slice(0, 19) : '';
    }
    function validAction(action) {
        try {
            const url = new URL(action, window.location.origin), base = new URL(config.baseUrl, window.location.origin);
            if (url.origin === base.origin && url.pathname.startsWith(base.pathname + '/') && /^\d+$/.test(url.pathname.slice(base.pathname.length + 1))) return url.href;
        } catch (error) {}
        return '';
    }
    function resetSave() {
        saving = false;
        field('btnSave').hidden = false;
        field('btnSave').disabled = false;
        field('btnSaveDisabled').hidden = true;
        form.removeAttribute('aria-busy');
    }
    function openModal() {
        modalElement.hidden = false;
        if (window.bootstrap?.Modal) window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
        else if (window.jQuery && typeof $.fn.modal === 'function') $('#parameter-modal').modal('show');
        else showLoadError('The parameter dialog could not initialize. Refresh this page to reload its scripts.');
    }
    function populate(data, action, title) {
        resetSave();
        activeType = '';
        field('parameterActionError').hidden = true;
        field('parameterTitle').value = title || 'Edit Parameter: ' + (data.name || '');
        field('h5parameterTitle').textContent = field('parameterTitle').value;
        field('parameterMethod').value = 'put';
        form.querySelector('input[name="_method"]').value = 'put';
        const url = validAction(action);
        field('parameterAction').value = url;
        if (url) form.action = url; else form.removeAttribute('action');
        ['name','title','type','category','description','value'].forEach(id => { field(id).value = data[id] == null ? '' : String(data[id]); });
        field('parameterWithDuration').value = data.with_duration || 'N';
        field('start_date').value = dateInputValue(data.start_date);
        field('end_date').value = dateInputValue(data.end_date);
        loadValue();
    }
    window.showParameter = function (data, action) {
        if (!data) return;
        populate(data, action);
        form.querySelectorAll('[data-parameter-error], #parameterValidationSummary').forEach(element => { element.hidden = true; });
        openModal();
    };
    field('type').addEventListener('change', () => { copyEditorValue(); loadValue(); });
    ['start_date','end_date'].forEach(id => field(id).addEventListener('input', updateDateOrder));
    window.loadValue = loadValue;
    window.ocSubmit = () => form.requestSubmit();
    form.addEventListener('submit', event => {
        if (saving) { event.preventDefault(); return; }
        copyEditorValue();
        updateDateOrder();
        const action = validAction(field('parameterAction').value);
        if (!action) {
            event.preventDefault();
            field('parameterActionError').textContent = 'The update link is unavailable. Close this dialog and reopen the parameter from the directory.';
            field('parameterActionError').hidden = false;
            return;
        }
        if (!form.reportValidity()) { event.preventDefault(); return; }
        form.action = action;
        saving = true;
        field('btnSave').disabled = true;
        field('btnSave').hidden = true;
        field('btnSaveDisabled').hidden = false;
        form.setAttribute('aria-busy', 'true');
    });
    modalElement.addEventListener('hidden.bs.modal', resetSave);
    window.addEventListener('pageshow', resetSave);
    function showLoadError(message) {
        field('parametersLoadError').textContent = message;
        field('parametersLoadError').hidden = false;
        field('parameterCountBadge').textContent = 'Unavailable';
    }
    function plainText(value) { return new DOMParser().parseFromString(String(value ?? ''), 'text/html').body.textContent || ''; }
    function escapeHtml(value) { return String(value ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
    function renderText(data, type) { const text = plainText(data); return type === 'display' ? escapeHtml(text || '—') : text; }
    if (window.jQuery && $.fn.DataTable) {
        $('#datatable-parameters').on('xhr.dt', function (event, settings, response, xhr) {
            if (response && !response.error) { field('parametersLoadError').hidden = true; return; }
            showLoadError(xhr?.status === 419 ? 'Your session has expired. Refresh this page and sign in again.' : 'The parameter directory could not be loaded. Use Refresh to try again.');
            return true;
        });
        const table = $('#datatable-parameters').DataTable({
            ajax:{url:config.dataUrl,type:'POST',headers:{'X-CSRF-TOKEN':config.csrf}},
            stateSave:true,searchDelay:500,serverSide:true,processing:true,autoWidth:false,
            dom:'<"parameters-table-controls"lf><"parameters-table-scroll"t><"parameters-table-bottom"ip>',
            responsive:Boolean($.fn.dataTable.Responsive),
            columns:[
                {data:'name',name:'name',responsivePriority:1,render:function(data,type){const text=plainText(data);if(type!=='display')return text;return '<div class="parameters-name"><span class="parameters-name-icon" aria-hidden="true"><i class="fa fa-sliders"></i></span><span>'+escapeHtml(text)+'</span></div>';}},
                {data:'description',name:'description',defaultContent:'—',render:renderText},
                {data:'category',name:'category',defaultContent:'—',responsivePriority:3,render:renderText},
                {data:'editor',name:'editor',defaultContent:'—',orderable:false,render:renderText},
                {data:'updated_at',name:'updated_at',defaultContent:'—',render:renderText},
                {data:'actions',orderable:false,searchable:false,className:'parameters-actions',responsivePriority:2}
            ],
            order:[[0,'desc']],lengthMenu:[[10,25,50,-1],[10,25,50,'All']],
            language:{search:'Search',searchPlaceholder:'Parameter or category',lengthMenu:'Show _MENU_ parameters',emptyTable:'No parameters available.',zeroRecords:'No matching parameters found.',info:'Showing _START_–_END_ of _TOTAL_ parameters',infoEmpty:'No parameters to show'},
            drawCallback:function(){const info=this.api().page.info();field('parameterCountBadge').textContent=info.recordsDisplay+(info.recordsDisplay===1?' parameter':' parameters');}
        });
        field('btnRefreshParameters').addEventListener('click',()=>table.ajax.reload(null,false));
    } else {
        showLoadError('The directory could not initialize. Refresh this page to reload its scripts.');
        field('btnRefreshParameters').addEventListener('click',()=>window.location.reload());
    }
    if (config.hasErrors) { populate(config.old,config.old.action,config.old.modalTitle); openModal(); }
});
</script>
@endpush

@push('css')
<style>

    #direk-parameters, #direk-parameter-editor, #parameter-modal { --parameter-bg:#fff; --parameter-inset:#f6f7fb; --parameter-border:#e6e9f0; --parameter-title:#172033; --parameter-copy:#637086; --parameter-accent:#635bff; --parameter-accent-text:#5850df; --parameter-soft:#eeedff; color:var(--parameter-copy); }
    #direk-parameters[data-theme="dark"], #direk-parameter-editor[data-theme="dark"], #parameter-modal[data-theme="dark"] { --parameter-bg:#192130; --parameter-inset:#151c29; --parameter-border:#2c3546; --parameter-title:#eef0f8; --parameter-copy:#95a0b5; --parameter-accent-text:#a19cff; --parameter-soft:#2b2850; }
    #direk-parameters [hidden], #direk-parameter-editor [hidden], #parameter-modal [hidden] { display:none !important; }
    .direk-parameter-scope .parameters-shell { max-width:1600px; margin:0 auto; }
    .direk-parameter-scope .parameters-eyebrow { display:block; color:var(--parameter-accent-text); font-size:10px; font-weight:700; letter-spacing:.14em; }
    .direk-parameter-scope .parameters-page-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; margin:8px 0 26px; }
    .direk-parameter-scope .parameters-page-heading h2 { margin:9px 0 8px; color:var(--parameter-title); font-size:clamp(27px,3vw,36px); font-weight:700; line-height:1.2; letter-spacing:-.04em; }
    .direk-parameter-scope .parameters-page-heading p { margin:0; color:var(--parameter-copy); font-size:13px; line-height:1.7; }
    .direk-parameter-scope .parameters-feature-icon { display:grid; place-items:center; width:60px; height:60px; flex-shrink:0; border:1px solid var(--parameter-border); border-radius:20px; background:var(--parameter-bg); color:var(--parameter-accent-text); font-size:23px; }
    .direk-parameter-scope .parameters-card { border:1px solid var(--parameter-border); border-radius:20px; background:var(--parameter-bg); overflow:hidden; box-shadow:0 5px 24px rgba(20,25,60,.035); }
    .direk-parameter-scope .parameters-card-header { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; padding:23px 24px; border-bottom:1px solid var(--parameter-border); }
    .direk-parameter-scope .parameters-card-header h3 { margin:0; color:var(--parameter-title); font-size:16px; font-weight:650; letter-spacing:-.02em; }
    .direk-parameter-scope .parameters-card-header p { margin:5px 0 0; color:var(--parameter-copy); font-size:12px; line-height:1.6; }
    .direk-parameter-scope .parameters-badge { display:inline-flex; align-items:center; border-radius:7px; background:var(--parameter-soft); color:var(--parameter-accent-text); padding:5px 9px; font-size:10px; font-weight:650; }
    .direk-parameter-scope .parameters-button { display:inline-flex; min-height:44px; align-items:center; justify-content:center; gap:8px; border:1px solid var(--parameter-border); border-radius:10px; background:var(--parameter-bg); padding:10px 15px; color:var(--parameter-title); font-size:12px; font-weight:600; text-decoration:none; margin:0; }
    .direk-parameter-scope .parameters-primary { border-color:#635bff; background:#635bff; color:#fff; box-shadow:0 3px 8px rgba(99,91,255,.15); }
    .direk-parameter-scope .parameters-primary:hover { background:#534be5; color:#fff; }
    .direk-parameter-scope .parameters-button:disabled { opacity:.6; cursor:wait; }
    .direk-parameter-scope button:focus-visible, .direk-parameter-scope a:focus-visible { outline:2px solid var(--parameter-accent-text); outline-offset:3px; }
    .direk-parameter-scope .parameters-info { display:flex; align-items:flex-start; gap:9px; padding:13px 24px; background:var(--parameter-inset); color:var(--parameter-copy); font-size:12px; line-height:1.7; }
    .direk-parameter-scope .parameters-table-wrap { padding:0 22px 18px; }
    .direk-parameter-scope .parameters-table-controls, .direk-parameter-scope .parameters-table-bottom { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; padding:18px 2px; }
    .direk-parameter-scope .parameters-table-bottom { padding-bottom:0; }
    .direk-parameter-scope .parameters-table-scroll { overflow-x:auto; }
    #datatable-parameters { width:100% !important; margin:0; border-collapse:separate !important; border-spacing:0; }
    #datatable-parameters thead th { position:static; border:0; border-bottom:1px solid var(--parameter-border); background:var(--parameter-inset); color:var(--parameter-copy); padding:13px 12px; font-size:10px; font-weight:650; text-transform:uppercase; letter-spacing:.06em; }
    #datatable-parameters tbody td { padding:17px 12px; border:0; border-bottom:1px solid var(--parameter-border); background:var(--parameter-bg); color:var(--parameter-copy); vertical-align:middle; font-size:12px; line-height:1.7; overflow-wrap:anywhere; }
    #datatable-parameters tbody tr:hover td { background:var(--parameter-inset); }
    #datatable-parameters .parameters-name { display:flex; align-items:center; gap:12px; min-width:160px; color:var(--parameter-title); font-weight:600; }
    #datatable-parameters .parameters-name-icon { display:grid; place-items:center; width:38px; height:38px; flex-shrink:0; border:1px solid var(--parameter-border); border-radius:12px; background:var(--parameter-soft); color:var(--parameter-accent-text); }
    #datatable-parameters .parameters-actions { white-space:nowrap; text-align:right; }
    #datatable-parameters .parameters-actions .btn-group { display:inline-flex; align-items:center; gap:6px; }
    #datatable-parameters .parameters-actions form { display:inline-flex; margin:0; }
    #datatable-parameters .parameters-actions button, #datatable-parameters .parameters-actions a { display:inline-flex; align-items:center; justify-content:center; min-width:40px; min-height:40px; border-radius:9px; text-decoration:none; }
    #datatable-parameters .parameters-actions button:hover, #datatable-parameters .parameters-actions a:hover { background:var(--parameter-soft); }
    #datatable-parameters .dataTables_empty { padding:48px 16px; text-align:center; }
    #datatable-parameters_wrapper .dataTables_length, #datatable-parameters_wrapper .dataTables_filter, #datatable-parameters_wrapper .dataTables_info, #datatable-parameters_wrapper .dataTables_paginate { float:none !important; margin:0; padding:0; color:var(--parameter-copy); font-size:12px; }
    #datatable-parameters_wrapper label { display:flex; align-items:center; gap:8px; margin:0; font-weight:400; color:var(--parameter-copy); }
    #datatable-parameters_wrapper input, #datatable-parameters_wrapper select { min-height:40px; margin:0; border:1px solid var(--parameter-border); border-radius:9px; background:var(--parameter-bg); color:var(--parameter-title); padding:8px 10px; outline:none; }
    #datatable-parameters_wrapper .dataTables_filter input { width:250px; }
    #datatable-parameters_wrapper .pagination { display:flex; gap:4px; flex-wrap:wrap; margin:0; }
    #datatable-parameters_wrapper .page-link, #datatable-parameters_wrapper .paginate_button { border:1px solid var(--parameter-border) !important; border-radius:8px !important; background:var(--parameter-bg) !important; color:var(--parameter-copy) !important; font-size:12px; }
    #datatable-parameters_wrapper li.paginate_button { padding:0 !important; border:0 !important; background:none !important; }
    #datatable-parameters_wrapper .active .page-link, #datatable-parameters_wrapper .paginate_button.current { border-color:#635bff !important; background:#635bff !important; color:#fff !important; }
    #parameter-modal .modal-content { border:1px solid var(--parameter-border); border-radius:20px; background:var(--parameter-bg) !important; color:var(--parameter-copy); overflow:hidden; }
    #parameter-modal .modal-header { padding:23px 24px; border-color:var(--parameter-border); background:linear-gradient(125deg,var(--parameter-soft),var(--parameter-bg) 75%); }
    #parameter-modal .modal-title { color:var(--parameter-title); margin:7px 0 0; font-size:22px; font-weight:650; letter-spacing:-.03em; }
    #parameter-modal .modal-body { padding:24px; }
    #parameter-modal .modal-footer { border-color:var(--parameter-border); background:var(--parameter-inset); padding:16px 24px; }
    #parameter-modal[data-theme="dark"] .btn-close { filter:invert(1); }
    .direk-parameter-scope .parameters-field { display:block; box-sizing:border-box; width:100%; min-height:44px; border:1px solid var(--parameter-border); border-radius:10px; padding:10px 12px; background:var(--parameter-bg); color:var(--parameter-title); font-size:13px; line-height:1.6; outline:none; }
    .direk-parameter-scope textarea.parameters-field { resize:vertical; }
    .direk-parameter-scope .parameters-label { display:block; margin-bottom:7px; color:var(--parameter-title); font-size:12px; font-weight:600; }
    .direk-parameter-scope .parameters-field:focus, #datatable-parameters_wrapper input:focus { border-color:#635bff; box-shadow:0 0 0 3px rgba(99,91,255,.12); }
    .direk-parameter-scope .parameters-error { margin:6px 0 0; color:#e55378; font-size:11px; line-height:1.6; }
    .direk-parameter-scope .parameters-alert { margin-bottom:18px; padding:14px 16px; border:1px solid #f4b8c7; border-radius:12px; background:#fff1f4; color:#aa2b4d; font-size:12px; line-height:1.8; }
    .direk-parameter-scope[data-theme="dark"] .parameters-alert { border-color:#663449; background:#352332; color:#ffb3c5; }
    .direk-parameter-scope .parameters-alert ul { margin:6px 0 0; padding-left:18px; }
    @media(max-width:639px) { .direk-parameter-scope .parameters-feature-icon { display:none; } .direk-parameter-scope .parameters-card-header { padding:20px 16px; } .direk-parameter-scope .parameters-table-wrap { padding:0 12px 12px; } #datatable-parameters_wrapper .dataTables_filter { width:100%; } #datatable-parameters_wrapper .dataTables_filter label { width:100%; } #datatable-parameters_wrapper .dataTables_filter input { min-width:0; width:0; flex:1; } .direk-parameter-scope .parameters-details, .direk-parameter-scope .parameters-permission-tools { padding:18px; } .direk-parameter-scope .role-permission-toggle { padding:18px; } .direk-parameter-scope .role-permission-panel { padding:0 12px 14px; } .direk-parameter-scope .parameters-permission-search { width:100%; } }
    @media(prefers-reduced-motion:reduce) { .direk-parameter-scope * { transition:none !important; } }

    #parameter-modal .parameter-modal-subtitle { margin:7px 0 0; color:var(--parameter-copy); font-size:12px; line-height:1.7; }
    #parameter-modal .staff-section-title { display:flex; align-items:center; gap:10px; margin-bottom:18px; }
    #parameter-modal .staff-section-title h3 { margin:0; color:var(--parameter-title); font-size:13px; font-weight:650; }
    #parameter-modal .staff-section-divider { border-top:1px solid var(--parameter-border); padding-top:22px; margin-top:22px; }
    #datatable-parameters .staff-action-edit, #datatable-parameters .staff-action-status { border:0; background:transparent; color:var(--parameter-accent-text); }
    #direk-parameters .staff-checking-enabled { background:#e8f7f0; color:#287452; }
    #direk-parameters .staff-checking-disabled { background:var(--parameter-inset); color:var(--parameter-copy); }
    #direk-parameters[data-theme="dark"] .staff-checking-enabled { background:#203c35; color:#9fe0bb; }
    #direk-parameters #staffsLoadError { margin:18px 22px 0; }


    #datatable-parameters .parameter-action-edit { border:0; background:transparent; color:var(--parameter-accent-text); }
    #direk-parameters #parametersLoadError { margin:18px 22px 0; }
    #parameter-modal .parameter-editor-grid { display:grid; grid-template-columns:280px minmax(0,1fr); gap:26px; }
    #parameter-modal .parameter-details { padding-right:24px; border-right:1px solid var(--parameter-border); }
    #parameter-modal .parameter-details h3, #parameter-modal .parameter-value-area h3 { margin:0 0 18px; color:var(--parameter-title); font-size:13px; font-weight:650; }
    #parameter-modal .parameter-value-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    #parameter-modal .parameter-value-heading h3 { margin:0; }
    #parameter-modal .parameter-value-hint { margin:10px 0 18px; color:var(--parameter-copy); font-size:12px; line-height:1.7; }
    #parameter-modal .parameter-duration { margin-top:24px; border-top:1px solid var(--parameter-border); padding-top:22px; }
    #parameter-modal .parameter-duration h3 { margin-bottom:0; }
    #parameter-modal #divQuill { border:1px solid var(--parameter-border); border-radius:12px; overflow:hidden; }
    #parameter-modal .ql-toolbar.ql-snow, #parameter-modal .ql-container.ql-snow { border:0; background:var(--parameter-bg); color:var(--parameter-title); }
    #parameter-modal .ql-toolbar.ql-snow { border-bottom:1px solid var(--parameter-border); background:var(--parameter-inset); }
    #parameter-modal .ql-editor { min-height:260px; font-size:13px; line-height:1.8; color:var(--parameter-title); }
    #parameter-modal .ql-snow .ql-stroke { stroke:var(--parameter-copy); }
    #parameter-modal .ql-snow .ql-fill { fill:var(--parameter-copy); }
    #parameter-modal .ql-snow .ql-picker, #parameter-modal .ql-snow .ql-picker-options { color:var(--parameter-title); background:var(--parameter-bg); }
    #parameter-modal #category[readonly] { background:var(--parameter-inset); color:var(--parameter-copy); }
    #parameter-modal #value { min-height:280px; }
    @media(max-width:991px) { #parameter-modal .parameter-editor-grid { grid-template-columns:1fr; } #parameter-modal .parameter-details { padding:0 0 22px; border:0; border-bottom:1px solid var(--parameter-border); } }

</style>
@endpush

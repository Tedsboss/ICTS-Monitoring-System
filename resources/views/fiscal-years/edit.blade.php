@extends('layouts.app')
@section('content')
@php
    $isInUse = (int) ($fiscalYear->allocations_count ?? $fiscalYear->allocations()->count()) > 0;
    $fyDark = session('user_settings.class_theme', '') === 'dark';
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Fiscal Year Management'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-fiscal-years" class="direk-fy-scope px-4 pb-8 pt-4" data-theme="{{ $fyDark ? 'dark' : 'light' }}">
    <div class="fy-form-shell">
        <a href="{{ route('fiscal-years.index') }}" class="fy-button mb-4"><i class="fa fa-arrow-left" aria-hidden="true"></i>Back to fiscal years</a>
        <header class="fy-page-heading"><div><span class="fy-eyebrow">FINANCIAL PLANNING</span><h2>Review your planning year.</h2><p>Update an unused year or review the period linked to existing allocations.</p></div></header>
        <div id="pageMessage"></div>
        @if ($errors->any())<div class="fy-alert" role="alert"><strong>Please review these fields.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="fy-form-grid">
            <aside class="fy-form-aside">
                <span class="fy-feature-icon" aria-hidden="true"><i class="fa fa-calendar"></i></span>
                <h3>Start with a clear period.</h3><p>Choose a fiscal year and its owning office. Each office can have one record for the same year.</p>
                <ul><li><i class="fa fa-calendar" aria-hidden="true"></i>Years from 2000 to 2100</li><li><i class="fa fa-sitemap" aria-hidden="true"></i>Office ownership</li><li><i class="fa fa-link" aria-hidden="true"></i>Allocation planning</li></ul>
            </aside>
            <section class="fy-card" aria-labelledby="fiscalYearDetailsTitle">
                <div class="fy-card-header"><div><h3 id="fiscalYearDetailsTitle">Fiscal year details</h3><p>Select the budget period and owning office.</p></div></div>
                <form method="POST" action="{{ route('fiscal-years.update', $fiscalYear) }}" id="fiscalYearForm">
                    @csrf
                    @method('PUT')
                    @if ($isInUse)
                        <input type="hidden" name="year" value="{{ $fiscalYear->year }}">
                        <input type="hidden" name="staff_id" value="{{ $fiscalYear->staff_id }}">
                    @endif
                    
                    <div class="fy-form-body">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div><label for="year" class="fy-label">Fiscal year</label><input type="number" name="year" id="year" min="2000" max="2100" step="1" class="fy-field" required @disabled($isInUse) value="{{ $isInUse ? $fiscalYear->year : old('year', $fiscalYear->year) }}" placeholder="e.g. 2027"><p class="fy-field-help">Enter a whole year from 2000 to 2100.</p>@error('year')<p class="fy-error">{{ $message }}</p>@enderror</div>
                            <div><label for="staff_id" class="fy-label">Staff / Office</label><select name="staff_id" id="staff_id" class="fy-field" required @disabled($isInUse)><option value="">Select Staff / Office</option>@foreach ($staffOptions as $staff)<option value="{{ $staff->id }}" @selected(($isInUse ? $fiscalYear->staff_id : old('staff_id', $fiscalYear->staff_id)) == $staff->id)>{{ $staff->name }}{{ $staff->abbreviation ? ' — ' . $staff->abbreviation : '' }}</option>@endforeach</select><p class="fy-field-help">The office that owns this fiscal year.</p>@error('staff_id')<p class="fy-error">{{ $message }}</p>@enderror</div>
                        </div>
                        
                        <div class="fy-warning"><i class="fa fa-info-circle" aria-hidden="true"></i><span>{{ $isInUse ? 'This fiscal year is used by allocations. Its year and office are locked. Create a separate record for a different period.' : 'Unused fiscal years may be updated. Each Staff / Office can have only one record for a given year.' }}</span></div>
                    </div>
                    <div class="fy-form-actions"><a href="{{ route('fiscal-years.index') }}" class="fy-button">Cancel</a><button type="submit" id="updateFiscalYearButton" @disabled($isInUse) class="fy-button fy-primary"><i class="fa fa-check" id="updateFiscalYearIcon" aria-hidden="true"></i><span id="updateFiscalYearText">Update Fiscal Year</span></button></div>
                </form>
            </section>
        </div>
    </div>
    <div class="mt-6">@include('layouts.footers.auth.footer')</div>
</div>
@endsection
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('fiscalYearForm');
    const button = document.getElementById('updateFiscalYearButton');
    const icon = document.getElementById('updateFiscalYearIcon');
    const text = document.getElementById('updateFiscalYearText');
    const locked = @json($isInUse);
    let saving = false;
    form.addEventListener('submit', function (event) {
        if (locked || saving || !form.reportValidity()) { event.preventDefault(); return; }
        saving = true;
        button.disabled = true;
        icon.className = 'fa fa-spinner fa-spin';
        text.textContent = 'Saving…';
        form.setAttribute('aria-busy', 'true');
    });
    window.addEventListener('pageshow', function () {
        saving = false;
        button.disabled = locked;
        icon.className = 'fa fa-check';
        text.textContent = 'Update Fiscal Year';
        form.removeAttribute('aria-busy');
    });
});
</script>
@endpush
@push('css')
<style>

    #direk-fiscal-years, #direk-fy-editor, #fy-type-modal { --fy-bg:#fff; --fy-inset:#f6f7fb; --fy-border:#e6e9f0; --fy-title:#172033; --fy-copy:#637086; --fy-accent:#635bff; --fy-accent-text:#5850df; --fy-soft:#eeedff; color:var(--fy-copy); }
    #direk-fiscal-years[data-theme="dark"], #direk-fy-editor[data-theme="dark"], #fy-type-modal[data-theme="dark"] { --fy-bg:#192130; --fy-inset:#151c29; --fy-border:#2c3546; --fy-title:#eef0f8; --fy-copy:#95a0b5; --fy-accent-text:#a19cff; --fy-soft:#2b2850; }
    #direk-fiscal-years [hidden], #direk-fy-editor [hidden], #fy-type-modal [hidden] { display:none !important; }
    .direk-fy-scope .fy-shell { max-width:1600px; margin:0 auto; }
    .direk-fy-scope .fy-eyebrow { display:block; color:var(--fy-accent-text); font-size:10px; font-weight:700; letter-spacing:.14em; }
    .direk-fy-scope .fy-page-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; margin:8px 0 26px; }
    .direk-fy-scope .fy-page-heading h2 { margin:9px 0 8px; color:var(--fy-title); font-size:clamp(27px,3vw,36px); font-weight:700; line-height:1.2; letter-spacing:-.04em; }
    .direk-fy-scope .fy-page-heading p { margin:0; color:var(--fy-copy); font-size:13px; line-height:1.7; }
    .direk-fy-scope .fy-feature-icon { display:grid; place-items:center; width:60px; height:60px; flex-shrink:0; border:1px solid var(--fy-border); border-radius:20px; background:var(--fy-bg); color:var(--fy-accent-text); font-size:23px; }
    .direk-fy-scope .fy-card { border:1px solid var(--fy-border); border-radius:20px; background:var(--fy-bg); overflow:hidden; box-shadow:0 5px 24px rgba(20,25,60,.035); }
    .direk-fy-scope .fy-card-header { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; padding:23px 24px; border-bottom:1px solid var(--fy-border); }
    .direk-fy-scope .fy-card-header h3 { margin:0; color:var(--fy-title); font-size:16px; font-weight:650; letter-spacing:-.02em; }
    .direk-fy-scope .fy-card-header p { margin:5px 0 0; color:var(--fy-copy); font-size:12px; line-height:1.6; }
    .direk-fy-scope .fy-badge { display:inline-flex; align-items:center; border-radius:7px; background:var(--fy-soft); color:var(--fy-accent-text); padding:5px 9px; font-size:10px; font-weight:650; }
    .direk-fy-scope .fy-button { display:inline-flex; min-height:44px; align-items:center; justify-content:center; gap:8px; border:1px solid var(--fy-border); border-radius:10px; background:var(--fy-bg); padding:10px 15px; color:var(--fy-title); font-size:12px; font-weight:600; text-decoration:none; margin:0; }
    .direk-fy-scope .fy-primary { border-color:#635bff; background:#635bff; color:#fff; box-shadow:0 3px 8px rgba(99,91,255,.15); }
    .direk-fy-scope .fy-primary:hover { background:#534be5; color:#fff; }
    .direk-fy-scope .fy-button:disabled { opacity:.6; cursor:wait; }
    .direk-fy-scope button:focus-visible, .direk-fy-scope a:focus-visible { outline:2px solid var(--fy-accent-text); outline-offset:3px; }
    .direk-fy-scope .fy-info { display:flex; align-items:flex-start; gap:9px; padding:13px 24px; background:var(--fy-inset); color:var(--fy-copy); font-size:12px; line-height:1.7; }
    .direk-fy-scope .fy-table-wrap { padding:0 22px 18px; }
    .direk-fy-scope .fy-table-controls, .direk-fy-scope .fy-table-bottom { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; padding:18px 2px; }
    .direk-fy-scope .fy-table-bottom { padding-bottom:0; }
    .direk-fy-scope .fy-table-scroll { overflow-x:auto; }
    #fiscalYearTable { width:100% !important; margin:0; border-collapse:separate !important; border-spacing:0; }
    #fiscalYearTable thead th { position:static; border:0; border-bottom:1px solid var(--fy-border); background:var(--fy-inset); color:var(--fy-copy); padding:13px 12px; font-size:10px; font-weight:650; text-transform:uppercase; letter-spacing:.06em; }
    #fiscalYearTable tbody td { padding:17px 12px; border:0; border-bottom:1px solid var(--fy-border); background:var(--fy-bg); color:var(--fy-copy); vertical-align:middle; font-size:12px; line-height:1.7; overflow-wrap:anywhere; }
    #fiscalYearTable tbody tr:hover td { background:var(--fy-inset); }
    #fiscalYearTable .fy-name { display:flex; align-items:center; gap:12px; min-width:160px; color:var(--fy-title); font-weight:600; }
    #fiscalYearTable .fy-name-icon { display:grid; place-items:center; width:38px; height:38px; flex-shrink:0; border:1px solid var(--fy-border); border-radius:12px; background:var(--fy-soft); color:var(--fy-accent-text); }
    #fiscalYearTable .fy-actions { white-space:nowrap; text-align:right; }
    #fiscalYearTable .fy-actions .btn-group { display:inline-flex; align-items:center; gap:6px; }
    #fiscalYearTable .fy-actions form { display:inline-flex; margin:0; }
    #fiscalYearTable .fy-actions button, #fiscalYearTable .fy-actions a { display:inline-flex; align-items:center; justify-content:center; min-width:40px; min-height:40px; border-radius:9px; text-decoration:none; }
    #fiscalYearTable .fy-actions button:hover, #fiscalYearTable .fy-actions a:hover { background:var(--fy-soft); }
    #fiscalYearTable .dataTables_empty { padding:48px 16px; text-align:center; }
    #fiscalYearTable_wrapper .dataTables_length, #fiscalYearTable_wrapper .dataTables_filter, #fiscalYearTable_wrapper .dataTables_info, #fiscalYearTable_wrapper .dataTables_paginate { float:none !important; margin:0; padding:0; color:var(--fy-copy); font-size:12px; }
    #fiscalYearTable_wrapper label { display:flex; align-items:center; gap:8px; margin:0; font-weight:400; color:var(--fy-copy); }
    #fiscalYearTable_wrapper input, #fiscalYearTable_wrapper select { min-height:40px; margin:0; border:1px solid var(--fy-border); border-radius:9px; background:var(--fy-bg); color:var(--fy-title); padding:8px 10px; outline:none; }
    #fiscalYearTable_wrapper .dataTables_filter input { width:250px; }
    #fiscalYearTable_wrapper .pagination { display:flex; gap:4px; flex-wrap:wrap; margin:0; }
    #fiscalYearTable_wrapper .page-link, #fiscalYearTable_wrapper .paginate_button { border:1px solid var(--fy-border) !important; border-radius:8px !important; background:var(--fy-bg) !important; color:var(--fy-copy) !important; font-size:12px; }
    #fiscalYearTable_wrapper li.paginate_button { padding:0 !important; border:0 !important; background:none !important; }
    #fiscalYearTable_wrapper .active .page-link, #fiscalYearTable_wrapper .paginate_button.current { border-color:#635bff !important; background:#635bff !important; color:#fff !important; }
    #fy-type-modal .modal-content { border:1px solid var(--fy-border); border-radius:20px; background:var(--fy-bg) !important; color:var(--fy-copy); overflow:hidden; }
    #fy-type-modal .modal-header { padding:23px 24px; border-color:var(--fy-border); background:linear-gradient(125deg,var(--fy-soft),var(--fy-bg) 75%); }
    #fy-type-modal .modal-title { color:var(--fy-title); margin:7px 0 0; font-size:22px; font-weight:650; letter-spacing:-.03em; }
    #fy-type-modal .modal-body { padding:24px; }
    #fy-type-modal .modal-footer { border-color:var(--fy-border); background:var(--fy-inset); padding:16px 24px; }
    #fy-type-modal[data-theme="dark"] .btn-close { filter:invert(1); }
    .direk-fy-scope .fy-field { display:block; box-sizing:border-box; width:100%; min-height:44px; border:1px solid var(--fy-border); border-radius:10px; padding:10px 12px; background:var(--fy-bg); color:var(--fy-title); font-size:13px; line-height:1.6; outline:none; }
    .direk-fy-scope textarea.fy-field { resize:vertical; }
    .direk-fy-scope .fy-label { display:block; margin-bottom:7px; color:var(--fy-title); font-size:12px; font-weight:600; }
    .direk-fy-scope .fy-field:focus, #fiscalYearTable_wrapper input:focus { border-color:#635bff; box-shadow:0 0 0 3px rgba(99,91,255,.12); }
    .direk-fy-scope .fy-error { margin:6px 0 0; color:#e55378; font-size:11px; line-height:1.6; }
    .direk-fy-scope .fy-alert { margin-bottom:18px; padding:14px 16px; border:1px solid #f4b8c7; border-radius:12px; background:#fff1f4; color:#aa2b4d; font-size:12px; line-height:1.8; }
    .direk-fy-scope[data-theme="dark"] .fy-alert { border-color:#663449; background:#352332; color:#ffb3c5; }
    .direk-fy-scope .fy-alert ul { margin:6px 0 0; padding-left:18px; }
    @media(max-width:639px) { .direk-fy-scope .fy-feature-icon { display:none; } .direk-fy-scope .fy-card-header { padding:20px 16px; } .direk-fy-scope .fy-table-wrap { padding:0 12px 12px; } #fiscalYearTable_wrapper .dataTables_filter { width:100%; } #fiscalYearTable_wrapper .dataTables_filter label { width:100%; } #fiscalYearTable_wrapper .dataTables_filter input { min-width:0; width:0; flex:1; } .direk-fy-scope .fy-details, .direk-fy-scope .fy-permission-tools { padding:18px; } .direk-fy-scope .role-permission-toggle { padding:18px; } .direk-fy-scope .role-permission-panel { padding:0 12px 14px; } .direk-fy-scope .fy-permission-search { width:100%; } }
    @media(prefers-reduced-motion:reduce) { .direk-fy-scope * { transition:none !important; } }

    #fy-type-modal .fy-type-modal-subtitle { margin:7px 0 0; color:var(--fy-copy); font-size:12px; line-height:1.7; }
    #fy-type-modal .staff-section-title { display:flex; align-items:center; gap:10px; margin-bottom:18px; }
    #fy-type-modal .staff-section-title h3 { margin:0; color:var(--fy-title); font-size:13px; font-weight:650; }
    #fy-type-modal .staff-section-divider { border-top:1px solid var(--fy-border); padding-top:22px; margin-top:22px; }
    #fiscalYearTable .staff-action-edit, #fiscalYearTable .staff-action-status { border:0; background:transparent; color:var(--fy-accent-text); }
    #direk-fiscal-years .staff-checking-enabled { background:#e8f7f0; color:#287452; }
    #direk-fiscal-years .staff-checking-disabled { background:var(--fy-inset); color:var(--fy-copy); }
    #direk-fiscal-years[data-theme="dark"] .staff-checking-enabled { background:#203c35; color:#9fe0bb; }
    #direk-fiscal-years #staffsLoadError { margin:18px 22px 0; }


    #direk-fiscal-years .fy-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:22px; }
    #direk-fiscal-years .fy-stat { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:20px; border:1px solid var(--fy-border); border-radius:16px; background:var(--fy-bg); }
    #direk-fiscal-years .fy-stat p { margin:0; color:var(--fy-copy); font-size:11px; }
    #direk-fiscal-years .fy-stat strong { display:block; margin-top:8px; color:var(--fy-title); font-size:24px; font-weight:650; letter-spacing:-.03em; }
    #direk-fiscal-years .fy-stat i { display:grid; place-items:center; width:38px; height:38px; border-radius:12px; background:var(--fy-soft); color:var(--fy-accent-text); }
    #direk-fiscal-years .fy-tools { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; padding:20px 22px; }
    #direk-fiscal-years .fy-filters { display:flex; flex-wrap:wrap; gap:10px; }
    #direk-fiscal-years .fy-filter { width:170px; }
    #direk-fiscal-years .fy-search { width:280px; max-width:100%; }
    #fiscalYearTable { min-width:850px; }
    #fiscalYearTable .fy-description { color:var(--fy-title); font-weight:600; }
    #direk-fiscal-years .fy-type-co { background:var(--fy-soft); color:var(--fy-accent-text); }
    #direk-fiscal-years .fy-type-mooe { background:#e8f0ff; color:#3463ae; }
    #direk-fiscal-years .fy-available { background:#e8f7f0; color:#287452; }
    #direk-fiscal-years[data-theme="dark"] .fy-type-mooe { background:#22364f; color:#aacbff; }
    #direk-fiscal-years[data-theme="dark"] .fy-available { background:#203c35; color:#9fe0bb; }
    #direk-fiscal-years .fy-positive { margin-bottom:18px; padding:14px 16px; border:1px solid var(--fy-border); border-radius:12px; background:var(--fy-soft); color:var(--fy-accent-text); font-size:12px; line-height:1.7; }
    #fiscalYearTable .fy-action-edit, #fiscalYearTable .fy-delete { background:transparent; border:0; color:var(--fy-accent-text); }
    #fiscalYearTable .fy-delete { color:#e55378; }
    #fiscalYearTable .fy-locked { display:inline-flex; align-items:center; justify-content:center; min-width:40px; min-height:40px; color:var(--fy-copy); }
    #direk-fiscal-years .fy-form-shell { max-width:1180px; margin:0 auto; }
    #direk-fiscal-years .fy-form-grid { display:grid; grid-template-columns:280px minmax(0,1fr); gap:24px; align-items:start; }
    #direk-fiscal-years .fy-form-aside { padding:24px; border:1px solid var(--fy-border); border-radius:20px; background:var(--fy-bg); }
    #direk-fiscal-years .fy-form-aside h3 { margin:18px 0 8px; color:var(--fy-title); font-size:16px; font-weight:650; }
    #direk-fiscal-years .fy-form-aside p { margin:0; font-size:12px; color:var(--fy-copy); line-height:1.8; }
    #direk-fiscal-years .fy-form-aside ul { display:grid; gap:12px; padding:20px 0 0; margin:20px 0 0; list-style:none; border-top:1px solid var(--fy-border); color:var(--fy-copy); font-size:12px; }
    #direk-fiscal-years .fy-form-aside li i { margin-right:8px; color:var(--fy-accent-text); }
    #direk-fiscal-years .fy-form-body { padding:24px; }
    #direk-fiscal-years .fy-field-help { margin:7px 0 0; color:var(--fy-copy); font-size:11px; line-height:1.7; }
    #direk-fiscal-years .fy-form-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:10px; padding:18px 24px; border-top:1px solid var(--fy-border); background:var(--fy-inset); }
    #direk-fiscal-years .fy-warning { display:flex; align-items:flex-start; gap:10px; margin-top:22px; padding:16px; border:1px solid var(--fy-border); border-radius:12px; background:var(--fy-inset); color:var(--fy-copy); font-size:12px; line-height:1.8; }
    #direk-fiscal-years .fy-warning strong { color:var(--fy-title); }
    @media(max-width:991px) { #direk-fiscal-years .fy-form-grid { grid-template-columns:1fr; } #direk-fiscal-years .fy-form-aside { display:none; } }
    @media(max-width:639px) { #direk-fiscal-years .fy-stats { grid-template-columns:repeat(2,minmax(0,1fr)); } #direk-fiscal-years .fy-stat { padding:16px; } #direk-fiscal-years .fy-stat i { display:none; } #direk-fiscal-years .fy-tools { padding:18px 12px; } #direk-fiscal-years .fy-search { width:100%; } #direk-fiscal-years .fy-filters { width:100%; } #direk-fiscal-years .fy-filter { flex:1; min-width:0; } #direk-fiscal-years .fy-form-body { padding:18px; } }

    #direk-fiscal-years .fy-empty { text-align:center; padding:44px 16px !important; color:var(--fy-copy); line-height:1.8; }
</style>
@endpush

@extends('layouts.app')
@section('content')
@php
    $allocationCount = (int) ($level->allocations_count ?? $level->allocations()->count());
    $isInUse = $allocationCount > 0;
    $levelDark = session('user_settings.class_theme', '') === 'dark';
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Level Management'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-levels" class="direk-level-scope px-4 pb-8 pt-4" data-theme="{{ $levelDark ? 'dark' : 'light' }}">
    <div class="level-form-shell">
        <a href="{{ route('levels.index') }}" class="level-button mb-4"><i class="fa fa-arrow-left" aria-hidden="true"></i>Back to levels</a>
        <header class="level-page-heading"><div><span class="level-eyebrow">FINANCIAL PLANNING</span><h2>Refine your planning level.</h2><p>Review the level’s details and allocation usage before making changes.</p></div></header>
        <div id="pageMessage"></div>
        @if ($errors->any())<div class="level-alert" role="alert"><strong>Please review these fields.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="level-form-grid">
            <aside class="level-form-aside">
                <span class="level-feature-icon" aria-hidden="true"><i class="fa fa-cubes"></i></span>
                <h3>Make the funding stage clear.</h3><p>Give each level a recognizable code, a clear description, and an owning office.</p>
                <ul><li><i class="fa fa-cubes" aria-hidden="true"></i>Tier1 and Tier2</li><li><i class="fa fa-file-text-o" aria-hidden="true"></i>NEP, GAA, and Continuing</li><li><i class="fa fa-sitemap" aria-hidden="true"></i>Office ownership</li></ul>
            </aside>
            <section class="level-card" aria-labelledby="levelDetailsTitle">
                <div class="level-card-header"><div><h3 id="levelDetailsTitle">Level details</h3><p>Define the level and its owning office.</p></div></div>
                <form method="POST" action="{{ route('levels.update', $level) }}" id="levelForm">
                    @csrf
                    @method('PUT')
                    @if ($isInUse)
                        <input type="hidden" name="level_code" value="{{ $level->level_code }}">
                        <input type="hidden" name="staff_id" value="{{ $level->staff_id }}">
                    @endif
                    
                    <div class="level-form-body">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div><label for="level_code" class="level-label">Level code</label><input type="text" name="level_code" id="level_code" class="level-field" maxlength="50" required @disabled($isInUse) value="{{ $isInUse ? $level->level_code : old('level_code', $level->level_code) }}" placeholder="e.g. Tier1" autocomplete="off"><p class="level-field-help">Use the code your office uses for this funding stage.</p>@error('level_code')<p class="level-error">{{ $message }}</p>@enderror</div>
                            <div><label for="staff_id" class="level-label">Staff / Office</label><select name="staff_id" id="staff_id" class="level-field" required @disabled($isInUse)><option value="">Select Staff / Office</option>@foreach ($staffOptions as $staff)<option value="{{ $staff->id }}" @selected(($isInUse ? $level->staff_id : old('staff_id', $level->staff_id)) == $staff->id)>{{ $staff->name }}{{ $staff->abbreviation ? ' — ' . $staff->abbreviation : '' }}</option>@endforeach</select><p class="level-field-help">The office that owns this level.</p>@error('staff_id')<p class="level-error">{{ $message }}</p>@enderror</div>
                        </div>
                        <div class="mt-5"><label for="level_description" class="level-label">Level description</label><input type="text" name="level_description" id="level_description" class="level-field" maxlength="150" required value="{{ old('level_description', $level->level_description) }}" placeholder="e.g. Tier One" autocomplete="off"><p class="level-field-help">Provide the descriptive name of the level.</p>@error('level_description')<p class="level-error">{{ $message }}</p>@enderror</div>
                        
                        @if ($isInUse)<div class="level-warning"><i class="fa fa-link" aria-hidden="true"></i><span>This level is linked to {{ $allocationCount }} {{ $allocationCount === 1 ? 'allocation' : 'allocations' }}. Its code and Staff / Office are protected. You can still update the description.</span></div>@endif
                        <div class="level-warning"><i class="fa fa-info-circle" aria-hidden="true"></i><span>Use consistent codes and descriptions so your office can identify the correct level during allocation planning.</span></div>
                    </div>
                    <div class="level-form-actions"><a href="{{ route('levels.index') }}" class="level-button">Cancel</a><button type="submit" id="updateLevelButton" class="level-button level-primary"><i class="fa fa-check" id="updateLevelIcon" aria-hidden="true"></i><span id="updateLevelText">Update Level</span></button></div>
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
    const form = document.getElementById('levelForm');
    const button = document.getElementById('updateLevelButton');
    const icon = document.getElementById('updateLevelIcon');
    const text = document.getElementById('updateLevelText');
    let saving = false;
    form.addEventListener('submit', function (event) {
        if (saving || !form.reportValidity()) { event.preventDefault(); return; }
        saving = true;
        button.disabled = true;
        icon.className = 'fa fa-spinner fa-spin';
        text.textContent = 'Saving…';
        form.setAttribute('aria-busy', 'true');
    });
    window.addEventListener('pageshow', function () {
        saving = false;
        button.disabled = false;
        icon.className = 'fa fa-check';
        text.textContent = 'Update Level';
        form.removeAttribute('aria-busy');
    });
});
</script>
@endpush
@push('css')
<style>

    #direk-levels, #direk-level-editor, #level-type-modal { --level-bg:#fff; --level-inset:#f6f7fb; --level-border:#e6e9f0; --level-title:#172033; --level-copy:#637086; --level-accent:#635bff; --level-accent-text:#5850df; --level-soft:#eeedff; color:var(--level-copy); }
    #direk-levels[data-theme="dark"], #direk-level-editor[data-theme="dark"], #level-type-modal[data-theme="dark"] { --level-bg:#192130; --level-inset:#151c29; --level-border:#2c3546; --level-title:#eef0f8; --level-copy:#95a0b5; --level-accent-text:#a19cff; --level-soft:#2b2850; }
    #direk-levels [hidden], #direk-level-editor [hidden], #level-type-modal [hidden] { display:none !important; }
    .direk-level-scope .level-shell { max-width:1600px; margin:0 auto; }
    .direk-level-scope .level-eyebrow { display:block; color:var(--level-accent-text); font-size:10px; font-weight:700; letter-spacing:.14em; }
    .direk-level-scope .level-page-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; margin:8px 0 26px; }
    .direk-level-scope .level-page-heading h2 { margin:9px 0 8px; color:var(--level-title); font-size:clamp(27px,3vw,36px); font-weight:700; line-height:1.2; letter-spacing:-.04em; }
    .direk-level-scope .level-page-heading p { margin:0; color:var(--level-copy); font-size:13px; line-height:1.7; }
    .direk-level-scope .level-feature-icon { display:grid; place-items:center; width:60px; height:60px; flex-shrink:0; border:1px solid var(--level-border); border-radius:20px; background:var(--level-bg); color:var(--level-accent-text); font-size:23px; }
    .direk-level-scope .level-card { border:1px solid var(--level-border); border-radius:20px; background:var(--level-bg); overflow:hidden; box-shadow:0 5px 24px rgba(20,25,60,.035); }
    .direk-level-scope .level-card-header { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; padding:23px 24px; border-bottom:1px solid var(--level-border); }
    .direk-level-scope .level-card-header h3 { margin:0; color:var(--level-title); font-size:16px; font-weight:650; letter-spacing:-.02em; }
    .direk-level-scope .level-card-header p { margin:5px 0 0; color:var(--level-copy); font-size:12px; line-height:1.6; }
    .direk-level-scope .level-badge { display:inline-flex; align-items:center; border-radius:7px; background:var(--level-soft); color:var(--level-accent-text); padding:5px 9px; font-size:10px; font-weight:650; }
    .direk-level-scope .level-button { display:inline-flex; min-height:44px; align-items:center; justify-content:center; gap:8px; border:1px solid var(--level-border); border-radius:10px; background:var(--level-bg); padding:10px 15px; color:var(--level-title); font-size:12px; font-weight:600; text-decoration:none; margin:0; }
    .direk-level-scope .level-primary { border-color:#635bff; background:#635bff; color:#fff; box-shadow:0 3px 8px rgba(99,91,255,.15); }
    .direk-level-scope .level-primary:hover { background:#534be5; color:#fff; }
    .direk-level-scope .level-button:disabled { opacity:.6; cursor:wait; }
    .direk-level-scope button:focus-visible, .direk-level-scope a:focus-visible { outline:2px solid var(--level-accent-text); outline-offset:3px; }
    .direk-level-scope .level-info { display:flex; align-items:flex-start; gap:9px; padding:13px 24px; background:var(--level-inset); color:var(--level-copy); font-size:12px; line-height:1.7; }
    .direk-level-scope .level-table-wrap { padding:0 22px 18px; }
    .direk-level-scope .level-table-controls, .direk-level-scope .level-table-bottom { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; padding:18px 2px; }
    .direk-level-scope .level-table-bottom { padding-bottom:0; }
    .direk-level-scope .level-table-scroll { overflow-x:auto; }
    #levelTable { width:100% !important; margin:0; border-collapse:separate !important; border-spacing:0; }
    #levelTable thead th { position:static; border:0; border-bottom:1px solid var(--level-border); background:var(--level-inset); color:var(--level-copy); padding:13px 12px; font-size:10px; font-weight:650; text-transform:uppercase; letter-spacing:.06em; }
    #levelTable tbody td { padding:17px 12px; border:0; border-bottom:1px solid var(--level-border); background:var(--level-bg); color:var(--level-copy); vertical-align:middle; font-size:12px; line-height:1.7; overflow-wrap:anywhere; }
    #levelTable tbody tr:hover td { background:var(--level-inset); }
    #levelTable .level-name { display:flex; align-items:center; gap:12px; min-width:160px; color:var(--level-title); font-weight:600; }
    #levelTable .level-name-icon { display:grid; place-items:center; width:38px; height:38px; flex-shrink:0; border:1px solid var(--level-border); border-radius:12px; background:var(--level-soft); color:var(--level-accent-text); }
    #levelTable .level-actions { white-space:nowrap; text-align:right; }
    #levelTable .level-actions .btn-group { display:inline-flex; align-items:center; gap:6px; }
    #levelTable .level-actions form { display:inline-flex; margin:0; }
    #levelTable .level-actions button, #levelTable .level-actions a { display:inline-flex; align-items:center; justify-content:center; min-width:40px; min-height:40px; border-radius:9px; text-decoration:none; }
    #levelTable .level-actions button:hover, #levelTable .level-actions a:hover { background:var(--level-soft); }
    #levelTable .dataTables_empty { padding:48px 16px; text-align:center; }
    #levelTable_wrapper .dataTables_length, #levelTable_wrapper .dataTables_filter, #levelTable_wrapper .dataTables_info, #levelTable_wrapper .dataTables_paginate { float:none !important; margin:0; padding:0; color:var(--level-copy); font-size:12px; }
    #levelTable_wrapper label { display:flex; align-items:center; gap:8px; margin:0; font-weight:400; color:var(--level-copy); }
    #levelTable_wrapper input, #levelTable_wrapper select { min-height:40px; margin:0; border:1px solid var(--level-border); border-radius:9px; background:var(--level-bg); color:var(--level-title); padding:8px 10px; outline:none; }
    #levelTable_wrapper .dataTables_filter input { width:250px; }
    #levelTable_wrapper .pagination { display:flex; gap:4px; flex-wrap:wrap; margin:0; }
    #levelTable_wrapper .page-link, #levelTable_wrapper .paginate_button { border:1px solid var(--level-border) !important; border-radius:8px !important; background:var(--level-bg) !important; color:var(--level-copy) !important; font-size:12px; }
    #levelTable_wrapper li.paginate_button { padding:0 !important; border:0 !important; background:none !important; }
    #levelTable_wrapper .active .page-link, #levelTable_wrapper .paginate_button.current { border-color:#635bff !important; background:#635bff !important; color:#fff !important; }
    #level-type-modal .modal-content { border:1px solid var(--level-border); border-radius:20px; background:var(--level-bg) !important; color:var(--level-copy); overflow:hidden; }
    #level-type-modal .modal-header { padding:23px 24px; border-color:var(--level-border); background:linear-gradient(125deg,var(--level-soft),var(--level-bg) 75%); }
    #level-type-modal .modal-title { color:var(--level-title); margin:7px 0 0; font-size:22px; font-weight:650; letter-spacing:-.03em; }
    #level-type-modal .modal-body { padding:24px; }
    #level-type-modal .modal-footer { border-color:var(--level-border); background:var(--level-inset); padding:16px 24px; }
    #level-type-modal[data-theme="dark"] .btn-close { filter:invert(1); }
    .direk-level-scope .level-field { display:block; box-sizing:border-box; width:100%; min-height:44px; border:1px solid var(--level-border); border-radius:10px; padding:10px 12px; background:var(--level-bg); color:var(--level-title); font-size:13px; line-height:1.6; outline:none; }
    .direk-level-scope textarea.level-field { resize:vertical; }
    .direk-level-scope .level-label { display:block; margin-bottom:7px; color:var(--level-title); font-size:12px; font-weight:600; }
    .direk-level-scope .level-field:focus, #levelTable_wrapper input:focus { border-color:#635bff; box-shadow:0 0 0 3px rgba(99,91,255,.12); }
    .direk-level-scope .level-error { margin:6px 0 0; color:#e55378; font-size:11px; line-height:1.6; }
    .direk-level-scope .level-alert { margin-bottom:18px; padding:14px 16px; border:1px solid #f4b8c7; border-radius:12px; background:#fff1f4; color:#aa2b4d; font-size:12px; line-height:1.8; }
    .direk-level-scope[data-theme="dark"] .level-alert { border-color:#663449; background:#352332; color:#ffb3c5; }
    .direk-level-scope .level-alert ul { margin:6px 0 0; padding-left:18px; }
    @media(max-width:639px) { .direk-level-scope .level-feature-icon { display:none; } .direk-level-scope .level-card-header { padding:20px 16px; } .direk-level-scope .level-table-wrap { padding:0 12px 12px; } #levelTable_wrapper .dataTables_filter { width:100%; } #levelTable_wrapper .dataTables_filter label { width:100%; } #levelTable_wrapper .dataTables_filter input { min-width:0; width:0; flex:1; } .direk-level-scope .level-details, .direk-level-scope .level-permission-tools { padding:18px; } .direk-level-scope .role-permission-toggle { padding:18px; } .direk-level-scope .role-permission-panel { padding:0 12px 14px; } .direk-level-scope .level-permission-search { width:100%; } }
    @media(prefers-reduced-motion:reduce) { .direk-level-scope * { transition:none !important; } }

    #level-type-modal .level-type-modal-subtitle { margin:7px 0 0; color:var(--level-copy); font-size:12px; line-height:1.7; }
    #level-type-modal .staff-section-title { display:flex; align-items:center; gap:10px; margin-bottom:18px; }
    #level-type-modal .staff-section-title h3 { margin:0; color:var(--level-title); font-size:13px; font-weight:650; }
    #level-type-modal .staff-section-divider { border-top:1px solid var(--level-border); padding-top:22px; margin-top:22px; }
    #levelTable .staff-action-edit, #levelTable .staff-action-status { border:0; background:transparent; color:var(--level-accent-text); }
    #direk-levels .staff-checking-enabled { background:#e8f7f0; color:#287452; }
    #direk-levels .staff-checking-disabled { background:var(--level-inset); color:var(--level-copy); }
    #direk-levels[data-theme="dark"] .staff-checking-enabled { background:#203c35; color:#9fe0bb; }
    #direk-levels #staffsLoadError { margin:18px 22px 0; }


    #direk-levels .level-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:22px; }
    #direk-levels .level-stat { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:20px; border:1px solid var(--level-border); border-radius:16px; background:var(--level-bg); }
    #direk-levels .level-stat p { margin:0; color:var(--level-copy); font-size:11px; }
    #direk-levels .level-stat strong { display:block; margin-top:8px; color:var(--level-title); font-size:24px; font-weight:650; letter-spacing:-.03em; }
    #direk-levels .level-stat i { display:grid; place-items:center; width:38px; height:38px; border-radius:12px; background:var(--level-soft); color:var(--level-accent-text); }
    #direk-levels .level-tools { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; padding:20px 22px; }
    #direk-levels .level-filters { display:flex; flex-wrap:wrap; gap:10px; }
    #direk-levels .level-filter { width:170px; }
    #direk-levels .level-search { width:280px; max-width:100%; }
    #levelTable { min-width:850px; }
    #levelTable .level-description { color:var(--level-title); font-weight:600; }
    #direk-levels .level-type-co { background:var(--level-soft); color:var(--level-accent-text); }
    #direk-levels .level-type-mooe { background:#e8f0ff; color:#3463ae; }
    #direk-levels .level-available { background:#e8f7f0; color:#287452; }
    #direk-levels[data-theme="dark"] .level-type-mooe { background:#22364f; color:#aacbff; }
    #direk-levels[data-theme="dark"] .level-available { background:#203c35; color:#9fe0bb; }
    #direk-levels .level-positive { margin-bottom:18px; padding:14px 16px; border:1px solid var(--level-border); border-radius:12px; background:var(--level-soft); color:var(--level-accent-text); font-size:12px; line-height:1.7; }
    #levelTable .level-action-edit, #levelTable .level-delete { background:transparent; border:0; color:var(--level-accent-text); }
    #levelTable .level-delete { color:#e55378; }
    #levelTable .level-locked { display:inline-flex; align-items:center; justify-content:center; min-width:40px; min-height:40px; color:var(--level-copy); }
    #direk-levels .level-form-shell { max-width:1180px; margin:0 auto; }
    #direk-levels .level-form-grid { display:grid; grid-template-columns:280px minmax(0,1fr); gap:24px; align-items:start; }
    #direk-levels .level-form-aside { padding:24px; border:1px solid var(--level-border); border-radius:20px; background:var(--level-bg); }
    #direk-levels .level-form-aside h3 { margin:18px 0 8px; color:var(--level-title); font-size:16px; font-weight:650; }
    #direk-levels .level-form-aside p { margin:0; font-size:12px; color:var(--level-copy); line-height:1.8; }
    #direk-levels .level-form-aside ul { display:grid; gap:12px; padding:20px 0 0; margin:20px 0 0; list-style:none; border-top:1px solid var(--level-border); color:var(--level-copy); font-size:12px; }
    #direk-levels .level-form-aside li i { margin-right:8px; color:var(--level-accent-text); }
    #direk-levels .level-form-body { padding:24px; }
    #direk-levels .level-field-help { margin:7px 0 0; color:var(--level-copy); font-size:11px; line-height:1.7; }
    #direk-levels .level-form-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:10px; padding:18px 24px; border-top:1px solid var(--level-border); background:var(--level-inset); }
    #direk-levels .level-warning { display:flex; align-items:flex-start; gap:10px; margin-top:22px; padding:16px; border:1px solid var(--level-border); border-radius:12px; background:var(--level-inset); color:var(--level-copy); font-size:12px; line-height:1.8; }
    #direk-levels .level-warning strong { color:var(--level-title); }
    @media(max-width:991px) { #direk-levels .level-form-grid { grid-template-columns:1fr; } #direk-levels .level-form-aside { display:none; } }
    @media(max-width:639px) { #direk-levels .level-stats { grid-template-columns:repeat(2,minmax(0,1fr)); } #direk-levels .level-stat { padding:16px; } #direk-levels .level-stat i { display:none; } #direk-levels .level-tools { padding:18px 12px; } #direk-levels .level-search { width:100%; } #direk-levels .level-filters { width:100%; } #direk-levels .level-filter { flex:1; min-width:0; } #direk-levels .level-form-body { padding:18px; } }

    #direk-levels .level-empty { text-align:center; padding:44px 16px !important; color:var(--level-copy); line-height:1.8; }
    #direk-levels .level-field:disabled { background:var(--level-inset); color:var(--level-copy); cursor:not-allowed; }
</style>
@endpush

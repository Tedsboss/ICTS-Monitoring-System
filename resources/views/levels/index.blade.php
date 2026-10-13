@extends('layouts.app')
@section('content')
@php
    $levelDark = session('user_settings.class_theme', '') === 'dark';
    $totalLevels = $levels->count();
    $levelsInUse = $levels->where('allocations_count', '>', 0)->count();
    $unusedLevels = $totalLevels - $levelsInUse;
    $officeOptions = $staffOptions->whereIn('id', $levels->pluck('staff_id')->filter()->unique());
    $levelOffices = $officeOptions->keyBy('id');
    $officeCount = $officeOptions->count();
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Level Management'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-levels" class="direk-level-scope px-4 pb-8 pt-4" data-theme="{{ $levelDark ? 'dark' : 'light' }}">
    <div class="level-shell">
        <header class="level-page-heading">
            <div><span class="level-eyebrow">FINANCIAL PLANNING</span><h2>A clear stage for every budget.</h2><p>Organize the levels your offices use for allocation planning.</p></div>

            @if ($canAddLevels ?? false)<a href="{{ route('levels.create') }}" class="level-button level-primary"><i class="fa fa-plus" aria-hidden="true"></i>Add Level</a>@endif
        </header>
        <div id="pageMessage"></div>
        @if (session('success') || session('succes'))<div class="level-positive" role="status"><i class="fa fa-check-circle" aria-hidden="true"></i>{{ session('success') ?: session('succes') }}</div>@endif
        @if (session('error'))<div class="level-alert" role="alert">{{ session('error') }}</div>@endif
        <section class="level-stats" aria-label="Level summary">
            <div class="level-stat"><div><p>Level records</p><strong>{{ $totalLevels }}</strong></div><i class="fa fa-cubes" aria-hidden="true"></i></div>
            <div class="level-stat"><div><p>In allocations</p><strong>{{ $levelsInUse }}</strong></div><i class="fa fa-link" aria-hidden="true"></i></div>
            <div class="level-stat"><div><p>Offices</p><strong>{{ $officeCount }}</strong></div><i class="fa fa-sitemap" aria-hidden="true"></i></div>
            <div class="level-stat"><div><p>Not yet used</p><strong>{{ $unusedLevels }}</strong></div><i class="fa fa-circle-o" aria-hidden="true"></i></div>
        </section>
        <section class="level-card" aria-labelledby="levelDirectoryTitle">
            <div class="level-card-header"><div><h3 id="levelDirectoryTitle">Level directory</h3><p>Review level codes, descriptions, offices, and linked allocations.</p></div><span id="levelVisibleCount" class="level-badge" aria-live="polite">{{ $totalLevels }} {{ $totalLevels === 1 ? 'record' : 'records' }}</span></div>
            <div class="level-tools">
                <div class="level-search"><label for="levelSearch" class="sr-only">Search levels</label><input type="search" id="levelSearch" class="level-field" placeholder="Search code, description, or office…"></div>
                <div class="level-filters">
                    <div class="level-filter"><label for="levelClassification" class="sr-only">Office filter</label><select id="levelClassification" class="level-field"><option value="">All offices</option>@foreach ($officeOptions as $office)<option value="{{ $office->id }}">{{ $office->abbreviation ?: $office->name }}</option>@endforeach</select></div>
                    <div class="level-filter"><label for="levelUsage" class="sr-only">Allocation usage filter</label><select id="levelUsage" class="level-field"><option value="">All usage</option><option value="used">In allocations</option><option value="available">Not yet used</option></select></div>
                    <button type="button" id="btnClearLevelFilters" class="level-button" aria-label="Clear level filters"><i class="fa fa-refresh" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="level-table-wrap"><div class="level-table-scroll">
                <table id="levelTable" aria-label="Configured levels" style="width:100%">
                    <thead><tr><th scope="col">Level</th><th scope="col">Description</th><th scope="col">Staff / Office</th><th scope="col">Allocations</th><th scope="col">Usage</th><th scope="col" class="level-actions">Actions</th></tr></thead>
                    <tbody>
                        @forelse ($levels as $level)
                            @php
                                $allocationCount = (int) ($level->allocations_count ?? 0);
                                $officeId = (int) $level->staff_id;
                                $office = $levelOffices->get($officeId);
                            @endphp
                            <tr data-level-row data-classification="{{ $officeId }}" data-usage="{{ $allocationCount > 0 ? 'used' : 'available' }}" data-search="{{ mb_strtolower($level->level_code . ' ' . $level->level_description . ' ' . ($office?->name ?? '') . ' ' . ($office?->abbreviation ?? '')) }}">
                                <td><span class="level-badge">{{ $level->level_code }}</span></td><td><span class="level-description">{{ $level->level_description }}</span></td>
                                <td><span class="level-description">{{ $office?->name ?? 'Unassigned office' }}</span></td>
                                <td><span class="level-badge">{{ $allocationCount }}</span></td>
                                <td><span class="level-badge {{ $allocationCount === 0 ? 'level-available' : '' }}">{{ $allocationCount > 0 ? 'In use' : 'Not yet used' }}</span></td>
                                <td class="level-actions"><div class="btn-group" role="group" aria-label="Actions for level {{ $level->level_code }}">

                                    @if ($canEditLevels ?? false)<a href="{{ route('levels.edit', $level) }}" class="level-action-edit" title="Review level" aria-label="Review level {{ $level->level_code }}"><i class="fa fa-pencil" aria-hidden="true"></i></a>@endif
                                    @if (($canDeleteLevels ?? false) && $allocationCount === 0)
                                        <form method="POST" action="{{ route('levels.destroy', $level) }}" data-confirm="{{ 'Delete level ' . $level->level_code . '? This action cannot be undone.' }}" class="level-delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="level-delete" title="Delete level" aria-label="Delete level {{ $level->level_code }}"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                        </form>
                                    @elseif (($canDeleteLevels ?? false) && $allocationCount > 0)<span class="level-locked" title="This level is used by allocations." aria-label="Deletion unavailable because allocations use this level"><i class="fa fa-lock" aria-hidden="true"></i></span>@endif
                                    @if (!($canEditLevels ?? false) && !($canDeleteLevels ?? false))<span aria-label="No actions available">—</span>@endif
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="level-empty">No levels configured. Add a level to begin allocation planning.</td></tr>
                        @endforelse
                        <tr id="levelSearchEmpty" hidden><td colspan="6" class="level-empty">No levels match your filters.</td></tr>
                    </tbody>
                </table>
            </div></div>
        </section>
    </div>
    <div class="mt-6">@include('layouts.footers.auth.footer')</div>
</div>
@endsection
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const field = id => document.getElementById(id);
    const rows = Array.from(document.querySelectorAll('[data-level-row]'));
    function filterRows() {
        const term = field('levelSearch').value.trim().toLowerCase();
        const classification = field('levelClassification').value;
        const usage = field('levelUsage').value;
        let visible = 0;
        rows.forEach(row => {
            const matches = (!term || row.dataset.search.toLowerCase().includes(term))
                && (!classification || row.dataset.classification === classification)
                && (!usage || row.dataset.usage === usage);
            row.hidden = !matches;
            if (matches) visible++;
        });
        field('levelVisibleCount').textContent = visible + (visible === 1 ? ' record' : ' records');
        field('levelSearchEmpty').hidden = !rows.length || visible > 0;
    }
    field('levelSearch').addEventListener('input', filterRows);
    ['levelClassification','levelUsage'].forEach(id => field(id).addEventListener('change', filterRows));
    field('btnClearLevelFilters').addEventListener('click', () => {
        ['levelSearch','levelClassification','levelUsage'].forEach(id => { field(id).value = ''; });
        filterRows();
    });
    filterRows();
    document.querySelectorAll('.level-delete-form').forEach(function (form) {
        let deleting = false;
        const button = form.querySelector('button[type="submit"]');
        form.addEventListener('submit', function (event) {
            if (deleting || !confirm(form.dataset.confirm)) { event.preventDefault(); return; }
            deleting = true; button.disabled = true;
        });
        window.addEventListener('pageshow', function () { deleting = false; button.disabled = false; });
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
</style>
@endpush

@extends('layouts.app')
@section('content')
@php
    $expenseDark = session('user_settings.class_theme', '') === 'dark';
    $totalExpenseTypes = $expenseTypes->count();
    $expenseTypesInUse = $expenseTypes->where('allocation_expenses_count', '>', 0)->count();
    $availableExpenseTypes = $totalExpenseTypes - $expenseTypesInUse;
    $mooeCount = $expenseTypes->filter(fn ($expenseType) => strtoupper($expenseType->type) === 'MOOE')->count();
    $coCount = $expenseTypes->filter(fn ($expenseType) => strtoupper($expenseType->type) === 'CO')->count();
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Expense Type Management'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-expense-types" class="direk-expense-scope px-4 pb-8 pt-4" data-theme="{{ $expenseDark ? 'dark' : 'light' }}">
    <div class="expense-shell">
        <header class="expense-page-heading">
            <div><span class="expense-eyebrow">FINANCIAL PLANNING</span><h2>A clear structure for spending.</h2><p>Organize the expense classifications used by Allocation Management.</p></div>
            @if ($canAddExpenseTypes ?? false)
            <a href="{{ route('expense-types.create') }}" class="expense-button expense-primary"><i class="fa fa-plus" aria-hidden="true"></i>Add Expense Type</a>
            @endif
        </header>
        <div id="pageMessage"></div>
        @if (session('success') || session('succes'))<div class="expense-positive" role="status"><i class="fa fa-check-circle" aria-hidden="true"></i>{{ session('success') ?: session('succes') }}</div>@endif
        @if (session('error'))<div class="expense-alert" role="alert">{{ session('error') }}</div>@endif
        <section class="expense-stats" aria-label="Expense type summary">
            <div class="expense-stat"><div><p>Total types</p><strong>{{ $totalExpenseTypes }}</strong></div><i class="fa fa-tags" aria-hidden="true"></i></div>
            <div class="expense-stat"><div><p>In allocations</p><strong>{{ $expenseTypesInUse }}</strong></div><i class="fa fa-link" aria-hidden="true"></i></div>
            <div class="expense-stat"><div><p>MOOE / CO</p><strong>{{ $mooeCount }} / {{ $coCount }}</strong></div><i class="fa fa-pie-chart" aria-hidden="true"></i></div>
            <div class="expense-stat"><div><p>Not yet allocated</p><strong>{{ $availableExpenseTypes }}</strong></div><i class="fa fa-circle-o" aria-hidden="true"></i></div>
        </section>
        <section class="expense-card" aria-labelledby="expenseTypeDirectoryTitle">
            <div class="expense-card-header"><div><h3 id="expenseTypeDirectoryTitle">Expense type directory</h3><p>Review classifications, descriptions, and allocation usage.</p></div><span id="expenseTypeVisibleCount" class="expense-badge" aria-live="polite">{{ $totalExpenseTypes }} {{ $totalExpenseTypes === 1 ? 'type' : 'types' }}</span></div>
            <div class="expense-tools">
                <div class="expense-search"><label for="expenseTypeSearch" class="sr-only">Search expense types</label><input type="search" id="expenseTypeSearch" class="expense-field" placeholder="Search expense descriptions…"></div>
                <div class="expense-filters">
                    <div class="expense-filter"><label for="expenseTypeClassification" class="sr-only">Classification filter</label><select id="expenseTypeClassification" class="expense-field"><option value="">All classifications</option><option value="MOOE">MOOE</option><option value="CO">Capital Outlay</option></select></div>
                    <div class="expense-filter"><label for="expenseTypeUsage" class="sr-only">Allocation usage filter</label><select id="expenseTypeUsage" class="expense-field"><option value="">All usage</option><option value="used">In allocations</option><option value="available">Not yet allocated</option></select></div>
                    <button type="button" id="btnClearExpenseTypeFilters" class="expense-button" aria-label="Clear expense type filters"><i class="fa fa-refresh" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="expense-table-wrap"><div class="expense-table-scroll">
                <table id="expenseTypeTable" aria-label="Configured expense types" style="width:100%">
                    <thead><tr><th scope="col">Classification</th><th scope="col">Expense description</th><th scope="col">Allocation links</th><th scope="col">Usage</th><th scope="col" class="expense-actions">Actions</th></tr></thead>
                    <tbody>
                        @forelse ($expenseTypes as $expenseType)
                            @php
                                $allocationCount = (int) ($expenseType->allocation_expenses_count ?? 0);
                                $expenseTypeCode = strtoupper($expenseType->type);
                            @endphp
                            <tr data-expense-type-row data-classification="{{ $expenseTypeCode }}" data-usage="{{ $allocationCount > 0 ? 'used' : 'available' }}" data-search="{{ mb_strtolower($expenseType->type . ' ' . $expenseType->expense_description) }}">
                                <td><span class="expense-badge {{ $expenseTypeCode === 'MOOE' ? 'expense-type-mooe' : ($expenseTypeCode === 'CO' ? 'expense-type-co' : '') }}">{{ $expenseTypeCode }}</span></td>
                                <td><span class="expense-description">{{ $expenseType->expense_description }}</span></td>
                                <td><span class="expense-badge">{{ $allocationCount }}</span></td>
                                <td><span class="expense-badge {{ $allocationCount === 0 ? 'expense-available' : '' }}">{{ $allocationCount > 0 ? 'In use' : 'Not yet allocated' }}</span></td>
                                <td class="expense-actions"><div class="btn-group" role="group" aria-label="Actions for {{ $expenseType->expense_description }}">
                                    @if ($canEditExpenseTypes ?? false)
                                    <a href="{{ route('expense-types.edit', $expenseType) }}" class="expense-action-edit" title="Edit expense type" aria-label="Edit {{ $expenseType->expense_description }}"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                    @endif
                                    @if (($canDeleteExpenseTypes ?? false) && $allocationCount === 0)
                                        <form method="POST" action="{{ route('expense-types.destroy', $expenseType) }}" data-confirm="{{ 'Delete ' . $expenseType->type . ' — ' . $expenseType->expense_description . '? This action cannot be undone.' }}" onsubmit="return confirm(this.dataset.confirm);">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="expense-delete" title="Delete expense type" aria-label="Delete {{ $expenseType->expense_description }}"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                        </form>
                                    @elseif (($canDeleteExpenseTypes ?? false) && $allocationCount > 0)<span class="expense-locked" title="This expense type is used in an allocation." aria-label="Deletion unavailable because this expense type is used in an allocation"><i class="fa fa-lock" aria-hidden="true"></i></span>@endif
                                    @if (!($canEditExpenseTypes ?? false) && !($canDeleteExpenseTypes ?? false))<span aria-label="No actions available">—</span>@endif
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="expense-empty">No expense types configured. Add an expense type to make it available for Allocation Management.</td></tr>
                        @endforelse
                        <tr id="expenseTypeSearchEmpty" hidden><td colspan="5" class="expense-empty">No expense types match your filters.</td></tr>
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
    const rows = Array.from(document.querySelectorAll('[data-expense-type-row]'));
    function filterRows() {
        const term = field('expenseTypeSearch').value.trim().toLowerCase();
        const classification = field('expenseTypeClassification').value;
        const usage = field('expenseTypeUsage').value;
        let visible = 0;
        rows.forEach(row => {
            const matches = (!term || row.dataset.search.toLowerCase().includes(term))
                && (!classification || row.dataset.classification === classification)
                && (!usage || row.dataset.usage === usage);
            row.hidden = !matches;
            if (matches) visible++;
        });
        field('expenseTypeVisibleCount').textContent = visible + (visible === 1 ? ' type' : ' types');
        field('expenseTypeSearchEmpty').hidden = !rows.length || visible > 0;
    }
    field('expenseTypeSearch').addEventListener('input', filterRows);
    ['expenseTypeClassification','expenseTypeUsage'].forEach(id => field(id).addEventListener('change', filterRows));
    field('btnClearExpenseTypeFilters').addEventListener('click', () => {
        ['expenseTypeSearch','expenseTypeClassification','expenseTypeUsage'].forEach(id => { field(id).value = ''; });
        filterRows();
    });
    filterRows();
});
</script>
@endpush

@push('css')
<style>

    #direk-expense-types, #direk-expense-editor, #expense-type-modal { --expense-bg:#fff; --expense-inset:#f6f7fb; --expense-border:#e6e9f0; --expense-title:#172033; --expense-copy:#637086; --expense-accent:#635bff; --expense-accent-text:#5850df; --expense-soft:#eeedff; color:var(--expense-copy); }
    #direk-expense-types[data-theme="dark"], #direk-expense-editor[data-theme="dark"], #expense-type-modal[data-theme="dark"] { --expense-bg:#192130; --expense-inset:#151c29; --expense-border:#2c3546; --expense-title:#eef0f8; --expense-copy:#95a0b5; --expense-accent-text:#a19cff; --expense-soft:#2b2850; }
    #direk-expense-types [hidden], #direk-expense-editor [hidden], #expense-type-modal [hidden] { display:none !important; }
    .direk-expense-scope .expense-shell { max-width:1600px; margin:0 auto; }
    .direk-expense-scope .expense-eyebrow { display:block; color:var(--expense-accent-text); font-size:10px; font-weight:700; letter-spacing:.14em; }
    .direk-expense-scope .expense-page-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; margin:8px 0 26px; }
    .direk-expense-scope .expense-page-heading h2 { margin:9px 0 8px; color:var(--expense-title); font-size:clamp(27px,3vw,36px); font-weight:700; line-height:1.2; letter-spacing:-.04em; }
    .direk-expense-scope .expense-page-heading p { margin:0; color:var(--expense-copy); font-size:13px; line-height:1.7; }
    .direk-expense-scope .expense-feature-icon { display:grid; place-items:center; width:60px; height:60px; flex-shrink:0; border:1px solid var(--expense-border); border-radius:20px; background:var(--expense-bg); color:var(--expense-accent-text); font-size:23px; }
    .direk-expense-scope .expense-card { border:1px solid var(--expense-border); border-radius:20px; background:var(--expense-bg); overflow:hidden; box-shadow:0 5px 24px rgba(20,25,60,.035); }
    .direk-expense-scope .expense-card-header { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; padding:23px 24px; border-bottom:1px solid var(--expense-border); }
    .direk-expense-scope .expense-card-header h3 { margin:0; color:var(--expense-title); font-size:16px; font-weight:650; letter-spacing:-.02em; }
    .direk-expense-scope .expense-card-header p { margin:5px 0 0; color:var(--expense-copy); font-size:12px; line-height:1.6; }
    .direk-expense-scope .expense-badge { display:inline-flex; align-items:center; border-radius:7px; background:var(--expense-soft); color:var(--expense-accent-text); padding:5px 9px; font-size:10px; font-weight:650; }
    .direk-expense-scope .expense-button { display:inline-flex; min-height:44px; align-items:center; justify-content:center; gap:8px; border:1px solid var(--expense-border); border-radius:10px; background:var(--expense-bg); padding:10px 15px; color:var(--expense-title); font-size:12px; font-weight:600; text-decoration:none; margin:0; }
    .direk-expense-scope .expense-primary { border-color:#635bff; background:#635bff; color:#fff; box-shadow:0 3px 8px rgba(99,91,255,.15); }
    .direk-expense-scope .expense-primary:hover { background:#534be5; color:#fff; }
    .direk-expense-scope .expense-button:disabled { opacity:.6; cursor:wait; }
    .direk-expense-scope button:focus-visible, .direk-expense-scope a:focus-visible { outline:2px solid var(--expense-accent-text); outline-offset:3px; }
    .direk-expense-scope .expense-info { display:flex; align-items:flex-start; gap:9px; padding:13px 24px; background:var(--expense-inset); color:var(--expense-copy); font-size:12px; line-height:1.7; }
    .direk-expense-scope .expense-table-wrap { padding:0 22px 18px; }
    .direk-expense-scope .expense-table-controls, .direk-expense-scope .expense-table-bottom { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; padding:18px 2px; }
    .direk-expense-scope .expense-table-bottom { padding-bottom:0; }
    .direk-expense-scope .expense-table-scroll { overflow-x:auto; }
    #expenseTypeTable { width:100% !important; margin:0; border-collapse:separate !important; border-spacing:0; }
    #expenseTypeTable thead th { position:static; border:0; border-bottom:1px solid var(--expense-border); background:var(--expense-inset); color:var(--expense-copy); padding:13px 12px; font-size:10px; font-weight:650; text-transform:uppercase; letter-spacing:.06em; }
    #expenseTypeTable tbody td { padding:17px 12px; border:0; border-bottom:1px solid var(--expense-border); background:var(--expense-bg); color:var(--expense-copy); vertical-align:middle; font-size:12px; line-height:1.7; overflow-wrap:anywhere; }
    #expenseTypeTable tbody tr:hover td { background:var(--expense-inset); }
    #expenseTypeTable .expense-name { display:flex; align-items:center; gap:12px; min-width:160px; color:var(--expense-title); font-weight:600; }
    #expenseTypeTable .expense-name-icon { display:grid; place-items:center; width:38px; height:38px; flex-shrink:0; border:1px solid var(--expense-border); border-radius:12px; background:var(--expense-soft); color:var(--expense-accent-text); }
    #expenseTypeTable .expense-actions { white-space:nowrap; text-align:right; }
    #expenseTypeTable .expense-actions .btn-group { display:inline-flex; align-items:center; gap:6px; }
    #expenseTypeTable .expense-actions form { display:inline-flex; margin:0; }
    #expenseTypeTable .expense-actions button, #expenseTypeTable .expense-actions a { display:inline-flex; align-items:center; justify-content:center; min-width:40px; min-height:40px; border-radius:9px; text-decoration:none; }
    #expenseTypeTable .expense-actions button:hover, #expenseTypeTable .expense-actions a:hover { background:var(--expense-soft); }
    #expenseTypeTable .dataTables_empty { padding:48px 16px; text-align:center; }
    #expenseTypeTable_wrapper .dataTables_length, #expenseTypeTable_wrapper .dataTables_filter, #expenseTypeTable_wrapper .dataTables_info, #expenseTypeTable_wrapper .dataTables_paginate { float:none !important; margin:0; padding:0; color:var(--expense-copy); font-size:12px; }
    #expenseTypeTable_wrapper label { display:flex; align-items:center; gap:8px; margin:0; font-weight:400; color:var(--expense-copy); }
    #expenseTypeTable_wrapper input, #expenseTypeTable_wrapper select { min-height:40px; margin:0; border:1px solid var(--expense-border); border-radius:9px; background:var(--expense-bg); color:var(--expense-title); padding:8px 10px; outline:none; }
    #expenseTypeTable_wrapper .dataTables_filter input { width:250px; }
    #expenseTypeTable_wrapper .pagination { display:flex; gap:4px; flex-wrap:wrap; margin:0; }
    #expenseTypeTable_wrapper .page-link, #expenseTypeTable_wrapper .paginate_button { border:1px solid var(--expense-border) !important; border-radius:8px !important; background:var(--expense-bg) !important; color:var(--expense-copy) !important; font-size:12px; }
    #expenseTypeTable_wrapper li.paginate_button { padding:0 !important; border:0 !important; background:none !important; }
    #expenseTypeTable_wrapper .active .page-link, #expenseTypeTable_wrapper .paginate_button.current { border-color:#635bff !important; background:#635bff !important; color:#fff !important; }
    #expense-type-modal .modal-content { border:1px solid var(--expense-border); border-radius:20px; background:var(--expense-bg) !important; color:var(--expense-copy); overflow:hidden; }
    #expense-type-modal .modal-header { padding:23px 24px; border-color:var(--expense-border); background:linear-gradient(125deg,var(--expense-soft),var(--expense-bg) 75%); }
    #expense-type-modal .modal-title { color:var(--expense-title); margin:7px 0 0; font-size:22px; font-weight:650; letter-spacing:-.03em; }
    #expense-type-modal .modal-body { padding:24px; }
    #expense-type-modal .modal-footer { border-color:var(--expense-border); background:var(--expense-inset); padding:16px 24px; }
    #expense-type-modal[data-theme="dark"] .btn-close { filter:invert(1); }
    .direk-expense-scope .expense-field { display:block; box-sizing:border-box; width:100%; min-height:44px; border:1px solid var(--expense-border); border-radius:10px; padding:10px 12px; background:var(--expense-bg); color:var(--expense-title); font-size:13px; line-height:1.6; outline:none; }
    .direk-expense-scope textarea.expense-field { resize:vertical; }
    .direk-expense-scope .expense-label { display:block; margin-bottom:7px; color:var(--expense-title); font-size:12px; font-weight:600; }
    .direk-expense-scope .expense-field:focus, #expenseTypeTable_wrapper input:focus { border-color:#635bff; box-shadow:0 0 0 3px rgba(99,91,255,.12); }
    .direk-expense-scope .expense-error { margin:6px 0 0; color:#e55378; font-size:11px; line-height:1.6; }
    .direk-expense-scope .expense-alert { margin-bottom:18px; padding:14px 16px; border:1px solid #f4b8c7; border-radius:12px; background:#fff1f4; color:#aa2b4d; font-size:12px; line-height:1.8; }
    .direk-expense-scope[data-theme="dark"] .expense-alert { border-color:#663449; background:#352332; color:#ffb3c5; }
    .direk-expense-scope .expense-alert ul { margin:6px 0 0; padding-left:18px; }
    @media(max-width:639px) { .direk-expense-scope .expense-feature-icon { display:none; } .direk-expense-scope .expense-card-header { padding:20px 16px; } .direk-expense-scope .expense-table-wrap { padding:0 12px 12px; } #expenseTypeTable_wrapper .dataTables_filter { width:100%; } #expenseTypeTable_wrapper .dataTables_filter label { width:100%; } #expenseTypeTable_wrapper .dataTables_filter input { min-width:0; width:0; flex:1; } .direk-expense-scope .expense-details, .direk-expense-scope .expense-permission-tools { padding:18px; } .direk-expense-scope .role-permission-toggle { padding:18px; } .direk-expense-scope .role-permission-panel { padding:0 12px 14px; } .direk-expense-scope .expense-permission-search { width:100%; } }
    @media(prefers-reduced-motion:reduce) { .direk-expense-scope * { transition:none !important; } }

    #expense-type-modal .expense-type-modal-subtitle { margin:7px 0 0; color:var(--expense-copy); font-size:12px; line-height:1.7; }
    #expense-type-modal .staff-section-title { display:flex; align-items:center; gap:10px; margin-bottom:18px; }
    #expense-type-modal .staff-section-title h3 { margin:0; color:var(--expense-title); font-size:13px; font-weight:650; }
    #expense-type-modal .staff-section-divider { border-top:1px solid var(--expense-border); padding-top:22px; margin-top:22px; }
    #expenseTypeTable .staff-action-edit, #expenseTypeTable .staff-action-status { border:0; background:transparent; color:var(--expense-accent-text); }
    #direk-expense-types .staff-checking-enabled { background:#e8f7f0; color:#287452; }
    #direk-expense-types .staff-checking-disabled { background:var(--expense-inset); color:var(--expense-copy); }
    #direk-expense-types[data-theme="dark"] .staff-checking-enabled { background:#203c35; color:#9fe0bb; }
    #direk-expense-types #staffsLoadError { margin:18px 22px 0; }


    #direk-expense-types .expense-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:22px; }
    #direk-expense-types .expense-stat { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:20px; border:1px solid var(--expense-border); border-radius:16px; background:var(--expense-bg); }
    #direk-expense-types .expense-stat p { margin:0; color:var(--expense-copy); font-size:11px; }
    #direk-expense-types .expense-stat strong { display:block; margin-top:8px; color:var(--expense-title); font-size:24px; font-weight:650; letter-spacing:-.03em; }
    #direk-expense-types .expense-stat i { display:grid; place-items:center; width:38px; height:38px; border-radius:12px; background:var(--expense-soft); color:var(--expense-accent-text); }
    #direk-expense-types .expense-tools { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; padding:20px 22px; }
    #direk-expense-types .expense-filters { display:flex; flex-wrap:wrap; gap:10px; }
    #direk-expense-types .expense-filter { width:170px; }
    #direk-expense-types .expense-search { width:280px; max-width:100%; }
    #expenseTypeTable { min-width:850px; }
    #expenseTypeTable .expense-description { color:var(--expense-title); font-weight:600; }
    #direk-expense-types .expense-type-co { background:var(--expense-soft); color:var(--expense-accent-text); }
    #direk-expense-types .expense-type-mooe { background:#e8f0ff; color:#3463ae; }
    #direk-expense-types .expense-available { background:#e8f7f0; color:#287452; }
    #direk-expense-types[data-theme="dark"] .expense-type-mooe { background:#22364f; color:#aacbff; }
    #direk-expense-types[data-theme="dark"] .expense-available { background:#203c35; color:#9fe0bb; }
    #direk-expense-types .expense-positive { margin-bottom:18px; padding:14px 16px; border:1px solid var(--expense-border); border-radius:12px; background:var(--expense-soft); color:var(--expense-accent-text); font-size:12px; line-height:1.7; }
    #expenseTypeTable .expense-action-edit, #expenseTypeTable .expense-delete { background:transparent; border:0; color:var(--expense-accent-text); }
    #expenseTypeTable .expense-delete { color:#e55378; }
    #expenseTypeTable .expense-locked { display:inline-flex; align-items:center; justify-content:center; min-width:40px; min-height:40px; color:var(--expense-copy); }
    #direk-expense-types .expense-form-shell { max-width:1180px; margin:0 auto; }
    #direk-expense-types .expense-form-grid { display:grid; grid-template-columns:280px minmax(0,1fr); gap:24px; align-items:start; }
    #direk-expense-types .expense-form-aside { padding:24px; border:1px solid var(--expense-border); border-radius:20px; background:var(--expense-bg); }
    #direk-expense-types .expense-form-aside h3 { margin:18px 0 8px; color:var(--expense-title); font-size:16px; font-weight:650; }
    #direk-expense-types .expense-form-aside p { margin:0; font-size:12px; color:var(--expense-copy); line-height:1.8; }
    #direk-expense-types .expense-form-aside ul { display:grid; gap:12px; padding:20px 0 0; margin:20px 0 0; list-style:none; border-top:1px solid var(--expense-border); color:var(--expense-copy); font-size:12px; }
    #direk-expense-types .expense-form-aside li i { margin-right:8px; color:var(--expense-accent-text); }
    #direk-expense-types .expense-form-body { padding:24px; }
    #direk-expense-types .expense-field-help { margin:7px 0 0; color:var(--expense-copy); font-size:11px; line-height:1.7; }
    #direk-expense-types .expense-form-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:10px; padding:18px 24px; border-top:1px solid var(--expense-border); background:var(--expense-inset); }
    #direk-expense-types .expense-warning { display:flex; align-items:flex-start; gap:10px; margin-top:22px; padding:16px; border:1px solid var(--expense-border); border-radius:12px; background:var(--expense-inset); color:var(--expense-copy); font-size:12px; line-height:1.8; }
    #direk-expense-types .expense-warning strong { color:var(--expense-title); }
    @media(max-width:991px) { #direk-expense-types .expense-form-grid { grid-template-columns:1fr; } #direk-expense-types .expense-form-aside { display:none; } }
    @media(max-width:639px) { #direk-expense-types .expense-stats { grid-template-columns:repeat(2,minmax(0,1fr)); } #direk-expense-types .expense-stat { padding:16px; } #direk-expense-types .expense-stat i { display:none; } #direk-expense-types .expense-tools { padding:18px 12px; } #direk-expense-types .expense-search { width:100%; } #direk-expense-types .expense-filters { width:100%; } #direk-expense-types .expense-filter { flex:1; min-width:0; } #direk-expense-types .expense-form-body { padding:18px; } }

    #direk-expense-types .expense-empty { text-align:center; padding:44px 16px !important; color:var(--expense-copy); line-height:1.8; }
</style>
@endpush

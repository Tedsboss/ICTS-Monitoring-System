@push('css')
<style>
    .fp-modern { --fp-bg:#fff; --fp-inset:#f6f7fb; --fp-border:#e6e9f0; --fp-text:#172033; --fp-copy:#637086; --fp-accent:#635bff; --fp-soft:#eeedff; --fp-header:#f1f0fc; color:var(--fp-text); }
    .fp-modern[data-theme="dark"] { --fp-bg:#192130; --fp-inset:#151c29; --fp-border:#2c3546; --fp-text:#eef0f8; --fp-copy:#95a0b5; --fp-accent:#a19cff; --fp-soft:#2b2850; --fp-header:#242b40; }
    .fp-modern .fp-modern-heading { display:flex; align-items:center; justify-content:space-between; gap:22px; margin:6px 0 25px; }
    .fp-modern .fp-modern-eyebrow { display:block; color:var(--fp-accent); font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.12em; }
    .fp-modern .fp-modern-heading h2 { margin:11px 0 8px; color:var(--fp-text); font-size:30px; font-weight:700; letter-spacing:-.035em; line-height:1.2; }
    .fp-modern .fp-modern-heading p { margin:0; color:var(--fp-copy); font-size:13px; line-height:1.8; }
    .fp-modern .fp-modern-context { display:inline-flex; padding:9px 14px; border-radius:10px; background:var(--fp-soft); color:var(--fp-accent); font-size:12px; font-weight:600; white-space:nowrap; }
    .fp-modern .fp-modern-metrics { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; margin-bottom:22px; }
    .fp-modern .fp-modern-metrics article { background:var(--fp-bg); border:1px solid var(--fp-border); border-radius:16px; padding:22px; }
    .fp-modern .fp-modern-metrics span { color:var(--fp-copy); font-size:11px; }
    .fp-modern .fp-modern-metrics strong { display:block; color:var(--fp-text); font-size:24px; font-weight:700; margin:10px 0; overflow-wrap:anywhere; font-variant-numeric:tabular-nums; }
    .fp-modern .fp-modern-metrics p { color:var(--fp-copy); margin:0; font-size:11px; line-height:1.7; }
    .fp-modern [class~="bg-white"], .fp-modern [class~="bg-slate-50"] { background:var(--fp-bg) !important; }
    .fp-modern [class~="bg-slate-100"] { background:var(--fp-inset) !important; }
    .fp-modern [class~="border-slate-200"], .fp-modern [class~="border-slate-300"], .fp-modern [class~="border-slate-100"] { border-color:var(--fp-border) !important; }
    .fp-modern [class~="text-slate-900"], .fp-modern [class~="text-slate-800"], .fp-modern [class~="text-slate-700"] { color:var(--fp-text) !important; }
    .fp-modern [class~="text-slate-600"], .fp-modern [class~="text-slate-500"], .fp-modern [class~="text-slate-400"] { color:var(--fp-copy) !important; }
    .fp-modern [class~="bg-sky-50"], .fp-modern [class~="bg-sky-100"], .fp-modern [class~="bg-violet-100"] { background:var(--fp-soft) !important; }
    .fp-modern [class~="text-sky-700"], .fp-modern [class~="text-sky-800"], .fp-modern [class~="text-violet-700"] { color:var(--fp-accent) !important; }
    .fp-modern [class~="bg-sky-600"], #fp-builder #btnSavePlan, #fp-builder #btnSavePlan2, #targetDistributionModal #btnApplyTargetDistribution { background:#635bff !important; border-color:#635bff !important; color:#fff !important; }
    .fp-modern [class~="bg-sky-600"]:hover:not(:disabled), #fp-builder #btnSavePlan:hover:not(:disabled), #fp-builder #btnSavePlan2:hover:not(:disabled) { background:#5148e5 !important; }
    .fp-modern section[class~="rounded-2xl"] { border-radius:17px; box-shadow:0 3px 20px rgba(15,23,42,.025); }
    .fp-modern button, .fp-modern a[class~="rounded-lg"] { letter-spacing:0; }
    .fp-modern button:focus-visible, .fp-modern a:focus-visible { outline:2px solid var(--fp-accent); outline-offset:3px; }
    .fp-modern button:disabled { cursor:not-allowed; opacity:.55; box-shadow:none; }
    .fp-modern input:not([type="radio"]):not([type="checkbox"]), .fp-modern select, .fp-modern textarea { background:var(--fp-bg); color:var(--fp-text); border-color:var(--fp-border); }
    .fp-modern input:focus, .fp-modern select:focus, .fp-modern textarea:focus { border-color:var(--fp-accent); box-shadow:0 0 0 3px var(--fp-soft); }
    .fp-modern input:disabled, .fp-modern select:disabled, .fp-modern textarea:disabled { background:var(--fp-inset); color:var(--fp-copy); }
    #fp-builder .builder-table-wrap { border-color:var(--fp-border); border-radius:13px; background:var(--fp-bg); }
    #fp-builder #builderTable { color:var(--fp-text); background:var(--fp-bg); }
    #fp-builder #builderTable thead th { background:var(--fp-header); color:var(--fp-copy); border-color:var(--fp-border); font-size:10px; padding:12px 10px; letter-spacing:.04em; }
    #fp-builder #builderTable td { border-color:var(--fp-border); padding:10px 8px; }
    #fp-builder #builderBody tr[data-row-type="header"] td { background:var(--fp-header); }
    #fp-builder #builderBody tr[data-row-type="subheader"] td { background:var(--fp-inset); }
    #fp-builder .builder-input { min-height:36px; border-radius:8px; border-color:var(--fp-border); background:var(--fp-bg); color:var(--fp-text); font-size:11px; }
    #fp-builder .builder-input:disabled, #fp-builder .prexc-code-input[readonly] { background:var(--fp-inset); color:var(--fp-copy); }
    #fp-builder .builder-insert { background:var(--fp-bg); color:var(--fp-accent); border-color:var(--fp-border); border-radius:8px; }
    #fp-builder .personnel-toggle, #fp-builder .personnel-menu { border-color:var(--fp-border); background:var(--fp-bg); color:var(--fp-text); }
    #fp-builder .personnel-search-wrap { border-color:var(--fp-border); color:var(--fp-copy); }
    #fp-builder .personnel-option { color:var(--fp-text); }
    #fp-builder .personnel-option:hover { background:var(--fp-inset); }
    #fp-builder .personnel-chip { color:var(--fp-accent); background:var(--fp-soft); }
    #fp-builder .personnel-placeholder, #fp-builder .personnel-option-position { color:var(--fp-copy); }
    #fp-builder .builder-distribute { background:var(--fp-soft); color:var(--fp-accent); border-color:var(--fp-border); }
    #fp-builder #submissionReadinessPanel { border-left:4px solid var(--fp-accent) !important; }
    #fp-builder #builderStatusBadge { border:1px solid var(--fp-border); }
    #fp-report #fpTable { background:var(--fp-bg); color:var(--fp-text); }
    #fp-report #fpTable th, #fp-report #fpTable td { border-color:var(--fp-border); }
    #fp-report #fpTable tbody td { background:var(--fp-bg); }
    #fp-report #fpTable tbody tr:hover td { background:var(--fp-inset); }
    #fp-report #fpTable thead th { background:var(--fp-header); color:var(--fp-text); }
    #fp-report #fpTable .section-header-row td, #fp-report #fpTable .grand-total-row td { background:var(--fp-header); color:var(--fp-text); }
    #fp-report #fpTable .subheader-row td, #fp-report #fpTable .subtotal-row td { background:var(--fp-inset); color:var(--fp-text); }
    #fp-report .allocation-summary-table td, #fp-report .allocation-summary-table th { border-color:var(--fp-border); color:var(--fp-text); background:var(--fp-bg); }
    #fp-plans table { color:var(--fp-text); }
    #fp-plans table thead tr { background:var(--fp-inset) !important; }
    #fp-plans table th { font-size:10px; letter-spacing:.04em; color:var(--fp-copy); }
    #fp-plans table tbody td { border-color:var(--fp-border); vertical-align:middle; }
    #fp-plans table tbody tr:hover { background:var(--fp-inset); }
    #fp-plans .pagination .page-link { border-color:var(--fp-border); background:var(--fp-bg); color:var(--fp-copy); border-radius:8px; font-size:12px; }
    #fp-plans .pagination .active .page-link { background:#635bff; border-color:#635bff; color:#fff; }
    .fp-modern[data-theme="dark"] [class~="bg-emerald-50"], .fp-modern[data-theme="dark"] [class~="bg-emerald-100"] { background:#203c35 !important; }
    .fp-modern[data-theme="dark"] [class~="text-emerald-700"], .fp-modern[data-theme="dark"] [class~="text-emerald-800"] { color:#9fe0bb !important; }
    .fp-modern[data-theme="dark"] [class~="bg-amber-50"], .fp-modern[data-theme="dark"] [class~="bg-amber-100"] { background:#403424 !important; }
    .fp-modern[data-theme="dark"] [class~="text-amber-700"], .fp-modern[data-theme="dark"] [class~="text-amber-800"], .fp-modern[data-theme="dark"] [class~="text-amber-900"] { color:#f3d491 !important; }
    .fp-modern[data-theme="dark"] [class~="bg-rose-50"], .fp-modern[data-theme="dark"] [class~="bg-rose-100"] { background:#432934 !important; }
    .fp-modern[data-theme="dark"] [class~="text-rose-700"], .fp-modern[data-theme="dark"] [class~="text-rose-800"] { color:#ffadbe !important; }
    .fp-modern[data-theme="dark"] [class~="bg-cyan-50"], .fp-modern[data-theme="dark"] [class~="bg-cyan-100"] { background:#203b43 !important; }
    .fp-modern[data-theme="dark"] [class~="text-cyan-700"] { color:#a0e1f2 !important; }
    @media(max-width:900px) { .fp-modern .fp-modern-heading { align-items:flex-start; flex-direction:column; gap:14px; } .fp-modern .fp-modern-heading h2 { font-size:26px; } .fp-modern .fp-modern-metrics { grid-template-columns:1fr; } }
</style>
@endpush

@push('css')
<style>
    .wp-modern { --wp-bg:#fff; --wp-inset:#f6f7fb; --wp-border:#e6e9f0; --wp-text:#172033; --wp-copy:#637086; --wp-accent:#635bff; --wp-soft:#eeedff; --wp-header:#f1f0fc; color:var(--wp-text); }
    .wp-modern[data-theme="dark"] { --wp-bg:#192130; --wp-inset:#151c29; --wp-border:#2c3546; --wp-text:#eef0f8; --wp-copy:#95a0b5; --wp-accent:#a19cff; --wp-soft:#2b2850; --wp-header:#242b40; }
    .wp-modern .wp-modern-heading { display:flex; align-items:center; justify-content:space-between; gap:22px; margin:6px 0 25px; }
    .wp-modern .wp-modern-eyebrow { display:block; color:var(--wp-accent); font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.12em; }
    .wp-modern .wp-modern-heading h2 { margin:11px 0 8px; color:var(--wp-text); font-size:30px; font-weight:700; letter-spacing:-.035em; line-height:1.2; }
    .wp-modern .wp-modern-heading p { margin:0; color:var(--wp-copy); font-size:13px; line-height:1.8; }
    .wp-modern .wp-modern-context { display:inline-flex; padding:9px 14px; border-radius:10px; background:var(--wp-soft); color:var(--wp-accent); font-size:12px; font-weight:600; white-space:nowrap; }
    .wp-modern .wp-modern-metrics { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; margin-bottom:22px; }
    .wp-modern .wp-modern-metrics article { background:var(--wp-bg); border:1px solid var(--wp-border); border-radius:16px; padding:22px; }
    .wp-modern .wp-modern-metrics span { color:var(--wp-copy); font-size:11px; }
    .wp-modern .wp-modern-metrics strong { display:block; color:var(--wp-text); font-size:24px; font-weight:700; margin:10px 0; overflow-wrap:anywhere; font-variant-numeric:tabular-nums; }
    .wp-modern .wp-modern-metrics p { color:var(--wp-copy); margin:0; font-size:11px; line-height:1.7; }
    .wp-modern [class~="bg-white"], .wp-modern [class~="bg-slate-50"] { background:var(--wp-bg) !important; }
    .wp-modern [class~="bg-slate-100"] { background:var(--wp-inset) !important; }
    .wp-modern [class~="border-slate-200"], .wp-modern [class~="border-slate-300"], .wp-modern [class~="border-slate-100"] { border-color:var(--wp-border) !important; }
    .wp-modern [class~="text-slate-900"], .wp-modern [class~="text-slate-800"], .wp-modern [class~="text-slate-700"] { color:var(--wp-text) !important; }
    .wp-modern [class~="text-slate-600"], .wp-modern [class~="text-slate-500"], .wp-modern [class~="text-slate-400"] { color:var(--wp-copy) !important; }
    .wp-modern [class~="bg-sky-50"], .wp-modern [class~="bg-sky-100"], .wp-modern [class~="bg-violet-100"] { background:var(--wp-soft) !important; }
    .wp-modern [class~="text-sky-700"], .wp-modern [class~="text-sky-800"], .wp-modern [class~="text-violet-700"] { color:var(--wp-accent) !important; }
    .wp-modern [class~="bg-sky-600"], #wp-builder #btnSavePlan, #wp-builder #btnSavePlanBottom, #wp-builder .save-button { background:#635bff !important; border-color:#635bff !important; color:#fff !important; }
    .wp-modern [class~="bg-sky-600"]:hover:not(:disabled), #wp-builder #btnSavePlan:hover:not(:disabled), #wp-builder #btnSavePlanBottom:hover:not(:disabled) { background:#5148e5 !important; }
    .wp-modern section[class~="rounded-2xl"] { border-radius:17px; box-shadow:0 3px 20px rgba(15,23,42,.025); }
    .wp-modern button, .wp-modern a[class~="rounded-lg"] { letter-spacing:0; }
    .wp-modern button:focus-visible, .wp-modern a:focus-visible { outline:2px solid var(--wp-accent); outline-offset:3px; }
    .wp-modern button:disabled { cursor:not-allowed; opacity:.55; box-shadow:none; }
    .wp-modern input:not([type="radio"]):not([type="checkbox"]), .wp-modern select, .wp-modern textarea { background:var(--wp-bg); color:var(--wp-text); border-color:var(--wp-border); }
    .wp-modern input:focus, .wp-modern select:focus, .wp-modern textarea:focus { border-color:var(--wp-accent); box-shadow:0 0 0 3px var(--wp-soft); }
    .wp-modern input:disabled, .wp-modern select:disabled, .wp-modern textarea:disabled { background:var(--wp-inset); color:var(--wp-copy); }
    #wp-builder .work-plan-builder-wrap { border-radius:13px; border:1px solid var(--wp-border); background:var(--wp-bg); }
    #wp-builder .work-plan-builder-table { color:var(--wp-text); background:var(--wp-bg); }
    #wp-builder .work-plan-builder-table th { background:var(--wp-header); color:var(--wp-copy); border-color:var(--wp-border); padding:13px 10px; font-size:10px; text-transform:uppercase; letter-spacing:.04em; }
    #wp-builder .work-plan-builder-table td { background:var(--wp-bg); border-color:var(--wp-border); padding:12px 10px; }
    #wp-builder .builder-input, #wp-builder .signatory-input { color:var(--wp-text); background:var(--wp-bg); border-color:var(--wp-border); border-radius:8px; }
    #wp-builder .builder-input:disabled, #wp-builder .signatory-input:disabled { color:var(--wp-copy); background:var(--wp-inset); }
    #wp-builder .builder-input:focus, #wp-builder .signatory-input:focus { border-color:var(--wp-accent); box-shadow:0 0 0 3px var(--wp-soft); }
    #wp-builder .target-entry { border-color:var(--wp-border); border-radius:12px; padding:13px; background:var(--wp-inset); }
    #wp-builder .target-month-heading { color:var(--wp-copy); }
    #wp-builder .target-month-option { background:var(--wp-bg); border-color:var(--wp-border); color:var(--wp-copy); border-radius:7px; }
    #wp-builder .target-month-option input { accent-color:#635bff; }
    #wp-builder .target-month-action, #wp-builder .add-target, #wp-builder .add-row-button, #wp-builder .sync-button { background:var(--wp-soft); color:var(--wp-accent); border-color:var(--wp-border); border-radius:8px; }
    #wp-builder .target-month-action:hover:not(:disabled), #wp-builder .add-target:hover:not(:disabled), #wp-builder .sync-button:hover:not(:disabled) { background:var(--wp-header); }
    #wp-builder .structural-row td { background:var(--wp-inset); }
    #wp-builder .structural-row.header-row td { background:var(--wp-header); }
    #wp-builder .structural-title, #wp-builder .row-type-label, #wp-builder .fp-readonly-value { color:var(--wp-text); }
    #wp-builder .fp-prexc-code { color:var(--wp-copy); }
    #wp-builder .fp-source-badge { border-color:var(--wp-border); color:var(--wp-accent); background:var(--wp-soft); }
    #wp-builder .signatory-card { background:var(--wp-inset); border-color:var(--wp-border); border-radius:12px; }
    #wp-builder .signatory-title { color:var(--wp-copy); }
    #wp-report .work-plan-table { color:var(--wp-text); background:var(--wp-bg); }
    #wp-report .work-plan-table th { background:var(--wp-header); color:var(--wp-text); border-color:var(--wp-border); }
    #wp-report .work-plan-table td { background:var(--wp-bg); color:var(--wp-text); border-color:var(--wp-border); }
    #wp-report .work-plan-table .section-header-row td { background:var(--wp-header); color:var(--wp-text); }
    #wp-report .work-plan-table .subheader-row td { background:var(--wp-inset); color:var(--wp-text); }
    #wp-report .signatory-label, #wp-report .signatory-position { color:var(--wp-copy); }
    #wp-report .signatory-name { color:var(--wp-text); border-color:var(--wp-border); }
    #wp-plans table { color:var(--wp-text); }
    #wp-plans table thead tr { background:var(--wp-inset) !important; }
    #wp-plans table th { font-size:10px; letter-spacing:.04em; color:var(--wp-copy); }
    #wp-plans table tbody td { border-color:var(--wp-border); vertical-align:middle; }
    #wp-plans table tbody tr:hover { background:var(--wp-inset); }
    #wp-plans .pagination .page-link { border-color:var(--wp-border); background:var(--wp-bg); color:var(--wp-copy); border-radius:8px; font-size:12px; }
    #wp-plans .pagination .active .page-link { background:#635bff; border-color:#635bff; color:#fff; }
    .wp-modern[data-theme="dark"] [class~="bg-emerald-50"], .wp-modern[data-theme="dark"] [class~="bg-emerald-100"] { background:#203c35 !important; }
    .wp-modern[data-theme="dark"] [class~="text-emerald-700"], .wp-modern[data-theme="dark"] [class~="text-emerald-800"] { color:#9fe0bb !important; }
    .wp-modern[data-theme="dark"] [class~="bg-amber-50"], .wp-modern[data-theme="dark"] [class~="bg-amber-100"] { background:#403424 !important; }
    .wp-modern[data-theme="dark"] [class~="text-amber-700"], .wp-modern[data-theme="dark"] [class~="text-amber-800"], .wp-modern[data-theme="dark"] [class~="text-amber-900"] { color:#f3d491 !important; }
    .wp-modern[data-theme="dark"] [class~="bg-rose-50"], .wp-modern[data-theme="dark"] [class~="bg-rose-100"] { background:#432934 !important; }
    .wp-modern[data-theme="dark"] [class~="text-rose-700"], .wp-modern[data-theme="dark"] [class~="text-rose-800"] { color:#ffadbe !important; }
    .wp-modern[data-theme="dark"] [class~="bg-cyan-50"], .wp-modern[data-theme="dark"] [class~="bg-cyan-100"] { background:#203b43 !important; }
    .wp-modern[data-theme="dark"] [class~="text-cyan-700"] { color:#a0e1f2 !important; }
    @media(max-width:900px) { .wp-modern .wp-modern-heading { align-items:flex-start; flex-direction:column; gap:14px; } .wp-modern .wp-modern-heading h2 { font-size:26px; } .wp-modern .wp-modern-metrics { grid-template-columns:1fr; } }
</style>
@endpush

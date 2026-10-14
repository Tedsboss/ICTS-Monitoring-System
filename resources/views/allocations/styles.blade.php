@push('css')
<style>
    #direk-allocations { --allocation-bg:#fff; --allocation-inset:#f6f7fb; --allocation-border:#e6e9f0; --allocation-text:#172033; --allocation-copy:#637086; --allocation-accent:#635bff; --allocation-soft:#eeedff; --allocation-green:#167552; --allocation-red:#c33b54; padding:24px; color:var(--allocation-text); }
    #direk-allocations[data-theme="dark"] { --allocation-bg:#192130; --allocation-inset:#151c29; --allocation-border:#2c3546; --allocation-text:#eef0f8; --allocation-copy:#95a0b5; --allocation-accent:#a19cff; --allocation-soft:#2b2850; --allocation-green:#73d6b1; --allocation-red:#fb94a5; }
    #direk-allocations *, #direk-allocations *::before, #direk-allocations *::after { box-sizing:border-box; }
    #direk-allocations [hidden] { display:none !important; }
    #direk-allocations h2, #direk-allocations h3, #direk-allocations h4 { color:var(--allocation-text); margin:0; letter-spacing:-.025em; }
    #direk-allocations .allocation-heading { display:flex; justify-content:space-between; align-items:center; gap:24px; margin-bottom:26px; }
    #direk-allocations .allocation-heading h2 { font-size:30px; font-weight:700; }
    #direk-allocations .allocation-heading p { margin:10px 0 0; font-size:13px; line-height:1.7; color:var(--allocation-copy); }
    #direk-allocations .allocation-eyebrow, #direk-allocations .allocation-step { display:block; font-size:10px; font-weight:700; color:var(--allocation-accent); text-transform:uppercase; letter-spacing:.12em; margin-bottom:10px; }
    #direk-allocations .allocation-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:10px 15px; border:1px solid var(--allocation-border); border-radius:10px; background:var(--allocation-bg); color:var(--allocation-text); font-size:12px; font-weight:600; text-decoration:none; white-space:nowrap; cursor:pointer; transition:background .15s; }
    #direk-allocations .allocation-button:hover { background:var(--allocation-soft); color:var(--allocation-accent); }
    #direk-allocations .allocation-primary { background:#635bff; border-color:#635bff; color:white; box-shadow:0 3px 10px rgba(99,91,255,.16); }
    #direk-allocations .allocation-primary:hover { background:#5148e5; color:white; }
    #direk-allocations .allocation-remove { color:var(--allocation-red); background:transparent; }
    #direk-allocations .allocation-button:disabled { opacity:.5; cursor:not-allowed; box-shadow:none; }
    #direk-allocations .allocation-button:focus-visible, #direk-allocations summary:focus-visible { outline:2px solid var(--allocation-accent); outline-offset:3px; }
    #direk-allocations .allocation-panel { border:1px solid var(--allocation-border); border-radius:17px; background:var(--allocation-bg); overflow:hidden; margin-bottom:20px; box-shadow:0 3px 20px rgba(15,23,42,.025); }
    #direk-allocations .allocation-panel-heading { display:flex; align-items:center; justify-content:space-between; gap:18px; padding:23px; border-bottom:1px solid var(--allocation-border); }
    #direk-allocations .allocation-panel h3 { font-size:16px; font-weight:700; }
    #direk-allocations .allocation-panel-heading p { color:var(--allocation-copy); font-size:12px; margin:7px 0 0; line-height:1.7; }
    #direk-allocations .allocation-panel-body { padding:24px; }
    #direk-allocations .allocation-editor-grid { display:grid; grid-template-columns:minmax(0,1fr) 320px; gap:24px; align-items:start; }
    #direk-allocations .allocation-main { min-width:0; }
    #direk-allocations .allocation-field-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:22px; }
    #direk-allocations .allocation-wide { grid-column:1/-1; }
    #direk-allocations label { display:block; font-size:12px; font-weight:600; color:var(--allocation-text); margin:0; }
    #direk-allocations label > span { color:var(--allocation-accent); }
    #direk-allocations select, #direk-allocations input[type="number"] { display:block; width:100%; min-width:0; min-height:44px; border:1px solid var(--allocation-border); border-radius:10px; background:var(--allocation-bg); color:var(--allocation-text); padding:11px 12px; font-size:12px; outline:0; }
    #direk-allocations label select, #direk-allocations .allocation-money-input { margin-top:8px; }
    #direk-allocations input:focus, #direk-allocations select:focus { border-color:var(--allocation-accent); box-shadow:0 0 0 3px var(--allocation-soft); }
    #direk-allocations select:disabled { background:var(--allocation-inset); color:var(--allocation-copy); cursor:not-allowed; }
    #direk-allocations small { display:block; color:var(--allocation-copy); font-size:10px; line-height:1.7; margin-top:7px; }
    #direk-allocations .allocation-money-input { position:relative; }
    #direk-allocations .allocation-money-input > span { position:absolute; top:13px; left:13px; color:var(--allocation-copy); font-size:13px; pointer-events:none; }
    #direk-allocations .allocation-money-input input { padding-left:32px; }
    #direk-allocations .allocation-summary { position:sticky; top:24px; }
    #direk-allocations .allocation-help { font-size:12px; color:var(--allocation-copy); line-height:1.8; margin:12px 0; }
    #direk-allocations .allocation-summary-label { display:block; font-size:10px; color:var(--allocation-copy); font-weight:600; }
    #direk-allocations .allocation-summary .allocation-summary-label { margin-top:24px; }
    #direk-allocations .allocation-summary-total { display:block; font-size:26px; font-weight:700; color:var(--allocation-accent); margin:8px 0 22px; overflow-wrap:anywhere; font-variant-numeric:tabular-nums; }
    #direk-allocations .allocation-summary-line { display:flex; justify-content:space-between; gap:12px; border-top:1px solid var(--allocation-border); padding:13px 0; font-size:11px; color:var(--allocation-copy); }
    #direk-allocations .allocation-summary-line strong { color:var(--allocation-text); text-align:right; font-variant-numeric:tabular-nums; }
    #direk-allocations .allocation-summary-note { border-top:1px solid var(--allocation-border); padding-top:15px; font-size:11px; margin-bottom:0; }
    #direk-allocations .allocation-status { padding:13px; font-size:11px; line-height:1.8; background:var(--allocation-inset); border-radius:9px; }
    #direk-allocations .allocation-status[data-state="error"] { color:var(--allocation-red); border:1px solid var(--allocation-red); }
    #direk-allocations .allocation-status[data-state="ready"] { color:var(--allocation-green); }
    #direk-allocations .allocation-table-wrap { overflow-x:auto; }
    #direk-allocations .allocation-table { width:100%; border-collapse:collapse; color:var(--allocation-text); font-size:12px; }
    #direk-allocations .allocation-table th { background:var(--allocation-inset); font-size:10px; font-weight:700; color:var(--allocation-copy); text-transform:uppercase; letter-spacing:.04em; padding:15px 20px; text-align:left; border-bottom:1px solid var(--allocation-border); }
    #direk-allocations .allocation-table td { padding:20px; border-bottom:1px solid var(--allocation-border); vertical-align:top; }
    #direk-allocations .allocation-table tbody tr:last-child td { border-bottom:0; }
    #direk-allocations .allocation-expense-table { min-width:680px; }
    #direk-allocations .allocation-expense-table td { vertical-align:middle; }
    #direk-allocations .allocation-expense-table td:nth-child(2) { width:50%; }
    #direk-allocations .allocation-expense-table tfoot { background:var(--allocation-inset); font-weight:700; border-top:1px solid var(--allocation-border); }
    #direk-allocations .expense-row-number { color:var(--allocation-copy); font-variant-numeric:tabular-nums; }
    #direk-allocations .allocation-number { font-variant-numeric:tabular-nums; white-space:nowrap; }
    #direk-allocations .allocation-empty { text-align:center; padding:45px 24px; }
    #direk-allocations .allocation-empty > i { display:grid; place-items:center; width:52px; height:52px; margin:0 auto 17px; border-radius:15px; background:var(--allocation-soft); color:var(--allocation-accent); font-size:22px; }
    #direk-allocations .allocation-empty h4 { font-size:15px; font-weight:600; }
    #direk-allocations .allocation-empty p { color:var(--allocation-copy); font-size:12px; line-height:1.7; margin:10px 0 18px; }
    #direk-allocations .allocation-notice { background:var(--allocation-soft); border:1px solid var(--allocation-border); border-radius:11px; color:var(--allocation-text); padding:15px 18px; margin-bottom:20px; font-size:12px; line-height:1.8; }
    #direk-allocations .allocation-notice ul { margin:6px 0 0; padding-left:20px; }
    #direk-allocations .allocation-danger { color:var(--allocation-red); border-color:var(--allocation-red); background:var(--allocation-bg); }
    #direk-allocations .allocation-success { color:var(--allocation-green); background:var(--allocation-bg); }
    #direk-allocations .allocation-form-actions { display:flex; justify-content:flex-end; gap:12px; margin:24px 0; }
    #direk-allocations .allocation-metrics { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; }
    #direk-allocations .allocation-metrics article { padding:22px; }
    #direk-allocations .allocation-metrics strong { display:block; margin:10px 0; font-size:23px; font-weight:700; overflow-wrap:anywhere; font-variant-numeric:tabular-nums; }
    #direk-allocations .allocation-metrics p { margin:0; color:var(--allocation-copy); font-size:10px; line-height:1.9; }
    #direk-allocations .allocation-filter-grid { display:flex; align-items:flex-end; flex-wrap:wrap; gap:15px; }
    #direk-allocations .allocation-filter-grid label { flex:1 1 170px; }
    #direk-allocations .allocation-filter-actions { display:flex; gap:8px; }
    #direk-allocations .allocation-badge { display:inline-flex; padding:5px 9px; border-radius:20px; background:var(--allocation-soft); color:var(--allocation-accent); font-size:10px; font-weight:600; white-space:nowrap; }
    #direk-allocations .allocation-badge-danger { color:var(--allocation-red); background:var(--allocation-inset); }
    #direk-allocations .allocation-directory { min-width:1140px; }
    #direk-allocations .allocation-directory tbody tr:hover { background:var(--allocation-inset); }
    #direk-allocations .allocation-directory td:first-child { min-width:160px; max-width:210px; }
    #direk-allocations .allocation-directory td:nth-child(2) { min-width:240px; max-width:310px; }
    #direk-allocations .allocation-directory td > strong { display:block; font-weight:600; line-height:1.7; }
    #direk-allocations .allocation-money-cell { white-space:nowrap; font-variant-numeric:tabular-nums; }
    #direk-allocations .allocation-context-tags { display:flex; gap:6px; margin:10px 0 0; flex-wrap:wrap; }
    #direk-allocations .allocation-context-tags span { background:var(--allocation-soft); color:var(--allocation-accent); padding:4px 8px; border-radius:6px; font-size:10px; font-weight:600; }
    #direk-allocations .allocation-expense-details { margin-top:13px; }
    #direk-allocations .allocation-expense-details summary { cursor:pointer; color:var(--allocation-accent); font-size:10px; }
    #direk-allocations .allocation-expense-details > div { border:1px solid var(--allocation-border); background:var(--allocation-bg); border-radius:8px; padding:12px; margin-top:10px; }
    #direk-allocations .allocation-expense-details p { margin:0 0 9px; font-size:10px; line-height:1.7; }
    #direk-allocations .allocation-expense-details p:last-child { margin:0; }
    #direk-allocations .allocation-expense-details b { display:block; font-variant-numeric:tabular-nums; }
    #direk-allocations .allocation-utilization { min-width:130px; }
    #direk-allocations .allocation-utilization > strong { display:block; margin-bottom:9px; font-size:15px; }
    #direk-allocations .allocation-progress { background:var(--allocation-border); height:5px; border-radius:10px; overflow:hidden; margin-top:12px; }
    #direk-allocations .allocation-progress span { display:block; height:100%; background:var(--allocation-accent); }
    #direk-allocations .allocation-row-actions { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
    #direk-allocations .allocation-row-actions .allocation-button { font-size:11px; min-height:36px; padding:8px 10px; }
    #direk-allocations .allocation-locked { color:var(--allocation-copy); font-size:10px; display:inline-flex; align-items:center; gap:5px; }
    #direk-allocations .allocation-directory-note { color:var(--allocation-copy); padding:17px 24px; font-size:11px; margin:0; line-height:1.8; border-top:1px solid var(--allocation-border); }
    #direk-allocations .allocation-accent { color:var(--allocation-accent); }
    #direk-allocations .allocation-positive { color:var(--allocation-green); }
    #direk-allocations .allocation-negative { color:var(--allocation-red); }
    @media(max-width:1200px) { #direk-allocations .allocation-metrics { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media(max-width:1000px) { #direk-allocations .allocation-editor-grid { grid-template-columns:1fr; } #direk-allocations .allocation-summary { position:static; } }
    @media(max-width:600px) { #direk-allocations { padding:16px; } #direk-allocations .allocation-heading { align-items:flex-start; flex-direction:column; gap:16px; } #direk-allocations .allocation-heading h2 { font-size:25px; } #direk-allocations .allocation-panel-heading { flex-wrap:wrap; padding:19px; } #direk-allocations .allocation-panel-body { padding:19px; } #direk-allocations .allocation-field-grid { grid-template-columns:1fr; } #direk-allocations .allocation-metrics { gap:10px; } #direk-allocations .allocation-metrics article { padding:16px; } #direk-allocations .allocation-metrics strong { font-size:19px; } }
    @media(prefers-reduced-motion:reduce) { #direk-allocations .allocation-button { transition:none; } }
/* Directory depth styling; editor layout and control contracts remain in the base rules. */
#direk-allocations{--allocation-edge:#d7d5ea}
#direk-allocations[data-theme="dark"]{--allocation-edge:#101624}
#direk-allocations.allocation-directory-page{padding:25px 27px 35px}
#direk-allocations.allocation-directory-page .allocation-heading{position:relative;isolation:isolate;padding:32px 34px;min-height:175px;border:1px solid #66518b;border-radius:24px;background:radial-gradient(ellipse at 80% 10%,rgba(165,117,229,.24),transparent 58%),linear-gradient(120deg,#25253f,#392953);box-shadow:inset 0 1px 0 rgba(235,211,255,.2),0 5px 0 #231c36,0 17px 30px rgba(41,29,77,.12);overflow:hidden;margin-bottom:29px}
#direk-allocations.allocation-directory-page .allocation-heading::after{content:"";position:absolute;z-index:-1;right:115px;top:-90px;width:290px;height:290px;border:1px solid rgba(227,192,255,.11);border-radius:50%;box-shadow:0 0 0 28px rgba(225,198,255,.025),0 0 0 64px rgba(225,198,255,.015);pointer-events:none}
#direk-allocations.allocation-directory-page .allocation-heading h2{color:#f5efff;font-size:32px;line-height:1.2;letter-spacing:-.9px;font-weight:650}
#direk-allocations.allocation-directory-page .allocation-heading .allocation-eyebrow{font-size:9px;color:#ccb6ee;letter-spacing:.14em}
#direk-allocations.allocation-directory-page .allocation-heading p{color:#c8bddb;font-size:12px;max-width:470px;line-height:1.85}
#direk-allocations.allocation-directory-page .allocation-heading .allocation-primary{background:#dfd0fa;border-color:#dfd0fa;color:#3c275e;box-shadow:0 4px 0 #a891ce,0 10px 20px rgba(20,13,38,.15);min-height:45px}
#direk-allocations.allocation-directory-page .allocation-heading .allocation-primary:hover{background:#eee3ff;color:#3c275e}
#direk-allocations .allocation-panel{border-radius:19px;box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 4px 0 var(--allocation-edge),0 12px 25px rgba(36,28,70,.035);margin-bottom:24px}
#direk-allocations .allocation-button{border-radius:10px;min-height:44px;transition:background .15s ease,box-shadow .15s ease}
#direk-allocations .allocation-primary{background:linear-gradient(135deg,#9275e5,#7257c8);border-color:#8163d7;box-shadow:0 3px 0 #574096,0 8px 15px rgba(78,43,142,.12)}
#direk-allocations .allocation-primary:hover{background:linear-gradient(135deg,#8467d8,#654bbb)}
#direk-allocations.allocation-directory-page .allocation-metrics{gap:17px;margin-bottom:8px}
#direk-allocations.allocation-directory-page .allocation-metrics article{position:relative;padding:23px;min-height:162px;background:linear-gradient(145deg,var(--allocation-bg),var(--allocation-inset));overflow:hidden}
#direk-allocations.allocation-directory-page .allocation-metrics article::before{content:"";position:absolute;left:21px;right:21px;top:0;height:2px;background:linear-gradient(90deg,var(--allocation-accent),transparent);opacity:.65}
#direk-allocations.allocation-directory-page .allocation-metric-symbol{position:absolute;top:20px;right:20px;font-size:18px;color:var(--allocation-accent);opacity:.65}
#direk-allocations.allocation-directory-page .allocation-metrics article:nth-child(4) .allocation-metric-symbol{color:var(--allocation-green)}
#direk-allocations.allocation-directory-page .allocation-metrics .allocation-summary-label{font-size:10px;font-weight:600;letter-spacing:.01em;padding-right:26px}
#direk-allocations.allocation-directory-page .allocation-metrics strong{font-size:clamp(20px,1.75vw,27px);font-weight:650;letter-spacing:-.5px;margin:19px 0 12px;line-height:1.25}
#direk-allocations.allocation-directory-page .allocation-metrics p{font-size:10px;line-height:1.85}
#direk-allocations .allocation-panel-heading{padding:23px 25px;background:linear-gradient(135deg,var(--allocation-bg),var(--allocation-inset));border-color:var(--allocation-border)}
#direk-allocations .allocation-panel-heading h3{font-size:16px;font-weight:650;letter-spacing:-.35px}
#direk-allocations .allocation-panel-heading p{font-size:11px;color:var(--allocation-copy);line-height:1.8}
#direk-allocations .allocation-panel-body{padding:23px 25px}
#direk-allocations .allocation-filter-grid{gap:17px}
#direk-allocations .allocation-filter-grid label{font-size:11px;font-weight:600;flex-basis:175px}
#direk-allocations .allocation-filter-grid select{font-size:12px;min-height:46px;border-radius:11px;background:var(--allocation-inset);padding-right:20px}
#direk-allocations .allocation-filter-actions{gap:10px}
#direk-allocations .allocation-badge{font-size:10px;padding:6px 9px;border-radius:8px;border:1px solid var(--allocation-border)}
#direk-allocations.allocation-directory-page .allocation-directory th{padding:14px 20px;font-size:9px;font-weight:650;letter-spacing:.07em}
#direk-allocations.allocation-directory-page .allocation-directory td{padding:22px 20px;line-height:1.7}
#direk-allocations.allocation-directory-page .allocation-directory td>strong{font-size:12px;font-weight:650}
#direk-allocations.allocation-directory-page .allocation-directory .allocation-money-cell>strong{font-size:13px;font-weight:650}
#direk-allocations .allocation-context-tags span{border:1px solid var(--allocation-border);border-radius:7px;font-size:9px;padding:4px 7px}
#direk-allocations .allocation-expense-details summary{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--allocation-border);border-radius:7px;padding:5px 8px;background:var(--allocation-inset);font-size:10px;list-style:none}
#direk-allocations .allocation-expense-details summary::-webkit-details-marker{display:none}
#direk-allocations .allocation-expense-details summary::before{content:"+";font-size:12px;font-weight:600}
#direk-allocations .allocation-expense-details[open] summary::before{content:"\2212"}
#direk-allocations .allocation-expense-details>div{border-radius:10px;box-shadow:0 4px 10px rgba(25,19,50,.04)}
#direk-allocations .allocation-expense-details p{font-size:10px;overflow-wrap:anywhere}
#direk-allocations .allocation-utilization{min-width:135px}
#direk-allocations .allocation-utilization>strong{font-size:17px;line-height:1.2;margin-bottom:11px;font-variant-numeric:tabular-nums}
#direk-allocations .allocation-progress{height:6px;border-radius:9px;margin-top:12px;background:var(--allocation-border)}
#direk-allocations .allocation-progress span{border-radius:9px;background:linear-gradient(90deg,#b39bea,#8164da)}
#direk-allocations .allocation-utilization[data-state="over"] .allocation-progress span{background:var(--allocation-red)}
#direk-allocations .allocation-utilization[data-state="near"] .allocation-progress span{background:#d7a85a}
#direk-allocations .allocation-utilization[data-state="near"] .allocation-badge{background:var(--allocation-inset);color:#a77b37}
#direk-allocations[data-theme="dark"] .allocation-utilization[data-state="near"] .allocation-badge{color:#edc685}
#direk-allocations .allocation-row-actions .allocation-button{min-height:39px;font-size:10px;border-radius:8px}
#direk-allocations .allocation-row-actions .allocation-remove{border-color:rgba(215,86,123,.3)}
#direk-allocations .allocation-locked{padding:5px 7px;border:1px solid var(--allocation-border);background:var(--allocation-inset);border-radius:7px;font-size:9px}
#direk-allocations .allocation-directory-note{background:var(--allocation-inset);font-size:10px;padding:18px 25px;line-height:1.85}
#direk-allocations .allocation-empty{padding:49px 27px}
#direk-allocations .allocation-empty>i{background:var(--allocation-soft);border:1px solid var(--allocation-border);box-shadow:0 3px 0 var(--allocation-edge)}
@media(max-width:1200px){#direk-allocations.allocation-directory-page .allocation-metrics strong{font-size:25px}}
@media(max-width:600px){#direk-allocations.allocation-directory-page{padding:18px 15px 28px}#direk-allocations.allocation-directory-page .allocation-heading{padding:26px 24px;min-height:0;gap:20px}#direk-allocations.allocation-directory-page .allocation-heading h2{font-size:28px}#direk-allocations.allocation-directory-page .allocation-metrics{gap:12px}#direk-allocations.allocation-directory-page .allocation-metrics article{padding:19px 16px;min-height:155px}#direk-allocations.allocation-directory-page .allocation-metrics strong{font-size:21px}#direk-allocations.allocation-directory-page .allocation-metric-symbol{right:14px;top:17px;font-size:15px}#direk-allocations .allocation-panel-heading,#direk-allocations .allocation-panel-body{padding:20px}#direk-allocations .allocation-filter-actions{width:100%}#direk-allocations .allocation-filter-actions .allocation-button{flex:1}}
@media(max-width:380px){#direk-allocations.allocation-directory-page .allocation-metrics{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){#direk-allocations .allocation-button{transition:none!important}}

/* Create/edit presentation, shared across the existing form modes. */
#direk-allocations.allocation-editor-page{padding:25px 27px 35px}
#direk-allocations.allocation-editor-page .allocation-heading{position:relative;padding:30px 34px;min-height:165px;border:1px solid #66518b;border-radius:24px;background:radial-gradient(ellipse at 80% 10%,rgba(165,117,229,.24),transparent 58%),linear-gradient(120deg,#25253f,#392953);box-shadow:inset 0 1px 0 rgba(235,211,255,.2),0 5px 0 #231c36,0 17px 30px rgba(41,29,77,.12);overflow:hidden;margin-bottom:29px}
#direk-allocations.allocation-editor-page .allocation-heading h2{color:#f5efff;font-size:31px;line-height:1.25;font-weight:650;letter-spacing:-.8px}
#direk-allocations.allocation-editor-page .allocation-heading .allocation-eyebrow{color:#ccb6ee;font-size:9px;letter-spacing:.14em}
#direk-allocations.allocation-editor-page .allocation-heading p{color:#c8bddb;font-size:12px;line-height:1.85;max-width:510px}
#direk-allocations.allocation-editor-page .allocation-heading>.allocation-button{border-color:#76608c;background:rgba(239,218,255,.06);color:#eee3fb;min-height:44px;font-size:11px}
#direk-allocations.allocation-editor-page .allocation-heading>.allocation-button:hover{background:rgba(239,218,255,.12)}
#direk-allocations .allocation-workflow{display:flex;align-items:center;gap:16px;margin:0 0 24px;padding:14px 18px;border:1px solid var(--allocation-border);border-radius:13px;background:var(--allocation-bg);box-shadow:0 3px 0 var(--allocation-edge)}
#direk-allocations .allocation-workflow>span{display:inline-flex;align-items:center;gap:9px;font-size:11px;color:var(--allocation-copy)}
#direk-allocations .allocation-workflow b{display:grid;place-items:center;width:24px;height:24px;border:1px solid var(--allocation-border);border-radius:7px;background:var(--allocation-soft);color:var(--allocation-accent);font-size:9px}
#direk-allocations .allocation-workflow>i{color:var(--allocation-copy);opacity:.6;font-size:12px}
#direk-allocations.allocation-editor-page .allocation-editor-grid{grid-template-columns:minmax(0,1fr) 310px;gap:25px}
#direk-allocations.allocation-editor-page .allocation-panel-heading{padding:22px 25px;align-items:center}
#direk-allocations.allocation-editor-page .allocation-step{font-size:9px;letter-spacing:.13em;margin-bottom:8px}
#direk-allocations.allocation-editor-page .allocation-panel-heading h3{font-size:17px;letter-spacing:-.35px}
#direk-allocations.allocation-editor-page .allocation-panel-body{padding:25px}
#direk-allocations.allocation-editor-page .allocation-field-grid{gap:22px}
#direk-allocations.allocation-editor-page label{font-size:11px;font-weight:600}
#direk-allocations.allocation-editor-page select,#direk-allocations.allocation-editor-page input[type="number"]{min-height:46px;font-size:13px;border-radius:11px;background:var(--allocation-inset)}
#direk-allocations.allocation-editor-page select:disabled{opacity:.85}
#direk-allocations.allocation-editor-page .allocation-money-input>span{top:14px}
#direk-allocations.allocation-editor-page .allocation-money-input input{font-variant-numeric:tabular-nums;font-weight:600}
#direk-allocations.allocation-editor-page .allocation-expense-table th{font-size:9px;padding:14px 18px;letter-spacing:.06em}
#direk-allocations.allocation-editor-page .allocation-expense-table td{padding:18px}
#direk-allocations.allocation-editor-page .allocation-expense-table select,#direk-allocations.allocation-editor-page .allocation-expense-table input{background:var(--allocation-bg)}
#direk-allocations.allocation-editor-page .allocation-expense-table .allocation-remove{min-height:40px;border-color:rgba(215,86,123,.3);font-size:10px}
#direk-allocations.allocation-editor-page .allocation-expense-table tfoot th,#direk-allocations.allocation-editor-page .allocation-expense-table tfoot td{font-size:11px;padding:17px 18px}
#direk-allocations.allocation-editor-page .allocation-summary{top:96px;position:sticky;overflow:hidden;background:linear-gradient(145deg,var(--allocation-bg),var(--allocation-inset));border-radius:20px}
#direk-allocations.allocation-editor-page .allocation-summary>.allocation-panel-body{position:relative;padding:27px 23px}
#direk-allocations .allocation-summary-symbol{position:absolute;right:22px;top:26px;color:var(--allocation-accent);font-size:20px;opacity:.7}
#direk-allocations.allocation-editor-page .allocation-summary .allocation-step{padding-right:32px}
#direk-allocations.allocation-editor-page .allocation-summary h3{font-size:18px;margin-top:4px;font-weight:650;letter-spacing:-.4px}
#direk-allocations.allocation-editor-page .allocation-summary .allocation-help{font-size:11px;line-height:1.8}
#direk-allocations.allocation-editor-page .allocation-summary-total{margin:12px 0 24px;font-size:27px;line-height:1.25;letter-spacing:-.5px;font-weight:650}
#direk-allocations.allocation-editor-page .allocation-summary-line{padding:13px 0;font-size:10px;align-items:baseline}
#direk-allocations.allocation-editor-page .allocation-summary-line strong{font-size:12px;line-height:1.6;max-width:65%;overflow-wrap:anywhere}
#direk-allocations.allocation-editor-page .allocation-status{padding:13px 14px;border-radius:10px;margin-top:13px;font-size:10px;border:1px solid var(--allocation-border);background:var(--allocation-bg)}
#direk-allocations.allocation-editor-page .allocation-status[data-state="ready"]{border-color:var(--allocation-green);color:var(--allocation-green)}
#direk-allocations.allocation-editor-page .allocation-status[data-state="error"]{border-color:var(--allocation-red);color:var(--allocation-red)}
#direk-allocations.allocation-editor-page .allocation-form-actions{padding:18px 22px;border:1px solid var(--allocation-border);border-radius:14px;background:var(--allocation-bg);box-shadow:0 3px 0 var(--allocation-edge);margin:0 0 25px;align-items:center;gap:11px}
#direk-allocations.allocation-editor-page .allocation-form-actions .allocation-button{min-height:44px;font-size:11px}
@media(max-width:1100px){#direk-allocations.allocation-editor-page .allocation-editor-grid{grid-template-columns:minmax(0,1fr) 280px;gap:20px}#direk-allocations.allocation-editor-page .allocation-summary-total{font-size:25px}}
@media(max-width:1000px){#direk-allocations.allocation-editor-page .allocation-editor-grid{grid-template-columns:1fr}#direk-allocations.allocation-editor-page .allocation-summary{position:static}#direk-allocations.allocation-editor-page .allocation-summary>.allocation-panel-body{padding:25px}}
@media(max-width:600px){#direk-allocations.allocation-editor-page{padding:18px 15px 28px}#direk-allocations.allocation-editor-page .allocation-heading{padding:26px 24px;min-height:0}#direk-allocations.allocation-editor-page .allocation-heading h2{font-size:28px}#direk-allocations .allocation-workflow{gap:10px;flex-wrap:wrap;padding:13px 15px}#direk-allocations .allocation-workflow>span{font-size:10px;gap:6px}#direk-allocations.allocation-editor-page .allocation-panel-heading,#direk-allocations.allocation-editor-page .allocation-panel-body{padding:21px}#direk-allocations.allocation-editor-page .allocation-form-actions{padding:16px}#direk-allocations.allocation-editor-page .allocation-form-actions .allocation-button{flex:1}}

</style>
@endpush

@extends('layouts.app')

@section('content')
@php
    $logsDark = session('user_settings.class_theme', '') === 'dark';
    $canshowhistory = auth()->user()->can('showhistory', App\Models\SystemLog::class);
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'System Logs'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-systemlogs" data-theme="{{ $logsDark ? 'dark' : 'light' }}">
    <header class="logs-heading">
        <div><span class="logs-eyebrow">Administration / Audit trail</span><h2>Activity, in focus.</h2><p>Follow account activity and inspect the changes behind each recorded event.</p></div>
        <button type="button" id="reloadLogs" class="logs-button"><i class="fa fa-refresh" aria-hidden="true"></i> Refresh logs</button>
    </header>
    <div class="logs-overview">
        <article><span class="logs-icon"><i class="fa fa-history" aria-hidden="true"></i></span><div><span class="logs-label">Recorded events</span><strong id="logsTotal">—</strong></div></article>
        <article><span class="logs-icon"><i class="fa fa-filter" aria-hidden="true"></i></span><div><span class="logs-label">Matching your search</span><strong id="logsFiltered">—</strong></div></article>
        <article class="logs-overview-note"><span class="logs-icon"><i class="fa fa-shield" aria-hidden="true"></i></span><div><span class="logs-label">Audit workspace</span><p>Review only · records stay intact</p></div></article>
    </div>
    <div id="logsMessage" class="logs-notice" role="alert" hidden></div>
    <section class="logs-panel" aria-labelledby="logsDirectoryTitle">
        <div class="logs-panel-title"><div><h3 id="logsDirectoryTitle">Event directory</h3><p>Newest activity appears first. Use search or narrow the results by column.</p></div><span class="logs-tag">System activity</span></div>
        <div class="logs-search-row">
            <label class="logs-search"><i class="fa fa-search" aria-hidden="true"></i><span class="visually-hidden">Search all logs</span><input id="logsSearch" type="search" placeholder="Search account, activity, IP address…" autocomplete="off"></label>
            <button id="resetLogFilters" type="button" class="logs-button">Clear filters</button>
        </div>
        <div class="logs-filters">
            <label>Account<input type="search" data-log-column="0" placeholder="Filter account" aria-label="Filter by account"></label>
            <label>Activity<input type="search" data-log-column="1" placeholder="Filter activity" aria-label="Filter by activity"></label>
            <label>IP address<input type="search" data-log-column="2" placeholder="Filter IP address" aria-label="Filter by IP address"></label>
            <label>Recorded date<input type="search" data-log-column="3" placeholder="YYYY-MM-DD" aria-label="Filter by recorded date"></label>
        </div>
        <div class="logs-table-wrap">
            <table id="datatable-logs" class="table" style="width:100%">
                <caption class="visually-hidden">Recorded account activity, IP addresses, dates, and change details.</caption>
                <thead><tr><th scope="col">Account</th><th scope="col">Activity</th><th scope="col">IP address</th><th scope="col">Recorded at</th>@if ($canshowhistory)<th scope="col">Details</th>@endif</tr></thead>
                <tbody></tbody>
            </table>
        </div>
        <p class="logs-footnote">Change details are available for events with a recorded history and the required permission.</p>
    </section>
</div>
<div id="historyModal" class="modal fade logs-history-modal" data-theme="{{ $logsDark ? 'dark' : 'light' }}" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content">
        <div class="modal-header"><div><span class="logs-eyebrow">Recorded changes</span><h3 id="historyModalLabel">Event details</h3><p id="historyRecordLabel"></p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close details"></button></div>
        <div class="modal-body">
            <div id="historyLoading" class="history-state" role="status" hidden><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Loading event details…</div>
            <div id="historyError" class="logs-notice" role="alert" hidden></div>
            <div id="divHistories" hidden><div class="history-code-heading"><span>History data</span><span>Read only</span></div><pre id="json-container" tabindex="0" aria-label="Recorded change data"></pre></div>
        </div>
        <div class="modal-footer"><button type="button" class="logs-button" data-bs-dismiss="modal">Close details</button></div>
    </div></div>
</div>
@include('layouts.footers.auth.footer')
@endsection

@push('css')
<style>
    #direk-systemlogs, .logs-history-modal { --log-bg:#fff; --log-inset:#f6f7fb; --log-border:#e6e9f0; --log-text:#172033; --log-copy:#637086; --log-accent:#635bff; --log-soft:#eeedff; color:var(--log-text); }
    #direk-systemlogs[data-theme="dark"], .logs-history-modal[data-theme="dark"] { --log-bg:#192130; --log-inset:#151c29; --log-border:#2c3546; --log-text:#eef0f8; --log-copy:#95a0b5; --log-accent:#a19cff; --log-soft:#2b2850; }
    #direk-systemlogs { padding:24px; }
    #direk-systemlogs h2, #direk-systemlogs h3, .logs-history-modal h3 { color:var(--log-text); margin:0; letter-spacing:-.025em; }
    .logs-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:24px; }
    .logs-eyebrow { display:block; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.12em; color:var(--log-accent); margin-bottom:10px; }
    #direk-systemlogs .logs-heading h2 { font-size:30px; font-weight:700; }
    .logs-heading p, .logs-panel-title p, .logs-history-modal .modal-header p { margin:8px 0 0; color:var(--log-copy); font-size:13px; line-height:1.7; }
    .logs-button, .log-history-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:1px solid var(--log-border); background:var(--log-bg); color:var(--log-text); border-radius:10px; padding:10px 15px; min-height:42px; font-size:12px; font-weight:600; cursor:pointer; }
    .logs-button:hover, .log-history-button:hover { background:var(--log-soft); color:var(--log-accent); }
    .logs-button:focus-visible, .log-history-button:focus-visible { outline:2px solid var(--log-accent); outline-offset:3px; }
    .logs-overview { display:grid; grid-template-columns:1fr 1fr 1.3fr; gap:16px; margin-bottom:24px; }
    .logs-overview article { display:flex; align-items:center; gap:16px; padding:22px; border:1px solid var(--log-border); border-radius:16px; background:var(--log-bg); }
    .logs-icon { width:44px; height:44px; flex-shrink:0; display:grid; place-items:center; background:var(--log-soft); color:var(--log-accent); border-radius:13px; }
    .logs-label { display:block; color:var(--log-copy); font-size:11px; font-weight:600; }
    .logs-overview strong { display:block; margin-top:6px; font-size:24px; font-weight:700; font-variant-numeric:tabular-nums; }
    .logs-overview-note p { margin:8px 0 0; font-size:12px; color:var(--log-text); }
    .logs-panel { background:var(--log-bg); border:1px solid var(--log-border); border-radius:18px; overflow:hidden; box-shadow:0 4px 24px rgba(20,30,55,.025); }
    .logs-panel-title { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:24px; border-bottom:1px solid var(--log-border); }
    #direk-systemlogs .logs-panel-title h3 { font-size:16px; font-weight:700; }
    .logs-tag { background:var(--log-soft); color:var(--log-accent); border-radius:20px; padding:7px 12px; font-size:10px; font-weight:700; white-space:nowrap; }
    .logs-search-row { display:flex; align-items:center; gap:12px; padding:20px 24px 12px; }
    .logs-search { display:flex; align-items:center; gap:10px; flex:1; margin:0; padding:0 14px; background:var(--log-inset); color:var(--log-copy); border:1px solid var(--log-border); border-radius:10px; }
    .logs-search input { width:100%; min-width:0; border:0; padding:12px 0; background:transparent; color:var(--log-text); outline:0; font-size:12px; }
    .logs-search:focus-within { outline:2px solid var(--log-accent); outline-offset:1px; }
    .logs-filters { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; padding:0 24px 20px; }
    .logs-filters label { margin:0; font-size:10px; font-weight:600; color:var(--log-copy); }
    .logs-filters input { display:block; width:100%; padding:9px 11px; border-radius:8px; border:1px solid var(--log-border); background:var(--log-bg); color:var(--log-text); font-size:11px; margin-top:5px; }
    .logs-filters input:focus { outline:2px solid var(--log-accent); outline-offset:1px; }
    .logs-table-wrap { padding:0 24px 16px; overflow-x:auto; }
    #datatable-logs { border-collapse:collapse; min-width:780px; margin:0; color:var(--log-text); font-size:12px; }
    #datatable-logs thead th { padding:14px 12px; background:var(--log-inset); color:var(--log-copy); font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid var(--log-border); }
    #datatable-logs tbody td { padding:16px 12px; border-bottom:1px solid var(--log-border); vertical-align:middle; white-space:normal; }
    #datatable-logs tbody td:first-child { font-weight:600; min-width:140px; }
    #datatable-logs tbody td:nth-child(2) { min-width:280px; max-width:600px; overflow-wrap:anywhere; line-height:1.7; }
    #datatable-logs tbody td:nth-child(3) { font-family:monospace; font-size:11px; }
    #datatable-logs tbody td:nth-child(4) { white-space:nowrap; font-size:11px; color:var(--log-copy); }
    #datatable-logs tbody tr:hover { background:var(--log-inset); }
    .log-history-button { min-height:34px; padding:6px 10px; background:var(--log-soft); color:var(--log-accent); font-size:11px; }
    #direk-systemlogs .dataTables_wrapper { color:var(--log-copy); font-size:11px; }
    #direk-systemlogs .dataTables_length { margin:4px 0 14px; }
    #direk-systemlogs .dataTables_length select { background:var(--log-bg); color:var(--log-text); border:1px solid var(--log-border); border-radius:7px; padding:5px; }
    #direk-systemlogs .dataTables_info, #direk-systemlogs .dataTables_paginate { padding-top:18px; }
    #direk-systemlogs .page-link { color:var(--log-text); background:var(--log-bg); border-color:var(--log-border); font-size:11px; }
    #direk-systemlogs .page-item.active .page-link { background:var(--log-accent); border-color:var(--log-accent); color:#fff; }
    .logs-footnote { margin:0; padding:16px 24px; border-top:1px solid var(--log-border); color:var(--log-copy); font-size:11px; line-height:1.6; }
    .logs-notice { background:var(--log-soft); color:var(--log-text); border:1px solid var(--log-border); border-radius:10px; padding:14px 16px; font-size:12px; line-height:1.7; margin-bottom:16px; }
    .logs-history-modal .modal-content { background:var(--log-bg); color:var(--log-text); border:1px solid var(--log-border); border-radius:18px; overflow:hidden; }
    .logs-history-modal .modal-header, .logs-history-modal .modal-footer { border-color:var(--log-border); padding:20px 24px; }
    .logs-history-modal .modal-body { padding:24px; }
    .logs-history-modal h3 { font-size:20px; }
    .logs-history-modal[data-theme="dark"] .btn-close { filter:invert(1); }
    .history-state { padding:40px 12px; text-align:center; font-size:13px; color:var(--log-copy); }
    .history-code-heading { display:flex; justify-content:space-between; padding:12px 16px; border:1px solid var(--log-border); border-bottom:0; border-radius:10px 10px 0 0; font-size:10px; color:var(--log-copy); background:var(--log-inset); }
    #json-container { margin:0; padding:20px; min-height:160px; max-height:55vh; overflow:auto; white-space:pre; color:#dce2f3; background:#111827; font:12px/1.8 Consolas,monospace; border-radius:0 0 10px 10px; }
    @media(max-width:900px) { .logs-overview { grid-template-columns:1fr 1fr; } .logs-overview-note { grid-column:1/-1; } .logs-filters { grid-template-columns:1fr 1fr; } }
    @media(max-width:575px) { #direk-systemlogs { padding:16px; } .logs-heading { align-items:flex-start; flex-direction:column; } #direk-systemlogs .logs-heading h2 { font-size:25px; } .logs-overview { gap:10px; } .logs-overview article { padding:15px; gap:10px; } .logs-icon { width:34px; height:34px; } .logs-panel-title { align-items:flex-start; } .logs-panel-title,.logs-table-wrap,.logs-filters { padding-left:16px; padding-right:16px; } .logs-search-row { padding-left:16px; padding-right:16px; flex-wrap:wrap; } .logs-search { flex-basis:100%; } .logs-tag { display:none; } }
</style>
@endpush

@push('js')
<script>
var table = null;
(function () {
    'use strict';
    const canShowHistory = @json($canshowhistory);
    const csrfToken = @json(csrf_token());
    let activeHistoryRequest = null;
    let historySequence = 0;
    let searchTimer = null;

    function message(elementId, text) {
        const element = document.getElementById(elementId);
        element.textContent = text;
        element.hidden = !text;
    }

    function formatHistory(value) {
        if (typeof value === 'string') {
            try { return JSON.stringify(JSON.parse(value), null, 2); }
            catch (_) { return value || 'No change data was recorded.'; }
        }
        return value == null ? 'No change data was recorded.' : JSON.stringify(value, null, 2);
    }

    function historyError(xhr) {
        const body = xhr.responseJSON || {};
        const errors = body.errors ? Object.values(body.errors).flat() : [];
        return body.error || errors.join(' ') || body.message || 'Unable to load event details. Please try again.';
    }

    window.showHistory = function (id) {
        if (!canShowHistory || !Number.isSafeInteger(Number(id)) || Number(id) < 1) return;
        const modalElement = document.getElementById('historyModal');
        if (!window.bootstrap?.Modal) {
            message('logsMessage', 'The details dialog could not be initialized. Refresh the page and try again.');
            return;
        }
        const sequence = ++historySequence;
        if (activeHistoryRequest) activeHistoryRequest.abort();
        document.getElementById('divHistories').hidden = true;
        document.getElementById('json-container').textContent = '';
        document.getElementById('historyLoading').hidden = false;
        document.getElementById('historyRecordLabel').textContent = 'Event #' + Number(id);
        message('historyError', '');
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
        activeHistoryRequest = $.ajax({
            url: @json(route('gethistory')), type: 'POST', dataType: 'json',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            data: { systemlog_id: Number(id) }
        }).done(function (response) {
            if (sequence !== historySequence) return;
            if (response.error) { message('historyError', response.error); return; }
            document.getElementById('json-container').textContent = formatHistory(response.data);
            document.getElementById('divHistories').hidden = false;
        }).fail(function (xhr, status) {
            if (sequence !== historySequence || status === 'abort') return;
            message('historyError', historyError(xhr));
        }).always(function () {
            if (sequence !== historySequence) return;
            document.getElementById('historyLoading').hidden = true;
            activeHistoryRequest = null;
        });
    };

    $(document).ready(function () {
        if (!$.fn.DataTable) {
            message('logsMessage', 'The log table could not be initialized. Refresh the page and try again.');
            return;
        }
        const columns = [
            { data:'name', name:'system_logs.name', defaultContent:'—' },
            { data:'activity', name:'system_logs.activity', defaultContent:'—' },
            { data:'ipaddress', name:'system_logs.ipaddress', defaultContent:'—' },
            { data:'created_at', name:'system_logs.created_at', defaultContent:'—' }
        ];
        if (canShowHistory) columns.push({ data:'actions', orderable:false, searchable:false, defaultContent:'' });
        $('#datatable-logs').on('xhr.dt', function (_, settings, json) {
            if (!json || !Number.isFinite(Number(json.recordsTotal))) return;
            document.getElementById('logsTotal').textContent = Number(json.recordsTotal).toLocaleString();
            document.getElementById('logsFiltered').textContent = Number(json.recordsFiltered).toLocaleString();
            message('logsMessage', '');
        });
        table = $('#datatable-logs').DataTable({
            ajax: {
                url: @json(route('getsystemlogs')), type:'POST', dataType:'json',
                headers:{ 'X-CSRF-TOKEN':csrfToken, 'Accept':'application/json' },
                error:function (xhr) {
                    $('#datatable-logs_processing').hide();
                    document.getElementById('logsTotal').textContent = '—';
                    document.getElementById('logsFiltered').textContent = '—';
                    message('logsMessage', historyError(xhr));
                }
            },
            serverSide:true, processing:true, searchDelay:500,
            columns:columns, order:[[3,'desc']], pageLength:25,
            lengthMenu:[[10,25,50,100],[10,25,50,100]],
            pagingType:'full_numbers', responsive:false,
            dom:'lrtip', stateSave:false,
            language:{ emptyTable:'No activity has been recorded yet.', zeroRecords:'No events match your filters.', processing:'Loading recorded events…', info:'Showing _START_–_END_ of _TOTAL_ events', infoEmpty:'No events to display', lengthMenu:'Show _MENU_ events' }
        });
        function searchLogs() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                table.search(document.getElementById('logsSearch').value);
                document.querySelectorAll('[data-log-column]').forEach(function (input) {
                    table.column(Number(input.dataset.logColumn)).search(input.value);
                });
                table.draw();
            }, 450);
        }
        document.getElementById('logsSearch').addEventListener('input', searchLogs);
        document.querySelectorAll('[data-log-column]').forEach(function (input) { input.addEventListener('input', searchLogs); });
        document.getElementById('resetLogFilters').addEventListener('click', function () {
            clearTimeout(searchTimer);
            document.getElementById('logsSearch').value = '';
            document.querySelectorAll('[data-log-column]').forEach(function (input) { input.value = ''; });
            table.search('').columns().search('').order([[3,'desc']]).draw();
        });
        document.getElementById('reloadLogs').addEventListener('click', function () { table.ajax.reload(null, false); });
        document.getElementById('historyModal').addEventListener('hidden.bs.modal', function () {
            ++historySequence;
            if (activeHistoryRequest) activeHistoryRequest.abort();
            activeHistoryRequest = null;
            document.getElementById('historyLoading').hidden = true;
        });
    });
})();
</script>
@endpush

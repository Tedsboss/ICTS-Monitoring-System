@php
    $allocationDark = session('user_settings.class_theme', '') === 'dark';
    $contextLocked = $editing && (int) ($allocation->financial_plans_count ?? 0) > 0;
    $contextValue = fn ($field) => $contextLocked ? $allocation->{$field} : old($field, $editing ? $allocation->{$field} : null);
    $expenseOptions = $expenseTypes->map(fn ($item) => [
        'id' => (int) $item->id, 'staff_id' => (int) $item->staff_id,
        'type' => $item->type, 'description' => $item->expense_description,
    ])->values();
    $initialExpenses = session()->hasOldInput()
        ? old('expenses', [])
        : ($editing ? $allocation->expenses->map(fn ($item) => [
            'expense_id' => $item->expense_id, 'cost' => $item->cost,
        ])->values()->all() : []);
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Allocation Management', 'subtitle' => $editing ? 'Edit allocation' : 'Create allocation'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-allocations" data-theme="{{ $allocationDark ? 'dark' : 'light' }}">
    <header class="allocation-heading"><div><span class="allocation-eyebrow">Financial workspace / Budget setup</span><h2>{{ $editing ? 'Refine your allocation.' : 'Give your plan a budget.' }}</h2><p>Set the funding ceiling and distribute it across the expenses your office needs.</p></div><a href="{{ route('allocations.index') }}" class="allocation-button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to allocations</a></header>
    @if ($errors->any())
        <div class="allocation-notice allocation-danger" role="alert"><strong>Please correct the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if ($contextLocked)
        <div class="allocation-notice" role="note"><i class="fa fa-lock" aria-hidden="true"></i> Financial Plan rows use this allocation. Office, fiscal year, level, and program are locked. Budget changes are checked against overall programmed usage and the amounts assigned to each expense in existing Financial Plans.</div>
    @endif
    <div id="pageMessage" class="allocation-notice allocation-danger" role="alert" hidden></div>
    <form method="POST" action="{{ $editing ? route('allocations.update', $allocation) : route('allocations.store') }}" id="allocationForm">
        @csrf
        @if ($editing) @method('PUT') @endif
        @if ($contextLocked)
            @foreach (['staff_id', 'year_id', 'level_id', 'program_id'] as $field)<input type="hidden" name="{{ $field }}" value="{{ $allocation->{$field} }}">@endforeach
        @endif
        <div class="allocation-editor-grid">
            <div class="allocation-main">
                <section class="allocation-panel">
                    <div class="allocation-panel-heading"><div><span class="allocation-step">01 / Context</span><h3>Allocation information</h3><p>Each office, fiscal year, level, and program combination has one allocation.</p></div></div>
                    <div class="allocation-panel-body allocation-field-grid">
                        <label class="allocation-wide" for="staff_id">Staff / Office <span aria-hidden="true">*</span><select id="staff_id" name="staff_id" required @disabled($contextLocked)><option value="">Select Staff / Office</option>@foreach ($staffOptions as $staff)<option value="{{ $staff->id }}" @selected($contextValue('staff_id') == $staff->id)>{{ $staff->name }}{{ $staff->abbreviation ? ' — ' . $staff->abbreviation : '' }}</option>@endforeach</select><small>Year, level, and expense choices follow the selected office.</small></label>
                        <label for="year_id">Fiscal year <span aria-hidden="true">*</span><select id="year_id" name="year_id" required @disabled($contextLocked)><option value="">Select fiscal year</option>@foreach ($fiscalYears as $year)<option value="{{ $year->id }}" data-staff-id="{{ $year->staff_id }}" @selected($contextValue('year_id') == $year->id)>{{ $year->year }}</option>@endforeach</select></label>
                        <label for="level_id">Allocation level <span aria-hidden="true">*</span><select id="level_id" name="level_id" required @disabled($contextLocked)><option value="">Select level</option>@foreach ($levels as $level)<option value="{{ $level->id }}" data-staff-id="{{ $level->staff_id }}" @selected($contextValue('level_id') == $level->id)>{{ $level->level_code }}{{ $level->level_description ? ' — ' . $level->level_description : '' }}</option>@endforeach</select></label>
                        <label class="allocation-wide" for="program_id">Program / Project <span aria-hidden="true">*</span><select id="program_id" name="program_id" required @disabled($contextLocked)><option value="">Select Program / Project</option>@foreach ($programs as $program)<option value="{{ $program->id }}" @selected($contextValue('program_id') == $program->id)>{{ $program->program }}</option>@endforeach</select></label>
                    </div>
                </section>
                <section class="allocation-panel">
                    <div class="allocation-panel-heading"><div><span class="allocation-step">02 / Funding</span><h3>Overall budget</h3><p>MOOE and Capital Outlay each have their own funding ceiling.</p></div></div>
                    <div class="allocation-panel-body allocation-field-grid">
                        <label for="mooe_budget">MOOE budget <span aria-hidden="true">*</span><div class="allocation-money-input"><span aria-hidden="true">₱</span><input type="number" id="mooe_budget" name="mooe_budget" value="{{ old('mooe_budget', $editing ? $allocation->mooe_budget : '0.00') }}" min="0" max="999999999999.99" step="0.01" inputmode="decimal" required></div></label>
                        <label for="co_budget">Capital Outlay budget <span aria-hidden="true">*</span><div class="allocation-money-input"><span aria-hidden="true">₱</span><input type="number" id="co_budget" name="co_budget" value="{{ old('co_budget', $editing ? $allocation->co_budget : '0.00') }}" min="0" max="999999999999.99" step="0.01" inputmode="decimal" required></div></label>
                    </div>
                </section>
                <section class="allocation-panel">
                    <div class="allocation-panel-heading"><div><span class="allocation-step">03 / Distribution</span><h3>Expense breakdown</h3><p>Add each expense type once and enter its funding amount.</p></div><button type="button" id="addExpense" class="allocation-button allocation-primary"><i class="fa fa-plus" aria-hidden="true"></i> Add expense</button></div>
                    <div class="allocation-table-wrap"><table class="allocation-table allocation-expense-table"><caption class="visually-hidden">Expense types and allocation amounts</caption><thead><tr><th scope="col">#</th><th scope="col">Expense type</th><th scope="col">Amount (PHP)</th><th scope="col">Action</th></tr></thead><tbody id="expenseRows"></tbody><tfoot><tr><th colspan="2" scope="row">Total expense allocation</th><td id="totalCost" class="allocation-number">₱0.00</td><td></td></tr></tfoot></table></div>
                    <div id="noExpenses" class="allocation-empty"><i class="fa fa-list-alt" aria-hidden="true"></i><h4>Your expense breakdown starts here</h4><p>Select an office, then add an expense type and amount.</p></div>
                    <noscript><div class="allocation-notice allocation-danger">Enable JavaScript to add and edit expense lines.</div></noscript>
                </section>
                <div class="allocation-form-actions"><a href="{{ route('allocations.index') }}" class="allocation-button">Cancel</a><button type="submit" id="saveAllocation" class="allocation-button allocation-primary"><i class="fa fa-save" aria-hidden="true"></i><span id="saveAllocationText">{{ $editing ? 'Update allocation' : 'Save allocation' }}</span></button></div>
            </div>
            <aside class="allocation-summary allocation-panel" aria-labelledby="allocationSummaryTitle">
                <div class="allocation-panel-body"><span class="allocation-step">Live summary</span><h3 id="allocationSummaryTitle">Your funding picture</h3><p class="allocation-help">Unassigned funding may remain within each overall budget.</p><span class="allocation-summary-label">Total overall budget</span><strong id="overallBudgetTotal" class="allocation-summary-total">₱0.00</strong><div class="allocation-summary-line"><span>MOOE expenses</span><strong id="mooeExpensesTotal">₱0.00</strong></div><div class="allocation-summary-line"><span>CO expenses</span><strong id="coExpensesTotal">₱0.00</strong></div><div class="allocation-summary-line"><span>MOOE unassigned</span><strong id="mooeUnassigned">₱0.00</strong></div><div class="allocation-summary-line"><span>CO unassigned</span><strong id="coUnassigned">₱0.00</strong></div><div id="budgetStatus" class="allocation-status" role="status" aria-live="polite"></div><p class="allocation-help allocation-summary-note">Expense allocations cannot exceed their MOOE or CO budget. Financial Plans use these expense amounts as their planning limits.</p></div>
            </aside>
        </div>
    </form>
</div>
@include('layouts.footers.auth.footer')
@include('allocations.styles')

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    const form = document.getElementById('allocationForm');
    const rows = document.getElementById('expenseRows');
    const staff = document.getElementById('staff_id');
    const addButton = document.getElementById('addExpense');
    const saveButton = document.getElementById('saveAllocation');
    const expenseTypes = @json($expenseOptions);
    const initialExpenses = @json($initialExpenses);
    const defaultLabel = @json($editing ? 'Update allocation' : 'Save allocation');
    let rowIndex = 0;
    let saving = false;
    const field = id => document.getElementById(id);
    function cents(value) {
        const raw = String(value ?? '').trim();
        if (!/^\d{1,12}(?:\.\d{1,2})?$/.test(raw)) return null;
        const [whole, fraction = ''] = raw.split('.');
        return Number(whole) * 100 + Number(fraction.padEnd(2, '0'));
    }
    function money(value) { return '₱' + (value / 100).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}); }
    function availableTypes() { return expenseTypes.filter(item => Number(item.staff_id) === Number(staff.value) && ['MOOE','CO'].includes(String(item.type).trim().toUpperCase())); }
    function message(text) { field('pageMessage').textContent = text; field('pageMessage').hidden = !text; }
    function expenseSelectOptions(select, selected) {
        select.replaceChildren(new Option('Select expense type', ''));
        const available = availableTypes();
        available.forEach(item => select.add(new Option(String(item.type) + ' — ' + String(item.description), String(item.id))));
        if (selected && !available.some(item => String(item.id) === String(selected))) {
            const item = expenseTypes.find(item => String(item.id) === String(selected));
            select.add(new Option((item ? String(item.description) : 'Expense #' + selected) + ' — unavailable for this office', String(selected)));
        }
        select.value = String(selected || '');
    }
    function syncOptions() {
        const selected = Array.from(rows.querySelectorAll('.expense-select')).map(select => select.value).filter(Boolean);
        rows.querySelectorAll('.expense-select').forEach(select => {
            Array.from(select.options).forEach(option => { option.disabled = Boolean(option.value && selected.includes(option.value) && option.value !== select.value); });
        });
        addButton.disabled = saving || !staff.value || rows.children.length >= availableTypes().length;
        addButton.title = !staff.value ? 'Select a Staff / Office first.' : (availableTypes().length === 0 ? 'No MOOE or CO expense types are configured for this office.' : 'Add another expense line');
    }
    function state() {
        const budgets = {MOOE:cents(field('mooe_budget').value), CO:cents(field('co_budget').value)};
        const totals = {MOOE:0, CO:0};
        const errors = [];
        const selected = new Set();
        if (budgets.MOOE === null || budgets.CO === null) errors.push('Enter valid budgets with up to two decimal places.');
        rows.querySelectorAll('.expense-row').forEach((row, index) => {
            const select = row.querySelector('.expense-select');
            const value = cents(row.querySelector('.cost-input').value);
            const expense = availableTypes().find(item => String(item.id) === select.value);
            row.querySelector('.expense-row-number').textContent = index + 1;
            select.setCustomValidity('');
            if (!expense) {
                errors.push('Line ' + (index + 1) + ': select an expense type for this office.');
                if (select.value) select.setCustomValidity('Select an expense type belonging to this office.');
            } else if (selected.has(select.value)) {
                errors.push('Line ' + (index + 1) + ': this expense type is already selected.');
                select.setCustomValidity('Each expense type can only be selected once.');
            } else {
                selected.add(select.value);
                if (value !== null) totals[String(expense.type).trim().toUpperCase()] += value;
            }
            if (value === null) errors.push('Line ' + (index + 1) + ': enter a valid expense amount.');
        });
        for (const type of ['MOOE','CO']) {
            if (!Number.isSafeInteger(totals[type])) errors.push(type + ' expenses are too large to calculate safely.');
            if (budgets[type] !== null && totals[type] > budgets[type]) errors.push(type + ' budget exceeded by ' + money(totals[type] - budgets[type]) + '.');
        }
        return {budgets, totals, errors};
    }
    function refresh() {
        const result = state();
        const m = result.budgets.MOOE ?? 0, c = result.budgets.CO ?? 0;
        field('overallBudgetTotal').textContent = money(m + c);
        field('totalCost').textContent = money(result.totals.MOOE + result.totals.CO);
        field('mooeExpensesTotal').textContent = money(result.totals.MOOE);
        field('coExpensesTotal').textContent = money(result.totals.CO);
        field('mooeUnassigned').textContent = money(m - result.totals.MOOE);
        field('coUnassigned').textContent = money(c - result.totals.CO);
        const status = field('budgetStatus');
        status.dataset.state = result.errors.length ? 'error' : (rows.children.length ? 'ready' : 'empty');
        status.textContent = result.errors.length ? result.errors.join(' ') : (rows.children.length ? 'Expense breakdown is within budget.' : 'Add at least one expense line to save.');
        field('noExpenses').hidden = rows.children.length > 0;
        saveButton.disabled = saving || result.errors.length > 0 || rows.children.length === 0;
        syncOptions();
        return result;
    }
    function addRow(expenseId = '', cost = '') {
        const index = rowIndex++;
        const row = document.createElement('tr');
        row.className = 'expense-row';
        const number = document.createElement('td');
        number.className = 'expense-row-number';
        const selectCell = document.createElement('td');
        const select = document.createElement('select');
        select.className = 'expense-select'; select.name = 'expenses[' + index + '][expense_id]'; select.required = true;
        select.setAttribute('aria-label', 'Expense type for line ' + (index + 1));
        expenseSelectOptions(select, expenseId); selectCell.appendChild(select);
        const costCell = document.createElement('td');
        const input = document.createElement('input');
        input.className = 'cost-input'; input.type = 'number'; input.min = '0'; input.max = '999999999999.99'; input.step = '0.01'; input.required = true;
        input.name = 'expenses[' + index + '][cost]'; input.value = String(cost ?? ''); input.inputMode = 'decimal';
        input.setAttribute('aria-label', 'Expense amount for line ' + (index + 1)); costCell.appendChild(input);
        const actionCell = document.createElement('td');
        const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'allocation-button allocation-remove'; remove.textContent = 'Remove';
        remove.addEventListener('click', function () { if (saving) return; row.remove(); message(''); refresh(); });
        actionCell.appendChild(remove);
        row.append(number, selectCell, costCell, actionCell); rows.appendChild(row);
        select.addEventListener('change', function () { message(''); refresh(); });
        input.addEventListener('input', function () { message(''); refresh(); });
        refresh();
        return select;
    }
    function syncOffice() {
        ['year_id','level_id'].forEach(id => {
            const select = field(id);
            Array.from(select.options).forEach(option => {
                if (!option.value) return;
                const belongs = Number(option.dataset.staffId) === Number(staff.value);
                option.hidden = !belongs; option.disabled = !belongs;
            });
            if (!select.disabled && select.selectedOptions[0]?.disabled) select.value = '';
        });
        rows.querySelectorAll('.expense-select').forEach(select => expenseSelectOptions(select, select.value));
        refresh();
    }
    addButton.addEventListener('click', function () { if (!addButton.disabled && !saving) addRow().focus(); });
    staff.addEventListener('change', syncOffice);
    field('mooe_budget').addEventListener('input', function () { message(''); refresh(); });
    field('co_budget').addEventListener('input', function () { message(''); refresh(); });
    form.addEventListener('submit', function (event) {
        if (saving) { event.preventDefault(); return; }
        const result = refresh();
        if (!form.checkValidity() || result.errors.length || rows.children.length === 0) {
            event.preventDefault(); message(result.errors.join(' ') || 'Complete the required fields and add at least one expense line.'); form.reportValidity(); return;
        }
        saving = true; saveButton.disabled = true; addButton.disabled = true;
        field('saveAllocationText').textContent = 'Saving…';
    });
    window.addEventListener('pageshow', function () { saving = false; field('saveAllocationText').textContent = defaultLabel; refresh(); });
    syncOffice();
    if (Array.isArray(initialExpenses)) initialExpenses.forEach(item => addRow(item.expense_id ?? '', item.cost ?? ''));
    else if (initialExpenses && typeof initialExpenses === 'object') Object.values(initialExpenses).forEach(item => addRow(item?.expense_id ?? '', item?.cost ?? ''));
    refresh();
});
</script>
@endpush

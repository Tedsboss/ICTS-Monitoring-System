@extends('layouts.app')
@section('content')
<div class="px-4 pb-8 pt-4">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
        <div class="container-fluid px-0">
            @include('layouts.navbars.auth.topnav', ['title' => 'Allocation Management'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>
    <div id="pageMessage"></div>
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h4 class="mb-1 text-lg font-bold text-slate-800">Edit Allocation</h4>
                <p class="mb-0 text-sm text-slate-500">Update the Staff / Office, Fiscal Year, Level, Program / Project, budget, and expenses.</p>
            </div>
            <a href="{{ route('allocations.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i class="fa fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </section>
    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="mb-2 font-semibold">
                <i class="fa fa-exclamation-circle mr-1"></i>
                Please correct the following:
            </div>
            <ul class="mb-0 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('allocations.update', $allocation) }}" id="allocationForm">
        @csrf
        @method('PUT')
        <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h5 class="mb-0 text-base font-bold text-slate-800">Allocation Information</h5>
                <p class="mb-0 mt-1 text-xs text-slate-500">Update the Staff / Office, Fiscal Year, Level, Program / Project, and overall budget.</p>
            </div>
            <div class="p-5">
                <div class="mb-5">
                    <label for="staff_id" class="mb-2 block text-sm font-semibold text-slate-700">
                        Staff / Office <span class="text-red-500">*</span>
                    </label>
                    <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                        <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-slate-400">
                            <i class="fa fa-user"></i>
                        </div>
                        <select name="staff_id" id="staff_id" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-0" required>
                            <option value="">Select Staff / Office</option>
                            @foreach($staffOptions as $staff)
                                <option value="{{ $staff->id }}" @selected(old('staff_id', $allocation->staff_id) == $staff->id)>
                                    {{ $staff->name }}{{ $staff->abbreviation ? ' - ' . $staff->abbreviation : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mb-0 mt-1.5 text-xs text-slate-500">Select the Staff / Office that owns this allocation.</p>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="year_id" class="mb-2 block text-sm font-semibold text-slate-700">
                            Fiscal Year <span class="text-red-500">*</span>
                        </label>
                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-slate-400">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <select name="year_id" id="year_id" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-0" required>
                                <option value="">Select Fiscal Year</option>
                                @foreach($fiscalYears as $fiscalYear)
                                    <option value="{{ $fiscalYear->id }}" @selected(old('year_id', $allocation->year_id) == $fiscalYear->id)>
                                        {{ $fiscalYear->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mb-0 mt-1.5 text-xs text-slate-500">Select the fiscal year covered by this allocation.</p>
                    </div>
                    <div>
                        <label for="level_id" class="mb-2 block text-sm font-semibold text-slate-700">
                            Level <span class="text-red-500">*</span>
                        </label>
                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-slate-400">
                                <i class="fa fa-layer-group"></i>
                            </div>
                            <select name="level_id" id="level_id" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-0" required>
                                <option value="">Select Level</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}" @selected(old('level_id', $allocation->level_id) == $level->id)>
                                        {{ $level->level_code }} - {{ $level->level_description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mb-0 mt-1.5 text-xs text-slate-500">Select the allocation level such as Tier1, Tier2, NEP, GAA, or Continuing.</p>
                    </div>
                </div>
                <div class="mt-5">
                    <label for="program_id" class="mb-2 block text-sm font-semibold text-slate-700">
                        Program / Project <span class="text-red-500">*</span>
                    </label>
                    <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                        <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-slate-400">
                            <i class="fa fa-sitemap"></i>
                        </div>
                        <select name="program_id" id="program_id" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-0" required>
                            <option value="">Select Program / Project</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" @selected(old('program_id', $allocation->program_id) == $program->id)>
                                    {{ $program->program }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mb-0 mt-1.5 text-xs text-slate-500">Select the Program / Project that owns this budget.</p>
                </div>
                <div class="mt-5">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Overall Budget <span class="text-red-500">*</span>
                    </label>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="mooe_budget" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">MOOE</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-400">₱</span>
                                <input type="number" name="mooe_budget" id="mooe_budget" value="{{ old('mooe_budget', $allocation->mooe_budget) }}" min="0" step="0.01" inputmode="decimal" class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-8 pr-3 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="0.00" required>
                            </div>
                        </div>
                        <div>
                            <label for="co_budget" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">CO</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-400">₱</span>
                                <input type="number" name="co_budget" id="co_budget" value="{{ old('co_budget', $allocation->co_budget) }}" min="0" step="0.01" inputmode="decimal" class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-8 pr-3 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-600">Total Overall Budget</span>
                        <span id="overallBudgetTotal" class="text-lg font-bold text-slate-800">₱0.00</span>
                    </div>
                    <p class="mb-0 mt-1 text-xs text-slate-500">This is the total MOOE and CO budget assigned to the selected Program / Project.</p>
                </div>
                <div id="budgetStatus" class="mt-4"></div>
            </div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h5 class="mb-0 text-base font-bold text-slate-800">Allocation Expenses</h5>
                    <p class="mb-0 mt-1 text-xs text-slate-500">Update the expense categories and corresponding allocation amounts.</p>
                </div>
                <button type="button" id="addExpense" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    <i class="fa fa-plus"></i>
                    <span>Add Expense</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left">
                            <th class="w-16 px-5 py-3 text-center font-semibold text-slate-600">#</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Expense Type</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Cost</th>
                            <th class="px-5 py-3 text-right font-semibold text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody id="expenseRows"></tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200 bg-slate-50">
                            <th colspan="2" class="px-5 py-4 text-right font-bold text-slate-700">Total</th>
                            <th id="totalCost" class="px-5 py-4 font-bold text-slate-800">₱0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div id="noExpenses" class="px-5 py-12 text-center text-sm text-slate-500">
                <i class="fa fa-folder-open-o mb-2 block text-2xl text-slate-300"></i>
                No expenses are configured for this allocation yet.
                Click <strong>Add Expense</strong> to begin.
            </div>
            <div class="border-t border-slate-200 bg-slate-50 px-5 py-4">
                <div class="flex items-start gap-3 rounded-xl border border-sky-100 bg-sky-50/60 p-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-600 text-white">
                        <i class="fa fa-info-circle"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-sm font-bold text-sky-800">About Allocation Expenses</h6>
                        <p class="mb-0 text-xs leading-5 text-slate-600">Each expense type can only be added once to an allocation. MOOE and CO expenses are checked against their respective overall budgets.</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-5 py-4">
                <a href="{{ route('allocations.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="fa fa-times text-slate-400"></i>
                    <span>Cancel</span>
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    <i class="fa fa-save"></i>
                    <span>Update Allocation</span>
                </button>
            </div>
        </section>
    </form>
    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('allocationForm');
    const addExpenseButton = document.getElementById('addExpense');
    const expenseRows = document.getElementById('expenseRows');
    const noExpenses = document.getElementById('noExpenses');
    const totalCost = document.getElementById('totalCost');
    const mooeBudget = document.getElementById('mooe_budget');
    const coBudget = document.getElementById('co_budget');
    const overallBudgetTotal = document.getElementById('overallBudgetTotal');
    const budgetStatus = document.getElementById('budgetStatus');
    const submitButton = form.querySelector('button[type="submit"]');
    const expenseTypes = [
        @foreach($expenseTypes as $expenseType)
            {
                id: {{ $expenseType->id }},
                type: @json($expenseType->type),
                description: @json($expenseType->expense_description)
            },
        @endforeach
    ];
    const existingExpenses = [
        @foreach($allocation->expenses as $expense)
            {
                id: {{ $expense->expense_id }},
                cost: @json($expense->cost)
            },
        @endforeach
    ];
    let rowIndex = 0;
    function formatAmount(value) {
        return Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    function getBudgetValues() {
        return {
            mooe: parseFloat(mooeBudget.value) || 0,
            co: parseFloat(coBudget.value) || 0
        };
    }
    function getExpenseTotals() {
        let mooe = 0;
        let co = 0;
        expenseRows.querySelectorAll('.expense-row').forEach(function (row) {
            const select = row.querySelector('.expense-select');
            const input = row.querySelector('.cost-input');
            if (!select || !input) {
                return;
            }
            const expense = expenseTypes.find(function (item) {
                return item.id === Number(select.value);
            });
            if (!expense) {
                return;
            }
            const cost = parseFloat(input.value) || 0;
            const type = String(expense.type || '').trim().toUpperCase();
            if (type === 'MOOE') {
                mooe += cost;
            } else if (type === 'CO') {
                co += cost;
            }
        });
        return {
            mooe: mooe,
            co: co,
            total: mooe + co
        };
    }
    function updateOverallBudget() {
        const budgets = getBudgetValues();
        overallBudgetTotal.textContent = '₱' + formatAmount(budgets.mooe + budgets.co);
    }
    function updateTotal() {
        const totals = getExpenseTotals();
        totalCost.textContent = '₱' + formatAmount(totals.total);
    }
    function updateBudgetStatus() {
        const budgets = getBudgetValues();
        const totals = getExpenseTotals();
        const mooeExceeded = totals.mooe > budgets.mooe;
        const coExceeded = totals.co > budgets.co;
        if (mooeExceeded || coExceeded) {
            let messages = '';
            if (mooeExceeded) {
                messages +=
                    '<div>MOOE budget exceeded by <strong>₱' +
                    formatAmount(totals.mooe - budgets.mooe) +
                    '</strong>.</div>';
            }
            if (coExceeded) {
                messages +=
                    '<div>CO budget exceeded by <strong>₱' +
                    formatAmount(totals.co - budgets.co) +
                    '</strong>.</div>';
            }
            budgetStatus.innerHTML =
                '<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">' +
                    '<div class="flex items-start gap-3">' +
                        '<i class="fa fa-exclamation-triangle mt-0.5"></i>' +
                        '<div>' +
                            '<div class="font-bold">Budget Limit Exceeded</div>' +
                            '<div class="mt-1">' + messages + '</div>' +
                            '<div class="mt-1 text-xs">Reduce the expense amount or increase the corresponding budget before updating.</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            submitButton.disabled = true;
            submitButton.classList.add('cursor-not-allowed', 'opacity-50');
            return;
        }
        if (totals.total > 0) {
            budgetStatus.innerHTML =
                '<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">' +
                    '<div class="flex items-start gap-3">' +
                        '<i class="fa fa-check-circle mt-0.5"></i>' +
                        '<div>' +
                            '<div class="font-bold">Budget Within Limit</div>' +
                            '<div class="mt-1">MOOE remaining: ₱' +
                            formatAmount(Math.max(budgets.mooe - totals.mooe, 0)) +
                            ' &nbsp;|&nbsp; CO remaining: ₱' +
                            formatAmount(Math.max(budgets.co - totals.co, 0)) +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        } else {
            budgetStatus.innerHTML = '';
        }
        submitButton.disabled = false;
        submitButton.classList.remove('cursor-not-allowed', 'opacity-50');
    }
    function getSelectedExpenseIds() {
        return Array.from(
            expenseRows.querySelectorAll('.expense-select')
        )
            .map(function (select) {
                return select.value;
            })
            .filter(Boolean);
    }
    function updateEmptyMessage() {
        noExpenses.style.display =
            expenseRows.children.length > 0 ? 'none' : 'block';
    }
    function updateExpenseOptions() {
        const selectedValues = getSelectedExpenseIds();
        expenseRows.querySelectorAll('.expense-select').forEach(function (select) {
            const currentValue = select.value;
            select.querySelectorAll('option[data-expense-id]').forEach(function (option) {
                option.disabled =
                    selectedValues.includes(option.value) &&
                    option.value !== currentValue;
            });
        });
    }
    function updateAddButton() {
        const allUsed =
            expenseTypes.length > 0 &&
            expenseRows.children.length >= expenseTypes.length;
        addExpenseButton.disabled = allUsed;
        if (allUsed) {
            addExpenseButton.classList.add('cursor-not-allowed', 'opacity-50');
            addExpenseButton.setAttribute(
                'title',
                'All available expense types have been added.'
            );
        } else {
            addExpenseButton.classList.remove('cursor-not-allowed', 'opacity-50');
            addExpenseButton.removeAttribute('title');
        }
    }
    function updateRowNumbers() {
        expenseRows
            .querySelectorAll('.expense-row-number')
            .forEach(function (element, index) {
                element.textContent = index + 1;
            });
    }
    function refreshValidation() {
        updateOverallBudget();
        updateTotal();
        updateBudgetStatus();
    }
    function addExpenseRow(expenseId, cost) {
        expenseId = expenseId || '';
        cost = cost || '';
        if (expenseRows.children.length >= expenseTypes.length) {
            return;
        }
        const row = document.createElement('tr');
        row.className = 'expense-row border-b border-slate-100 align-middle';
        let options = '<option value="">Select Expense</option>';
        expenseTypes.forEach(function (expense) {
            const selected =
                String(expense.id) === String(expenseId)
                    ? 'selected'
                    : '';
            options +=
                '<option value="' + expense.id + '" ' +
                'data-expense-id="' + expense.id + '" ' +
                selected + '>' +
                expense.type + ' - ' +
                expense.description +
                '</option>';
        });
        row.innerHTML =
            '<td class="px-5 py-4 text-center">' +
                '<span class="expense-row-number inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600"></span>' +
            '</td>' +
            '<td class="px-5 py-4">' +
                '<select name="expenses[' + rowIndex + '][expense_id]" class="expense-select w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" required>' +
                    options +
                '</select>' +
            '</td>' +
            '<td class="px-5 py-4">' +
                '<div class="relative">' +
                    '<span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-400">₱</span>' +
                    '<input type="number" name="expenses[' + rowIndex + '][cost]" class="cost-input w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-8 pr-3 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" value="' + cost + '" min="0" step="0.01" inputmode="decimal" placeholder="0.00" required>' +
                '</div>' +
            '</td>' +
            '<td class="px-5 py-4 text-right">' +
                '<button type="button" class="remove-expense inline-flex items-center justify-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100">' +
                    '<i class="fa fa-trash"></i> Remove' +
                '</button>' +
            '</td>';
        expenseRows.appendChild(row);
        rowIndex++;
        updateRowNumbers();
        updateEmptyMessage();
        updateExpenseOptions();
        updateAddButton();
        refreshValidation();
    }
    addExpenseButton.addEventListener('click', function () {
        addExpenseRow();
    });
    expenseRows.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-expense');
        if (!button) {
            return;
        }
        const row = button.closest('tr');
        if (row) {
            row.remove();
        }
        updateRowNumbers();
        updateEmptyMessage();
        updateExpenseOptions();
        updateAddButton();
        refreshValidation();
    });
    expenseRows.addEventListener('input', function (event) {
        if (event.target.classList.contains('cost-input')) {
            if (parseFloat(event.target.value) < 0) {
                event.target.value = 0;
            }
            refreshValidation();
        }
    });
    expenseRows.addEventListener('change', function (event) {
        if (event.target.classList.contains('expense-select')) {
            updateExpenseOptions();
            updateAddButton();
            refreshValidation();
        }
    });
    mooeBudget.addEventListener('input', refreshValidation);
    coBudget.addEventListener('input', refreshValidation);
    form.addEventListener('submit', function (event) {
        const budgets = getBudgetValues();
        const totals = getExpenseTotals();
        if (expenseRows.children.length === 0) {
            event.preventDefault();
            noExpenses.classList.remove('text-slate-500');
            noExpenses.classList.add('text-red-600');
            noExpenses.innerHTML =
                '<i class="fa fa-exclamation-circle mb-2 block text-2xl"></i>' +
                'Please add at least one expense before updating.';
            return;
        }
        if (totals.mooe > budgets.mooe || totals.co > budgets.co) {
            event.preventDefault();
            refreshValidation();
            return;
        }
        submitButton.disabled = true;
        submitButton.classList.add('cursor-not-allowed', 'opacity-70');
        submitButton.innerHTML =
            '<i class="fa fa-spinner fa-spin"></i>' +
            '<span>Updating...</span>';
    });
    @if(old('expenses'))
        @foreach(old('expenses') as $expense)
            addExpenseRow(
                @json($expense['expense_id'] ?? ''),
                @json($expense['cost'] ?? '')
            );
        @endforeach
    @else
        existingExpenses.forEach(function (expense) {
            addExpenseRow(expense.id, expense.cost);
        });
    @endif
    updateRowNumbers();
    updateEmptyMessage();
    updateExpenseOptions();
    updateAddButton();
    refreshValidation();
});
</script>
@endsection

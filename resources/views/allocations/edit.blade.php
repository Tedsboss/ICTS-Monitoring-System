@extends('layouts.app')
@section('content')
<div class="px-4 pb-8 pt-4">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0 shadow-none border-radius-xl z-index-sticky"
         id="navbarBlur"
         data-scroll="false">
        <div class="container-fluid px-0">
            @include('layouts.navbars.auth.topnav', ['title' => 'Allocation Management'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>
    <div id="pageMessage"></div>
    {{-- Page Header --}}
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h4 class="mb-1 text-lg font-bold text-slate-800">
                    Edit Allocation
                </h4>
                <p class="mb-0 text-sm text-slate-500">
                    Update the Staff / Office, Fiscal Year, Level, and allocation expenses.
                </p>
            </div>
            <a href="{{ route('allocations.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300
                      bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm
                      transition hover:bg-slate-50">
                <i class="fa fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </section>
    {{-- Validation Errors --}}
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
    <form method="POST"
          action="{{ route('allocations.update', $allocation) }}"
          id="allocationForm">
        @csrf
        @method('PUT')
        {{-- Allocation Information --}}
        <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h5 class="mb-0 text-base font-bold text-slate-800">
                    Allocation Information
                </h5>
                <p class="mb-0 mt-1 text-xs text-slate-500">
                    Update the Staff / Office, Fiscal Year, and Level for this allocation.
                </p>
            </div>
            <div class="p-5">
                <div class="grid gap-5 md:grid-cols-3">
                    {{-- Staff / Office --}}
                    <div>
                        <label for="staff_id"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Staff / Office
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white
                                    transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200
                                        bg-slate-50 text-slate-400">
                                <i class="fa fa-user"></i>
                            </div>
                            <select name="staff_id"
                                    id="staff_id"
                                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                                           text-sm text-slate-700 outline-none focus:ring-0"
                                    required>
                                <option value="">Select Staff / Office</option>
                                @foreach($staffOptions as $staff)
                                    <option value="{{ $staff->id }}"
                                        @selected(old('staff_id', $allocation->staff_id) == $staff->id)>
                                        {{ $staff->name }}{{ $staff->abbreviation ? ' - ' . $staff->abbreviation : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mb-0 mt-1.5 text-xs text-slate-500">
                            Select the Staff / Office that owns this allocation.
                        </p>
                    </div>
                    {{-- Fiscal Year --}}
                    <div>
                        <label for="year_id"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Fiscal Year
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white
                                    transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200
                                        bg-slate-50 text-slate-400">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <select name="year_id"
                                    id="year_id"
                                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                                           text-sm text-slate-700 outline-none focus:ring-0"
                                    required>
                                <option value="">Select Fiscal Year</option>
                                @foreach($fiscalYears as $fiscalYear)
                                    <option value="{{ $fiscalYear->id }}"
                                        @selected(old('year_id', $allocation->year_id) == $fiscalYear->id)>
                                        {{ $fiscalYear->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mb-0 mt-1.5 text-xs text-slate-500">
                            Select the fiscal year covered by this allocation.
                        </p>
                    </div>
                    {{-- Level --}}
                    <div>
                        <label for="level_id"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Level
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white
                                    transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200
                                        bg-slate-50 text-slate-400">
                                <i class="fa fa-layer-group"></i>
                            </div>
                            <select name="level_id"
                                    id="level_id"
                                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                                           text-sm text-slate-700 outline-none focus:ring-0"
                                    required>
                                <option value="">Select Level</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}"
                                        @selected(old('level_id', $allocation->level_id) == $level->id)>
                                        {{ $level->level_code }} - {{ $level->level_description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mb-0 mt-1.5 text-xs text-slate-500">
                            Select the allocation level such as Tier1, Tier2, NEP, GAA, or Continuing.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        {{-- Allocation Expenses --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h5 class="mb-0 text-base font-bold text-slate-800">
                        Allocation Expenses
                    </h5>
                    <p class="mb-0 mt-1 text-xs text-slate-500">
                        Update the expense categories and corresponding allocation amounts.
                    </p>
                </div>
                <button type="button"
                        id="addExpense"
                        class="inline-flex items-center justify-center gap-2 rounded-lg
                               bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm
                               transition hover:bg-sky-700">
                    <i class="fa fa-plus"></i>
                    <span>Add Expense</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left">
                            <th class="w-16 px-5 py-3 text-center font-semibold text-slate-600">
                                #
                            </th>
                            <th class="px-5 py-3 font-semibold text-slate-600">
                                Expense Type
                            </th>
                            <th class="px-5 py-3 font-semibold text-slate-600">
                                Cost
                            </th>
                            <th class="px-5 py-3 text-right font-semibold text-slate-600">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody id="expenseRows"></tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200 bg-slate-50">
                            <th colspan="3"
                                class="px-5 py-4 text-right font-bold text-slate-700">
                                Total
                            </th>
                            <th id="totalCost"
                                class="px-5 py-4 text-right font-bold text-slate-800">
                                0.00
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- Empty State --}}
            <div id="noExpenses"
                 class="px-5 py-12 text-center text-sm text-slate-500">
                <i class="fa fa-folder-open-o mb-2 block text-2xl text-slate-300"></i>
                No expenses are configured for this allocation yet.
                Click <strong>Add Expense</strong> to begin.
            </div>
            {{-- Information --}}
            <div class="border-t border-slate-200 bg-slate-50 px-5 py-4">
                <div class="flex items-start gap-3 rounded-xl border border-sky-100 bg-sky-50/60 p-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-600 text-white">
                        <i class="fa fa-info-circle"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-sm font-bold text-sky-800">
                            About Allocation Expenses
                        </h6>
                        <p class="mb-0 text-xs leading-5 text-slate-600">
                            Each expense type can only be added once to an allocation.
                            The total allocation is calculated automatically.
                        </p>
                    </div>
                </div>
            </div>
            {{-- Form Actions --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-5 py-4">
                <a href="{{ route('allocations.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300
                          bg-white px-5 py-2.5 text-sm font-semibold text-slate-700
                          transition hover:bg-slate-50">
                    <i class="fa fa-times text-slate-400"></i>
                    <span>Cancel</span>
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg
                               bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                               transition hover:bg-sky-700">
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

    function getSelectedExpenseIds() {
        return Array.from(
            expenseRows.querySelectorAll('.expense-select')
        )
            .map(select => select.value)
            .filter(Boolean);
    }

    function updateTotal() {
        let total = 0;

        expenseRows.querySelectorAll('.cost-input').forEach(input => {
            const value = parseFloat(input.value);
            total += Number.isFinite(value) ? value : 0;
        });

        totalCost.textContent = '₱' + formatAmount(total);
    }

    function updateEmptyMessage() {
        const hasRows = expenseRows.children.length > 0;
        noExpenses.style.display = hasRows ? 'none' : 'block';
    }

    function updateExpenseOptions() {
        const selectedValues = getSelectedExpenseIds();

        expenseRows.querySelectorAll('.expense-select').forEach(select => {
            const currentValue = select.value;

            select.querySelectorAll('option[data-expense-id]').forEach(option => {
                option.disabled =
                    selectedValues.includes(option.value) &&
                    option.value !== currentValue;
            });
        });
    }

    function updateAddButton() {
        const selectedCount = expenseRows.children.length;
        const allUsed =
            expenseTypes.length > 0 &&
            selectedCount >= expenseTypes.length;

        addExpenseButton.disabled = allUsed;

        if (allUsed) {
            addExpenseButton.classList.add(
                'cursor-not-allowed',
                'opacity-50'
            );

            addExpenseButton.setAttribute(
                'title',
                'All available expense types have been added.'
            );
        } else {
            addExpenseButton.classList.remove(
                'cursor-not-allowed',
                'opacity-50'
            );

            addExpenseButton.removeAttribute('title');
        }
    }

    function updateRowNumbers() {
        expenseRows
            .querySelectorAll('.expense-row-number')
            .forEach((element, index) => {
                element.textContent = index + 1;
            });
    }

    function addExpenseRow(expenseId = '', cost = '') {
        if (expenseRows.children.length >= expenseTypes.length) {
            return;
        }

        const row = document.createElement('tr');

        row.className =
            'expense-row border-b border-slate-100 align-middle';

        let options =
            '<option value="">Select Expense</option>';

        expenseTypes.forEach(expense => {
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
                '<span class="expense-row-number inline-flex h-7 w-7 ' +
                'items-center justify-center rounded-full bg-slate-100 ' +
                'text-xs font-bold text-slate-600"></span>' +
            '</td>' +

            '<td class="px-5 py-4">' +
                '<select ' +
                    'name="expenses[' + rowIndex + '][expense_id]" ' +
                    'class="expense-select w-full rounded-xl border ' +
                    'border-slate-300 bg-white px-3 py-2.5 text-sm ' +
                    'text-slate-700 outline-none transition ' +
                    'focus:border-sky-500 focus:ring-2 focus:ring-sky-100" ' +
                    'required>' +
                    options +
                '</select>' +
            '</td>' +

            '<td class="px-5 py-4">' +
                '<div class="relative">' +
                    '<span class="pointer-events-none absolute inset-y-0 ' +
                    'left-0 flex items-center pl-3 text-sm font-semibold ' +
                    'text-slate-400">₱</span>' +

                    '<input ' +
                        'type="number" ' +
                        'name="expenses[' + rowIndex + '][cost]" ' +
                        'class="cost-input w-full rounded-xl border ' +
                        'border-slate-300 bg-white py-2.5 pl-8 pr-3 ' +
                        'text-sm text-slate-700 outline-none transition ' +
                        'focus:border-sky-500 focus:ring-2 focus:ring-sky-100" ' +
                        'value="' + cost + '" ' +
                        'min="0" ' +
                        'step="0.01" ' +
                        'inputmode="decimal" ' +
                        'placeholder="0.00" ' +
                        'required>' +
                '</div>' +
            '</td>' +

            '<td class="px-5 py-4 text-right">' +
                '<button ' +
                    'type="button" ' +
                    'class="remove-expense inline-flex items-center ' +
                    'justify-center gap-1 rounded-lg border ' +
                    'border-red-200 bg-red-50 px-3 py-2 text-xs ' +
                    'font-semibold text-red-700 transition hover:bg-red-100">' +
                    '<i class="fa fa-trash"></i> Remove' +
                '</button>' +
            '</td>';

        expenseRows.appendChild(row);
        rowIndex++;

        updateRowNumbers();
        updateEmptyMessage();
        updateExpenseOptions();
        updateAddButton();
        updateTotal();
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
        updateTotal();
    });

    expenseRows.addEventListener('input', function (event) {
        if (event.target.classList.contains('cost-input')) {
            if (parseFloat(event.target.value) < 0) {
                event.target.value = 0;
            }

            updateTotal();
        }
    });

    expenseRows.addEventListener('change', function (event) {
        if (event.target.classList.contains('expense-select')) {
            updateExpenseOptions();
            updateAddButton();
        }
    });

    form.addEventListener('submit', function (event) {
        if (expenseRows.children.length === 0) {
            event.preventDefault();

            noExpenses.classList.remove('text-slate-500');
            noExpenses.classList.add('text-red-600');

            noExpenses.innerHTML =
                '<i class="fa fa-exclamation-circle ' +
                'mb-2 block text-2xl"></i>' +
                'Please add at least one expense before updating.';

            return;
        }

        const submitButton =
            form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;

            submitButton.classList.add(
                'cursor-not-allowed',
                'opacity-70'
            );

            submitButton.innerHTML =
                '<i class="fa fa-spinner fa-spin"></i>' +
                '<span>Updating...</span>';
        }
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
            addExpenseRow(
                expense.id,
                expense.cost
            );
        });
    @endif

    updateRowNumbers();
    updateEmptyMessage();
    updateExpenseOptions();
    updateAddButton();
    updateTotal();
});
</script>
@endsection

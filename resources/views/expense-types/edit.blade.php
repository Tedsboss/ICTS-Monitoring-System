@extends('layouts.app')

@section('content')
<div class="px-4 pb-8 pt-4">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0 shadow-none border-radius-xl z-index-sticky"
         id="navbarBlur"
         data-scroll="false">
        <div class="container-fluid px-0">
            @include('layouts.navbars.auth.topnav', ['title' => 'Expense Type Management'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>

    @php
        $allocationCount = $expenseType->allocationExpenses()->count();
        $isInUse = $allocationCount > 0;
    @endphp

    <div id="pageMessage"></div>

    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h4 class="mb-1 text-lg font-bold text-slate-800">
                    Edit Expense Type
                </h4>
                <p class="mb-0 text-sm text-slate-500">
                    Update the expense type used by Allocation Management.
                </p>
            </div>

            <a href="{{ route('expense-types.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300
                      bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm
                      transition hover:bg-slate-50">
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

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h5 class="mb-0 text-base font-bold text-slate-800">
                        Expense Type Information
                    </h5>

                    <p class="mb-0 mt-1 text-xs text-slate-500">
                        Update the expense classification and its description.
                    </p>
                </div>

                @if($isInUse)
                    <span class="inline-flex w-fit items-center rounded-full bg-sky-100 px-3 py-1
                                 text-xs font-semibold text-sky-700">
                        <i class="fa fa-link mr-1"></i>
                        {{ $allocationCount }}
                        {{ $allocationCount === 1 ? 'Allocation' : 'Allocations' }}
                    </span>
                @else
                    <span class="inline-flex w-fit items-center rounded-full bg-emerald-100 px-3 py-1
                                 text-xs font-semibold text-emerald-700">
                        <i class="fa fa-check-circle mr-1"></i>
                        Not in Use
                    </span>
                @endif
            </div>
        </div>

        <form method="POST"
              action="{{ route('expense-types.update', $expenseType) }}"
              id="expenseTypeForm">
            @csrf
            @method('PUT')

            <div class="p-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="type" class="mb-2 block text-sm font-semibold text-slate-700">
                            Expense Type <span class="text-red-500">*</span>
                        </label>

                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white
                                    transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200
                                        bg-slate-50 text-slate-400">
                                <i class="fa fa-money"></i>
                            </div>

                            <select name="type"
                                    id="type"
                                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                                           text-sm text-slate-700 outline-none focus:ring-0"
                                    required
                                    autofocus>
                                <option value="">Select Type</option>
                                <option value="MOOE" @selected(old('type', $expenseType->type) === 'MOOE')>
                                    MOOE
                                </option>
                                <option value="CO" @selected(old('type', $expenseType->type) === 'CO')>
                                    Capital Outlay (CO)
                                </option>
                            </select>
                        </div>

                        <p class="mb-0 mt-1.5 text-xs text-slate-500">
                            Select whether the expense belongs to MOOE or Capital Outlay.
                        </p>

                        @error('type')
                            <p class="mt-1.5 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="expense_description" class="mb-2 block text-sm font-semibold text-slate-700">
                            Expense Description <span class="text-red-500">*</span>
                        </label>

                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white
                                    transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200
                                        bg-slate-50 text-slate-400">
                                <i class="fa fa-file-text-o"></i>
                            </div>

                            <input type="text"
                                   name="expense_description"
                                   id="expense_description"
                                   maxlength="150"
                                   value="{{ old('expense_description', $expenseType->expense_description) }}"
                                   placeholder="e.g. ICT Equipment"
                                   autocomplete="off"
                                   class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                                          text-sm text-slate-700 outline-none focus:ring-0"
                                   required>
                        </div>

                        <p class="mb-0 mt-1.5 text-xs text-slate-500">
                            Enter the specific expense description used for allocation.
                        </p>

                        @error('expense_description')
                            <p class="mt-1.5 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                @if($isInUse)
                    <div class="mt-5 flex items-start gap-4 rounded-2xl border border-amber-100 bg-amber-50/60 p-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                            <i class="fa fa-exclamation-triangle"></i>
                        </div>

                        <div>
                            <h6 class="mb-1 text-sm font-bold text-amber-800">
                                Expense Type In Use
                            </h6>

                            <p class="mb-2 text-xs leading-5 text-slate-600">
                                This Expense Type is currently assigned to
                                <strong>{{ $allocationCount }}</strong>
                                {{ $allocationCount === 1 ? 'allocation' : 'allocations' }}.
                            </p>

                            <p class="mb-0 text-xs leading-5 text-slate-600">
                                Changes to the Type or Expense Description will be reflected
                                wherever this Expense Type is displayed.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="mt-5 flex items-start gap-4 rounded-2xl border border-sky-100 bg-sky-50/60 p-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-600 text-white">
                            <i class="fa fa-lightbulb-o"></i>
                        </div>

                        <div>
                            <h6 class="mb-1 text-sm font-bold text-sky-800">
                                About Expense Types
                            </h6>

                            <p class="mb-0 text-xs leading-5 text-slate-600">
                                Expense Types define the categories that can receive an allocation amount.
                                The same Type and Expense Description combination cannot be added twice.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4">
                <a href="{{ route('expense-types.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300
                          bg-white px-5 py-2.5 text-sm font-semibold text-slate-700
                          transition hover:bg-slate-50">
                    <i class="fa fa-times text-slate-400"></i>
                    <span>Cancel</span>
                </a>

                <button type="submit"
                        id="updateExpenseTypeButton"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600
                               px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                               transition hover:bg-sky-700">
                    <i class="fa fa-save" id="updateExpenseTypeIcon"></i>
                    <span id="updateExpenseTypeText">Update Expense Type</span>
                </button>
            </div>
        </form>
    </section>

    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('expenseTypeForm');
    const button = document.getElementById('updateExpenseTypeButton');
    const icon = document.getElementById('updateExpenseTypeIcon');
    const text = document.getElementById('updateExpenseTypeText');

    if (!form || !button) return;

    form.addEventListener('submit', function () {
        if (button.disabled) return;

        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');
        icon.className = 'fa fa-spinner fa-spin';
        text.textContent = 'Updating...';
    });
});
</script>
@endsection
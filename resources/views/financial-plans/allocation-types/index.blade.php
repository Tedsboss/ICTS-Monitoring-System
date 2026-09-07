@extends('layouts.app')
@section('content')
@php
    $isAllocationTypeAdmin = $isAdministrator;
    $allocationTypeModuleId = App\Models\Module::where('name', 'Allocation Type Management')->value('id');
    $allocationTypePermissions = $isAllocationTypeAdmin
        ? collect(['view', 'add', 'edit', 'delete'])
        : collect(optional(auth()->user()->role)->permissions ?? [])
            ->where('module_id', $allocationTypeModuleId)
            ->pluck('name')
            ->map(fn ($name) => strtolower((string) $name));
    $canAddAllocationType = $isAllocationTypeAdmin || $allocationTypePermissions->contains('add');
    $canEditAllocationType = $isAllocationTypeAdmin || $allocationTypePermissions->contains('edit');
    $canDeleteAllocationType = $isAllocationTypeAdmin || $allocationTypePermissions->contains('delete');
@endphp
<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', [
            'title' => 'Financial Plan Allocation Types'
        ])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div class="px-4 pb-8 pt-4">
    {{-- Page Messages --}}
    <div id="pageMessage"></div>
    {{-- Header --}}
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h4 class="mb-1 text-lg font-bold text-slate-800">
                    Allocation Type Management
                </h4>
                <p class="mb-0 text-sm text-slate-500">
                    Configure the Financial Plan Allocation Types available to each Staff/Office.
                </p>
            </div>
            @if ($canAddAllocationType)
                <button
                    type="button"
                    id="btnAddAllocationType"
                    class="inline-flex items-center justify-center gap-2 rounded-lg
                           bg-sky-600 px-4 py-2 text-sm font-semibold text-white
                           shadow-sm transition hover:bg-sky-700"
                >
                    <i class="fa fa-plus"></i>
                    <span>Add Allocation Type</span>
                </button>
            @endif
        </div>
    </section>
    {{-- Staff / Office --}}
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-5">
            <div class="max-w-xl">
                <label
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Staff / Office
                </label>
                @if ($isAdministrator)
                    <select
                        id="staffFilter"
                        class="form-control"
                    >
                        @foreach ($staffs as $staff)
                            <option
                                value="{{ $staff->id }}"
                                @selected(
                                    (int) $selectedStaffId ===
                                    (int) $staff->id
                                )
                            >
                                {{ $staff->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mb-0 mt-2 text-xs text-slate-500">
                        Select the Staff/Office whose Allocation Types
                        you want to manage.
                    </p>
                @else
                    @php
                        $currentStaff = $staffs->first();
                    @endphp
                    <div
                        class="flex min-h-[42px] items-center rounded-lg
                            border border-slate-200 bg-slate-50
                            px-3 py-2"
                    >
                        <i
                            class="fa fa-building-o mr-2 text-slate-400"
                            aria-hidden="true"
                        ></i>
                        <span class="text-sm font-semibold text-slate-700">
                            {{ $currentStaff?->name ?? 'Staff/Office' }}
                        </span>
                    </div>
                    <p class="mb-0 mt-2 text-xs text-slate-500">
                        You can manage Allocation Types only for your
                        assigned Staff/Office.
                    </p>
                @endif
            </div>
        </div>
    </section>
    {{-- Allocation Types --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h5 class="mb-0 text-base font-bold text-slate-800">
                        Configured Allocation Types
                    </h5>
                    <p class="mb-0 mt-1 text-xs text-slate-500">
                        These options will appear in the Financial Plan Builder.
                    </p>
                </div>
                <span
                    id="allocationTypeCount"
                    class="inline-flex items-center rounded-full bg-slate-100
                           px-3 py-1 text-xs font-semibold text-slate-600"
                >
                    {{ $allocationTypes->count() }}
                    {{ $allocationTypes->count() === 1 ? 'type' : 'types' }}
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-left">
                        <th class="px-5 py-3 font-semibold text-slate-600">
                            Code
                        </th>
                        <th class="px-5 py-3 font-semibold text-slate-600">
                            Name
                        </th>
                        <th class="px-5 py-3 text-center font-semibold text-slate-600">
                            MOOE
                        </th>
                        <th class="px-5 py-3 text-center font-semibold text-slate-600">
                            Capital Outlay
                        </th>
                        <th class="px-5 py-3 text-center font-semibold text-slate-600">
                            Sort Order
                        </th>
                        <th class="px-5 py-3 text-center font-semibold text-slate-600">
                            Status
                        </th>
                        <th class="px-5 py-3 text-right font-semibold text-slate-600">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody id="allocationTypeBody">
                    @forelse ($allocationTypes as $allocationType)
                        <tr
                            class="allocation-type-row border-b border-slate-100"
                            data-id="{{ $allocationType->id }}"
                            data-staff-id="{{ $allocationType->staff_id }}"
                            data-code="{{ $allocationType->code }}"
                            data-name="{{ $allocationType->name }}"
                            data-allows-mooe="{{ $allocationType->allows_mooe ? 1 : 0 }}"
                            data-allows-capital-outlay="{{ $allocationType->allows_capital_outlay ? 1 : 0 }}"
                            data-sort-order="{{ $allocationType->sort_order }}"
                            data-is-active="{{ $allocationType->is_active ? 1 : 0 }}"
                        >
                            <td class="px-5 py-4">
                                <span class="font-mono font-semibold text-slate-700">
                                    {{ $allocationType->code }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-medium text-slate-700">
                                {{ $allocationType->name }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if ($allocationType->allows_mooe)
                                    <span
                                        class="inline-flex rounded-full bg-emerald-100
                                               px-2.5 py-1 text-xs font-semibold
                                               text-emerald-700"
                                    >
                                        Yes
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-full bg-slate-100
                                               px-2.5 py-1 text-xs font-semibold
                                               text-slate-500"
                                    >
                                        No
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if ($allocationType->allows_capital_outlay)
                                    <span
                                        class="inline-flex rounded-full bg-emerald-100
                                               px-2.5 py-1 text-xs font-semibold
                                               text-emerald-700"
                                    >
                                        Yes
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-full bg-slate-100
                                               px-2.5 py-1 text-xs font-semibold
                                               text-slate-500"
                                    >
                                        No
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center text-slate-600">
                                {{ $allocationType->sort_order }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if ($allocationType->is_active)
                                    <span
                                        class="inline-flex rounded-full bg-sky-100
                                               px-2.5 py-1 text-xs font-semibold
                                               text-sky-700"
                                    >
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-full bg-slate-100
                                               px-2.5 py-1 text-xs font-semibold
                                               text-slate-500"
                                    >
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if ($canEditAllocationType || $canDeleteAllocationType)
                                    <div class="inline-flex items-center gap-2">
                                        @if ($canEditAllocationType)
                                        <button
                                            type="button"
                                            class="btn-edit-allocation-type inline-flex items-center
                                                   justify-center rounded-lg border border-sky-200
                                                   bg-sky-50 px-3 py-2 text-xs font-semibold
                                                   text-sky-700 transition hover:bg-sky-100"
                                        >
                                            <i class="fa fa-pencil mr-1"></i>
                                            Edit
                                        </button>
                                        @endif
                                        @if ($canDeleteAllocationType)
                                        <button
                                            type="button"
                                            class="btn-delete-allocation-type inline-flex items-center
                                                   justify-center rounded-lg border border-red-200
                                                   bg-red-50 px-3 py-2 text-xs font-semibold
                                                   text-red-700 transition hover:bg-red-100"
                                        >
                                            <i class="fa fa-trash mr-1"></i>
                                            Delete
                                        </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyAllocationTypeRow">
                            <td
                                colspan="7"
                                class="px-5 py-12 text-center text-sm text-slate-500"
                            >
                                <i class="fa fa-folder-open-o mb-2 block text-2xl text-slate-300"></i>
                                No Allocation Types are configured for this Staff/Office.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @if ($canAddAllocationType || $canEditAllocationType)
        <div id="allocationTypeModal" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-slate-900/60 px-4 py-6 backdrop-blur-[1px]">
        <div class="w-full max-w-4xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-sky-50 text-xl text-sky-600">
                        <i class="fa fa-cube"></i>
                    </div>
                    <div>
                        <h5 id="allocationTypeModalTitle" class="mb-0 text-xl font-bold text-slate-800">Add Allocation Type</h5>
                        <p class="mb-0 mt-1 text-sm text-slate-500">Create an Allocation Type to be used in the Financial Plan.</p>
                    </div>
                </div>
                <button type="button" id="btnCloseAllocationTypeModal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm transition hover:bg-slate-50 hover:text-slate-700">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <form id="allocationTypeForm">
                @csrf
                <input type="hidden" id="allocationTypeId" value="">
                <input type="hidden" id="allocationTypeStaffId" name="staff_id" value="{{ $selectedStaffId }}">
                <div class="max-h-[72vh] overflow-y-auto px-6 py-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="allocationTypeCode" class="mb-2 block text-sm font-semibold text-slate-700">Code <span class="text-red-500">*</span></label>
                            <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                                <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-slate-400">
                                    <i class="fa fa-tag"></i>
                                </div>
                                <input type="text" id="allocationTypeCode" name="code" maxlength="50" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-0" placeholder="e.g. REG_FUND" required>
                            </div>
                            <p class="mb-0 mt-1.5 text-xs text-slate-500">A short internal code. Spaces will automatically be converted.</p>
                        </div>
                        <div>
                            <label for="allocationTypeName" class="mb-2 block text-sm font-semibold text-slate-700">Allocation Type Name <span class="text-red-500">*</span></label>
                            <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                                <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-slate-400">
                                    <i class="fa fa-file-text-o"></i>
                                </div>
                                <input type="text" id="allocationTypeName" name="name" maxlength="150" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-0" placeholder="e.g. Regular Fund" required>
                            </div>
                            <p class="mb-0 mt-1.5 text-xs text-slate-500">The display name of the Allocation Type.</p>
                        </div>
                    </div>
                    <div class="mt-5 rounded-2xl border border-slate-200 p-5">
                        <div class="mb-4">
                            <h6 class="mb-1 text-sm font-bold text-slate-800">Allowed Expense Categories <span class="text-red-500">*</span></h6>
                            <p class="mb-0 text-xs text-slate-500">Select the expense categories that are allowed for this Allocation Type.</p>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="group flex cursor-pointer items-start gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/40">
                                <input type="checkbox" id="allocationTypeAllowsMooe" name="allows_mooe" value="1" class="mt-1 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-sky-50 text-lg text-sky-600 group-hover:bg-white">
                                    <i class="fa fa-money"></i>
                                </span>
                                <span>
                                    <span class="block text-sm font-bold text-slate-800">MOOE</span>
                                    <span class="mt-1 block text-xs leading-5 text-slate-500">Maintenance and Other Operating Expenses</span>
                                </span>
                            </label>
                            <label class="group flex cursor-pointer items-start gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/40">
                                <input type="checkbox" id="allocationTypeAllowsCo" name="allows_capital_outlay" value="1" class="mt-1 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-sky-50 text-lg text-sky-600 group-hover:bg-white">
                                    <i class="fa fa-building"></i>
                                </span>
                                <span>
                                    <span class="block text-sm font-bold text-slate-800">Capital Outlay</span>
                                    <span class="mt-1 block text-xs leading-5 text-slate-500">Capital expenditure allocation</span>
                                </span>
                            </label>
                        </div>
                        <div class="mt-4 flex items-center gap-2 rounded-lg bg-sky-50 px-3 py-2.5 text-xs font-medium text-sky-700">
                            <i class="fa fa-info-circle"></i>
                            <span>At least one (1) expense category must be selected.</span>
                        </div>
                    </div>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="allocationTypeSortOrder" class="mb-2 block text-sm font-semibold text-slate-700">Sort Order</label>
                            <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                                <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-slate-400">
                                    <i class="fa fa-sort-numeric-asc"></i>
                                </div>
                                <input type="number" id="allocationTypeSortOrder" name="sort_order" min="0" max="9999" value="0" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-0">
                            </div>
                            <p class="mb-0 mt-1.5 text-xs text-slate-500">Determines the order in the Allocation Type list.</p>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                            <label class="flex min-h-[42px] cursor-pointer items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-2.5">
                                <span class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <i class="fa fa-toggle-on text-sky-600"></i>
                                    Active
                                </span>
                                <input type="checkbox" id="allocationTypeActive" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            </label>
                            <p class="mb-0 mt-1.5 text-xs text-slate-500">Active types are available in the Financial Plan Builder.</p>
                        </div>
                    </div>
                    <div class="mt-5 flex items-start gap-4 rounded-2xl border border-sky-100 bg-sky-50/60 p-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-600 text-white">
                            <i class="fa fa-lightbulb-o"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 text-sm font-bold text-sky-800">About Allocation Types</h6>
                            <p class="mb-0 text-xs leading-5 text-slate-600">Allocation Types define how budget is allocated and which expense categories can be used within the Financial Plan.</p>
                        </div>
                    </div>
                    <div id="allocationTypeErrors" class="mt-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" id="btnCancelAllocationType" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <i class="fa fa-times text-slate-400"></i>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" id="btnSaveAllocationType" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                        <i class="fa fa-save"></i>
                        <span>Save Allocation Type</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    {{-- Footer --}}
    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>
</div>
@endsection
@push('js')
<script>
$(function () {
    const indexUrl =
        '{{ route("financial-plan-allocation-types.index") }}';
    const storeUrl =
        '{{ route("financial-plan-allocation-types.store") }}';
    const updateUrlTemplate =
        '{{ route("financial-plan-allocation-types.update", ":id") }}';
    const destroyUrlTemplate =
        '{{ route("financial-plan-allocation-types.destroy", ":id") }}';
    // Show message
    function showMessage(message, type = 'success') {
        const success = type === 'success';
        const classes = success
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
            : 'border-red-200 bg-red-50 text-red-700';
        const icon = success
            ? 'fa-check-circle'
            : 'fa-exclamation-circle';
        $('#pageMessage').html(`
            <div class="mb-4 rounded-lg border px-4 py-3 text-sm ${classes}">
                <i class="fa ${icon} mr-1"></i>
                ${escapeHtml(message)}
            </div>
        `);
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    // Escape text
    function escapeHtml(value) {
        return $('<div>')
            .text(value ?? '')
            .html();
    }
    // Open modal
    function openModal() {
        $('#allocationTypeModal')
            .removeClass('hidden')
            .addClass('flex');
        $('body').addClass('overflow-hidden');
    }
    // Close modal
    function closeModal() {
        $('#allocationTypeModal')
            .addClass('hidden')
            .removeClass('flex');
        $('body').removeClass('overflow-hidden');
        $('#allocationTypeErrors')
            .addClass('hidden')
            .empty();
    }
    // Reset form
    function resetForm() {
        $('#allocationTypeForm')[0].reset();
        $('#allocationTypeId').val('');
        $('#allocationTypeStaffId')
            .val('{{ (int) $selectedStaffId }}');
        $('#allocationTypeCode')
            .prop('readonly', false);
        $('#allocationTypeSortOrder').val(0);
        $('#allocationTypeActive')
            .prop('checked', true);
        $('#allocationTypeModalTitle')
            .text('Add Allocation Type');
        $('#allocationTypeErrors')
            .addClass('hidden')
            .empty();
    }
    // Display validation errors
    function showErrors(xhr) {
        const errors =
            xhr.responseJSON?.errors || {};
        const messages = [];
        Object.values(errors).forEach(function (items) {
            if (Array.isArray(items)) {
                items.forEach(function (message) {
                    messages.push(message);
                });
            }
        });
        if (!messages.length) {
            messages.push(
                xhr.responseJSON?.message ||
                'Unable to save Allocation Type.'
            );
        }
        $('#allocationTypeErrors')
            .removeClass('hidden')
            .html(
                messages
                    .map(function (message) {
                        return `
                            <div>
                                <i class="fa fa-exclamation-circle mr-1"></i>
                                ${escapeHtml(message)}
                            </div>
                        `;
                    })
                    .join('')
            );
    }
    @if ($isAdministrator)
    // Change Staff/Office
        $('#staffFilter').on('change', function () {
            const staffId = $(this).val();
            window.location.href =
                indexUrl +
                '?staff_id=' +
                encodeURIComponent(staffId);
        });
    @endif
    // Add
    $('#btnAddAllocationType').on('click', function () {
        resetForm();
        openModal();
    });
    // Edit
    $(document).on(
        'click',
        '.btn-edit-allocation-type',
        function () {
            const $row =
                $(this).closest('.allocation-type-row');
            resetForm();
            $('#allocationTypeId')
                .val($row.data('id'));
            $('#allocationTypeStaffId')
                .val($row.data('staff-id'));
            $('#allocationTypeCode')
                .val($row.data('code'));
            $('#allocationTypeName')
                .val($row.data('name'));
            $('#allocationTypeAllowsMooe')
                .prop(
                    'checked',
                    Number($row.data('allows-mooe')) === 1
                );
            $('#allocationTypeAllowsCo')
                .prop(
                    'checked',
                    Number(
                        $row.data('allows-capital-outlay')
                    ) === 1
                );
            $('#allocationTypeSortOrder')
                .val($row.data('sort-order'));
            $('#allocationTypeActive')
                .prop(
                    'checked',
                    Number($row.data('is-active')) === 1
                );
            $('#allocationTypeModalTitle')
                .text('Edit Allocation Type');
            openModal();
        }
    );
    // Close
    $('#btnCloseAllocationTypeModal, #btnCancelAllocationType')
        .on('click', function () {
            closeModal();
        });
    // Close by clicking backdrop
    $('#allocationTypeModal').on('click', function (event) {
        if (event.target === this) {
            closeModal();
        }
    });
    // Close with Escape
    $(document).on('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            !$('#allocationTypeModal').hasClass('hidden')
        ) {
            closeModal();
        }
    });
    // Save
    $('#allocationTypeForm').on('submit', function (event) {
        event.preventDefault();
        const id =
            $('#allocationTypeId').val();
        const isEdit =
            id !== '';
        const url = isEdit
            ? updateUrlTemplate.replace(':id', id)
            : storeUrl;
        const payload = {
            staff_id:
                Number($('#allocationTypeStaffId').val()),
            code:
                $('#allocationTypeCode')
                    .val()
                    .trim(),
            name:
                $('#allocationTypeName')
                    .val()
                    .trim(),
            allows_mooe:
                $('#allocationTypeAllowsMooe')
                    .is(':checked')
                    ? 1
                    : 0,
            allows_capital_outlay:
                $('#allocationTypeAllowsCo')
                    .is(':checked')
                    ? 1
                    : 0,
            sort_order:
                Number(
                    $('#allocationTypeSortOrder').val()
                ) || 0,
            is_active:
                $('#allocationTypeActive')
                    .is(':checked')
                    ? 1
                    : 0
        };
        const $button =
            $('#btnSaveAllocationType');
        const originalHtml =
            $button.html();
        $('#allocationTypeErrors')
            .addClass('hidden')
            .empty();
        $button
            .prop('disabled', true)
            .html(
                '<i class="fa fa-spinner fa-spin"></i>' +
                '<span> Saving...</span>'
            );
        $.ajax({
            url: url,
            type: isEdit
                ? 'PUT'
                : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            headers: {
                'X-CSRF-TOKEN':
                    '{{ csrf_token() }}'
            }
        }).done(function (response) {
            closeModal();
            showMessage(
                response.message ||
                'Allocation Type saved successfully.'
            );
            setTimeout(function () {
                window.location.reload();
            }, 500);
        }).fail(function (xhr) {
            showErrors(xhr);
        }).always(function () {
            $button
                .prop('disabled', false)
                .html(originalHtml);
        });
    });
    // Delete
    $(document).on(
        'click',
        '.btn-delete-allocation-type',
        function () {
            const $row =
                $(this).closest('.allocation-type-row');
            const id =
                $row.data('id');
            const name =
                $row.data('name');
            if (
                !confirm(
                    'Delete "' +
                    name +
                    '"?\n\n' +
                    'Allocation Types already used by a Financial Plan cannot be deleted.'
                )
            ) {
                return;
            }
            const $button =
                $(this);
            const originalHtml =
                $button.html();
            $button
                .prop('disabled', true)
                .html(
                    '<i class="fa fa-spinner fa-spin"></i>'
                );
            $.ajax({
                url:
                    destroyUrlTemplate.replace(
                        ':id',
                        id
                    ),
                type:
                    'DELETE',
                headers: {
                    'X-CSRF-TOKEN':
                        '{{ csrf_token() }}'
                }
            }).done(function (response) {
                $row.remove();
                showMessage(
                    response.message ||
                    'Allocation Type deleted successfully.'
                );
                const count =
                    $('.allocation-type-row').length;
                $('#allocationTypeCount')
                    .text(
                        count +
                        ' ' +
                        (
                            count === 1
                                ? 'type'
                                : 'types'
                        )
                    );
                if (count === 0) {
                    $('#allocationTypeBody').html(`
                        <tr id="emptyAllocationTypeRow">
                            <td
                                colspan="7"
                                class="px-5 py-12 text-center text-sm text-slate-500"
                            >
                                <i class="fa fa-folder-open-o mb-2 block text-2xl text-slate-300"></i>
                                No Allocation Types are configured for this Staff/Office.
                            </td>
                        </tr>
                    `);
                }
            }).fail(function (xhr) {
                showMessage(
                    xhr.responseJSON?.errors?.allocation_type?.[0] ||
                    xhr.responseJSON?.message ||
                    'Unable to delete Allocation Type.',
                    'danger'
                );
                $button
                    .prop('disabled', false)
                    .html(originalHtml);
            });
        }
    );
});
</script>
@endpush

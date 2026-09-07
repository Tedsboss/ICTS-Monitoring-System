@extends('layouts.app')

@section('content')

    {{-- Navbar --}}
    <nav
        class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
        id="navbarBlur"
        data-scroll="false"
    >
        <div class="container-fluid py-1 px-3">
            @include('layouts.navbars.auth.topnav', ['title' => 'Staff Personnel'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>

    {{-- Main content --}}
    <div class="container-fluid">

        {{-- Success message --}}
        @if (session()->has('succes'))
            <div
                class="mt-4 flex items-start gap-3 rounded-xl
                       border border-emerald-200 bg-emerald-50
                       px-4 py-3 text-sm text-emerald-800"
            >
                <i class="fa fa-check-circle mt-0.5" aria-hidden="true"></i>

                <div class="flex-1">
                    {{ session('succes') }}
                </div>
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div
                class="mt-4 flex items-start gap-3 rounded-xl
                       border border-rose-200 bg-rose-50
                       px-4 py-3 text-sm text-rose-800"
            >
                <i
                    class="fa fa-exclamation-circle mt-0.5"
                    aria-hidden="true"
                ></i>

                <div class="flex-1">
                    <div class="font-semibold">
                        Please check the information below.
                    </div>

                    <ul class="mb-0 mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Main card --}}
        <div class="mt-4">
            <div
                class="overflow-hidden rounded-2xl
                       border border-slate-200 bg-white shadow-sm"
            >

                {{-- Header --}}
                <div
                    class="flex flex-col gap-4 border-b border-slate-200
                           px-5 py-5
                           xl:flex-row xl:items-center xl:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center
                                   justify-center rounded-xl
                                   bg-slate-100 text-slate-600"
                        >
                            <i class="fa fa-users" aria-hidden="true"></i>
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2
                                    class="mb-0 text-base font-bold
                                           text-slate-900"
                                >
                                    Staff Personnel
                                </h2>

                                <span
                                    class="inline-flex items-center
                                           rounded-full bg-slate-100
                                           px-2.5 py-1 text-xs
                                           font-semibold text-slate-600"
                                >
                                    {{ $personnel->count() }}
                                    {{ $personnel->count() === 1 ? 'person' : 'personnel' }}
                                </span>
                            </div>

                            <p class="mb-0 mt-1 text-sm text-slate-500">
                                Manage personnel available for Financial Plan assignments.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        id="btnAddPersonnel"
                        class="inline-flex w-fit items-center gap-2
                               rounded-lg border-0 bg-sky-600
                               px-4 py-2.5 text-sm font-semibold
                               text-white shadow-sm transition
                               hover:bg-sky-700"
                    >
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        Add Personnel
                    </button>
                </div>

                {{-- Toolbar --}}
                <div
                    class="flex flex-col gap-4 border-b border-slate-200
                           bg-slate-50/50 px-5 py-4
                           lg:flex-row lg:items-end
                           lg:justify-between"
                >
                    <form
                        method="GET"
                        action="{{ route('staff-personnel.index') }}"
                        class="flex flex-col gap-3 sm:flex-row sm:items-end"
                    >
                        <div>
                            <label
                                for="staffFilter"
                                class="mb-1.5 block text-xs
                                       font-semibold uppercase
                                       tracking-wide text-slate-600"
                            >
                                Staff / Office
                            </label>

                            <select
                                id="staffFilter"
                                name="staff_id"
                                class="block w-full min-w-0 rounded-lg
                                       border border-slate-300 bg-white
                                       px-3 py-2 text-sm text-slate-700
                                       shadow-sm outline-none transition
                                       focus:border-sky-500
                                       focus:ring-2 focus:ring-sky-100
                                       sm:min-w-[360px]"
                            >
                                <option value="">
                                    All Staff / Offices
                                </option>

                                @foreach ($staffs as $staff)
                                    <option
                                        value="{{ $staff->id }}"
                                        {{ (string) $staffId === (string) $staff->id ? 'selected' : '' }}
                                    >
                                        {{ $staff->name }}

                                        @if (filled($staff->abbreviation))
                                            ({{ $staff->abbreviation }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="submit"
                                class="inline-flex h-[38px] items-center
                                       gap-2 rounded-lg border
                                       border-slate-300 bg-white
                                       px-4 text-sm font-semibold
                                       text-slate-700 shadow-sm
                                       transition hover:bg-slate-50"
                            >
                                <i
                                    class="fa fa-filter"
                                    aria-hidden="true"
                                ></i>

                                Filter
                            </button>

                            @if ($staffId !== null)
                                <a
                                    href="{{ route('staff-personnel.index') }}"
                                    class="inline-flex h-[38px]
                                           items-center gap-2 rounded-lg
                                           border border-slate-300
                                           bg-white px-4 text-sm
                                           font-semibold text-slate-700
                                           shadow-sm transition
                                           hover:bg-slate-50"
                                >
                                    <i
                                        class="fa fa-refresh"
                                        aria-hidden="true"
                                    ></i>

                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>

                    <div
                        class="inline-flex w-fit items-center gap-2
                               rounded-full border border-slate-200
                               bg-white px-3 py-1.5
                               text-xs font-semibold text-slate-600"
                    >
                        <i class="fa fa-sitemap" aria-hidden="true"></i>
                        Personnel Directory
                    </div>
                </div>

                {{-- Information --}}
                <div
                    class="flex items-start gap-2
                           border-b border-sky-100
                           bg-sky-50 px-5 py-3
                           text-xs text-sky-700"
                >
                    <i
                        class="fa fa-info-circle mt-0.5"
                        aria-hidden="true"
                    ></i>

                    <span>
                        Inactive personnel will not appear in new Financial Plan
                        Assigned Personnel selections.
                    </span>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[900px] border-collapse"
                    >
                        <thead>
                            <tr class="bg-slate-50">
                                <th
                                    class="whitespace-nowrap border-b
                                           border-slate-200 px-5 py-3
                                           text-left text-xs font-bold
                                           uppercase tracking-wide
                                           text-slate-500"
                                >
                                    Name
                                </th>

                                <th
                                    class="whitespace-nowrap border-b
                                           border-slate-200 px-4 py-3
                                           text-left text-xs font-bold
                                           uppercase tracking-wide
                                           text-slate-500"
                                >
                                    Position
                                </th>

                                <th
                                    class="whitespace-nowrap border-b
                                           border-slate-200 px-4 py-3
                                           text-left text-xs font-bold
                                           uppercase tracking-wide
                                           text-slate-500"
                                >
                                    Staff / Office
                                </th>

                                <th
                                    class="whitespace-nowrap border-b
                                           border-slate-200 px-4 py-3
                                           text-center text-xs font-bold
                                           uppercase tracking-wide
                                           text-slate-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="whitespace-nowrap border-b
                                           border-slate-200 px-5 py-3
                                           text-right text-xs font-bold
                                           uppercase tracking-wide
                                           text-slate-500"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($personnel as $person)
                                <tr class="transition hover:bg-slate-50">

                                    {{-- Name --}}
                                    <td
                                        class="border-b border-slate-100
                                               px-5 py-4"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0
                                                       items-center justify-center
                                                       rounded-full bg-slate-100
                                                       text-xs font-bold
                                                       uppercase text-slate-600"
                                            >
                                                {{ mb_substr(trim($person->name), 0, 1) }}
                                            </div>

                                            <div
                                                class="text-sm font-bold
                                                       text-slate-900"
                                            >
                                                {{ $person->name }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Position --}}
                                    <td
                                        class="border-b border-slate-100
                                               px-4 py-4 text-sm
                                               text-slate-600"
                                    >
                                        @if (filled($person->position))
                                            {{ $person->position }}
                                        @else
                                            <span class="text-slate-400">
                                                —
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Staff --}}
                                    <td
                                        class="border-b border-slate-100
                                               px-4 py-4"
                                    >
                                        @if ($person->staff)
                                            <div
                                                class="max-w-[420px]
                                                       text-sm font-semibold
                                                       text-slate-800"
                                            >
                                                {{ $person->staff->name }}
                                            </div>

                                            @if (filled($person->staff->abbreviation))
                                                <div
                                                    class="mt-1 text-xs
                                                           text-slate-500"
                                                >
                                                    {{ $person->staff->abbreviation }}
                                                </div>
                                            @endif
                                        @else
                                            <span
                                                class="inline-flex items-center
                                                       gap-1.5 text-sm
                                                       font-medium text-rose-500"
                                            >
                                                <i
                                                    class="fa fa-exclamation-circle"
                                                    aria-hidden="true"
                                                ></i>

                                                Staff unavailable
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td
                                        class="border-b border-slate-100
                                               px-4 py-4 text-center"
                                    >
                                        @if ($person->is_active)
                                            <span
                                                class="inline-flex items-center
                                                       gap-1.5 rounded-full
                                                       bg-emerald-100
                                                       px-3 py-1.5
                                                       text-xs font-bold
                                                       text-emerald-700"
                                            >
                                                <i
                                                    class="fa fa-check-circle"
                                                    aria-hidden="true"
                                                ></i>

                                                Active
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center
                                                       gap-1.5 rounded-full
                                                       bg-slate-100
                                                       px-3 py-1.5
                                                       text-xs font-bold
                                                       text-slate-600"
                                            >
                                                <i
                                                    class="fa fa-ban"
                                                    aria-hidden="true"
                                                ></i>

                                                Inactive
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td
                                        class="border-b border-slate-100
                                               px-5 py-4"
                                    >
                                        <div
                                            class="flex flex-wrap items-center
                                                   justify-end gap-2"
                                        >
                                            <button
                                                type="button"
                                                class="btn-edit-personnel
                                                       inline-flex h-[32px]
                                                       items-center gap-1.5
                                                       rounded-lg border
                                                       border-sky-200
                                                       bg-sky-50 px-3
                                                       text-xs font-semibold
                                                       text-sky-700
                                                       transition
                                                       hover:bg-sky-100"
                                                data-name="{{ $person->name }}"
                                                data-position="{{ $person->position }}"
                                                data-staff-id="{{ $person->staff_id }}"
                                                data-update-url="{{ route('staff-personnel.update', $person->id) }}"
                                                title="Edit personnel"
                                            >
                                                <i
                                                    class="fa fa-pencil"
                                                    aria-hidden="true"
                                                ></i>

                                                Edit
                                            </button>

                                            <a
                                                href="{{ route('staff-personnel.switchstatus', $person->id) }}"
                                                class="inline-flex h-[32px]
                                                       items-center gap-1.5
                                                       rounded-lg border
                                                       px-3 text-xs
                                                       font-semibold transition
                                                       {{ $person->is_active
                                                            ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                            : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                                       }}"
                                                title="{{ $person->is_active ? 'Deactivate personnel' : 'Activate personnel' }}"
                                                onclick="return confirm(
                                                    '{{ $person->is_active ? 'Deactivate' : 'Activate' }} {{ addslashes($person->name) }}?'
                                                );"
                                            >
                                                @if ($person->is_active)
                                                    <i
                                                        class="fa fa-lock"
                                                        aria-hidden="true"
                                                    ></i>

                                                    Deactivate
                                                @else
                                                    <i
                                                        class="fa fa-unlock"
                                                        aria-hidden="true"
                                                    ></i>

                                                    Activate
                                                @endif
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('staff-personnel.destroy', $person->id) }}"
                                                class="m-0"
                                                onsubmit="return confirm(
                                                    'Delete {{ addslashes($person->name) }}? Use Deactivate instead if this person should only be removed from future selections.'
                                                );"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex h-[32px]
                                                           w-[34px] items-center
                                                           justify-center
                                                           rounded-lg border
                                                           border-rose-200
                                                           bg-rose-50 text-xs
                                                           text-rose-600
                                                           transition
                                                           hover:bg-rose-100"
                                                    title="Delete personnel"
                                                >
                                                    <i
                                                        class="fa fa-trash"
                                                        aria-hidden="true"
                                                    ></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="5"
                                        class="px-6 py-14 text-center"
                                    >
                                        <div
                                            class="mx-auto flex h-12 w-12
                                                   items-center justify-center
                                                   rounded-full bg-slate-100
                                                   text-xl text-slate-400"
                                        >
                                            <i
                                                class="fa fa-users"
                                                aria-hidden="true"
                                            ></i>
                                        </div>

                                        <p
                                            class="mb-0 mt-3 text-sm
                                                   font-semibold
                                                   text-slate-600"
                                        >
                                            No staff personnel found.
                                        </p>

                                        <p
                                            class="mb-0 mt-1 text-xs
                                                   text-slate-400"
                                        >
                                            Add personnel to make them available
                                            in Financial Plan assignments.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        @include('layouts.footers.auth.footer')

    </div>

    {{-- Personnel modal --}}
    <div
        id="personnelModal"
        class="fixed inset-0 z-[9999] hidden
               items-center justify-center p-4"
    >
        {{-- Backdrop --}}
        <div
            id="personnelModalBackdrop"
            class="absolute inset-0 bg-slate-900/60"
        ></div>

        {{-- Modal --}}
        <div
            class="relative z-10 w-full max-w-lg
                   overflow-hidden rounded-2xl bg-white
                   shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="personnelModalTitle"
        >

            {{-- Modal header --}}
            <div
                class="flex items-start justify-between
                       border-b border-slate-200 px-5 py-4"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 items-center
                               justify-center rounded-lg
                               bg-slate-100 text-slate-600"
                    >
                        <i class="fa fa-user" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2
                            id="personnelModalTitle"
                            class="mb-0 text-base font-bold
                                   text-slate-900"
                        >
                            Add Personnel
                        </h2>

                        <p class="mb-0 mt-1 text-xs text-slate-500">
                            Personnel do not need a DIREK user account.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    id="btnClosePersonnelModal"
                    class="inline-flex h-8 w-8 items-center
                           justify-center rounded-lg border-0
                           bg-transparent text-slate-400
                           transition hover:bg-slate-100
                           hover:text-slate-700"
                    aria-label="Close"
                >
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Form --}}
            <form
                id="personnelForm"
                method="POST"
                action="{{ route('staff-personnel.store') }}"
            >
                @csrf

                <div id="personnelMethodContainer"></div>

                <div class="space-y-4 p-5">

                    {{-- Staff --}}
                    <div>
                        <label
                            for="personnelStaffId"
                            class="mb-1.5 block text-xs
                                   font-semibold uppercase
                                   tracking-wide text-slate-600"
                        >
                            Staff / Office
                            <span class="text-rose-500">*</span>
                        </label>

                        <select
                            id="personnelStaffId"
                            name="staff_id"
                            required
                            class="block w-full rounded-lg
                                   border border-slate-300 bg-white
                                   px-3 py-2.5 text-sm
                                   text-slate-700 shadow-sm
                                   outline-none transition
                                   focus:border-sky-500
                                   focus:ring-2 focus:ring-sky-100"
                        >
                            <option value="">
                                Select Staff / Office
                            </option>

                            @foreach ($staffs as $staff)
                                <option value="{{ $staff->id }}">
                                    {{ $staff->name }}

                                    @if (filled($staff->abbreviation))
                                        ({{ $staff->abbreviation }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Name --}}
                    <div>
                        <label
                            for="personnelName"
                            class="mb-1.5 block text-xs
                                   font-semibold uppercase
                                   tracking-wide text-slate-600"
                        >
                            Personnel Name
                            <span class="text-rose-500">*</span>
                        </label>

                        <input
                            type="text"
                            id="personnelName"
                            name="name"
                            maxlength="150"
                            required
                            autocomplete="off"
                            placeholder="e.g. Juan Dela Cruz"
                            class="block w-full rounded-lg
                                   border border-slate-300 bg-white
                                   px-3 py-2.5 text-sm
                                   text-slate-700 shadow-sm
                                   outline-none transition
                                   placeholder:text-slate-400
                                   focus:border-sky-500
                                   focus:ring-2 focus:ring-sky-100"
                        >
                    </div>

                    {{-- Position --}}
                    <div>
                        <label
                            for="personnelPosition"
                            class="mb-1.5 block text-xs
                                   font-semibold uppercase
                                   tracking-wide text-slate-600"
                        >
                            Position
                        </label>

                        <input
                            type="text"
                            id="personnelPosition"
                            name="position"
                            maxlength="150"
                            autocomplete="off"
                            placeholder="e.g. Information Systems Analyst"
                            class="block w-full rounded-lg
                                   border border-slate-300 bg-white
                                   px-3 py-2.5 text-sm
                                   text-slate-700 shadow-sm
                                   outline-none transition
                                   placeholder:text-slate-400
                                   focus:border-sky-500
                                   focus:ring-2 focus:ring-sky-100"
                        >
                    </div>

                </div>

                {{-- Modal footer --}}
                <div
                    class="flex items-center justify-end gap-2
                           border-t border-slate-200 bg-slate-50
                           px-5 py-4"
                >
                    <button
                        type="button"
                        id="btnCancelPersonnel"
                        class="inline-flex items-center
                               rounded-lg border border-slate-300
                               bg-white px-4 py-2.5
                               text-sm font-semibold
                               text-slate-700 shadow-sm
                               transition hover:bg-slate-100"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2
                               rounded-lg border-0 bg-sky-600
                               px-4 py-2.5 text-sm
                               font-semibold text-white
                               shadow-sm transition
                               hover:bg-sky-700"
                    >
                        <i class="fa fa-save" aria-hidden="true"></i>

                        <span id="savePersonnelText">
                            Save Personnel
                        </span>
                    </button>
                </div>
            </form>

        </div>
    </div>

@endsection

@push('js')

<script>
$(document).ready(function () {
    const $modal = $('#personnelModal');
    const $form = $('#personnelForm');
    const $methodContainer = $('#personnelMethodContainer');
    const $modalTitle = $('#personnelModalTitle');
    const $saveText = $('#savePersonnelText');
    const $staffInput = $('#personnelStaffId');
    const $nameInput = $('#personnelName');
    const $positionInput = $('#personnelPosition');

    const storeUrl = @json(route('staff-personnel.store'));
    const currentStaffId = @json($staffId);

    // Open modal
    function openPersonnelModal() {
        $modal
            .removeClass('hidden')
            .addClass('flex');

        $('body').css('overflow', 'hidden');

        setTimeout(function () {
            $nameInput.trigger('focus');
        }, 50);
    }

    // Close modal
    function closePersonnelModal() {
        $modal
            .addClass('hidden')
            .removeClass('flex');

        $('body').css('overflow', '');
    }

    // Prepare add form
    function prepareAddPersonnel() {
        $form[0].reset();

        $form.attr(
            'action',
            storeUrl
        );

        $methodContainer.empty();

        $modalTitle.text(
            'Add Personnel'
        );

        $saveText.text(
            'Save Personnel'
        );

        if (currentStaffId) {
            $staffInput.val(
                String(currentStaffId)
            );
        }
    }

    // Prepare edit form
    function prepareEditPersonnel($button) {
        $form[0].reset();

        $form.attr(
            'action',
            $button.data('update-url')
        );

        $methodContainer.html(
            '<input type="hidden" name="_method" value="PUT">'
        );

        $modalTitle.text(
            'Edit Personnel'
        );

        $saveText.text(
            'Update Personnel'
        );

        $staffInput.val(
            String(
                $button.data('staff-id') || ''
            )
        );

        $nameInput.val(
            $button.data('name') || ''
        );

        $positionInput.val(
            $button.data('position') || ''
        );
    }

    // Add personnel
    $('#btnAddPersonnel').on(
        'click',
        function () {
            prepareAddPersonnel();
            openPersonnelModal();
        }
    );

    // Edit personnel
    $('.btn-edit-personnel').on(
        'click',
        function () {
            prepareEditPersonnel(
                $(this)
            );

            openPersonnelModal();
        }
    );

    // Close modal
    $('#btnClosePersonnelModal, #btnCancelPersonnel').on(
        'click',
        closePersonnelModal
    );

    // Close from backdrop
    $('#personnelModalBackdrop').on(
        'click',
        closePersonnelModal
    );

    // Close with Escape
    $(document).on(
        'keydown',
        function (event) {
            if (
                event.key === 'Escape'
                && !$modal.hasClass('hidden')
            ) {
                closePersonnelModal();
            }
        }
    );
});
</script>

@endpush

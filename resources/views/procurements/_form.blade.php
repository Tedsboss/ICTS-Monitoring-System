@csrf

{{-- Procurement Information --}}
<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

    {{-- Funding Source --}}
    <div>
        <label
            for="funding_source"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
        >
            Funding Source
        </label>

        <select
            id="funding_source"
            name="funding_source"
            class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition
                   {{ $errors->has('funding_source')
                        ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                        : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
            required
        >
            <option value="">Select...</option>

            @foreach ($fundingSourceOptions as $option)
                <option
                    value="{{ $option }}"
                    @selected(
                        old(
                            'funding_source',
                            $procurement->funding_source ?? ''
                        ) === $option
                    )
                >
                    {{ $option }}
                </option>
            @endforeach
        </select>

        @error('funding_source')
            <p class="mt-1.5 text-xs font-medium text-rose-600">
                {{ $message }}
            </p>
        @enderror
    </div>


    {{-- Expense Class --}}
    <div>
        <label
            for="expense_class"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
        >
            Expense Class
        </label>

        <select
            id="expense_class"
            name="expense_class"
            class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition
                   {{ $errors->has('expense_class')
                        ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                        : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
            required
        >
            <option value="">Select...</option>

            @foreach (['MOOE', 'CO'] as $class)
                <option
                    value="{{ $class }}"
                    @selected(
                        old(
                            'expense_class',
                            $procurement->expense_class ?? ''
                        ) === $class
                    )
                >
                    {{ $class }}
                </option>
            @endforeach
        </select>

        @error('expense_class')
            <p class="mt-1.5 text-xs font-medium text-rose-600">
                {{ $message }}
            </p>
        @enderror
    </div>


    {{-- Division Assigned --}}
    <div>
        <label
            for="division_assigned"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
        >
            Division Assigned
        </label>

        <select
            id="division_assigned"
            name="division_assigned"
            class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition
                   {{ $errors->has('division_assigned')
                        ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                        : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
            required
        >
            <option value="">Select...</option>

            @foreach ($divisions as $division)

                @php
                    $value = $division->abbreviation ?: $division->name;
                @endphp

                <option
                    value="{{ $value }}"
                    @selected(
                        old(
                            'division_assigned',
                            $procurement->division_assigned ?? ''
                        ) === $value
                    )
                >
                    {{ $division->name }}

                    @if ($division->abbreviation)
                        ({{ $division->abbreviation }})
                    @endif
                </option>

            @endforeach
        </select>

        @error('division_assigned')
            <p class="mt-1.5 text-xs font-medium text-rose-600">
                {{ $message }}
            </p>
        @enderror
    </div>


    {{-- Procurement Title --}}
    <div class="md:col-span-2 xl:col-span-3">

        <label
            for="procurement_title"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
        >
            Procurement Title
        </label>

        <input
            id="procurement_title"
            type="text"
            name="procurement_title"
            value="{{ old('procurement_title', $procurement->procurement_title ?? '') }}"
            maxlength="255"
            placeholder="Enter procurement title"
            class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition
                   placeholder:text-slate-400
                   {{ $errors->has('procurement_title')
                        ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                        : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
            required
        >

        @error('procurement_title')
            <p class="mt-1.5 text-xs font-medium text-rose-600">
                {{ $message }}
            </p>
        @enderror

    </div>

</div>


{{-- Procurement Details --}}
<div class="mt-7 border-t border-slate-200 pt-6">

    <div class="mb-4">

        <h3 class="text-sm font-bold text-slate-900">
            Procurement Details
        </h3>

        <p class="mt-1 text-xs text-slate-500">
            Enter the programmed amount, quarter, and current procurement status.
        </p>

    </div>


    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">

        {{-- Amount --}}
        <div class="sm:col-span-1 lg:col-span-1 xl:col-span-2">

            <label
                for="amount"
                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
            >
                Amount
            </label>

            <div class="relative">

                <span
                    class="pointer-events-none absolute inset-y-0 left-0
                           flex items-center pl-3 text-sm text-slate-400"
                >
                    ₱
                </span>

                <input
                    id="amount"
                    type="number"
                    step="0.01"
                    min="0"
                    name="amount"
                    value="{{ old('amount', $procurement->amount ?? 0) }}"
                    class="block w-full rounded-lg border py-2.5 pl-8 pr-3
                           text-right text-sm text-slate-700 shadow-sm
                           outline-none transition
                           {{ $errors->has('amount')
                                ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                                : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
                    required
                >

            </div>

            @error('amount')
                <p class="mt-1.5 text-xs font-medium text-rose-600">
                    {{ $message }}
                </p>
            @enderror

        </div>


        {{-- Quarter --}}
        <div>

            <label
                for="quarter"
                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
            >
                Quarter
            </label>

            <input
                id="quarter"
                type="text"
                name="quarter"
                value="{{ old('quarter', $procurement->quarter ?? '') }}"
                placeholder="e.g. Q1"
                maxlength="20"
                class="block w-full rounded-lg border px-3 py-2.5
                       text-sm text-slate-700 shadow-sm outline-none
                       transition placeholder:text-slate-400
                       {{ $errors->has('quarter')
                            ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                            : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
            >

            @error('quarter')
                <p class="mt-1.5 text-xs font-medium text-rose-600">
                    {{ $message }}
                </p>
            @enderror

        </div>


        {{-- Status Fields --}}
        @foreach ([
            'procurement_status' => 'Procurement',
            'payment_status' => 'Payment',
            'retention_status' => 'Retention',
        ] as $field => $label)

            <div>

                <label
                    for="{{ $field }}"
                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
                >
                    {{ $label }}
                </label>

                <select
                    id="{{ $field }}"
                    name="{{ $field }}"
                    class="block w-full rounded-lg border px-3 py-2.5
                           text-sm text-slate-700 shadow-sm outline-none
                           transition
                           {{ $errors->has($field)
                                ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                                : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
                >
                    <option value="">--</option>

                    <option
                        value="OK"
                        @selected(
                            old(
                                $field,
                                $procurement->{$field} ?? ''
                            ) === 'OK'
                        )
                    >
                        OK
                    </option>
                </select>

                @error($field)
                    <p class="mt-1.5 text-xs font-medium text-rose-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        @endforeach

    </div>

</div>


{{-- Financial Plan Link --}}
<div class="mt-7 border-t border-slate-200 pt-6">

    <div class="mb-4">

        <h3 class="text-sm font-bold text-slate-900">
            Work and Financial Plan Link
        </h3>

        <p class="mt-1 text-xs text-slate-500">
            Optionally associate this procurement record with an existing WFP line item.
        </p>

    </div>


    <div>

        <label
            for="financial_plan_item_id"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
        >
            Financial Plan Line Item
        </label>

        <select
            id="financial_plan_item_id"
            name="financial_plan_item_id"
            class="block w-full rounded-lg border px-3 py-2.5
                   text-sm text-slate-700 shadow-sm outline-none
                   transition
                   {{ $errors->has('financial_plan_item_id')
                        ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                        : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
        >
            <option value="">
                — Not linked —
            </option>

            @foreach ($financialPlanItems as $item)

                <option
                    value="{{ $item->id }}"
                    @selected(
                        old(
                            'financial_plan_item_id',
                            $procurement->financial_plan_item_id ?? ''
                        ) == $item->id
                    )
                >
                    {{ $item->program_classification }}
                    —
                    {{ $item->specific_activity }}
                    ({{ $item->prexc_code }})
                </option>

            @endforeach

        </select>


        <p class="mt-1.5 text-xs text-slate-500">
            Leave this as “Not linked” when the procurement is not associated with a specific WFP line item.
        </p>


        @error('financial_plan_item_id')
            <p class="mt-1.5 text-xs font-medium text-rose-600">
                {{ $message }}
            </p>
        @enderror

    </div>

</div>


{{-- Actions --}}
<div class="mt-7 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">

    <button
        type="submit"
        class="inline-flex items-center gap-2 rounded-lg bg-sky-600
               px-5 py-2.5 text-sm font-semibold text-white shadow-sm
               transition hover:bg-sky-700 focus:outline-none
               focus:ring-2 focus:ring-sky-200"
    >
        <i class="fa fa-save"></i>
        <span>Save</span>
    </button>


    <a
        href="{{ route('procurements.index') }}"
        class="inline-flex items-center gap-2 rounded-lg border
               border-slate-300 bg-white px-5 py-2.5 text-sm
               font-semibold text-slate-700 shadow-sm transition
               hover:bg-slate-50"
    >
        <i class="fa fa-times"></i>
        <span>Cancel</span>
    </a>

</div>

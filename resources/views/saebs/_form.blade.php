@csrf

<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

    {{-- As Of Date --}}
    <div>
        <label
            for="as_of_date"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
        >
            As Of Date
        </label>

        <input
            id="as_of_date"
            type="date"
            name="as_of_date"
            value="{{ old('as_of_date', $saeb->as_of_date ? \Carbon\Carbon::parse($saeb->as_of_date)->format('Y-m-d') : '') }}"
            class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition
                   {{ $errors->has('as_of_date')
                        ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                        : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
            required
        >

        @error('as_of_date')
            <p class="mt-1.5 text-xs font-medium text-rose-600">
                {{ $message }}
            </p>
        @enderror
    </div>


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
                    @selected(old('funding_source', $saeb->funding_source ?? '') === $option)
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


    {{-- Allotment Class --}}
    <div>
        <label
            for="allotment_class"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
        >
            Allotment Class
        </label>

        <select
            id="allotment_class"
            name="allotment_class"
            class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition
                   {{ $errors->has('allotment_class')
                        ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                        : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
            required
        >
            <option value="">Select...</option>

            @foreach (['MOOE', 'CO'] as $class)
                <option
                    value="{{ $class }}"
                    @selected(old('allotment_class', $saeb->allotment_class ?? '') === $class)
                >
                    {{ $class }}
                </option>
            @endforeach
        </select>

        @error('allotment_class')
            <p class="mt-1.5 text-xs font-medium text-rose-600">
                {{ $message }}
            </p>
        @enderror
    </div>


    {{-- Expense Class --}}
    <div class="md:col-span-2 xl:col-span-3">
        <label
            for="expense_class"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
        >
            Expense Class
        </label>

        <input
            id="expense_class"
            type="text"
            name="expense_class"
            value="{{ old('expense_class', $saeb->expense_class ?? '') }}"
            placeholder="e.g. ICT Software Subscription"
            maxlength="150"
            class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition
                   {{ $errors->has('expense_class')
                        ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                        : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
            required
        >

        @error('expense_class')
            <p class="mt-1.5 text-xs font-medium text-rose-600">
                {{ $message }}
            </p>
        @enderror
    </div>

</div>


{{-- Financial Values --}}
<div class="mt-6">

    <div class="mb-4">
        <h3 class="text-sm font-bold text-slate-900">
            Financial Details
        </h3>

        <p class="mt-1 text-xs text-slate-500">
            Enter the allotment, obligation, allocation allotment, and balance values.
        </p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">

        @foreach ([
            'allotment' => 'Allotment',
            'obligated' => 'Obligated',
            'aa' => 'AA',
            'balances' => 'Balances',
        ] as $field => $label)

            <div>
                <label
                    for="{{ $field }}"
                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
                >
                    {{ $label }}
                </label>

                <div class="relative">

                    <span
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400"
                    >
                        ₱
                    </span>

                    <input
                        id="{{ $field }}"
                        type="number"
                        step="0.01"
                        min="0"
                        name="{{ $field }}"
                        value="{{ old($field, $saeb->{$field} ?? 0) }}"
                        class="block w-full rounded-lg border py-2.5 pl-8 pr-3 text-right text-sm
                               text-slate-700 shadow-sm outline-none transition
                               {{ $errors->has($field)
                                    ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-2 focus:ring-rose-100'
                                    : 'border-slate-300 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100' }}"
                        required
                    >

                </div>

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
<div class="mt-6">

    <label
        for="financial_plan_item_id"
        class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600"
    >
        Financial Plan Line Item
    </label>

    <select
        id="financial_plan_item_id"
        name="financial_plan_item_id"
        class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition
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
                        $saeb->financial_plan_item_id ?? ''
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
        Optional. Link this SAEB entry to an existing Financial Plan line item.
    </p>

    @error('financial_plan_item_id')
        <p class="mt-1.5 text-xs font-medium text-rose-600">
            {{ $message }}
        </p>
    @enderror

</div>


{{-- Actions --}}
<div
    class="mt-7 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5"
>
    <button
        type="submit"
        class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-5 py-2.5
               text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700
               focus:outline-none focus:ring-2 focus:ring-sky-200"
    >
        <i class="fa fa-save"></i>

        <span>Save</span>
    </button>

    <a
        href="{{ route('saebs.index') }}"
        class="inline-flex items-center gap-2 rounded-lg border border-slate-300
               bg-white px-5 py-2.5 text-sm font-semibold text-slate-700
               shadow-sm transition hover:bg-slate-50"
    >
        <i class="fa fa-times"></i>

        <span>Cancel</span>
    </a>
</div>

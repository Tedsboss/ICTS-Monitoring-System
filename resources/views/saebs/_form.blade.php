@csrf

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">As Of Date</label>
        <input type="date" name="as_of_date" class="form-control @error('as_of_date') is-invalid @enderror"
               value="{{ old('as_of_date', $saeb->as_of_date?->format('Y-m-d') ?? '') }}" required>
        @error('as_of_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Funding Source</label>
        <select name="funding_source" class="form-select @error('funding_source') is-invalid @enderror" required>
            <option value="">Select...</option>
            @foreach ($fundingSourceOptions as $option)
                <option value="{{ $option }}" @selected(old('funding_source', $saeb->funding_source ?? '') === $option)>
                    {{ $option }}
                </option>
            @endforeach
        </select>
        @error('funding_source') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Allotment Class</label>
        <select name="allotment_class" class="form-select @error('allotment_class') is-invalid @enderror" required>
            <option value="">Select...</option>
            @foreach (['MOOE', 'CO'] as $class)
                <option value="{{ $class }}" @selected(old('allotment_class', $saeb->allotment_class ?? '') === $class)>{{ $class }}</option>
            @endforeach
        </select>
        @error('allotment_class') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Expense Class</label>
        <input type="text" name="expense_class" class="form-control @error('expense_class') is-invalid @enderror"
               value="{{ old('expense_class', $saeb->expense_class ?? '') }}" placeholder="e.g. ICT Software Subscription" required>
        @error('expense_class') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Allotment</label>
        <input type="number" step="0.01" name="allotment" class="form-control @error('allotment') is-invalid @enderror"
               value="{{ old('allotment', $saeb->allotment ?? 0) }}" required>
        @error('allotment') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Obligated</label>
        <input type="number" step="0.01" name="obligated" class="form-control @error('obligated') is-invalid @enderror"
               value="{{ old('obligated', $saeb->obligated ?? 0) }}" required>
        @error('obligated') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">AA</label>
        <input type="number" step="0.01" name="aa" class="form-control @error('aa') is-invalid @enderror"
               value="{{ old('aa', $saeb->aa ?? 0) }}" required>
        @error('aa') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Balances</label>
        <input type="number" step="0.01" name="balances" class="form-control @error('balances') is-invalid @enderror"
               value="{{ old('balances', $saeb->balances ?? 0) }}" required>
        @error('balances') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Financial Plan Line Item</label>
        <select name="financial_plan_item_id" class="form-select @error('financial_plan_item_id') is-invalid @enderror">
            <option value="">— Not linked —</option>
            @foreach ($financialPlanItems as $item)
                <option value="{{ $item->id }}"
                    @selected(old('financial_plan_item_id', $saeb->financial_plan_item_id ?? '') == $item->id)>
                    {{ $item->program_classification }} — {{ $item->specific_activity }} ({{ $item->prexc_code }})
                </option>
            @endforeach
        </select>
        @error('financial_plan_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('saebs.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

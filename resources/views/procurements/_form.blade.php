@csrf

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Funding Source</label>
        <select name="funding_source" class="form-select @error('funding_source') is-invalid @enderror" required>
            <option value="">Select...</option>
            @foreach ($fundingSourceOptions as $option)
                <option value="{{ $option }}" @selected(old('funding_source', $procurement->funding_source ?? '') === $option)>
                    {{ $option }}
                </option>
            @endforeach
        </select>
        @error('funding_source') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Expense Class</label>
        <select name="expense_class" class="form-select @error('expense_class') is-invalid @enderror" required>
            <option value="">Select...</option>
            @foreach (['MOOE', 'CO'] as $class)
                <option value="{{ $class }}" @selected(old('expense_class', $procurement->expense_class ?? '') === $class)>{{ $class }}</option>
            @endforeach
        </select>
        @error('expense_class') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Division Assigned</label>
        <select name="division_assigned" class="form-select @error('division_assigned') is-invalid @enderror" required>
            <option value="">Select...</option>
            @foreach ($divisions as $division)
                @php($value = $division->abbreviation ?: $division->name)
                <option value="{{ $value }}" @selected(old('division_assigned', $procurement->division_assigned ?? '') === $value)>
                    {{ $division->name }} @if($division->abbreviation) ({{ $division->abbreviation }}) @endif
                </option>
            @endforeach
        </select>
        @error('division_assigned') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Procurement Title</label>
        <input type="text" name="procurement_title" class="form-control @error('procurement_title') is-invalid @enderror"
               value="{{ old('procurement_title', $procurement->procurement_title ?? '') }}" required>
        @error('procurement_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Amount</label>
        <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror"
               value="{{ old('amount', $procurement->amount ?? 0) }}" required>
        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Quarter</label>
        <input type="text" name="quarter" class="form-control @error('quarter') is-invalid @enderror"
               value="{{ old('quarter', $procurement->quarter ?? '') }}" placeholder="e.g. Q1">
        @error('quarter') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label">Procurement</label>
        <select name="procurement_status" class="form-select @error('procurement_status') is-invalid @enderror">
            <option value="">--</option>
            <option value="OK" @selected(old('procurement_status', $procurement->procurement_status ?? '') === 'OK')>OK</option>
        </select>
        @error('procurement_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label">Payment</label>
        <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
            <option value="">--</option>
            <option value="OK" @selected(old('payment_status', $procurement->payment_status ?? '') === 'OK')>OK</option>
        </select>
        @error('payment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label">Retention</label>
        <select name="retention_status" class="form-select @error('retention_status') is-invalid @enderror">
            <option value="">--</option>
            <option value="OK" @selected(old('retention_status', $procurement->retention_status ?? '') === 'OK')>OK</option>
        </select>
        @error('retention_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Financial Plan Line Item</label>
        <select name="financial_plan_item_id" class="form-select @error('financial_plan_item_id') is-invalid @enderror">
            <option value="">— Not linked —</option>
            @foreach ($financialPlanItems as $item)
                <option value="{{ $item->id }}"
                    @selected(old('financial_plan_item_id', $procurement->financial_plan_item_id ?? '') == $item->id)>
                    {{ $item->program_classification }} — {{ $item->specific_activity }} ({{ $item->prexc_code }})
                </option>
            @endforeach
        </select>
        @error('financial_plan_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('procurements.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

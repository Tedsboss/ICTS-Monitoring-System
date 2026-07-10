<div
  class="uplift-builder-card {{ $level > 0 ? 'is-child ms-' . min($level * 3, 5) : 'uplift-draggable-field' }} p-3 mb-3"
  data-field-id="{{ $field->id }}"
  data-field-level="{{ $level }}"
  @if($level === 0) draggable="true" @endif>
  <div class="d-flex align-items-start mb-3">
    <div class="d-flex align-items-start gap-2">
      @if($level === 0)
        <button type="button" class="uplift-drag-handle" title="Drag to reorder">
          <i class="fa fa-arrows"></i>
        </button>
      @endif

      <div>
      @if($field->section)
        <span class="badge uplift-section-badge">{{ $field->section }}</span>
      @endif
      <span class="badge bg-light text-dark">Line {{ $field->row_number }}</span>
      <span class="badge bg-light text-dark">Position {{ $field->order }}</span>
      <span class="badge bg-{{ $field->status == 1 ? 'success' : 'secondary' }}">{{ $field->status == 1 ? 'Active' : 'Inactive' }}</span>
      @if($level > 0)
        <span class="badge bg-info">Nested</span>
      @endif
      </div>
    </div>
    <div class="ms-auto text-end">
      <p class="text-xs text-muted mb-0">{{ ucwords(str_replace('_', ' ', $field->value_type)) }}</p>
      <p class="text-xs text-muted mb-0">{{ $field->indicators->count() }} indicator{{ $field->indicators->count() == 1 ? '' : 's' }}</p>
    </div>
  </div>

  <form method="post" action="{{ route('uplift-builder.fields.update', [$measure, $field]) }}">
    @csrf
    @method('put')
    @include('uplift-builder.partials.field-form', ['field' => $field, 'fields' => $fields])
    <div class="text-end">
      @can('delete', $measure->pillar)
        <button class="btn btn-danger btn-sm mb-0" type="submit" form="delete-field-{{ $field->id }}" onclick="return confirm('Remove this field and keep old history?')">Remove</button>
      @endcan
      @can('update', $measure->pillar)
        <button class="btn btn-primary btn-sm mb-0" type="submit">Save Field</button>
      @endcan
    </div>
  </form>
  <form method="post" action="{{ route('uplift-builder.fields.destroy', [$measure, $field]) }}" id="delete-field-{{ $field->id }}" hidden>
    @csrf
    @method('delete')
  </form>

  <div class="border-top mt-3 pt-3">
    <div class="d-flex align-items-center mb-2">
      <h6 class="mb-0">Indicators</h6>
      <span class="badge bg-light text-dark ms-2">{{ $field->indicators->count() }}</span>
    </div>

    @foreach($field->indicators as $indicator)
      <div class="border rounded p-3 mb-2">
        <form method="post" action="{{ route('uplift-builder.indicators.update', [$measure, $field, $indicator]) }}">
          @csrf
          @method('put')
          @include('uplift-builder.partials.indicator-form', ['field' => $field, 'indicator' => $indicator])
          <div class="text-end">
            @can('delete', $measure->pillar)
              <button class="btn btn-danger btn-sm mb-0" type="submit" form="delete-indicator-{{ $indicator->id }}" onclick="return confirm('Remove this indicator?')">Remove</button>
            @endcan
            @can('update', $measure->pillar)
              <button class="btn btn-primary btn-sm mb-0" type="submit">Save Indicator</button>
            @endcan
          </div>
        </form>
        <form method="post" action="{{ route('uplift-builder.indicators.destroy', [$measure, $field, $indicator]) }}" id="delete-indicator-{{ $indicator->id }}" hidden>
          @csrf
          @method('delete')
        </form>
      </div>
    @endforeach

    @can('create', App\Models\UpliftPillar::class)
      <div class="border rounded p-3 bg-light">
        <form method="post" action="{{ route('uplift-builder.indicators.store', [$measure, $field]) }}">
          @csrf
          <p class="text-sm font-weight-bold mb-2">Add Indicator</p>
          @include('uplift-builder.partials.indicator-form', ['field' => $field, 'indicator' => null])
          <button class="btn btn-info btn-sm mb-0" type="submit">
            <i class="fa fa-plus me-1"></i> Add Indicator
          </button>
        </form>
      </div>
    @endcan
  </div>

  @foreach($field->children as $child)
    @include('uplift-builder.partials.field-card', ['field' => $child, 'measure' => $measure, 'fields' => $fields, 'level' => $level + 1])
  @endforeach
</div>

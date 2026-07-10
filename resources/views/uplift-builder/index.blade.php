@php
  $class_theme = session('user_settings.class_theme', '');
  $activePillarsCount = $pillars->where('status', 1)->count();
  $activeMeasuresCount = $measures->where('status', 1)->count();
@endphp
@extends('layouts.app')

@section('content')
  <style>
    .uplift-overview-card {
      border: 1px solid #dbe9f7;
      border-radius: 8px;
      background: linear-gradient(135deg, #ffffff 0%, #eef7ff 100%);
      box-shadow: 0 10px 26px rgba(8, 66, 143, .08);
      overflow: hidden;
    }

    .uplift-stat {
      min-height: 74px;
      border: 1px solid #dbe9f7;
      border-radius: 8px;
      background: rgba(255, 255, 255, .72);
      padding: 12px;
    }

    .uplift-stat-label {
      color: #3b78aa;
      font-size: .68rem;
      font-weight: 800;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .uplift-stat-value {
      color: #05306f;
      font-size: 1.35rem;
      font-weight: 850;
      line-height: 1;
    }

    .uplift-create-panel {
      border: 1px solid #dbe9f7;
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 8px 18px rgba(8, 66, 143, .06);
    }

    .uplift-workspace-card {
      border: 1px solid #dbe9f7;
      border-radius: 8px;
      box-shadow: 0 10px 26px rgba(8, 66, 143, .08);
      overflow: hidden;
    }

    .uplift-workspace-card > .card-header {
      border-bottom: 1px solid #dbe9f7;
      background: linear-gradient(135deg, #ffffff 0%, #eef7ff 100%);
    }

    .uplift-pillar-filter {
      display: flex;
      width: 100%;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      border: 1px solid #dbe9f7;
      border-radius: 8px;
      background: #fff;
      color: #344767;
      padding: 10px 12px;
      text-align: left;
      transition: .15s ease;
    }

    .uplift-pillar-filter:hover,
    .uplift-pillar-filter.is-active {
      border-color: #8dbdeb;
      background: #eef7ff;
      color: #08428f;
    }

    .uplift-pillar-filter-title {
      font-size: .82rem;
      font-weight: 800;
      line-height: 1.25;
    }

    .uplift-pillar-filter-meta {
      color: #55769a;
      font-size: .68rem;
      font-weight: 700;
    }

    .uplift-pillar-form {
      border: 1px solid #edf2f7;
      border-radius: 8px;
      background: #fbfdff;
      overflow: hidden;
    }

    .uplift-pillar-row {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 10px;
      align-items: center;
      width: 100%;
      border: 0;
      background: transparent;
      color: #344767;
      padding: 12px;
      text-align: left;
    }

    .uplift-pillar-row:hover,
    .uplift-pillar-row.is-active {
      background: #eef7ff;
      color: #08428f;
    }

    .uplift-pillar-row-main {
      min-width: 0;
      cursor: pointer;
    }

    .uplift-pillar-row-actions {
      display: flex;
      align-items: center;
      gap: 7px;
    }

    .uplift-pillar-edit {
      border-top: 1px solid #edf2f7;
      padding: 12px;
      background: #fff;
    }

    .uplift-table {
      border-collapse: separate;
      border-spacing: 0;
    }

    .uplift-table thead th {
      border-bottom: 1px solid #e9ecef;
      background: #fff;
      padding-top: 12px;
      padding-bottom: 12px;
    }

    .uplift-table tbody td {
      border-bottom: 1px solid #f1f3f5;
      padding-top: 14px;
      padding-bottom: 14px;
      vertical-align: middle;
    }

    .uplift-empty-state {
      padding: 42px 16px;
      color: #55769a;
      text-align: center;
    }



    .uplift-title-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .uplift-title-item {
      display: grid;
      grid-template-columns: minmax(280px, 1fr) minmax(105px, .35fr) minmax(100px, .35fr) minmax(86px, auto) minmax(86px, auto) auto;
      gap: 12px;
      align-items: center;
      border: 1px solid #edf2f7;
      border-radius: 8px;
      background: #fff;
      padding: 12px;
    }

    .uplift-title-item:hover {
      border-color: #cfe3f8;
      background: #fbfdff;
    }

    .uplift-title-main {
      min-width: 0;
    }

    .uplift-title-pill {
      color: #3b78aa;
      font-size: .67rem;
      font-weight: 800;
      letter-spacing: .035em;
      line-height: 1.2;
      text-transform: uppercase;
    }

    .uplift-title-name {
      color: #172b4d;
      font-size: .88rem;
      font-weight: 800;
      line-height: 1.35;
      white-space: normal;
      overflow-wrap: anywhere;
      word-break: normal;
    }

    .uplift-title-meta-label {
      color: #8392ab;
      font-size: .62rem;
      font-weight: 800;
      letter-spacing: .04em;
      line-height: 1;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    .uplift-title-meta-value {
      color: #344767;
      font-size: .78rem;
      font-weight: 700;
      line-height: 1.25;
      overflow-wrap: anywhere;
    }

    .uplift-title-count {
      text-align: center;
    }

    .uplift-title-actions {
      display: flex;
      justify-content: flex-end;
      gap: 7px;
      white-space: nowrap;
    }

    .uplift-title-actions .btn {
      padding-left: 10px;
      padding-right: 10px;
    }

    @media (max-width: 1199.98px) {
      .uplift-title-item {
        grid-template-columns: minmax(260px, 1fr) minmax(96px, auto) minmax(96px, auto) auto;
      }

      .uplift-title-meta-agency,
      .uplift-title-meta-sector {
        display: none;
      }
    }

    @media (max-width: 767.98px) {
      .uplift-title-item {
        grid-template-columns: 1fr;
        align-items: stretch;
      }

      .uplift-title-count {
        text-align: left;
      }

      .uplift-title-actions {
        justify-content: stretch;
      }

      .uplift-title-actions .btn,
      .uplift-title-actions form,
      .uplift-title-actions button {
        width: 100%;
      }
    }



    /* Prevent long response/measure/assistance titles from pushing the action buttons off-screen. */
    .uplift-title-item,
    .uplift-title-item * {
      box-sizing: border-box;
    }

    .uplift-title-item {
      grid-template-columns: minmax(0, 1fr) auto !important;
      grid-template-areas:
        "title actions"
        "agency actions"
        "sector actions"
        "fields actions"
        "indicators actions";
      overflow: hidden;
      max-width: 100%;
    }

    .uplift-title-item > * {
      min-width: 0;
      max-width: 100%;
    }

    .uplift-title-main {
      grid-area: title;
      overflow: hidden;
    }

    .uplift-title-meta-agency {
      grid-area: agency;
    }

    .uplift-title-meta-sector {
      grid-area: sector;
    }

    .uplift-title-item > .uplift-title-count:nth-child(4) {
      grid-area: fields;
    }

    .uplift-title-item > .uplift-title-count:nth-child(5) {
      grid-area: indicators;
    }

    .uplift-title-actions {
      grid-area: actions;
      align-self: start;
      flex-shrink: 0;
      min-width: max-content;
    }

    .uplift-title-pill,
    .uplift-title-name,
    .uplift-title-meta-value,
    .uplift-title-main .text-xs {
      max-width: 100%;
      white-space: normal;
      overflow-wrap: anywhere;
      word-break: break-word;
    }

    .uplift-title-meta-agency,
    .uplift-title-meta-sector,
    .uplift-title-count {
      text-align: left;
    }

    @media (min-width: 1200px) {
      .uplift-title-meta-agency,
      .uplift-title-meta-sector,
      .uplift-title-count {
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .uplift-title-meta-label {
        margin-bottom: 0;
      }
    }


    /* Compact measure metadata: keep Lead/Sector/Fields/Indicators in one clean row under the title. */
    .uplift-title-item {
      grid-template-columns: minmax(0, 1fr) auto !important;
      grid-template-areas: "title actions" !important;
      align-items: start;
      gap: 12px;
      overflow: hidden;
      max-width: 100%;
    }

    .uplift-title-main {
      grid-area: title;
      min-width: 0;
      overflow: hidden;
    }

    .uplift-title-actions {
      grid-area: actions;
      align-self: start;
      flex-shrink: 0;
      min-width: max-content;
    }

    .uplift-title-meta-strip {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 9px;
      min-width: 0;
    }

    .uplift-title-meta-chip {
      display: inline-flex;
      align-items: center;
      max-width: 100%;
      min-width: 0;
      gap: 5px;
      border: 1px solid #edf2f7;
      border-radius: 999px;
      background: #f8fbff;
      color: #344767;
      padding: 4px 8px;
      font-size: .72rem;
      font-weight: 700;
      line-height: 1.2;
    }

    .uplift-title-meta-chip strong {
      flex: 0 0 auto;
      color: #3b78aa;
      font-size: .62rem;
      font-weight: 850;
      letter-spacing: .035em;
      text-transform: uppercase;
    }

    .uplift-title-meta-chip span {
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .uplift-title-meta-chip .badge {
      flex: 0 0 auto;
      padding: 3px 7px;
      font-size: .68rem;
    }

    .uplift-title-meta-agency,
    .uplift-title-meta-sector,
    .uplift-title-count,
    .uplift-title-main .d-xl-none {
      display: none !important;
    }

    @media (max-width: 767.98px) {
      .uplift-title-item {
        grid-template-columns: 1fr !important;
        grid-template-areas:
          "title"
          "actions" !important;
        align-items: stretch;
      }

      .uplift-title-actions {
        justify-content: stretch;
        min-width: 0;
        width: 100%;
      }

      .uplift-title-actions .btn,
      .uplift-title-actions form,
      .uplift-title-actions button {
        width: 100%;
      }
    }


    @media (max-width: 991.98px) {
      .uplift-builder-actions {
        align-items: stretch !important;
      }

      .uplift-builder-actions .btn {
        width: 100%;
      }
    }
  </style>

  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'UPLIFT Builder'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row mt-4">
      <div class="col-12">
        <div class="uplift-overview-card p-3">
          <div class="row align-items-center g-3">
            <div class="col-xl-5 col-lg-12">
              <p class="text-uppercase text-xs font-weight-bolder text-info mb-1">UPLIFT Form Structure</p>
              <h5 class="mb-1 text-dark">Build pillars, titles, fields, and indicators</h5>
              <p class="text-sm text-secondary mb-0">
                Manage the hierarchy agencies will use when preparing UPLIFT reports.
              </p>
            </div>

            <div class="col-xl-4 col-lg-7">
              <div class="row g-2">
                <div class="col-4">
                  <div class="uplift-stat">
                    <div class="uplift-stat-label">Pillars</div>
                    <div class="uplift-stat-value">{{ $pillars->count() }}</div>
                    <div class="text-xs text-secondary">{{ $activePillarsCount }} active</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="uplift-stat">
                    <div class="uplift-stat-label">Titles</div>
                    <div class="uplift-stat-value">{{ $measures->count() }}</div>
                    <div class="text-xs text-secondary">{{ $activeMeasuresCount }} active</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="uplift-stat">
                    <div class="uplift-stat-label">Fields</div>
                    <div class="uplift-stat-value">{{ $measures->sum('fields_count') }}</div>
                    <div class="text-xs text-secondary">{{ $measures->sum('indicators_count') }} indicators</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-3 col-lg-5">
              @can('create', App\Models\UpliftPillar::class)
                <div class="d-flex flex-column flex-sm-row flex-lg-column gap-2 uplift-builder-actions">
                  <button class="btn btn-primary mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#newMeasurePanel" aria-expanded="false" aria-controls="newMeasurePanel">
                    <i class="fa fa-plus me-1"></i>
                    New Title
                  </button>

                  <button class="btn btn-outline-primary mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#newPillarPanel" aria-expanded="false" aria-controls="newPillarPanel">
                    <i class="fa fa-sitemap me-1"></i>
                    New Pillar
                  </button>
                </div>
              @endcan
            </div>
          </div>
        </div>
      </div>
    </div>

    @can('create', App\Models\UpliftPillar::class)
      <div class="row mt-3">
        <div class="col-lg-4 mb-3">
          <div class="collapse" id="newPillarPanel">
            <div class="uplift-create-panel p-3">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                  <h6 class="mb-1">New Pillar</h6>
                  <p class="text-xs text-secondary mb-0">Add a UPLIFT category.</p>
                </div>
                <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#newPillarPanel" aria-label="Close"></button>
              </div>

              <form method="post" action="{{ route('uplift-builder.pillars.store') }}">
                @csrf
                <div class="mb-3">
                  <label>Pillar <span class="text-danger">*</span></label>
                  <input name="title" class="form-control" type="text" value="{{ old('title') }}" placeholder="Pillar title">
                  @error('title')<p class='text-danger text-xs pt-1'>{{ $message }}</p>@enderror
                </div>
                <div class="mb-3">
                  <label>Status <span class="text-danger">*</span></label>
                  <select name="status" id="pillar_status" class="hide-search">
                    <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                  </select>
                </div>
                <button class="btn btn-primary w-100 mb-0" type="submit">
                  <i class="fa fa-save me-1"></i>
                  Save Pillar
                </button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-8 mb-3">
          <div class="collapse" id="newMeasurePanel">
            <div class="uplift-create-panel p-3">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                  <h6 class="mb-1">New Response / Measure / Assistance</h6>
                  <p class="text-xs text-secondary mb-0">Create a tracker title under a pillar.</p>
                </div>
                <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#newMeasurePanel" aria-label="Close"></button>
              </div>

              <form method="post" action="{{ route('uplift-builder.measures.store') }}">
                @csrf
                <div class="row">
                  <div class="col-lg-6 mb-3">
                    <label>Pillar <span class="text-danger">*</span></label>
                    <select name="uplift_pillar_id" id="uplift_pillar_id" placeholder="Pillar">
                      <option value="">Pillar</option>
                      @foreach($pillars as $pillar)
                        <option value="{{ $pillar->id }}" {{ old('uplift_pillar_id') == $pillar->id ? 'selected' : '' }}>{{ $pillar->title }}</option>
                      @endforeach
                    </select>
                    @error('uplift_pillar_id')<p class='text-danger text-xs pt-1'>{{ $message }}</p>@enderror
                  </div>
                  <div class="col-lg-6 mb-3">
                    <label>Lead Agency</label>
                    <select name="lead_agency_id" id="lead_agency_id" placeholder="Lead Agency">
                      <option value="">Lead Agency</option>
                      @foreach($agencies as $agency)
                        <option value="{{ $agency->id }}" {{ old('lead_agency_id') == $agency->id ? 'selected' : '' }}>{{ $agency->selection_name }}</option>
                      @endforeach
                    </select>
                    @error('lead_agency_id')<p class='text-danger text-xs pt-1'>{{ $message }}</p>@enderror
                  </div>
                  <div class="col-lg-6 mb-3">
                    <label>Assigned Sector</label>
                    <select name="assigned_sector_id" id="assigned_sector_id" placeholder="Assigned Sector">
                      <option value="">Assigned Sector</option>
                      @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" {{ old('assigned_sector_id') == $sector->id ? 'selected' : '' }}>{{ $sector->name }} ({{ $sector->abbreviation }})</option>
                      @endforeach
                    </select>
                    @error('assigned_sector_id')<p class='text-danger text-xs pt-1'>{{ $message }}</p>@enderror
                  </div>
                </div>
                <div class="mb-3">
                  <label>Title of Response / Measure / Assistance <span class="text-danger">*</span></label>
                  <input name="title" class="form-control" type="text" value="{{ old('title') }}" placeholder="Title">
                </div>
                <div class="mb-3">
                  <label>Brief Description</label>
                  <textarea name="brief_description" class="form-control" rows="3" placeholder="Brief description">{{ old('brief_description') }}</textarea>
                </div>
                @if($predefinedTemplate)
                  <div class="mb-3">
                    <div class="form-check">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        name="predefined_template_key"
                        value="{{ $predefinedTemplate['key'] }}"
                        id="predefined_template_key"
                        {{ old('predefined_template_key') == $predefinedTemplate['key'] ? 'checked' : '' }}>
                      <label class="form-check-label text-sm" for="predefined_template_key">
                        Use predefined UPLIFT template
                      </label>
                    </div>
                    <p class="text-xs text-secondary mb-0">
                      Copies {{ $predefinedTemplate['name'] }}
                      ({{ $predefinedTemplate['fields_count'] }} fields, {{ $predefinedTemplate['indicators_count'] }} indicators).
                    </p>
                    @error('predefined_template_key')<p class='text-danger text-xs pt-1'>{{ $message }}</p>@enderror
                  </div>
                @endif
                <input type="hidden" name="status" value="1">
                <button class="btn btn-primary mb-0" type="submit">
                  <i class="fa fa-plus me-1"></i>
                  Add Title
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    @endcan

    <div class="row mt-4">
      <div class="col-xl-3 col-lg-4 mb-4">
        <div class="card uplift-workspace-card h-100">
          <div class="card-header">
            <div class="d-flex align-items-center">
              <div>
                <h5 class="mb-0">Pillars</h5>
                <p class="text-sm mb-0">Select a pillar to filter titles.</p>
              </div>
              <div class="ms-auto">
                <span class="badge bg-info">{{ $pillars->count() }} total</span>
              </div>
            </div>
          </div>

          <div class="card-body p-3">
            <button type="button" class="uplift-pillar-filter is-active mb-3" data-pillar-filter="all">
              <span>
                <span class="uplift-pillar-filter-title d-block">All Pillars</span>
                <span class="uplift-pillar-filter-meta">Show every title</span>
              </span>
              <span class="badge bg-info">{{ $measures->count() }}</span>
            </button>

            @forelse($pillars as $pillar)
              <div class="uplift-pillar-form mb-2">
                <div class="uplift-pillar-row">
                  <div class="uplift-pillar-row-main" data-pillar-filter="{{ $pillar->id }}">
                    <span class="uplift-pillar-filter-title d-block text-truncate">{{ $pillar->title }}</span>
                    <span class="uplift-pillar-filter-meta">
                      {{ $pillar->status == 1 ? 'Active' : 'Inactive' }}
                    </span>
                  </div>

                  <div class="uplift-pillar-row-actions">
                    <span class="badge bg-light text-dark">{{ $pillar->measures_count }}</span>
                    @can('update', $pillar)
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary mb-0"
                        data-bs-toggle="collapse"
                        data-bs-target="#pillarEdit{{ $pillar->id }}"
                        aria-expanded="false"
                        aria-controls="pillarEdit{{ $pillar->id }}">
                        <i class="fa fa-pencil"></i>
                      </button>
                    @endcan
                  </div>
                </div>

                @can('update', $pillar)
                  <div class="collapse" id="pillarEdit{{ $pillar->id }}">
                    <div class="uplift-pillar-edit">
                      <form method="post" action="{{ route('uplift-builder.pillars.update', $pillar) }}">
                        @csrf
                        @method('put')
                        <label>Pillar</label>
                        <input name="title" class="form-control mb-2" type="text" value="{{ $pillar->title }}">
                        <div class="row align-items-end">
                          <div class="col-7">
                            <label>Status</label>
                            <select name="status" id="pillar_status_{{ $pillar->id }}" class="hide-search">
                              <option value="1" {{ $pillar->status == 1 ? 'selected' : '' }}>Active</option>
                              <option value="0" {{ $pillar->status == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                          </div>
                          <div class="col-5 text-end">
                            <button class="btn btn-sm btn-primary mb-0" type="submit">Save</button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                @endcan
              </div>
            @empty
              <div class="uplift-empty-state">
                <p class="text-sm mb-0">No pillars found.</p>
              </div>
            @endforelse
          </div>
        </div>
      </div>

      <div class="col-xl-9 col-lg-8 mb-4">
        <div class="card uplift-workspace-card">
          <div class="card-header">
            <div class="d-flex align-items-center">
              <div>
                <h5 class="mb-0">Response / Measure / Assistance Titles</h5>
                <p class="text-sm mb-0">
                  <span id="titleFilterLabel">All pillars</span>
                </p>
              </div>
              <div class="ms-auto">
                <span class="badge bg-info"><span id="visibleTitleCount">{{ $measures->count() }}</span> shown</span>
              </div>
            </div>
          </div>

          <div class="card-body p-3">
            <div class="uplift-title-list" id="measureRows">
              @forelse($measures as $measure)
                <div class="uplift-title-item" data-pillar-id="{{ optional($measure->pillar)->id }}">
                  <div class="uplift-title-main">
                    <div class="uplift-title-pill mb-1">{{ optional($measure->pillar)->title ?: 'No pillar' }}</div>
                    <div class="uplift-title-name">{{ $measure->title }}</div>
                    @if($measure->status == 0)
                      <span class="badge bg-secondary mt-2">Inactive</span>
                    @endif
                    <div class="d-xl-none mt-2">
                      <span class="text-xs text-secondary me-2">
                        <strong>Lead:</strong> {{ optional($measure->leadAgency)->Abbreviation ?? optional($measure->leadAgency)->display_name ?? '—' }}
                      </span>
                      <span class="text-xs text-secondary">
                        <strong>Sector:</strong> {{ optional($measure->assignedSector)->abbreviation ?? optional($measure->assignedSector)->name ?? '—' }}
                      </span>
                    </div>

                    <div class="uplift-title-meta-strip">
                      <div class="uplift-title-meta-chip">
                        <strong>Lead</strong>
                        <span>{{ optional($measure->leadAgency)->Abbreviation ?? optional($measure->leadAgency)->display_name ?? '—' }}</span>
                      </div>
                      <div class="uplift-title-meta-chip">
                        <strong>Sector</strong>
                        <span>{{ optional($measure->assignedSector)->abbreviation ?? optional($measure->assignedSector)->name ?? '—' }}</span>
                      </div>
                      <div class="uplift-title-meta-chip">
                        <strong>Fields</strong>
                        <span class="badge bg-light text-dark">{{ $measure->fields_count }}</span>
                      </div>
                      <div class="uplift-title-meta-chip">
                        <strong>Indicators</strong>
                        <span class="badge bg-light text-dark">{{ $measure->indicators_count }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="uplift-title-meta-agency">
                    <div class="uplift-title-meta-label">Lead</div>
                    <div class="uplift-title-meta-value">{{ optional($measure->leadAgency)->Abbreviation ?? optional($measure->leadAgency)->display_name ?? '—' }}</div>
                  </div>

                  <div class="uplift-title-meta-sector">
                    <div class="uplift-title-meta-label">Sector</div>
                    <div class="uplift-title-meta-value">{{ optional($measure->assignedSector)->abbreviation ?? optional($measure->assignedSector)->name ?? '—' }}</div>
                  </div>

                  <div class="uplift-title-count">
                    <div class="uplift-title-meta-label">Fields</div>
                    <span class="badge bg-light text-dark">{{ $measure->fields_count }}</span>
                  </div>

                  <div class="uplift-title-count">
                    <div class="uplift-title-meta-label">Indicators</div>
                    <span class="badge bg-light text-dark">{{ $measure->indicators_count }}</span>
                  </div>

                  <div class="uplift-title-actions">
                    <a href="{{ route('uplift-builder.edit', $measure) }}" class="btn btn-sm btn-outline-primary mb-0">
                      <i class="fa fa-pencil me-1"></i>
                      Manage
                    </a>

                    @can('create', App\Models\UpliftPillar::class)
                      <form method="post" action="{{ route('uplift-builder.measures.duplicate', $measure) }}" class="mb-0">
                        @csrf
                        <button class="btn btn-sm btn-primary mb-0" type="submit">
                          <i class="fa fa-copy me-1"></i>
                          Duplicate
                        </button>
                      </form>
                    @endcan
                  </div>
                </div>
              @empty
                <div class="uplift-empty-state">
                  <p class="text-sm mb-0">No titles found.</p>
                </div>
              @endforelse
            </div>

            <div id="measureEmptyState" class="uplift-empty-state" style="display: none;">
              <p class="text-sm mb-0">No titles found for the selected pillar.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    @include('layouts.footers.auth.footer')
  </div>
@endsection

@push('js')
  <script>
    if (document.getElementById('pillar_status')) initTomSelect('pillar_status');
    if (document.getElementById('uplift_pillar_id')) initTomSelect('uplift_pillar_id', true);
    if (document.getElementById('lead_agency_id')) initTomSelect('lead_agency_id', true);
    if (document.getElementById('assigned_sector_id')) initTomSelect('assigned_sector_id', true);
    @foreach($pillars as $pillar)
      if (document.getElementById('pillar_status_{{ $pillar->id }}')) initTomSelect('pillar_status_{{ $pillar->id }}');
    @endforeach

    document.querySelectorAll('[data-pillar-filter]').forEach(function(filterControl) {
      filterControl.addEventListener('click', function() {
        const filter = filterControl.dataset.pillarFilter;
        const rows = document.querySelectorAll('#measureRows [data-pillar-id]');
        const visibleTitleCount = document.getElementById('visibleTitleCount');
        const titleFilterLabel = document.getElementById('titleFilterLabel');
        const emptyState = document.getElementById('measureEmptyState');
        let shown = 0;

        document.querySelectorAll('[data-pillar-filter]').forEach(function(item) {
          item.classList.toggle('is-active', item === filterControl);
          const row = item.closest('.uplift-pillar-row');
          if (row) row.classList.toggle('is-active', item === filterControl);
        });

        rows.forEach(function(row) {
          const shouldShow = filter === 'all' || row.dataset.pillarId === filter;
          row.style.display = shouldShow ? '' : 'none';
          if (shouldShow) shown++;
        });

        if (visibleTitleCount) visibleTitleCount.textContent = shown;
        if (titleFilterLabel) {
          const title = filterControl.querySelector('.uplift-pillar-filter-title');
          titleFilterLabel.textContent = title ? title.textContent : 'All pillars';
        }
        if (emptyState) emptyState.style.display = shown === 0 ? 'block' : 'none';
      });
    });
  </script>
@endpush

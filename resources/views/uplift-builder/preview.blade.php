@php
  $class_theme = session('user_settings.class_theme', '');
@endphp

@extends('layouts.app')

@section('content')
  <style>
    .preview-status-badge {
      display: inline-flex;
      align-items: center;
      min-height: 30px;
      padding: 7px 14px;
      border: 1px solid #f1cb64;
      border-radius: 999px;
      background: #fff7df;
      color: #8a6100;
      font-size: .74rem;
      font-weight: 800;
      text-transform: uppercase;
    }

    .preview-back-btn {
      min-height: 38px;
      border-radius: 8px;
      font-weight: 700;
    }

    .preview-note {
      border: 1px solid #bfdbfe;
      border-radius: 10px;
      background: #eff6ff;
      color: #1e3a8a;
      font-size: .78rem;
      font-weight: 700;
      padding: 10px 12px;
    }

    .uplift-preview-card,
    .uplift-preview-card > .card-body,
    .uplift-preview-card .submission-section,
    .uplift-preview-card .submission-field {
      overflow: visible;
    }

    .uplift-preview-card .ts-wrapper {
      position: relative;
      z-index: 20;
    }

    #loader {
      z-index: 20000 !important;
    }
  </style>

  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'UPLIFT Form Preview'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row mt-4">
      <div class="col-12">
        <div class="card submission-page-card uplift-preview-card">
          <div class="card-header">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
              <div>
                <p class="submission-eyebrow mb-1">Interactive UPLIFT Preview</p>
                <h5 class="submission-page-title mb-0">{{ $measure->title }}</h5>
                <p class="submission-page-subtitle text-sm mb-0">
                  {{ optional($measure->pillar)->title ?? 'No pillar assigned' }}
                </p>
              </div>

              <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                <span class="preview-status-badge">
                  <i class="fa fa-eye me-2"></i>
                  Preview
                </span>

                <a href="{{ route('uplift-builder.edit', $measure) }}" class="btn btn-outline-primary preview-back-btn mb-0">
                  <i class="fa fa-arrow-left me-2"></i>
                  Back to Edit
                </a>
              </div>
            </div>
          </div>

          <div class="card-body p-3">
            <div class="preview-note mb-3">
              <i class="fa fa-info-circle me-2"></i>
              Preview mode only. Inputs are editable for testing, but nothing will be saved or submitted.
            </div>

            <form id="uplift-preview-form" method="post" action="#" autocomplete="off" onsubmit="return false;">
              @include('uplift-submissions._form', ['readonly' => false, 'previewMode' => true])
            </form>
          </div>
        </div>
      </div>
    </div>

    @include('layouts.footers.auth.footer')
  </div>
@endsection


@section('scripts')
  @include('submissions.scripts')
@endsection

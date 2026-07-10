@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'View Submission'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row mt-4">
      <div class="col-12">
        <div class="card submission-page-card">
          <div class="card-header d-flex justify-content-between">
            <div>
              <p class="submission-eyebrow mb-1">View Indicator Submission</p>
              <h5 class="submission-page-title mb-0">{{ $form->title }}</h5>
              <p class="submission-page-subtitle text-sm mb-0">{{ optional($submission->agency)->display_name }}</p>
            </div>
            <div class="text-end ms-auto">
              @can('update', $submission)
                <a href="{{ route('submissions.edit', $submission) }}" class="btn btn-submission-edit btn-xs mb-0">
                  <i class="fa fa-pencil me-1"></i>
                  Edit
                </a>
              @endcan
              <a href="{{ route('submissions.index') }}" class="btn btn-light btn-xs mb-0">Back</a>
            </div>
          </div>
          <div class="card-body p-3">
            <x-submission-approval-actions
              :submission="$submission"
              :approve-route="route('submissions.approve', $submission)"
              :return-route="route('submissions.return', $submission)"
              :reject-route="route('submissions.reject', $submission)"
            />
            @include('submissions._form', ['readonly' => true])
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

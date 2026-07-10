@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Edit Submission'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row mt-4">
      <div class="col-12">
        <div class="card submission-page-card">
          <div class="card-header">
            <div>
              <p class="submission-eyebrow mb-1">Edit Indicator Submission</p>
              <h5 class="submission-page-title mb-0">{{ $form->title }}</h5>
              <p class="submission-page-subtitle text-sm mb-0">{{ optional($form->agency)->display_name }}</p>
            </div>
          </div>
          <div class="card-body p-3">
            @if(session('error'))
              <div class="alert alert-danger text-white">{{ session('error') }}</div>
            @endif
            <form method="post" action="{{ route('submissions.update', $submission) }}" autocomplete="off">
              @method('put')
              @include('submissions._form')
            </form>
            <form id="submission-submit-form" method="post" action="{{ route('submissions.submit', $submission) }}">
              @csrf
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

@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Home'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row mt-4">
      <div class="col-12">
        <div class="card shadow-sm border-0 overflow-hidden" style="background-image: url('{{ isset($class_theme) && $class_theme == 'dark' ? asset('assets/img/blue gradient technology wave line_8802318.png') : asset('assets/img/tp204-background-10.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
          <div class="card-body p-5">
            <div class="row align-items-center">
              <div class="col-lg-7">
                <span class="badge bg-white text-info mb-3">Unified Package for Livelihoods, Industry, Food, and Transport</span>
                <h5 class="text-uppercase text-muted mb-2">Welcome to</h5>
                <h1 class="text-info text-gradient mb-3">DEPDev - UPLIFT Portal</h1>
                <p class="lead mb-3">
                  UPLIFT is a government project designed to consolidate weekly updates and provide a clearer view of initiatives related to livelihoods, industry, food, and transport.
                </p>
                <p class="mb-0">
                  This portal gives stakeholders a single place to track progress, review updates, and keep reporting aligned across participating offices and programs.
                </p>
              </div>
              <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="card shadow-sm mb-3">
                  <div class="card-body py-3">
                    <h6 class="text-info mb-1">Weekly Updates</h6>
                    <p class="text-sm mb-0">Capture regular status updates in one place so reporting stays timely, organized, and easy to review.</p>
                  </div>
                </div>
                <div class="card shadow-sm mb-3">
                  <div class="card-body py-3">
                    <h6 class="text-info mb-1">Program Coordination</h6>
                    <p class="text-sm mb-0">Support alignment across offices handling livelihoods, industry, food, and transport initiatives.</p>
                  </div>
                </div>
                <div class="card shadow-sm">
                  <div class="card-body py-3">
                    <h6 class="text-info mb-1">Progress Visibility</h6>
                    <p class="text-sm mb-0">Give decision-makers and implementers a clearer view of accomplishments, issues, and next steps.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="mb-3">What UPLIFT Covers</h5>
            <p class="mb-0">
              UPLIFT brings together updates and monitoring information for government workstreams related to livelihoods, industry, food, and transport.
            </p>
          </div>
        </div>
      </div>
      <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="mb-3">What This Portal Supports</h5>
            <p class="mb-0">
              The platform supports consistent reporting and monitoring of weekly updates without scattering information across separate channels.
            </p>
          </div>
        </div>
      </div>
      </div>
    </div>

    @if ($homeannouncement->start_date <= \Carbon\Carbon::now() && $homeannouncement->end_date >= \Carbon\Carbon::now())
      @include('components.home-announcement')
    @endif
    @include('layouts.footers.auth.footer')
    @php
      $birthdate = auth()->user()->birthday ? \Carbon\Carbon::parse(auth()->user()->birthday) : null;
    @endphp
    @if($birthdate && $birthdate->isSameDay(\Carbon\Carbon::now()->setYear($birthdate->year)))
      @include('others.hbd')
    @endif
  </div>
@endsection

@push('js')
  <script src="/assets/js/plugins/chartjs.min.js"></script>
  <script>
    var table = null;

    $(document).ready(function() {
    });
  </script>
@endpush

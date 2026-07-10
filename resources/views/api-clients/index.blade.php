@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'API Clients'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">
    @if(session('api_token'))
      <div class="alert alert-warning text-white mt-4">
        Copy this token now. It will not be shown again: <strong>{{ session('api_token') }}</strong>
      </div>
    @endif

    <div class="row mt-4">
      <div class="col-lg-4 mb-4">
        <div class="card">
          <div class="card-header pb-0"><h5 class="mb-0">New API Client</h5></div>
          <div class="card-body">
            <form method="post" action="{{ route('api-clients.store') }}">
              @csrf
              <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input name="name" class="form-control" value="{{ old('name') }}" placeholder="Client name">
                @error('name')<p class="text-danger text-xs pt-1">{{ $message }}</p>@enderror
              </div>
              <div class="mb-3">
                <label>Allowed IPs <span class="text-danger">*</span></label>
                <textarea name="allowed_ips" class="form-control" rows="5" placeholder="One IP per line or comma-separated">{{ old('allowed_ips') }}</textarea>
                @error('allowed_ips')<p class="text-danger text-xs pt-1">{{ $message }}</p>@enderror
              </div>
              <button class="btn btn-primary mb-0" type="submit">Create Token</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card">
          <div class="card-header pb-0"><h5 class="mb-0">API Clients</h5></div>
          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Allowed IPs</th>
                    <th>Status</th>
                    <th>Last Used</th>
                    <th class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($clients as $client)
                    <tr>
                      <td class="text-sm">{{ $client->name }}</td>
                      <td class="text-sm">{{ implode(', ', $client->allowed_ips ?? []) }}</td>
                      <td class="text-sm">
                        <span class="badge bg-{{ $client->revoked_at ? 'secondary' : 'success' }}">{{ $client->revoked_at ? 'Revoked' : 'Active' }}</span>
                      </td>
                      <td class="text-sm">{{ $client->last_used_at?->format('Y-m-d H:i:s') }}</td>
                      <td class="text-center">
                        @if(!$client->revoked_at)
                          <form method="post" action="{{ route('api-clients.revoke', $client) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger mb-0" onclick="return confirm('Revoke this API client?')">Revoke</button>
                          </form>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="5" class="text-center text-sm">No API clients yet.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    @include('layouts.footers.auth.footer')
  </div>
@endsection

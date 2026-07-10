<div class="table-responsive">
  <table class="table mb-0">
    <thead>
      <tr>
        <th class="ps-1">
          <p class="mb-0">Name</p>
        </th>
        <th class="text-center">
          <p class="mb-0">IP</p>
        </th>
        <th class="text-center">
          <p class="mb-0">Last Seen</p>
        </th>
        <th class="text-center">
          <p class="mb-0">Status</p>
        </th>
        <th class="text-center">
          <p class="mb-0">Action</p>
        </th>
      </tr>
    </thead>
    <tbody id="tbodyTrustedDevices">
      @foreach (optional($user ?? null)->trusted_devices ?? [] as $trusted_device)
        <tr>
          <td class="ps-1">
            <div class="my-auto">
              <span class="text-sm d-block text-sm">{{ $trusted_device->device_name }}
                @if($trusted_device->location_city != null){{ ' - near ' . $trusted_device->location_city }}@endif
              </span>
              @if(session()->has('two_factor_current') && session('two_factor_current') == $trusted_device->id)
                <p class="mb-0 text-xs text-success">Your current session</p>
              @endif
            </div>
          </td>
          <td class="ps-1">
            <div class="text-center">
              <span class="d-block text-sm">{{ $trusted_device->ip }}</span>
            </div>
          </td>
          <td class="ps-1">
            <div class="text-center">
              <span class="d-block text-sm">{{ $trusted_device->last_seen_at }}</span>
            </div>
          </td>
          <td class="ps-1">
            <div class="text-center">
              @php
                $badge_color = '';
                if ($trusted_device->status == 'Revoked') {
                  $badge_color = 'danger';
                } else if ($trusted_device->status == 'Expired') {
                  $badge_color = 'warning';
                } else {
                  $badge_color = 'success';
                }
              @endphp
              <span class="badge badge-{{ $badge_color }} badge-sm my-auto ms-auto">{{ $trusted_device->status }}</span>
            </div>
          </td>
          <td class="ps-1">
            <div class="text-center">
              @can('revokeUserSession', [App\Models\TrustedDevice::class, $trusted_device])
                <a data-bs-toggle="tooltip" data-bs-original-title="Revoke" class="border-0 bg-transparent px-1" href="{{ route('user-profile.revoke', $trusted_device->id) }}"><i class="fa fa-close text-danger"></i></a>
              @endcan
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
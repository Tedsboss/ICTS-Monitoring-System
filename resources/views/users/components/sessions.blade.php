{{-- DIREK Trusted Devices --}}
@php
    $trustedDevices = optional($user ?? null)->trusted_devices ?? collect();
    $devicesDark = session('user_settings.class_theme', '') === 'dark';
    $devicesSurface = $devicesDark ? '!bg-[#192130] !border-[#2c3546]' : '!bg-white !border-slate-200';
    $devicesHeading = $devicesDark ? '!text-slate-100' : '!text-[#172033]';
    $devicesMuted = $devicesDark ? '!text-slate-400' : '!text-slate-500';
    $devicesDivider = $devicesDark ? '!border-[#2c3546]' : '!border-slate-200';
    $devicesInset = $devicesDark ? '!bg-[#151c29]' : '!bg-slate-50';
    $devicesIcon = $devicesDark ? '!bg-[#353052] !text-[#d3ceff]' : '!bg-[#efedff] !text-[#635bff]';
@endphp
<div data-theme="{{ $devicesDark ? 'dark' : 'light' }}" class="direk-devices !overflow-hidden !rounded-xl !border !border-solid {{ $devicesSurface }}">
    <div class="devices-heading !flex !flex-wrap !items-center !justify-between !gap-3 !border-0 !border-b !border-solid !px-4 !py-4 {{ $devicesDivider }} {{ $devicesInset }} sm:!px-5">
        <div class="!flex !items-center !gap-3">
            <span class="!flex !h-9 !w-9 !shrink-0 !items-center !justify-center !rounded-lg {{ $devicesIcon }}"><i class="fa fa-desktop" aria-hidden="true"></i></span>
            <div>
                <p class="!m-0 !text-sm !font-semibold {{ $devicesHeading }}">Trusted device history</p>
                <p class="!m-0 !mt-1 !text-xs {{ $devicesMuted }}">Review device activity and remove trust when needed.</p>
            </div>
        </div>
        <span class="!rounded-full !px-3 !py-1 !text-xs !font-semibold {{ $devicesIcon }}">{{ $trustedDevices->count() }} {{ $trustedDevices->count() === 1 ? 'device' : 'devices' }}</span>
    </div>
    @if ($trustedDevices->count() > 0)
        <div class="!overflow-x-auto">
            <table class="!m-0 !block !w-full !border-collapse lg:!table">
                <caption class="!sr-only">Trusted devices, their activity, status, and available actions.</caption>
                <thead class="!sr-only lg:!not-sr-only lg:!table-header-group">
                    <tr class="!border-0 !border-b !border-solid {{ $devicesDivider }} {{ $devicesInset }}">
                        <th scope="col" class="!px-5 !py-3 !text-left !text-[10px] !font-bold !uppercase !tracking-wider {{ $devicesMuted }}">Device</th>
                        <th scope="col" class="!px-3 !py-3 !text-left !text-[10px] !font-bold !uppercase !tracking-wider {{ $devicesMuted }}">IP address</th>
                        <th scope="col" class="!px-3 !py-3 !text-left !text-[10px] !font-bold !uppercase !tracking-wider {{ $devicesMuted }}">Last seen</th>
                        <th scope="col" class="!px-3 !py-3 !text-left !text-[10px] !font-bold !uppercase !tracking-wider {{ $devicesMuted }}">Status</th>
                        <th scope="col" class="!px-5 !py-3 !text-right !text-[10px] !font-bold !uppercase !tracking-wider {{ $devicesMuted }}">Action</th>
                    </tr>
                </thead>
                <tbody id="tbodyTrustedDevices" class="!block lg:!table-row-group">
                    @foreach ($trustedDevices as $trusted_device)
                        @php
                            $isCurrentSession = session()->has('two_factor_current')
                                && session('two_factor_current') == $trusted_device->id;
                            $status = $trusted_device->status ?: 'Active';
                            if ($status === 'Revoked') {
                                $deviceStatusClass = $devicesDark ? '!bg-rose-950 !text-rose-200' : '!bg-rose-50 !text-rose-700';
                            } elseif ($status === 'Expired') {
                                $deviceStatusClass = $devicesDark ? '!bg-amber-950 !text-amber-200' : '!bg-amber-50 !text-amber-800';
                            } elseif ($status === 'Active') {
                                $deviceStatusClass = $devicesDark ? '!bg-emerald-950 !text-emerald-200' : '!bg-emerald-50 !text-emerald-700';
                            } else {
                                $deviceStatusClass = $devicesIcon;
                            }
                        @endphp
                        <tr class="devices-row !grid !grid-cols-2 !gap-x-4 !gap-y-4 !border-0 !border-b !border-solid !p-4 {{ $devicesDivider }} last:!border-b-0 lg:!table-row lg:!p-0">
                            <td class="!col-span-2 !p-0 lg:!px-5 lg:!py-5">
                                <div class="!flex !items-start !gap-3">
                                    <span class="!flex !h-10 !w-10 !shrink-0 !items-center !justify-center !rounded-xl {{ $devicesIcon }}"><i class="fa fa-desktop" aria-hidden="true"></i></span>
                                    <div class="!min-w-0">
                                        <div class="!flex !flex-wrap !items-center !gap-2">
                                            <span class="!break-words !text-sm !font-semibold {{ $devicesHeading }}">{{ $trusted_device->device_name ?: 'Unknown device' }}</span>
                                            @if ($isCurrentSession)
                                                <span class="!inline-flex !items-center !gap-1.5 !rounded-full !px-2 !py-1 !text-[10px] !font-semibold {{ $devicesIcon }}"><span class="!h-1.5 !w-1.5 !rounded-full !bg-[#8276ef]" aria-hidden="true"></span>Current session</span>
                                            @endif
                                        </div>
                                        @if ($trusted_device->location_city)
                                            <p class="!m-0 !mt-1 !text-xs {{ $devicesMuted }}"><i class="fa fa-map-marker !mr-1" aria-hidden="true"></i>Near {{ $trusted_device->location_city }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="!min-w-0 !p-0 lg:!px-3 lg:!py-5">
                                <span class="!mb-1 !block !text-[10px] !font-semibold !uppercase !tracking-wide {{ $devicesMuted }} lg:!hidden" aria-hidden="true">IP address</span>
                                <span class="!break-all !font-mono !text-xs {{ $devicesMuted }}">{{ $trusted_device->ip ?: '—' }}</span>
                            </td>
                            <td class="!min-w-0 !p-0 lg:!px-3 lg:!py-5">
                                <span class="!mb-1 !block !text-[10px] !font-semibold !uppercase !tracking-wide {{ $devicesMuted }} lg:!hidden" aria-hidden="true">Last seen</span>
                                <span class="!text-xs {{ $devicesMuted }}">{{ $trusted_device->last_seen_at ?: '—' }}</span>
                            </td>
                            <td class="!p-0 lg:!px-3 lg:!py-5">
                                <span class="!mb-1 !block !text-[10px] !font-semibold !uppercase !tracking-wide {{ $devicesMuted }} lg:!hidden" aria-hidden="true">Status</span>
                                <span class="!inline-flex !rounded-full !px-2.5 !py-1 !text-[11px] !font-semibold {{ $deviceStatusClass }}">{{ $status }}</span>
                            </td>
                            <td class="!self-end !p-0 !text-right lg:!px-5 lg:!py-5">
                                @can('revokeUserSession', [App\Models\TrustedDevice::class, $trusted_device])
                                    @if (!$isCurrentSession)
                                        <a href="{{ route('user-profile.revoke', $trusted_device->id) }}" class="device-revoke !inline-flex !min-h-[44px] !items-center !justify-center !gap-2 !rounded-lg !border !border-solid !border-rose-300 !px-3 !py-2 !text-xs !font-semibold !no-underline !transition focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-rose-400 motion-reduce:!transition-none {{ $devicesDark ? '!bg-[#192130] !text-rose-300 hover:!bg-rose-950 hover:!text-rose-200' : '!bg-white !text-rose-600 hover:!bg-rose-50 hover:!text-rose-700' }}" aria-label="Revoke trust for {{ $trusted_device->device_name ?: 'Unknown device' }}" onclick="return confirm('Are you sure you want to revoke this trusted device?');"><i class="fa fa-times-circle" aria-hidden="true"></i>Revoke</a>
                                    @else
                                        <span class="!inline-flex !min-h-[44px] !items-center !gap-1.5 !text-xs {{ $devicesMuted }}"><i class="fa fa-check" aria-hidden="true"></i>This device</span>
                                    @endif
                                @else
                                    <span class="!text-xs {{ $devicesMuted }}">—<span class="!sr-only">No action available</span></span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="!px-6 !py-10 !text-center">
            <span class="!mx-auto !flex !h-14 !w-14 !items-center !justify-center !rounded-2xl {{ $devicesIcon }}"><i class="fa fa-desktop !text-xl" aria-hidden="true"></i></span>
            <h3 class="!m-0 !mt-4 !text-sm !font-semibold {{ $devicesHeading }}">No trusted devices yet</h3>
            <p class="!mx-auto !mb-0 !mt-2 !max-w-sm !text-xs !leading-6 {{ $devicesMuted }}">Devices trusted through two-factor authentication will appear here.</p>
        </div>
    @endif
</div>
@push('css')
<style>
.direk-devices{--device-bg:#fff;--device-subtle:#f6f7fb;--device-border:#e8ebf2;--device-text:#172033;--device-copy:#637086;--device-accent:#635bff;--device-accent-bg:#efedff;border-color:var(--device-border)!important;border-radius:14px!important}
.direk-devices[data-theme="dark"]{--device-bg:#192130;--device-subtle:#151c29;--device-border:#2c3546;--device-text:#eef2fb;--device-copy:#aab5c8;--device-accent:#a19cff;--device-accent-bg:#353052}
.direk-devices .devices-heading{padding:18px 20px!important;border-color:var(--device-border)!important;background:var(--device-subtle)!important}
.direk-devices .devices-heading>div>span{border-radius:11px!important;background:var(--device-accent-bg)!important;color:var(--device-accent)!important}
.direk-devices .devices-heading>div>div>p:first-child{font-size:13px!important;font-weight:650!important;color:var(--device-text)!important}
.direk-devices .devices-heading>div>div>p:nth-child(2){font-size:11px!important;color:var(--device-copy)!important;line-height:1.75!important}
.direk-devices .devices-heading>span{background:var(--device-accent-bg)!important;color:var(--device-accent)!important;font-size:10px!important}
.direk-devices table{font-size:12px;background:var(--device-bg);color:var(--device-text)}
.direk-devices thead tr{background:var(--device-subtle)!important;border-color:var(--device-border)!important}
.direk-devices th{font-size:9px!important;font-weight:600!important;letter-spacing:.08em!important;background:var(--device-subtle);color:var(--device-copy)!important}
.direk-devices .devices-row{border-color:var(--device-border)!important;transition:background .15s}
.direk-devices .devices-row:hover{background:var(--device-subtle)}
.direk-devices .devices-row>td:first-child>div>span{background:var(--device-accent-bg)!important;color:var(--device-accent)!important;border-radius:11px!important;font-size:15px}
.direk-devices .device-revoke{border-radius:9px!important;min-height:36px!important;font-size:10px!important;padding:8px 11px!important;border-color:rgba(232,78,105,.35)!important}
.direk-devices .devices-row>td{vertical-align:middle}
.direk-devices .devices-row>td:first-child .font-semibold{font-size:12px!important}
.direk-devices .devices-row>td:first-child .rounded-full{font-size:9px!important;background:var(--device-accent-bg)!important;color:var(--device-accent)!important}
.direk-devices [hidden]{display:none!important}
@media(max-width:1023.98px){.direk-devices .devices-row{padding:19px 16px!important;gap:16px!important}.direk-devices .devices-heading{padding:17px 16px!important}}
@media(prefers-reduced-motion:reduce){.direk-devices .devices-row{transition:none!important}}
</style>
@endpush

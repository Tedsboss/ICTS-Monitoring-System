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
                <p class="!m-0 !text-sm !font-semibold {{ $devicesHeading }}">Your trusted devices</p>
                <p class="!m-0 !mt-1 !text-xs {{ $devicesMuted }}">Review device activity and remove trust when needed.</p>
            </div>
        </div>
        <span class="!rounded-full !px-3 !py-1 !text-xs !font-semibold {{ $devicesIcon }}">{{ $trustedDevices->count() }} {{ $trustedDevices->count() === 1 ? 'device' : 'devices' }}</span>
    </div>
    @if ($trustedDevices->count() > 0)
        <div class="!overflow-x-auto">
            <table class="devices-table !m-0 !block !w-full !border-collapse lg:!table">
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
                        <tr data-current="{{ $isCurrentSession ? 'true' : 'false' }}" class="devices-row !grid !grid-cols-2 !gap-x-4 !gap-y-4 !border-0 !border-b !border-solid !p-4 {{ $devicesDivider }} last:!border-b-0 lg:!table-row lg:!p-0">
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
                                <span data-state="{{ $status }}" class="device-status !inline-flex !rounded-full !px-2.5 !py-1 !text-[11px] !font-semibold {{ $deviceStatusClass }}">{{ $status }}</span>
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
        <div class="devices-empty !px-6 !py-10 !text-center">
            <span class="!mx-auto !flex !h-14 !w-14 !items-center !justify-center !rounded-2xl {{ $devicesIcon }}"><i class="fa fa-desktop !text-xl" aria-hidden="true"></i></span>
            <h3 class="!m-0 !mt-4 !text-sm !font-semibold {{ $devicesHeading }}">No trusted devices yet</h3>
            <p class="!mx-auto !mb-0 !mt-2 !max-w-sm !text-xs !leading-6 {{ $devicesMuted }}">Devices trusted through two-factor authentication will appear here.</p>
        </div>
    @endif
</div>
@push('css')
<style>
.direk-devices{--device-bg:#fff;--device-subtle:#f6f4fc;--device-border:#e3def0;--device-text:#292940;--device-copy:#74758e;--device-accent:#8267d9;--device-accent-bg:#eee8fa;--device-edge:#d9d1e9;border:0!important;border-radius:0!important;background:transparent!important;overflow:visible!important;color:var(--device-text)}
.direk-devices[data-theme="dark"]{--device-bg:#192130;--device-subtle:#151c29;--device-border:#37384f;--device-text:#eeebfd;--device-copy:#abb0cd;--device-accent:#b7a0f5;--device-accent-bg:#302b45;--device-edge:#101523}
.direk-devices [hidden]{display:none!important}.direk-devices .devices-heading{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;padding:0 0 22px!important;border:0!important;background:transparent!important}.direk-devices .devices-heading>div{display:flex;align-items:center;gap:13px}.direk-devices .devices-heading>div>span{display:grid;place-items:center;width:40px!important;height:40px!important;flex-shrink:0;border:1px solid var(--device-border);border-radius:12px!important;background:var(--device-accent-bg)!important;color:var(--device-accent)!important;font-size:18px;box-shadow:0 3px 0 var(--device-edge)}.direk-devices .devices-heading>div>div>p:first-child{font-size:14px!important;line-height:1.5;font-weight:650!important;color:var(--device-text)!important;margin:0}.direk-devices .devices-heading>div>div>p:nth-child(2){font-size:11px!important;line-height:1.8;color:var(--device-copy)!important;margin-top:5px!important}.direk-devices .devices-heading>span{display:inline-flex;align-items:center;border:1px solid var(--device-border);border-radius:8px!important;background:var(--device-subtle)!important;color:var(--device-copy)!important;font-size:10px!important;padding:7px 10px!important}
.direk-devices .devices-table{display:table!important;border-collapse:separate!important;border-spacing:0;width:100%;margin:0!important;border:1px solid var(--device-border);border-radius:14px;background:var(--device-bg);color:var(--device-text);overflow:hidden;font-size:12px}.direk-devices .devices-table caption{position:absolute;width:1px;height:1px;margin:-1px;overflow:hidden;clip:rect(0,0,0,0)}.direk-devices .devices-table thead{position:static!important;display:table-header-group!important;width:auto!important;height:auto!important;padding:0!important;margin:0!important;overflow:visible!important;clip:auto!important;white-space:normal!important}.direk-devices .devices-table thead tr{background:var(--device-subtle)!important;border-color:var(--device-border)!important}.direk-devices .devices-table th{padding:12px 10px!important;background:var(--device-subtle);color:var(--device-copy)!important;font-size:9px!important;font-weight:650!important;letter-spacing:.07em!important;border-bottom:1px solid var(--device-border);text-align:left}.direk-devices .devices-table th:first-child{padding-left:17px!important}.direk-devices .devices-table th:last-child{text-align:right;padding-right:17px!important}
.direk-devices .devices-table tbody{display:table-row-group!important}.direk-devices .devices-row{display:table-row!important;transition:background .15s ease;background:var(--device-bg)}.direk-devices .devices-row>td{vertical-align:middle;padding:18px 10px!important;border-bottom:1px solid var(--device-border);overflow-wrap:anywhere;color:var(--device-copy);font-size:11px}.direk-devices .devices-row:last-child>td{border-bottom:0}.direk-devices .devices-row>td:first-child{padding-left:17px!important;min-width:180px;max-width:260px}.direk-devices .devices-row>td:last-child{padding-right:17px!important;white-space:nowrap}.direk-devices .devices-row>td>span:first-child[aria-hidden="true"]{display:none!important}.direk-devices .devices-row>td:first-child>div{display:flex;align-items:flex-start;gap:12px}.direk-devices .devices-row>td:first-child>div>span{display:grid;place-items:center;flex-shrink:0;width:31px!important;height:31px!important;background:transparent!important;border-radius:0!important;color:var(--device-accent)!important;font-size:19px}.direk-devices .devices-row>td:first-child>div>div{min-width:0}.direk-devices .devices-row>td:first-child>div>div>div{display:flex;flex-wrap:wrap;align-items:center;gap:7px}.direk-devices .devices-row>td:first-child>div>div>div>span:first-child{font-size:12px!important;font-weight:600!important;color:var(--device-text)!important}.direk-devices .devices-row>td:first-child>div>div>div>span:nth-child(2){background:var(--device-accent-bg)!important;color:var(--device-accent)!important;font-size:9px!important;padding:4px 7px!important;border-radius:6px!important}.direk-devices .devices-row>td:first-child p{font-size:10px!important;color:var(--device-copy)!important;line-height:1.6}.direk-devices .devices-row>td:nth-child(2)>span:last-child{font-size:10px!important;word-break:break-all}.direk-devices .devices-row>td:nth-child(3)>span:last-child{font-size:10px!important;line-height:1.6}
.direk-devices .devices-row[data-current="true"]{background:var(--device-subtle)}.direk-devices .devices-row[data-current="true"]>td:first-child{border-left:3px solid var(--device-accent)}.direk-devices .devices-row:hover{background:var(--device-subtle)}
.direk-devices .device-status{display:inline-flex;align-items:center;gap:5px;border-radius:7px!important;padding:5px 8px!important;font-size:9px!important;font-weight:650!important;line-height:1.4;white-space:nowrap;background:var(--device-accent-bg);color:var(--device-accent)}.direk-devices .device-status::before{content:"";width:5px;height:5px;border-radius:50%;background:currentColor}.direk-devices .device-status[data-state="Active"]{background:#e8f6ef!important;color:#247c5e!important}.direk-devices .device-status[data-state="Expired"]{background:#fff4dd!important;color:#9a6923!important}.direk-devices .device-status[data-state="Revoked"]{background:#fdebf0!important;color:#ae3d60!important}.direk-devices[data-theme="dark"] .device-status[data-state="Active"]{background:#193b30!important;color:#8dd4b8!important}.direk-devices[data-theme="dark"] .device-status[data-state="Expired"]{background:#3e3220!important;color:#edc785!important}.direk-devices[data-theme="dark"] .device-status[data-state="Revoked"]{background:#402435!important;color:#eea1b9!important}
.direk-devices .device-revoke{display:inline-flex;align-items:center;justify-content:center;gap:6px;border:1px solid rgba(215,86,123,.3)!important;border-radius:8px!important;min-height:40px!important;padding:9px 10px!important;font-size:10px!important;font-weight:600;background:transparent!important;color:#c64c73!important;text-decoration:none;transition:background .15s ease}.direk-devices .device-revoke:hover{background:#fdebf0!important}.direk-devices[data-theme="dark"] .device-revoke{color:#f2a1b9!important}.direk-devices[data-theme="dark"] .device-revoke:hover{background:#402435!important}.direk-devices .device-revoke:focus-visible{outline:2px solid #d2567c!important;outline-offset:3px}
.direk-devices .devices-empty{padding:35px 22px!important;border:1px dashed var(--device-border);border-radius:15px;background:var(--device-subtle)}.direk-devices .devices-empty>span{display:grid;place-items:center;width:51px!important;height:51px!important;border:1px solid var(--device-border);border-radius:15px!important;margin:0 auto;background:var(--device-bg)!important;color:var(--device-accent)!important;box-shadow:0 3px 0 var(--device-edge)}.direk-devices .devices-empty h3,#direk-profile .direk-devices .devices-empty h3{display:block;font-size:14px!important;margin:19px 0 0!important;color:var(--device-text)!important}.direk-devices .devices-empty h3::before{display:none!important}.direk-devices .devices-empty p{font-size:11px!important;line-height:1.8;color:var(--device-copy)!important;max-width:350px;margin:9px auto 0!important}
@media(max-width:1023.98px){.direk-devices .devices-table{display:block!important;border:0;border-radius:0;background:transparent;overflow:visible}.direk-devices .devices-table thead{position:absolute!important;width:1px!important;height:1px!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important}.direk-devices .devices-table tbody{display:flex!important;flex-direction:column;gap:14px}.direk-devices .devices-row{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;padding:19px 17px!important;border:1px solid var(--device-border)!important;border-radius:14px;background:var(--device-bg);box-shadow:0 3px 0 var(--device-edge)}.direk-devices .devices-row[data-current="true"]{border-left:3px solid var(--device-accent)!important}.direk-devices .devices-row>td{display:block;min-width:0!important;max-width:none!important;padding:0!important;border:0!important}.direk-devices .devices-row>td:first-child{grid-column:1/-1;padding:0 0 13px!important;border-bottom:1px solid var(--device-border)!important}.direk-devices .devices-row>td:last-child{align-self:end;padding:0!important}.direk-devices .devices-row>td>span:first-child[aria-hidden="true"]{display:block!important;font-size:9px!important;font-weight:600;color:var(--device-copy)!important;letter-spacing:.04em;margin-bottom:6px!important}.direk-devices .devices-heading{gap:12px}.direk-devices .devices-heading>span{margin-left:53px}}
@media(prefers-reduced-motion:reduce){.direk-devices .devices-row,.direk-devices .device-revoke{transition:none!important}}

</style>
@endpush

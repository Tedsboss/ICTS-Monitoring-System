{{-- DIREK Trusted Devices --}}

@php
    $trustedDevices = optional($user ?? null)->trusted_devices ?? collect();
@endphp

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">

    @if($trustedDevices->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th
                            class="px-5 py-3 text-left text-[11px] font-bold
                                   uppercase tracking-wide text-slate-500"
                        >
                            Device
                        </th>

                        <th
                            class="px-4 py-3 text-left text-[11px] font-bold
                                   uppercase tracking-wide text-slate-500"
                        >
                            IP Address
                        </th>

                        <th
                            class="px-4 py-3 text-left text-[11px] font-bold
                                   uppercase tracking-wide text-slate-500"
                        >
                            Last Seen
                        </th>

                        <th
                            class="px-4 py-3 text-center text-[11px] font-bold
                                   uppercase tracking-wide text-slate-500"
                        >
                            Status
                        </th>

                        <th
                            class="px-5 py-3 text-right text-[11px] font-bold
                                   uppercase tracking-wide text-slate-500"
                        >
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody
                    id="tbodyTrustedDevices"
                    class="divide-y divide-slate-100"
                >
                    @foreach ($trustedDevices as $trusted_device)
                        @php
                            $isCurrentSession =
                                session()->has('two_factor_current') &&
                                session('two_factor_current') == $trusted_device->id;

                            $status = $trusted_device->status ?? '';

                            if ($status === 'Revoked') {
                                $statusClass =
                                    'border-rose-200 bg-rose-50 text-rose-700';
                            } elseif ($status === 'Expired') {
                                $statusClass =
                                    'border-amber-200 bg-amber-50 text-amber-700';
                            } else {
                                $statusClass =
                                    'border-emerald-200 bg-emerald-50 text-emerald-700';
                            }
                        @endphp

                        <tr class="transition hover:bg-slate-50/80">

                            {{-- Device --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">

                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center
                                               justify-center rounded-xl
                                               bg-slate-100 text-slate-500"
                                    >
                                        <i class="fa fa-desktop"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p
                                                class="mb-0 text-sm font-semibold
                                                       text-slate-800"
                                            >
                                                {{ $trusted_device->device_name ?: 'Unknown Device' }}
                                            </p>

                                            @if($isCurrentSession)
                                                <span
                                                    class="inline-flex items-center
                                                           rounded-full
                                                           border border-emerald-200
                                                           bg-emerald-50
                                                           px-2 py-0.5
                                                           text-[10px] font-bold
                                                           uppercase tracking-wide
                                                           text-emerald-700"
                                                >
                                                    <span
                                                        class="mr-1.5 h-1.5 w-1.5
                                                               rounded-full
                                                               bg-emerald-500"
                                                    ></span>

                                                    Current
                                                </span>
                                            @endif
                                        </div>

                                        @if($trusted_device->location_city)
                                            <p
                                                class="mb-0 mt-1 text-xs
                                                       text-slate-500"
                                            >
                                                <i
                                                    class="fa fa-map-marker mr-1"
                                                    aria-hidden="true"
                                                ></i>

                                                Near {{ $trusted_device->location_city }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- IP Address --}}
                            <td class="px-4 py-4">
                                <span
                                    class="font-mono text-xs font-medium
                                           text-slate-600"
                                >
                                    {{ $trusted_device->ip ?: '—' }}
                                </span>
                            </td>

                            {{-- Last Seen --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <i
                                        class="fa fa-clock-o text-xs
                                               text-slate-400"
                                        aria-hidden="true"
                                    ></i>

                                    <span class="text-xs text-slate-600">
                                        {{ $trusted_device->last_seen_at ?: '—' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-4 text-center">
                                <span
                                    class="inline-flex items-center
                                           rounded-full border
                                           px-2.5 py-1
                                           text-[10px] font-bold
                                           uppercase tracking-wide
                                           {{ $statusClass }}"
                                >
                                    {{ $status ?: 'Active' }}
                                </span>
                            </td>

                            {{-- Action --}}
                            <td class="px-5 py-4 text-right">
                                @can(
                                    'revokeUserSession',
                                    [
                                        App\Models\TrustedDevice::class,
                                        $trusted_device
                                    ]
                                )
                                    @if(!$isCurrentSession)
                                        <a
                                            href="{{ route(
                                                'user-profile.revoke',
                                                $trusted_device->id
                                            ) }}"
                                            class="inline-flex h-8 w-8
                                                   items-center justify-center
                                                   rounded-lg border
                                                   border-rose-200 bg-white
                                                   text-rose-600 transition
                                                   hover:bg-rose-50
                                                   hover:text-rose-700"
                                            data-bs-toggle="tooltip"
                                            data-bs-original-title="Revoke Device"
                                            aria-label="Revoke trusted device"
                                            onclick="return confirm(
                                                'Are you sure you want to revoke this trusted device?'
                                            );"
                                        >
                                            <i
                                                class="fa fa-times"
                                                aria-hidden="true"
                                            ></i>
                                        </a>
                                    @else
                                        <span
                                            class="inline-flex h-8 w-8
                                                   items-center justify-center
                                                   rounded-lg bg-slate-100
                                                   text-slate-400"
                                            title="Current session"
                                        >
                                            <i
                                                class="fa fa-check"
                                                aria-hidden="true"
                                            ></i>
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400">
                                        —
                                    </span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else

        {{-- Empty State --}}
        <div class="px-6 py-10 text-center">
            <div
                class="mx-auto flex h-12 w-12 items-center
                       justify-center rounded-xl bg-slate-100
                       text-slate-400"
            >
                <i class="fa fa-desktop text-lg"></i>
            </div>

            <h3 class="mb-0 mt-3 text-sm font-semibold text-slate-800">
                No Trusted Devices
            </h3>

            <p
                class="mx-auto mb-0 mt-1 max-w-md text-xs
                       leading-5 text-slate-500"
            >
                Devices trusted through two-factor authentication
                will appear here.
            </p>
        </div>
    @endif
</div>

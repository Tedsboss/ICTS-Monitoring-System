@php
    $authNavTitle = $title ?? 'Dashboard';
    $authNavCurrent = $subtitle ?? $authNavTitle;
    $authNavLinks = $links ?? [];
    $authNavHasTrail = count($authNavLinks) > 0 || !request()->routeIs('home') || !empty($subtitle);
@endphp

{{-- Title-area partial: the existing parent navbar and date/time component remain in control. --}}
<div class="!flex !min-w-0 !flex-1 !items-center !gap-3 !rounded-xl !bg-[#142d45] !px-4 !py-3 !text-white md:!gap-4">
    {{-- Preserve Argon's desktop toggle hooks and its 1200px sidebar breakpoint. --}}
    <div class="sidenav-toggler !hidden !h-11 !w-11 !min-w-[44px] !shrink-0 !items-center !justify-center !p-0 min-[1200px]:!flex">
        <button type="button" class="nav-link !m-0 !grid !h-11 !w-11 !min-w-[44px] !shrink-0 !place-items-center !rounded-lg !border !border-solid !border-white/15 !bg-white/5 !p-0 !shadow-none hover:!bg-white/15 focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#e4cf9d]" aria-label="Toggle sidebar" aria-controls="sidenav-main" title="Toggle sidebar">
            <span class="sidenav-toggler-inner !flex !h-4 !w-5 !flex-col !justify-between" aria-hidden="true">
                <i class="sidenav-toggler-line !m-0 !block !h-[2px] !w-full !transform-none !rounded-full !bg-[#e4cf9d]"></i>
                <i class="sidenav-toggler-line !m-0 !block !h-[2px] !w-full !transform-none !rounded-full !bg-[#e4cf9d]"></i>
                <i class="sidenav-toggler-line !m-0 !block !h-[2px] !w-full !transform-none !rounded-full !bg-[#e4cf9d]"></i>
            </span>
        </button>
    </div>

    <div class="!min-w-0 !flex-1">
        <nav aria-label="Breadcrumb" class="!min-w-0">
            <ol class="!m-0 !mb-1.5 !flex !list-none !flex-wrap !items-center !gap-x-2 !gap-y-1 !bg-transparent !p-0 !text-xs !leading-5">
                <li class="!flex !items-center" @if (!$authNavHasTrail) aria-current="page" @endif>
                    @if ($authNavHasTrail)
                        <a href="{{ route('home') }}" class="!inline-flex !items-center !gap-1.5 !rounded !text-slate-300 !no-underline !transition-colors hover:!text-white focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#e4cf9d] motion-reduce:!transition-none">
                            <i class="fa fa-home" aria-hidden="true"></i><span>Home</span>
                        </a>
                    @else
                        <span class="!inline-flex !items-center !gap-1.5 !text-[#e4cf9d]"><i class="fa fa-home" aria-hidden="true"></i><span>Home</span></span>
                    @endif
                </li>

                @foreach ($authNavLinks as $link)
                    <li class="!flex !min-w-0 !max-w-full !items-center !gap-2">
                        <i class="fa fa-angle-right !shrink-0 !text-slate-400" aria-hidden="true"></i>
                        @if (!empty($link['url']))
                            <a href="{{ $link['url'] }}" class="!min-w-0 !break-words !rounded !text-slate-300 !no-underline !transition-colors hover:!text-white focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#e4cf9d] motion-reduce:!transition-none">{{ $link['name'] ?? '' }}</a>
                        @else
                            <span class="!min-w-0 !break-words !text-slate-300">{{ $link['name'] ?? '' }}</span>
                        @endif
                    </li>
                @endforeach

                @if ($authNavHasTrail)
                    <li class="!flex !min-w-0 !max-w-full !items-center !gap-2" aria-current="page">
                        <i class="fa fa-angle-right !shrink-0 !text-slate-400" aria-hidden="true"></i>
                        <span class="!min-w-0 !break-words !font-medium !text-[#e4cf9d]">{{ $authNavCurrent }}</span>
                    </li>
                @endif
            </ol>
        </nav>
        <h1 class="!m-0 !break-words !text-lg !font-semibold !leading-7 !tracking-tight !text-white md:!text-xl">{{ $authNavTitle }}</h1>
    </div>
</div>
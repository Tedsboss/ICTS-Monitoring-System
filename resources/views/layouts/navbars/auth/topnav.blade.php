{{-- ============================================================= --}}
{{-- DIREK Authenticated Top Navigation                            --}}
{{-- ============================================================= --}}

<div class="flex min-w-0 items-center gap-4">

    {{-- ========================================================= --}}
    {{-- SIDEBAR TOGGLER                                          --}}
    {{-- ========================================================= --}}

    <div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none pe-2">
        <a
            href="javascript:;"
            class="nav-link p-0"
            aria-label="Toggle sidebar"
            title="Toggle sidebar"
        >
            <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line bg-white"></i>
                <i class="sidenav-toggler-line bg-white"></i>
                <i class="sidenav-toggler-line bg-white"></i>
            </div>
        </a>
    </div>

    {{-- ========================================================= --}}
    {{-- BREADCRUMB / PAGE TITLE                                  --}}
    {{-- ========================================================= --}}

    <nav
        aria-label="breadcrumb"
        class="min-w-0"
    >
        {{-- Breadcrumb --}}
        <ol
            class="mb-1 flex flex-wrap items-center gap-1
                   bg-transparent p-0 text-xs text-white/70"
        >
            {{-- Home --}}
            <li class="flex items-center">
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center text-white/70
                           transition hover:text-white"
                    title="Home"
                >
                    <i
                        class="fa fa-home"
                        aria-hidden="true"
                    ></i>

                    <span class="sr-only">
                        Home
                    </span>
                </a>
            </li>

            {{-- Additional Breadcrumb Links --}}
            @foreach (($links ?? []) as $link)
                <li class="flex min-w-0 items-center gap-1">

                    <span
                        class="text-white/40"
                        aria-hidden="true"
                    >
                        /
                    </span>

                    @if (!empty($link['url']))
                        <a
                            href="{{ $link['url'] }}"
                            class="max-w-[180px] truncate
                                   text-white/70 transition
                                   hover:text-white"
                        >
                            {{ $link['name'] ?? '' }}
                        </a>
                    @else
                        <span
                            class="max-w-[180px] truncate
                                   text-white/70"
                        >
                            {{ $link['name'] ?? '' }}
                        </span>
                    @endif

                </li>
            @endforeach

            {{-- Current Page --}}
            <li
                class="flex min-w-0 items-center gap-1"
                aria-current="page"
            >
                <span
                    class="text-white/40"
                    aria-hidden="true"
                >
                    /
                </span>

                <span
                    class="max-w-[220px] truncate
                           font-medium text-white"
                    title="{{ $subtitle ?? $title ?? 'Dashboard' }}"
                >
                    {{ $subtitle ?? $title ?? 'Dashboard' }}
                </span>
            </li>
        </ol>

        {{-- Page Title --}}
        <h1
            class="mb-0 truncate text-lg font-bold
                   leading-tight text-white"
            title="{{ $title ?? 'Dashboard' }}"
        >
            {{ $title ?? 'Dashboard' }}
        </h1>
    </nav>

</div>

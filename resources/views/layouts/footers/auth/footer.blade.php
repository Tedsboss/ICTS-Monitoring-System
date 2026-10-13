@php
    $footerDark = session('user_settings.class_theme', '') === 'dark';
    $footerBorder = $footerDark ? '!border-[#2c3546]' : '!border-[#e8ebf2]';
    $footerHeading = $footerDark ? '!text-[#eef2fb]' : '!text-[#172033]';
    $footerCopy = $footerDark ? '!text-[#aab5c8]' : '!text-[#637086]';
    $footerAccent = $footerDark
        ? '!bg-[#353052] !text-[#a19cff]'
        : '!bg-[#efedff] !text-[#635bff]';
    $footerBagongLogo = $bagongPilipinasLogo
        ?? asset('assets/img/neda/bagongpilipinas.png');
    $footerDepdevLogo = $depdevLogo
        ?? $logo
        ?? asset('assets/img/neda/logo.png');
@endphp
<footer class="!px-4 !pb-5 !pt-3 md:!px-6" aria-label="DIREK site footer">
    <div class="!mx-auto !flex !max-w-[1600px] !flex-col !gap-5 !border-0 !border-t !border-solid !pt-5 lg:!flex-row lg:!items-center lg:!justify-between {{ $footerBorder }}">
        <div class="!flex !min-w-0 !items-start !gap-3">
            <span
                class="!grid !h-9 !w-9 !shrink-0 !place-items-center !rounded-xl !text-sm !font-bold {{ $footerAccent }}"
                aria-hidden="true"
            >D</span>
            <div class="!min-w-0">
                <p class="!m-0 !text-xs !font-semibold !tracking-wide {{ $footerHeading }}">
                    D.I.R.E.K.
                    <span class="!ml-1 !font-normal !tracking-normal {{ $footerCopy }}">
                        Connected workspace
                    </span>
                </p>
                <p class="!m-0 !mt-1 !text-[11px] !leading-5 {{ $footerCopy }}">
                    Department of Economy, Planning, and Development
                </p>
                <p class="!m-0 !mt-1 !text-[11px] !leading-5 {{ $footerCopy }}">
                    © {{ now()->year }}
                    <a
                        href="https://depdev.gov.ph/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="!rounded !text-inherit !no-underline !underline-offset-4 hover:!underline focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-violet-400"
                    >
                        Information and Communications Technology Staff
                        <span class="sr-only"> (opens in a new tab)</span>
                    </a>
                </p>
            </div>
        </div>
        <div
            class="!inline-flex !items-center !gap-4 !self-start !rounded-xl !border !border-solid !border-[#e8ebf2] !bg-white !px-4 !py-2.5 lg:!shrink-0 lg:!self-auto"
            aria-label="Government identity logos"
        >
            <img
                src="{{ $footerBagongLogo }}"
                alt="Bagong Pilipinas"
                width="200"
                height="90"
                loading="lazy"
                decoding="async"
                class="!block !h-10 !w-[88px] !object-contain"
            >
            <span class="!h-7 !w-px !bg-[#e8ebf2]" aria-hidden="true"></span>
            <img
                src="{{ $footerDepdevLogo }}"
                alt="Department of Economy, Planning, and Development"
                width="200"
                height="85"
                loading="lazy"
                decoding="async"
                class="!block !h-10 !w-[88px] !object-contain"
            >
        </div>
    </div>
</footer>

@php
    $footerDark = session('user_settings.class_theme', '') === 'dark';
    $footerSurface = $footerDark ? '!bg-[#1a293a] !border-[#34465a]' : '!bg-white !border-slate-200';
    $footerHeading = $footerDark ? '!text-slate-100' : '!text-[#203b55]';
    $footerCopy = $footerDark ? '!text-slate-300' : '!text-slate-600';
    $footerMuted = $footerDark ? '!text-slate-400' : '!text-slate-500';

    // Separate overrides prevent one logo path from replacing both identities.
    $footerBagongLogo = $bagongPilipinasLogo ?? asset('assets/img/neda/bagongpilipinas.png');
    $footerDepdevLogo = $depdevLogo ?? $logo ?? asset('assets/img/neda/logo.png');
@endphp

<footer class="!box-border !px-4 !pb-5 !pt-3 md:!px-6" aria-label="DIREK site footer">
    <div class="!flex !flex-col !gap-6 !rounded-xl !border !border-solid !px-5 !py-6 md:!px-6 xl:!flex-row xl:!items-center xl:!justify-between {{ $footerSurface }}">
        <div class="!min-w-0 !text-center xl:!text-left">
            <p class="!m-0 !text-sm !font-semibold !tracking-[0.12em] {{ $footerHeading }}">D.I.R.E.K.</p>
            <p class="!m-0 !mt-2 !text-xs !leading-6 {{ $footerCopy }}">Department of Economy, Planning, and Development</p>
            <p class="!m-0 !mt-2 !text-xs !leading-6 {{ $footerMuted }}">
                Copyright © {{ now()->year }}
                <a href="https://depdev.gov.ph/" target="_blank" rel="noopener noreferrer" class="!rounded !font-medium !no-underline !underline-offset-4 hover:!underline focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-2 {{ $footerCopy }}">Information and Communications Technology Staff<span class="sr-only"> (opens in a new tab)</span></a>
            </p>
        </div>

        <div class="!flex !flex-wrap !items-center !justify-center !gap-x-6 !gap-y-4 !rounded-lg !bg-white !px-4 !py-3 xl:!shrink-0" aria-label="Government identity logos">
            <img src="{{ $footerBagongLogo }}" alt="Bagong Pilipinas" width="200" height="90" loading="lazy" decoding="async" class="!block !h-auto !max-h-[72px] !w-[160px] !max-w-full !object-contain">
            <img src="{{ $footerDepdevLogo }}" alt="Department of Economy, Planning, and Development" width="200" height="85" loading="lazy" decoding="async" class="!block !h-auto !max-h-[72px] !w-[160px] !max-w-full !object-contain">
        </div>
    </div>
</footer>

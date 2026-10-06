@php
    $birthdayDark = session('user_settings.class_theme', '') === 'dark';
    $birthdaySurface = $birthdayDark ? '!bg-[#1a293a] !border-[#34465a]' : '!bg-white !border-slate-200';
    $birthdayCopy = $birthdayDark ? '!text-slate-300' : '!text-slate-600';
    $birthdayPayload = [
        'userId' => (string) auth()->id(),
        'year' => now()->year,
    ];
@endphp

<div class="modal fade" id="happyModal" tabindex="-1" role="dialog" aria-labelledby="happyModalTitle" aria-describedby="happyModalMessage" hidden>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable !mx-4 !my-6 !max-w-2xl sm:!mx-auto">
        <div class="modal-content !overflow-hidden !rounded-2xl !border !border-solid !shadow-xl {{ $birthdaySurface }}">
            <div class="modal-header !items-start !gap-4 !border-0 !bg-[#142d45] !px-6 !py-6 !text-white">
                <div>
                    <p class="!m-0 !text-xs !font-medium !uppercase !tracking-[0.12em] !text-[#e4cf9d]">A moment to celebrate</p>
                    <h2 class="modal-title !m-0 !mt-2 !text-2xl !font-semibold !tracking-tight !text-white" id="happyModalTitle">Happy birthday!</h2>
                </div>
                <button type="button" data-bs-dismiss="modal" aria-label="Close birthday greeting" class="!grid !h-11 !w-11 !shrink-0 !place-items-center !rounded-lg !border-0 !bg-transparent !p-0 !text-white/80 hover:!bg-white/10 hover:!text-white focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#e4cf9d]"><i class="fa fa-times" aria-hidden="true"></i></button>
            </div>

            <div class="modal-body !px-5 !py-5 !text-center md:!px-8 {{ $birthdayCopy }}">
                <img src="{{ asset('assets/img/hbd.png') }}" alt="Birthday greeting" class="!mx-auto !block !h-auto !max-h-[45vh] !w-full !max-w-lg !object-contain">
                <p id="happyModalMessage" class="!m-0 !mt-5 !text-sm !leading-7">Wishing you a wonderful birthday and a year filled with happiness and meaningful achievements.</p>
            </div>

            <div class="modal-footer !justify-between !gap-3 !border-0 !px-6 !pb-6 !pt-0 md:!px-8">
                <span class="!m-0 !text-xs {{ $birthdayCopy }}">This greeting closes automatically after 15 seconds.</span>
                <button type="button" data-bs-dismiss="modal" class="!m-0 !min-h-[44px] !rounded-lg !border-0 !bg-[#203b55] !px-5 !py-2.5 !font-sans !text-sm !font-semibold !text-white !transition hover:!bg-[#315e87] focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-2 motion-reduce:!transition-none">Thank you</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script>
(function () {
    'use strict';
    const birthday = {{ \Illuminate\Support\Js::from($birthdayPayload) }};
    const modal = document.getElementById('happyModal');
    const $modal = $(modal);
    const storageKey = `birthday_shown_${birthday.userId}_${birthday.year}`;
    let alreadyShown = false;
    let confettiTimer = null;
    let autoCloseTimer = null;
    let birthdayConfetti = null;
    let confettiCanvas = null;

    function hasBeenShown() {
        if (alreadyShown) return true;
        try {
            return window.localStorage.getItem(storageKey) === '1';
        } catch (error) {
            // A blocked storage API must not prevent the greeting from opening.
            return false;
        }
    }

    function rememberShown() {
        alreadyShown = true;
        try {
            window.localStorage.setItem(storageKey, '1');
        } catch (error) {
            // The in-memory flag still prevents repetition on this page.
        }
    }

    function stopCelebration() {
        if (autoCloseTimer !== null) clearTimeout(autoCloseTimer);
        if (confettiTimer !== null) clearInterval(confettiTimer);
        autoCloseTimer = null;
        confettiTimer = null;
        if (birthdayConfetti) birthdayConfetti.reset();
        birthdayConfetti = null;
        if (confettiCanvas) confettiCanvas.remove();
        confettiCanvas = null;
    }

    function startConfetti() {
        if (typeof window.confetti !== 'function') return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        confettiCanvas = document.createElement('canvas');
        confettiCanvas.className = '!pointer-events-none !fixed !inset-0 !h-full !w-full !z-[1070]';
        confettiCanvas.setAttribute('aria-hidden', 'true');
        document.body.appendChild(confettiCanvas);
        birthdayConfetti = window.confetti.create(confettiCanvas, { resize: true });

        const defaults = {
            particleCount: 18,
            spread: 70,
            startVelocity: 28,
            ticks: 100,
            colors: ['#d8b777', '#8eb3dc', '#ffffff'],
            disableForReducedMotion: true,
        };
        function burst() {
            birthdayConfetti({ ...defaults, angle: 60, origin: { x: 0.12, y: 0.7 } });
            birthdayConfetti({ ...defaults, angle: 120, origin: { x: 0.88, y: 0.7 } });
        }
        burst();
        confettiTimer = setInterval(burst, 700);
    }

    function showBirthday() {
        if (hasBeenShown()) return;
        const activeModal = document.querySelector('.modal.show:not(#happyModal)');
        if (activeModal) {
            // Let the announcement finish before opening another Bootstrap modal.
            $(activeModal).one('hidden.bs.modal', function () {
                setTimeout(showBirthday, 0);
            });
            return;
        }
        modal.hidden = false;
        $modal.modal('show');
    }

    $modal.on('shown.bs.modal', function () {
        rememberShown();
        stopCelebration();
        autoCloseTimer = setTimeout(function () { $modal.modal('hide'); }, 15000);
        startConfetti();
    });
    $modal.on('hide.bs.modal hidden.bs.modal', stopCelebration);
    window.addEventListener('pagehide', stopCelebration);
    $(showBirthday);
})();
</script>
@endpush

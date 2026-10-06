@extends('layouts.app')

@section('content')
<div id="direk-login" class="!box-border [&_*]:!box-border !grid !min-h-screen !w-full !grid-cols-1 !bg-white !font-sans !text-sm !leading-normal !text-[#24364a] md:!grid-cols-[minmax(0,54%)_minmax(0,46%)]">
    {{-- Agency photo and introduction --}}
    <aside class="!relative !isolate !flex !min-h-[240px] !flex-col !justify-between !gap-10 !overflow-hidden !bg-[#142d45] !px-6 !py-7 !text-white md:!px-8 md:!py-10 lg:!px-12 xl:!px-16" aria-labelledby="direk-story-title">
        <img src="{{ asset('assets/img/neda/header.jpg') }}" alt="" aria-hidden="true" class="!absolute !inset-0 !-z-20 !h-full !w-full !object-cover !object-center">
        <div class="!pointer-events-none !absolute !inset-0 !-z-10 !bg-[#102b48]/40" aria-hidden="true"></div>
        <div class="!pointer-events-none !absolute !inset-0 !-z-10 !bg-gradient-to-t !from-[#102b48] !via-[#102b48]/50 !to-[#102b48]/20" aria-hidden="true"></div>

        <div class="!flex !items-center !gap-4">
            <img src="{{ asset('assets/img/neda/logo.png') }}" class="!h-11 !w-11 !shrink-0 !object-contain md:!h-12 md:!w-12" width="48" height="48" alt="DEPDev logo">
            <div>
                <strong class="!block !text-xl !leading-tight !tracking-[0.15em]">D.I.R.E.K.</strong>
                <span class="!mt-1 !block !text-[11px] !leading-relaxed !tracking-wide !text-white/85">Department of Economy, Planning,<br>and Development</span>
            </div>
        </div>

        <div class="!max-w-lg md:!pb-4 lg:!pb-6">
            <span class="!mb-5 !hidden !text-xs !uppercase !tracking-[0.15em] !text-[#e4cf9d] md:!block">Planning for progress</span>
            <h1 id="direk-story-title" class="!m-0 !text-3xl !font-medium !leading-[1.13] !tracking-[-0.04em] !text-white md:!text-4xl lg:!text-5xl xl:!text-[56px]">
                One workspace.<br>A shared direction.
            </h1>
            <p class="!m-0 !mt-6 !hidden !max-w-sm !text-sm !leading-8 !text-[#d3e0e5] md:!block">
                Connect financial planning, work planning, and reporting.
                Move forward with a clearer view of your progress.
            </p>
            <div class="!mt-8 !hidden !flex-wrap !gap-x-5 !gap-y-3 !border-solid !border-0 !border-t !border-white/25 !pt-5 !text-xs !text-[#d3e0e5] md:!flex">
                <span>Financial planning</span>
                <span>Work planning</span>
                <span>Reporting</span>
            </div>
        </div>
    </aside>

    {{-- Account access --}}
    <div class="!flex !min-w-0 !flex-col !gap-8 !bg-white !px-6 !py-6 md:!gap-10 md:!px-8 md:!py-8 lg:!px-10 xl:!px-14">
        <header class="!flex !items-center !justify-end !gap-4">
            <a href="{{ route('guest.contactus.create') }}" class="!no-underline !rounded !text-xs !font-semibold !underline-offset-4 hover:!underline focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-4">Need help? <span class="!ml-1" aria-hidden="true">↗</span></a>
        </header>

        <main class="!mx-auto !my-auto !w-full !max-w-[360px] !py-4 xl:!max-w-[380px]">
            <div class="!mb-8">
                <span class="!text-[11px] !font-semibold !uppercase !tracking-[0.12em] !text-[#866b35]">Welcome to DIREK</span>
                <h2 class="!m-0 !mb-3 !mt-3 !text-3xl !font-semibold !leading-tight !tracking-[-0.04em] lg:!text-4xl !text-[#24364a]">Welcome back.</h2>
                <p class="!m-0 !text-sm !text-[#66717a]">Sign in with your registered account.</p>
            </div>

            <form method="POST" action="{{ route('login.perform') }}" id="loginForm">
                @csrf

                <div class="!mb-5">
                    <label for="email" class="!mb-2 !block !text-xs !font-semibold">Email address</label>
                    <input
                        type="email" id="email" name="email" value="{{ old('email') }}"
                        placeholder="you@example.gov.ph" autocomplete="username" required
                        class="!font-sans !border-solid !block !h-[50px] !w-full !rounded-md !border !bg-white !px-4 !text-base !text-[#24364a] !shadow-sm !outline-none !transition placeholder:!text-xs placeholder:!text-[#7b8388] focus:!border-[#80939c] focus:!ring-2 focus:!ring-[#1b426c]/10 motion-reduce:!transition-none md:!text-sm @error('email') !border-red-600 @else !border-[#d6dee7] @enderror"
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    >
                    @error('email')
                        <p class="!m-0 !mt-2 !text-xs !leading-relaxed !text-red-700" id="email-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="!mb-5">
                    <div class="!mb-2 !flex !items-baseline !justify-between !gap-3">
                        <label for="password" class="!block !text-xs !font-semibold">Password</label>
                        <a href="{{ route('reset') }}" class="!no-underline !rounded !text-[11px] !text-[#586a76] !underline-offset-4 hover:!underline focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-4">Forgot password?</a>
                    </div>
                    <div class="!relative">
                        <input
                            type="password" id="password" name="password"
                            placeholder="Enter your password" autocomplete="current-password" required
                            class="!font-sans !border-solid !block !h-[50px] !w-full !rounded-md !border !bg-white !py-3 !pl-4 !pr-14 !text-base !text-[#24364a] !shadow-sm !outline-none !transition placeholder:!text-xs placeholder:!text-[#7b8388] focus:!border-[#80939c] focus:!ring-2 focus:!ring-[#1b426c]/10 motion-reduce:!transition-none md:!text-sm @error('password') !border-red-600 @else !border-[#d6dee7] @enderror"
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                        >
                        <button type="button" id="passwordToggle" aria-label="Show password" aria-pressed="false" aria-controls="password" class="!border-0 !border-solid !p-0 !font-sans !absolute !right-1 !top-1 !grid !h-[42px] !w-[42px] !cursor-pointer !place-items-center !rounded !bg-transparent !text-[#6d7c85] hover:!bg-[#f4f7fa] focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e]">
                            <svg class="!h-5 !w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>
                                <path id="password-eye-slash" class="!hidden" d="m4 4 16 16"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="!m-0 !mt-2 !text-xs !leading-relaxed !text-red-700" id="password-error">{{ $message }}</p>
                    @enderror
                </div>

                <label class="!mb-6 !flex !w-fit !cursor-pointer !items-center !gap-2.5 !text-xs !text-[#57656e]">
                    <input type="checkbox" id="rememberMe" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="!m-0 !shrink-0 !rounded !border-solid !border !h-4 !w-4 !border-[#d6dee7] !text-[#1b426c] !accent-[#1b426c] focus:!ring-[#b2863e] focus-visible:!outline focus-visible:!outline-2 focus-visible:!outline-offset-2 focus-visible:!outline-[#b2863e]">
                    <span>Keep me signed in</span>
                </label>

                @error('g-recaptcha-response')
                    <p class="!m-0 !mb-3 !text-xs !leading-relaxed !text-red-700" role="alert">{{ $message }}</p>
                @enderror

                <button type="button" id="loginButton" onclick="if (document.getElementById('loginForm').reportValidity()) submitFormRecaptcha();" class="!border-solid !font-sans !flex !min-h-[50px] !w-full !cursor-pointer !items-center !justify-center !gap-4 !rounded-md !border !border-[#1b426c] !bg-[#1b426c] !px-5 !py-3 !text-[13px] !font-semibold !text-white !transition hover:!bg-[#285886] focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-4 disabled:!cursor-wait disabled:!opacity-60 motion-reduce:!transition-none">
                    <span>Sign in</span>
                    <svg class="!h-5 !w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 12h15m-6-6 6 6-6 6"/></svg>
                </button>

                <div class="!mb-5 !mt-7 !flex !items-center !gap-3">
                    <span class="!h-px !flex-1 !bg-[#e0e5eb]" aria-hidden="true"></span>
                    <span class="!text-center !text-xs !text-[#707b82]">Or continue with</span>
                    <span class="!h-px !flex-1 !bg-[#e0e5eb]" aria-hidden="true"></span>
                </div>

                <div class="!grid !grid-cols-1 !gap-3">
                    <a href="{{ route('azure.login') }}" class="!no-underline !border-solid !flex !min-h-[48px] !items-center !justify-center !gap-2.5 !rounded-md !border !border-[#d6dee7] !bg-white !px-3 !py-3 !text-xs !font-semibold !transition hover:!border-[#a6b1b5] hover:!bg-[#f4f7fa] focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-4 motion-reduce:!transition-none">
                        <svg class="!h-[18px] !w-[18px] !shrink-0" viewBox="0 0 20 20" aria-hidden="true"><path fill="#f25022" d="M1 1h8v8H1z"/><path fill="#7fba00" d="M11 1h8v8h-8z"/><path fill="#00a4ef" d="M1 11h8v8H1z"/><path fill="#ffb900" d="M11 11h8v8h-8z"/></svg>
                        <span>Microsoft</span>
                    </a>
                </div>
            </form>

            <p class="!m-0 !mt-7 !text-center !text-[11px] !leading-relaxed !text-[#6b757c]">
                Need access to the platform?
                <a href="{{ route('guest.contactus.create') }}" class="!no-underline !whitespace-nowrap !rounded !font-semibold !text-[#24364a] !underline-offset-4 hover:!underline focus-visible:!outline-none focus-visible:!ring-2 focus-visible:!ring-[#b2863e] focus-visible:!ring-offset-4">Contact support <span aria-hidden="true">↗</span></a>
            </p>
        </main>

        <footer class="!flex !flex-col !items-center !justify-center !gap-2 !text-center !text-[11px] !text-[#68767d]">
            <span>© {{ now()->year }} DEPDev. All rights reserved.</span>
            <span class="!flex !items-center !gap-1.5">
                <svg class="!h-3 !w-3 !shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                For authorized users only
            </span>
        </footer>
    </div>
</div>
@endsection

@push('css')
<link href="{{ asset('assets/css/direk-login-tailwind.css') }}" rel="stylesheet">
@endpush

@push('js')
@include('recaptchas.script', ['form_name' => 'loginForm', 'form_action' => 'login'])
<script>
(function () {
    'use strict';

    const form = document.getElementById('loginForm');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const toggle = document.getElementById('passwordToggle');
    const slash = document.getElementById('password-eye-slash');

    // Do not intercept submit: the existing reCAPTCHA script calls jQuery .submit().
    function submitViaEnterKey(event) {
        if (event.key !== 'Enter' || event.isComposing) return;
        event.preventDefault();
        if (!event.repeat && form.reportValidity()) {
            submitFormRecaptcha();
        }
    }

    email.addEventListener('keydown', submitViaEnterKey);
    password.addEventListener('keydown', submitViaEnterKey);

    toggle.addEventListener('click', function () {
        const showing = password.type === 'password';
        password.type = showing ? 'text' : 'password';
        toggle.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
        toggle.setAttribute('aria-pressed', String(showing));
        slash.classList.toggle('!hidden', !showing);
    });
})();
</script>
@endpush

{{-- DIREK Account Security & Settings --}}

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">

    {{-- Dark Theme --}}
    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-5">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"
                >
                    <i class="fa fa-moon-o"></i>
                </div>

                <div>
                    <p class="mb-0 text-sm font-semibold text-slate-800">
                        Dark Theme
                    </p>

                    <p class="mb-0 mt-0.5 text-xs text-slate-500">
                        Use the dark appearance for your DIREK account.
                    </p>
                </div>
            </div>
        </div>

        <label class="relative mb-0 inline-flex shrink-0 cursor-pointer items-center">
            <input
                type="checkbox"
                id="enabledark"
                name="enabledark"
                value="Y"
                class="peer sr-only"
                @if(old('enabledark', $user->enabledark ?? null) == 'Y')
                    checked
                @endif
            >

            <span
                class="h-6 w-11 rounded-full bg-slate-300 transition
                       after:absolute after:left-[2px] after:top-[2px]
                       after:h-5 after:w-5 after:rounded-full after:bg-white
                       after:shadow-sm after:transition-all after:content-['']
                       peer-checked:bg-sky-600
                       peer-checked:after:translate-x-full"
            ></span>
        </label>
    </div>

    @if(
        $user == null ||
        optional($user)->can(
            'enableMyEmailNotification',
            [App\Models\User::class, $user]
        )
    )
        {{-- Email Notification --}}
        <div class="border-t border-slate-200"></div>

        <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-5">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"
                    >
                        <i class="fa fa-envelope-o"></i>
                    </div>

                    <div>
                        <p class="mb-0 text-sm font-semibold text-slate-800">
                            Email Notifications
                        </p>

                        <p class="mb-0 mt-0.5 text-xs text-slate-500">
                            Receive DIREK account notifications through email.
                        </p>
                    </div>
                </div>
            </div>

            <label class="relative mb-0 inline-flex shrink-0 cursor-pointer items-center">
                <input
                    type="checkbox"
                    id="emailnotif"
                    name="emailnotif"
                    value="Y"
                    class="peer sr-only"
                    @if(old('emailnotif', $user->emailnotif ?? null) == 'Y')
                        checked
                    @endif
                >

                <span
                    class="h-6 w-11 rounded-full bg-slate-300 transition
                           after:absolute after:left-[2px] after:top-[2px]
                           after:h-5 after:w-5 after:rounded-full after:bg-white
                           after:shadow-sm after:transition-all after:content-['']
                           peer-checked:bg-sky-600
                           peer-checked:after:translate-x-full"
                ></span>
            </label>
        </div>
    @endif

    {{-- Two-Factor Authentication --}}
    <div class="border-t border-slate-200"></div>

    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-5">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"
                >
                    <i class="fa fa-shield"></i>
                </div>

                <div>
                    <p class="mb-0 text-sm font-semibold text-slate-800">
                        Two-Factor Authentication
                    </p>

                    <p class="mb-0 mt-0.5 text-xs text-slate-500">
                        Add an extra verification step when signing in.
                    </p>
                </div>
            </div>
        </div>

        <label class="relative mb-0 inline-flex shrink-0 cursor-pointer items-center">
            <input
                type="checkbox"
                id="twofactor"
                name="twofactor"
                value="Y"
                class="peer sr-only"
                onchange="ocTwoFactor()"
                @if(old('twofactor', $user->twofactor ?? null) == 'Y')
                    checked
                @endif
            >

            <span
                class="h-6 w-11 rounded-full bg-slate-300 transition
                       after:absolute after:left-[2px] after:top-[2px]
                       after:h-5 after:w-5 after:rounded-full after:bg-white
                       after:shadow-sm after:transition-all after:content-['']
                       peer-checked:bg-sky-600
                       peer-checked:after:translate-x-full"
            ></span>
        </label>
    </div>

    {{-- Two-Factor Method --}}
    <div
        id="divTwoFactorType"
        class="border-t border-slate-200 bg-slate-50 px-4 py-4 sm:px-5"
        @if(old('twofactor', $user->twofactor ?? null) != 'Y')
            hidden
        @endif
    >
        <div class="mb-3">
            <p class="mb-0 text-xs font-bold uppercase tracking-wide text-slate-500">
                Verification Method
            </p>

            <p class="mb-0 mt-1 text-xs text-slate-500">
                Select how DIREK should send your verification code.
            </p>
        </div>

        <div class="space-y-3">

            {{-- Email --}}
            <label
                for="twofactortype_email"
                class="flex cursor-pointer items-center gap-3 rounded-xl
                       border border-slate-200 bg-white px-4 py-3
                       transition hover:border-sky-300 hover:bg-sky-50/40"
            >
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center
                           rounded-lg bg-sky-50 text-sky-600"
                >
                    <i class="fa fa-envelope-o"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="mb-0 text-sm font-semibold text-slate-800">
                        Email
                    </p>

                    <p
                        class="mb-0 mt-0.5 truncate text-xs text-slate-500"
                        id="pEmail"
                    >
                        {{ old('email', $user->email ?? null) }}
                    </p>
                </div>

                <input
                    class="h-4 w-4 shrink-0 accent-sky-600"
                    type="radio"
                    name="twofactortype"
                    id="twofactortype_email"
                    value="Email"
                    @if(
                        old(
                            'twofactortype',
                            $user->twofactortype ?? null
                        ) == 'Email'
                    )
                        checked
                    @endif
                >
            </label>

            {{-- SMS --}}
            <div
                class="flex items-center gap-3 rounded-xl border
                       border-slate-200 bg-slate-100 px-4 py-3 opacity-70"
            >
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center
                           rounded-lg bg-slate-200 text-slate-500"
                >
                    <i class="fa fa-mobile"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="mb-0 text-sm font-semibold text-slate-700">
                            SMS
                        </p>

                        <span
                            class="rounded-full bg-amber-100 px-2 py-0.5
                                   text-[10px] font-bold uppercase
                                   tracking-wide text-amber-700"
                        >
                            Coming Soon
                        </span>
                    </div>

                    <p
                        class="mb-0 mt-0.5 truncate text-xs text-slate-500"
                        id="pSMS"
                    >
                        {{ old('phone', $user->phone ?? null) ?: 'No phone number' }}
                    </p>
                </div>

                <input
                    class="h-4 w-4 shrink-0"
                    type="radio"
                    name="twofactortype"
                    id="twofactortype_sms"
                    value="SMS"
                    @if(
                        old(
                            'twofactortype',
                            $user->twofactortype ?? null
                        ) == 'SMS'
                    )
                        checked
                    @endif
                    disabled
                >
            </div>

            {{-- Authenticator App --}}
            <div
                class="flex items-center gap-3 rounded-xl border
                       border-slate-200 bg-slate-100 px-4 py-3 opacity-70"
            >
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center
                           rounded-lg bg-slate-200 text-slate-500"
                >
                    <i class="fa fa-key"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="mb-0 text-sm font-semibold text-slate-700">
                            Authenticator App
                        </p>

                        <span
                            class="rounded-full bg-amber-100 px-2 py-0.5
                                   text-[10px] font-bold uppercase
                                   tracking-wide text-amber-700"
                        >
                            Coming Soon
                        </span>
                    </div>

                    <p
                        class="mb-0 mt-0.5 truncate text-xs text-slate-500"
                        id="pAuthApp"
                    >
                        Microsoft Authenticator
                    </p>
                </div>

                <input
                    class="h-4 w-4 shrink-0"
                    type="radio"
                    name="twofactortype"
                    id="twofactortype_auth_app"
                    value="Authenticator App"
                    @if(
                        old(
                            'twofactortype',
                            $user->twofactortype ?? null
                        ) == 'Authenticator App'
                    )
                        checked
                    @endif
                    disabled
                >
            </div>
        </div>
    </div>
</div>

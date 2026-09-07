@extends('layouts.app')

@section('content')

<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Edit User'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="px-4 pb-8 pt-4">

    <div class="mx-auto max-w-4xl">

        {{-- Back --}}
        <div class="mb-4">
            <a
                href="{{ route('user-management') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300
                       bg-white px-4 py-2 text-sm font-semibold text-slate-700
                       shadow-sm transition hover:bg-slate-50"
            >
                <i class="fa fa-arrow-left"></i>
                <span>Back to User Management</span>
            </a>
        </div>

        {{-- Page Header --}}
        <div class="mb-5">
            <h1 class="text-xl font-bold text-slate-900">
                Edit User
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Update the user's DIREK account and access role.
            </p>
        </div>

        {{-- Validation Summary --}}
        @if ($errors->any())
            <div
                class="mb-5 rounded-xl border border-rose-200 bg-rose-50
                       px-4 py-3 text-sm text-rose-800"
            >
                <div class="flex items-start gap-3">
                    <i class="fa fa-exclamation-circle mt-0.5"></i>

                    <div>
                        <p class="font-semibold">
                            Please review the information below.
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('user-edit.update', $user->id) }}"
        >
            @csrf

            {{-- Account Information --}}
            <section
                class="mb-5 overflow-hidden rounded-2xl border border-slate-200
                       bg-white shadow-sm"
            >
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-800">
                        Account Information
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Basic information used to identify and access the DIREK system.
                    </p>
                </div>

                <div class="p-5">

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        {{-- First Name --}}
                        <div>
                            <label
                                for="firstname"
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                First Name
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="text"
                                id="firstname"
                                name="firstname"
                                value="{{ old('firstname', $user->firstname) }}"
                                required
                                maxlength="150"
                                autocomplete="given-name"
                                class="block w-full rounded-lg border border-slate-300
                                       bg-white px-3 py-2.5 text-sm text-slate-700
                                       shadow-sm outline-none transition
                                       focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                            >

                            @error('firstname')
                                <p class="mt-1 text-xs font-medium text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <label
                                for="lastname"
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                Last Name
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="text"
                                id="lastname"
                                name="lastname"
                                value="{{ old('lastname', $user->lastname) }}"
                                required
                                maxlength="150"
                                autocomplete="family-name"
                                class="block w-full rounded-lg border border-slate-300
                                       bg-white px-3 py-2.5 text-sm text-slate-700
                                       shadow-sm outline-none transition
                                       focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                            >

                            @error('lastname')
                                <p class="mt-1 text-xs font-medium text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="md:col-span-2">
                            <label
                                for="email"
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                Email Address
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                maxlength="255"
                                autocomplete="email"
                                class="block w-full rounded-lg border border-slate-300
                                       bg-white px-3 py-2.5 text-sm text-slate-700
                                       shadow-sm outline-none transition
                                       focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                            >

                            @error('email')
                                <p class="mt-1 text-xs font-medium text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>
                </div>
            </section>

            {{-- DIREK Access --}}
            <section
                class="mb-5 overflow-hidden rounded-2xl border border-slate-200
                       bg-white shadow-sm"
            >
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-800">
                        DIREK Access
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Select what level of authority this account has in DIREK.
                    </p>
                </div>

                <div class="p-5">

                    <div>
                        <label
                            for="role"
                            class="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            Role
                            <span class="text-rose-500">*</span>
                        </label>

                        <select
                            name="role"
                            id="role"
                            required
                            class="block w-full rounded-lg border border-slate-300
                                   bg-white px-3 py-2.5 text-sm text-slate-700
                                   shadow-sm outline-none transition
                                   focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                        >
                            <option value="">
                                Select Role
                            </option>

                            @foreach ($roles as $role)
                                <option
                                    value="{{ $role->id }}"
                                    {{ (string) old('role', $user->role_id) === (string) $role->id ? 'selected' : '' }}
                                >
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('role')
                            <p class="mt-1 text-xs font-medium text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-2 text-xs text-slate-500">
                            Role determines what actions the user is allowed to perform.
                            Staff/Office assignment will determine which office records
                            the user can access.
                        </p>
                    </div>

                </div>
            </section>

            {{-- Password --}}
            <section
                class="mb-5 overflow-hidden rounded-2xl border border-slate-200
                       bg-white shadow-sm"
            >
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-800">
                        Change Password
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Leave both fields blank to keep the user's current password.
                    </p>
                </div>

                <div class="p-5">

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div>
                            <label
                                for="password"
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                New Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                autocomplete="new-password"
                                class="block w-full rounded-lg border border-slate-300
                                       bg-white px-3 py-2.5 text-sm text-slate-700
                                       shadow-sm outline-none transition
                                       focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                            >

                            @error('password')
                                <p class="mt-1 text-xs font-medium text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="confirm-password"
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                Confirm New Password
                            </label>

                            <input
                                type="password"
                                id="confirm-password"
                                name="confirm-password"
                                autocomplete="new-password"
                                class="block w-full rounded-lg border border-slate-300
                                       bg-white px-3 py-2.5 text-sm text-slate-700
                                       shadow-sm outline-none transition
                                       focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                            >

                            @error('confirm-password')
                                <p class="mt-1 text-xs font-medium text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div
                class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
            >
                <a
                    href="{{ route('user-management') }}"
                    class="inline-flex items-center justify-center rounded-lg border
                           border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold
                           text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg
                           bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white
                           shadow-sm transition hover:bg-sky-700"
                >
                    <i class="fa fa-save"></i>
                    <span>Save Changes</span>
                </button>
            </div>

        </form>

    </div>

    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>

</div>

@endsection

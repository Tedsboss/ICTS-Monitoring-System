@extends('layouts.app')

@section('content')
<div class="px-4 pb-8 pt-4">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0 shadow-none border-radius-xl z-index-sticky"
         id="navbarBlur"
         data-scroll="false">
        <div class="container-fluid px-0">
            @include('layouts.navbars.auth.topnav', ['title' => 'Level Management'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>

    <div id="pageMessage"></div>

    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h4 class="mb-1 text-lg font-bold text-slate-800">
                    Create Level
                </h4>
                <p class="mb-0 text-sm text-slate-500">
                    Add a level that can be assigned to an Allocation.
                </p>
            </div>

            <a href="{{ route('levels.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300
                      bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm
                      transition hover:bg-slate-50">
                <i class="fa fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </section>

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="mb-2 font-semibold">
                <i class="fa fa-exclamation-circle mr-1"></i>
                Please correct the following:
            </div>

            <ul class="mb-0 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h5 class="mb-0 text-base font-bold text-slate-800">
                Level Information
            </h5>

            <p class="mb-0 mt-1 text-xs text-slate-500">
                Define the code and description for the new allocation level.
            </p>
        </div>

        <form method="POST"
              action="{{ route('levels.store') }}"
              id="levelForm">
            @csrf

            <div class="p-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="level_code" class="mb-2 block text-sm font-semibold text-slate-700">
                            Level Code <span class="text-red-500">*</span>
                        </label>

                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white
                                    transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200
                                        bg-slate-50 text-slate-400">
                                <i class="fa fa-code"></i>
                            </div>

                            <input type="text"
                                   name="level_code"
                                   id="level_code"
                                   maxlength="50"
                                   value="{{ old('level_code') }}"
                                   placeholder="e.g. Tier1"
                                   autocomplete="off"
                                   class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                                          text-sm text-slate-700 outline-none focus:ring-0"
                                   required
                                   autofocus>
                        </div>

                        <p class="mb-0 mt-1.5 text-xs text-slate-500">
                            Enter a unique code used to identify the level.
                        </p>

                        @error('level_code')
                            <p class="mt-1.5 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="level_description" class="mb-2 block text-sm font-semibold text-slate-700">
                            Level Description <span class="text-red-500">*</span>
                        </label>

                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white
                                    transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200
                                        bg-slate-50 text-slate-400">
                                <i class="fa fa-align-left"></i>
                            </div>

                            <input type="text"
                                   name="level_description"
                                   id="level_description"
                                   maxlength="150"
                                   value="{{ old('level_description') }}"
                                   placeholder="e.g. Tier One"
                                   autocomplete="off"
                                   class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                                          text-sm text-slate-700 outline-none focus:ring-0"
                                   required>
                        </div>

                        <p class="mb-0 mt-1.5 text-xs text-slate-500">
                            Provide the descriptive name of the level.
                        </p>

                        @error('level_description')
                            <p class="mt-1.5 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5 flex items-start gap-4 rounded-2xl border border-sky-100 bg-sky-50/60 p-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-600 text-white">
                        <i class="fa fa-sitemap"></i>
                    </div>

                    <div>
                        <h6 class="mb-1 text-sm font-bold text-sky-800">
                            About Levels
                        </h6>

                        <p class="mb-0 text-xs leading-5 text-slate-600">
                            Levels identify the allocation classification used within a fiscal year.
                            Each Level can have one Allocation per fiscal year.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4">
                <a href="{{ route('levels.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300
                          bg-white px-5 py-2.5 text-sm font-semibold text-slate-700
                          transition hover:bg-slate-50">
                    <i class="fa fa-times text-slate-400"></i>
                    <span>Cancel</span>
                </a>

                <button type="submit"
                        id="saveLevelButton"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600
                               px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                               transition hover:bg-sky-700">
                    <i class="fa fa-save" id="saveLevelIcon"></i>
                    <span id="saveLevelText">Save Level</span>
                </button>
            </div>
        </form>
    </section>

    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('levelForm');
    const button = document.getElementById('saveLevelButton');
    const icon = document.getElementById('saveLevelIcon');
    const text = document.getElementById('saveLevelText');

    if (!form || !button) return;

    form.addEventListener('submit', function () {
        if (button.disabled) return;

        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');
        icon.className = 'fa fa-spinner fa-spin';
        text.textContent = 'Saving...';
    });
});
</script>
@endsection
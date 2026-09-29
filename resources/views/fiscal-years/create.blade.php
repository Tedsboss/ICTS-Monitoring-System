@extends('layouts.app')

@section('content')
<div class="px-4 pb-8 pt-4">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0 shadow-none border-radius-xl z-index-sticky"
         id="navbarBlur"
         data-scroll="false">
        <div class="container-fluid px-0">
            @include('layouts.navbars.auth.topnav', ['title' => 'Fiscal Year Management'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>

    <div id="pageMessage"></div>

    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h4 class="mb-1 text-lg font-bold text-slate-800">
                    Create Fiscal Year
                </h4>
                <p class="mb-0 text-sm text-slate-500">
                    Add a fiscal year that can be used for Allocation Management.
                </p>
            </div>

            <a href="{{ route('fiscal-years.index') }}"
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
                Fiscal Year Information
            </h5>

            <p class="mb-0 mt-1 text-xs text-slate-500">
                Define the fiscal year that will be available for allocations.
            </p>
        </div>

        <form method="POST"
              action="{{ route('fiscal-years.store') }}"
              id="fiscalYearForm">
            @csrf

            <div class="p-5">
                <div class="max-w-md">
                    <label for="year" class="mb-2 block text-sm font-semibold text-slate-700">
                        Fiscal Year <span class="text-red-500">*</span>
                    </label>

                    <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white
                                transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                        <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200
                                    bg-slate-50 text-slate-400">
                            <i class="fa fa-calendar"></i>
                        </div>

                        <input type="number"
                               name="year"
                               id="year"
                               min="2000"
                               max="2100"
                               value="{{ old('year') }}"
                               placeholder="e.g. 2028"
                               autocomplete="off"
                               class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                                      text-sm text-slate-700 outline-none focus:ring-0"
                               required
                               autofocus>
                    </div>

                    <p class="mb-0 mt-1.5 text-xs text-slate-500">
                        Enter a fiscal year between 2000 and 2100.
                    </p>

                    @error('year')
                        <p class="mt-1.5 text-xs font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mt-5 flex items-start gap-4 rounded-2xl border border-sky-100 bg-sky-50/60 p-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-600 text-white">
                        <i class="fa fa-info"></i>
                    </div>

                    <div>
                        <h6 class="mb-1 text-sm font-bold text-sky-800">
                            About Fiscal Years
                        </h6>

                        <p class="mb-0 text-xs leading-5 text-slate-600">
                            A fiscal year identifies the budget period for an Allocation.
                            Each fiscal year can be associated with multiple Levels.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4">
                <a href="{{ route('fiscal-years.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300
                          bg-white px-5 py-2.5 text-sm font-semibold text-slate-700
                          transition hover:bg-slate-50">
                    <i class="fa fa-times text-slate-400"></i>
                    <span>Cancel</span>
                </a>

                <button type="submit"
                        id="saveFiscalYearButton"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600
                               px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                               transition hover:bg-sky-700">
                    <i class="fa fa-save" id="saveFiscalYearIcon"></i>
                    <span id="saveFiscalYearText">Save Fiscal Year</span>
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
    const form = document.getElementById('fiscalYearForm');
    const button = document.getElementById('saveFiscalYearButton');
    const icon = document.getElementById('saveFiscalYearIcon');
    const text = document.getElementById('saveFiscalYearText');

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
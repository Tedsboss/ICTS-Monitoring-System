@extends('layouts.app')

@section('content')

<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Procurement – Add'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="px-4 pb-8 pt-4">

    {{-- Back --}}
    <div class="mb-4">

        <a
            href="{{ route('procurements.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border
                   border-slate-300 bg-white px-4 py-2 text-sm
                   font-semibold text-slate-700 shadow-sm transition
                   hover:bg-slate-50"
        >
            <i class="fa fa-arrow-left"></i>

            <span>Back to Procurements</span>
        </a>

    </div>


    {{-- Form Card --}}
    <section class="max-w-6xl">

        <div
            class="overflow-hidden rounded-2xl border
                   border-slate-200 bg-white shadow-sm"
        >

            {{-- Header --}}
            <div
                class="flex flex-col gap-4 border-b border-slate-200
                       px-6 py-5 sm:flex-row sm:items-center
                       sm:justify-between"
            >

                <div>

                    <h1 class="text-lg font-bold text-slate-900">
                        Add Procurement
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        New procurement entry based on Work and Financial Plan.
                    </p>

                </div>


                <div
                    class="inline-flex w-fit items-center gap-2 rounded-full
                           bg-sky-50 px-3 py-1.5 text-xs font-semibold
                           text-sky-700"
                >
                    <i class="fa fa-plus"></i>

                    <span>New Entry</span>
                </div>

            </div>


            {{-- Body --}}
            <div class="p-6">

                {{-- Validation Errors --}}
                @if ($errors->any())

                    <div
                        class="mb-6 rounded-xl border border-rose-200
                               bg-rose-50 px-4 py-3 text-sm text-rose-800"
                    >

                        <div class="flex items-start gap-3">

                            <div
                                class="flex h-8 w-8 shrink-0 items-center
                                       justify-center rounded-lg
                                       bg-rose-100 text-rose-600"
                            >
                                <i class="fa fa-exclamation-triangle"></i>
                            </div>


                            <div>

                                <p class="font-semibold">
                                    Please correct the following:
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


                {{-- Create Form --}}
                <form
                    method="POST"
                    action="{{ route('procurements.store') }}"
                >
                    @include('procurements._form')
                </form>

            </div>

        </div>

    </section>


    {{-- Footer --}}
    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>

</div>

@endsection

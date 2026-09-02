@extends('layouts.app')

@section('content')

<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Procurement – Edit'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="px-4 pb-8 pt-4">

    {{-- Back --}}
    <div class="mb-4">

        <a
            href="{{ route('procurements.index') }}"
            class="inline-flex items-center gap-2 rounded-lg
                   border border-slate-300 bg-white px-4 py-2
                   text-sm font-semibold text-slate-700 shadow-sm
                   transition hover:bg-slate-50"
        >
            <i class="fa fa-arrow-left"></i>

            <span>
                Back to Procurements
            </span>
        </a>

    </div>


    {{-- Edit Form --}}
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

                <div class="min-w-0">

                    <h1 class="text-lg font-bold text-slate-900">
                        Edit Procurement
                    </h1>

                    <p
                        class="mt-1 break-words text-sm
                               text-slate-500"
                    >
                        {{ $procurement->procurement_title ?: 'Procurement Entry' }}
                    </p>

                </div>


                <div
                    class="inline-flex w-fit shrink-0 items-center gap-2
                           rounded-full bg-amber-50 px-3 py-1.5
                           text-xs font-semibold text-amber-700"
                >
                    <i class="fa fa-pencil"></i>

                    <span>
                        Editing Entry
                    </span>
                </div>

            </div>


            {{-- Body --}}
            <div class="p-6">

                {{-- Success Message --}}
                @if (session('success'))

                    <div
                        class="mb-6 rounded-xl border
                               border-emerald-200 bg-emerald-50
                               px-4 py-3 text-sm text-emerald-800"
                    >

                        <div class="flex items-start gap-3">

                            <div
                                class="flex h-8 w-8 shrink-0
                                       items-center justify-center
                                       rounded-lg bg-emerald-100
                                       text-emerald-600"
                            >
                                <i class="fa fa-check"></i>
                            </div>


                            <div class="flex-1">

                                <p class="font-semibold">
                                    Changes saved successfully.
                                </p>

                                <p class="mt-0.5">
                                    {{ session('success') }}
                                </p>

                            </div>

                        </div>

                    </div>

                @endif


                {{-- Validation Errors --}}
                @if ($errors->any())

                    <div
                        class="mb-6 rounded-xl border
                               border-rose-200 bg-rose-50
                               px-4 py-3 text-sm text-rose-800"
                    >

                        <div class="flex items-start gap-3">

                            <div
                                class="flex h-8 w-8 shrink-0
                                       items-center justify-center
                                       rounded-lg bg-rose-100
                                       text-rose-600"
                            >
                                <i class="fa fa-exclamation-triangle"></i>
                            </div>


                            <div class="flex-1">

                                <p class="font-semibold">
                                    Please correct the following:
                                </p>

                                <ul class="mt-2 list-disc space-y-1 pl-5">

                                    @foreach ($errors->all() as $error)

                                        <li>
                                            {{ $error }}
                                        </li>

                                    @endforeach

                                </ul>

                            </div>

                        </div>

                    </div>

                @endif


                {{-- Procurement Form --}}
                <form
                    method="POST"
                    action="{{ route('procurements.update', $procurement) }}"
                >

                    @method('PUT')

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

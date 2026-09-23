<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="/assets/img/apple-icon.png">
    {{-- <link rel="icon" type="image/png" href="/assets/img/favicon.png"> --}}
    <link rel="icon" type="image/png" href="/assets/img/neda/logo.png">
    <title>DIREK App</title>
    @if (config('app.is_demo'))
        <meta name="keywords"
            content="creative tim, updivision, html dashboard, laravel, argon, html css dashboard laravel, laravel argon dashboard laravel, laravel argon dashboard laravel pro, laravel argon dashboard, laravel argon dashboard pro, argon admin, laravel dashboard, laravel dashboard pro, laravel admin, web dashboard, bootstrap 5 dashboard laravel, bootstrap 5, css3 dashboard, bootstrap 5 admin laravel, argon dashboard bootstrap 5 laravel, frontend, responsive bootstrap 5 dashboard, argon dashboard, argon laravel bootstrap 5 dashboard" />
        <meta name="description" content="Premium Admin Dashboard for Laravel with Ready to Use CRUDs" />
        <meta itemprop="name" content="Argon Dashboard 2 PRO Laravel by Creative Tim & UPDIVISION" />
        <meta itemprop="description" content="Premium Admin Dashboard for Laravel with Ready to Use CRUDs" />
        <meta itemprop="image"
            content="https://s3.amazonaws.com/creativetim_bucket/products/146/original/argon-dashboard-pro-laravel.jpg" />
        <meta name="twitter:card" content="product" />
        <meta name="twitter:site" content="@creativetim" />
        <meta name="twitter:title" content="Argon Dashboard 2 PRO Laravel by Creative Tim & UPDIVISION" />
        <meta name="twitter:description" content="Premium Admin Dashboard for Laravel with Ready to Use CRUDs" />
        <meta name="twitter:creator" content="@creativetim" />
        <meta name="twitter:image"
            content="https://s3.amazonaws.com/creativetim_bucket/products/146/original/argon-dashboard-pro-laravel.jpg" />
        <meta property="fb:app_id" content="655968634437471" />
        <meta property="og:title" content="Argon Dashboard 2 PRO Laravel by Creative Tim & UPDIVISION" />
        <meta property="og:type" content="article" />
        <meta property="og:url" content="https://www.creative-tim.com/live/argon-dashboard-pro-laravel" />
        <meta property="og:image"
            content="https://s3.amazonaws.com/creativetim_bucket/products/146/original/argon-dashboard-pro-laravel.jpg" />
        <meta property="og:description" content="Premium Admin Dashboard for Laravel with Ready to Use CRUDs" />
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="/assets/css/nucleo-svg.css" rel="stylesheet" />
    {{-- <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script> --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
    <link href="/assets/css/nucleo-svg.css" rel="stylesheet" />
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/ju/dt-1.11.5/datatables.min.css"/> --}}
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css"> --}}
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"> --}}
    <link id="pagestyle" href="/assets/css/argon-dashboard.css?v=1.0.8" rel="stylesheet" />
    <link href="{{ asset('assets/css/tailwind.css') }}" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.1/dist/css/bootstrap-select.min.css">
    <link href="{{ asset('assets') }}/css/plugins/datatables.min.css?v=1.0.2" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link id="pagestyle" href="/assets/css/plugins/tom-select.css?v=1.0.9" rel="stylesheet" />
    @stack('css')
    <style>
        .sticky {
            position: fixed;
            top: 0;
            width: 100%;
        }
        th {
            position: sticky;
            top: 0;
            background: white;
        }
        tbody {
            padding-top: 40px;
        }
    </style>
    <style>
        @media (min-width: 1200px) {
            body.g-sidenav-show #sidenav-main {
                width: 250px;
            }
            body.g-sidenav-show .main-content {
                margin-left: 270px !important;
                width: calc(100% - 270px);
                min-height: 100vh;
            }
        }
        @media (max-width: 1199.98px) {
            body.g-sidenav-show .main-content {
                margin-left: 0 !important;
                width: 100%;
            }
        }
    </style>
    <style>
        html.direk-loading body{visibility:hidden}
        html.direk-loading #loader{visibility:visible!important;display:flex!important;opacity:1!important}
    </style>
    <script>document.documentElement.classList.add('direk-loading');</script>
</head>
{{-- <body class="g-sidenav-show bg-gray-100 {{ $class ?? '' }}"> --}}
<body class="g-sidenav-show bg-gray-100 {{ $class ?? '' }} {{ isset($class_theme) && $class_theme == 'dark' ? 'dark-version' : '' }}">
    @guest
        @include('components.notifications.toast')
        @include('components.loader')
        @yield('content')
    @else
        @php
            $status =
                isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : null;
        @endphp
        @if (request()->routeIs('blocked') ||
                request()->routeIs('2fa.challenge.show') ||
                in_array($status, [401, 402, 403, 404, 419, 429, 500, 503]))
            @include('components.notifications.toast')
            @include('components.loader')
            @yield('content')
        @else
            @include('components.headers.image-hero')
            @include('layouts.navbars.auth.sidenav', ['bg' => 'bg-white'])
            <main class="main-content position-relative border-radius-lg">
                @include('components.notifications.toast')
                @include('components.loader')
                @yield('content')
            </main>
        @endif
    @endguest
    <script src="/assets/js/core/jquery.min.js"></script>
    <script src="/assets/js/core/popper.min.js"></script>
    <script src="/assets/js/core/bootstrap.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.1/dist/js/bootstrap-select.min.js"></script> --}}
    {{-- <script src="/assets/js/core/bootstrap.bundle.min.js"></script> --}}
    <script src="/assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="/assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="/assets/js/plugins/fullcalendar.min.js"></script>
    <script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
    {{-- <script src="/assets/js/plugins/jquery.dataTables.min.js"></script> --}}
    <script src="{{ asset('assets') }}/js/plugins/datatables.min.js?v=1.0.2"></script>
    <script src="/assets/js/plugins/choices.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script> --}}
    <script src="/assets/js/plugins/bootstrap-notify.js?v=1.0.1"></script>
    <script src="/assets/js/plugins/quill.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script> --}}
    <script src="/assets/js/plugins/tom-select.complete.min.js"></script>
    <script src="/assets/js/plugins/sweetalert.min.js"></script>
    @include('components.scripts.main')
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
        var choiceSelects = {};
        var tomSelects = {};
        var quills = {};
    </script>
    @yield('scripts')
    @stack('js')
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="/assets/js/argon-dashboard.js?v=1.0.4"></script>
    <script type="text/javascript" id="gwt-pst">
        if (document.getElementById("pst-time")) {
            var span = document.getElementById('pst-time');
            function time() {
                const month = ["January", "February", "March", "April", "May", "June", "July", "August", "September",
                    "October", "November", "December"
                ];
                const week = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                var d = new Date();
                var w = week[d.getDay()];
                var y = d.getFullYear();
                var n = month[d.getMonth()];
                var a = d.getDate();
                var s = d.getSeconds();
                var m = d.getMinutes();
                var h = d.getHours();
                var ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12;
                h = h ? h : 12;
                span.textContent = w + ", " + n + " " + a + ", " + y + " " + ("0" + h).substr(-2) + ":" + ("0" + m).substr(-
                    2) + ":" + ("0" + s).substr(-2) + " " + ampm;
            }
            setInterval(time, 1000);
        }
    </script>
    <script>
        function showDirekLoader(){
            document.documentElement.classList.add('direk-loading');
            $('#loader').stop(true,true).show();
        }
        function hideDirekLoader(){
            $('#loader').stop(true,true).fadeOut(150,function(){
                document.documentElement.classList.remove('direk-loading');
            });
        }
        $(window).on('beforeunload',showDirekLoader);
        $(window).on('pageshow',function(){hideDirekLoader();});
        $(window).on("blur", function() {
        });
        $(window).on("focus", function() {
        })
        $(document).on('focus', '.form-control', function() {
            $(this).closest('.input-group').addClass('focused');
        });
        $(document).on('blur', '.form-control', function() {
            $(this).closest('.input-group').removeClass('focused');
        });
        $(document).ready(function() {
            setTimeout(function() {
                $('#loader').fadeOut('slow');
            }, 500);
            @if (session()->has('succes'))
                showToast("success", "{{ session()->get('succes') }}")
            @endif
            @if (session()->has('error'))
                showToast("warning", "{{ session()->get('error') }}")
            @endif
            @if (session()->has('info'))
                showToast("info", "{{ session()->get('info') }}")
            @endif
            @if ($errors->any())
                showToast("warning", "{{ $errors->first() }}")
            @endif
        });
    </script>
</body>
</html>

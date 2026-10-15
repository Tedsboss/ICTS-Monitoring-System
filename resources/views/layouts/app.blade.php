@php
    $direkLayoutStatus = isset($exception) && method_exists($exception, 'getStatusCode')
        ? $exception->getStatusCode()
        : null;
    $direkHasShell = auth()->check()
        && !request()->routeIs('blocked', '2fa.challenge.show')
        && !in_array($direkLayoutStatus, [401, 402, 403, 404, 419, 429, 500, 503]);
    $direkDark = session('user_settings.class_theme', $class_theme ?? '') === 'dark';
    $direkEmbedded = request()->boolean('_direk_frame')
        || strtolower((string) request()->header('Sec-Fetch-Dest', '')) === 'iframe';
    $direkWorkspaceShell = $direkHasShell && !$direkEmbedded && !request()->boolean('_direk_standalone');
    if ($direkWorkspaceShell) session()->reflash();
    // Only the outer workspace renders the sidebar.
    $direkSidenavHtml = $direkHasShell && !$direkEmbedded
        ? view('layouts.navbars.auth.sidenav', ['bg' => 'bg-white', 'logo' => $logo ?? null])->render()
        : '';
@endphp
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
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/ju/dt-1.11.5/datatables.min.css"/> --}}
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css"> --}}
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"> --}}
    <link id="pagestyle" href="/assets/css/argon-dashboard.css?v=1.0.8" rel="stylesheet" />
    <link href="{{ asset('assets/css/tailwind.css') }}" rel="stylesheet" />
    @if (!request()->routeIs(['financial-plans.plans', 'work-plans.plans']))
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.1/dist/css/bootstrap-select.min.css">
        <link href="{{ asset('assets') }}/css/plugins/datatables.min.css?v=1.0.2" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link id="tom-select-style" href="/assets/css/plugins/tom-select.css?v=1.0.9" rel="stylesheet" />
    @endif
    <style>
        body.direk-workspace{--direk-page:#f4f5fa;--direk-surface:#fff;--direk-border:#e8ebf2;--direk-text:#172033;--direk-muted:#637086;--direk-accent:#635bff;background:var(--direk-page)!important;color:var(--direk-text);font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-size:14px;line-height:1.6;min-height:100vh}
        body.direk-workspace[data-direk-theme="dark"]{--direk-page:#111827;--direk-surface:#192130;--direk-border:#2c3546;--direk-text:#eef2fb;--direk-muted:#aab5c8;--direk-accent:#a19cff;color-scheme:dark}
        body.direk-workspace .direk-main{min-height:100vh;padding-top:16px;padding-bottom:12px;border-radius:0!important;transition:margin-left .2s ease,width .2s ease}
        body.direk-workspace .direk-main>.navbar-main{margin-top:0!important;margin-bottom:6px}
        body.direk-workspace .direk-main>.footer{margin-top:12px}
        body.direk-workspace ::selection{background:rgba(99,91,255,.2)}
        body.direk-workspace .sticky{position:fixed;top:0;z-index:1020}
        @media(min-width:1200px){
            body.direk-workspace:not(.g-sidenav-hidden) #sidenav-main{width:250px}
            body.direk-workspace:not(.g-sidenav-hidden) .direk-main{margin-left:270px!important;width:calc(100% - 270px)}
            body.direk-workspace.g-sidenav-hidden #sidenav-main{width:96px!important}
            body.direk-workspace.g-sidenav-hidden #sidenav-main:hover{width:250px!important}
            body.direk-workspace.g-sidenav-hidden .direk-main{margin-left:116px!important;width:calc(100% - 116px)}
        }
        @media(max-width:1199.98px){body.direk-workspace .direk-main{margin-left:0!important;width:100%;padding-top:12px}}
        @media(max-width:575.98px){body.direk-workspace .direk-main>.navbar-main{margin-left:16px!important;margin-right:16px!important}}
        @media(prefers-reduced-motion:reduce){body.direk-workspace .direk-main{transition:none!important}}
    </style>
    @if (!$direkWorkspaceShell)
        @stack('css')
    @endif
    <style>
        /*Floating feedback must remain above the sticky toolbar and its dropdowns. */
        body.direk-workspace [data-notify="container"],
        body.direk-workspace .toast-container,
        body.direk-workspace #toast-container {
            z-index:1090!important;
        }
        body.direk-workspace .navbar-main {
            z-index:1020!important;
        }
    </style>
    <style>
        html.direk-loading body{visibility:hidden}
        html.direk-loading #loader{visibility:visible!important;display:flex!important;opacity:1!important}
    </style>
    @if (!$direkWorkspaceShell)
        <script>document.documentElement.classList.add('direk-loading');</script>
    @endif
    <style>
        body.direk-frame-shell{overflow:hidden}
        body.direk-frame-shell .direk-frame-main{height:100vh;height:100dvh;padding:0;overflow:hidden}
        #direk-workspace-frame{display:block;width:100%;height:100%;border:0;background:var(--direk-page);opacity:0;transition:opacity .12s}
        #direk-workspace-frame[data-ready="true"]{opacity:1}
        .direk-frame-progress{position:absolute;inset:0 0 auto;height:3px;z-index:1100;background:var(--direk-accent)}
        .direk-frame-progress[hidden],.direk-frame-error[hidden]{display:none}
        .direk-frame-error{position:absolute;top:12px;left:16px;right:16px;z-index:1101;padding:12px;background:var(--direk-surface);color:var(--direk-text);border:1px solid var(--direk-border);border-radius:10px}
        .direk-frame-error button{margin-left:8px}
        html body.direk-workspace[data-direk-embedded="true"] main.direk-main{margin-left:0!important;margin-right:0!important;width:100%!important;max-width:100%!important}
        @media(max-width:1199.98px){body.direk-frame-shell #sidenav-main{transform:translateX(-280px)}body.direk-frame-shell.g-sidenav-pinned #sidenav-main{transform:translateX(0)}}
        @media(prefers-reduced-motion:reduce){#direk-workspace-frame{transition:none}}
    </style>
</head>
{{-- <body class="g-sidenav-show bg-gray-100 {{ $class ?? '' }}"> --}}
<body class="g-sidenav-show bg-gray-100 {{ $class ?? '' }} {{ $direkDark ? 'dark-version' : '' }} {{ $direkHasShell ? 'direk-workspace' : '' }} {{ $direkWorkspaceShell ? 'direk-frame-shell' : '' }}" data-direk-embedded="{{ $direkEmbedded ? 'true' : 'false' }}" data-direk-shell="{{ $direkHasShell ? 'true' : 'false' }}" data-direk-current-route="{{ request()->route()?->getName() }}" data-direk-theme="{{ $direkDark ? 'dark' : 'light' }}">
    @if ($direkWorkspaceShell)
        {!! $direkSidenavHtml !!}
        <main class="main-content direk-main direk-frame-main position-relative" aria-busy="true">
            <div id="direk-frame-progress" class="direk-frame-progress" role="status" aria-label="Loading page"></div>
            <div id="direk-frame-error" class="direk-frame-error" role="alert" hidden>
                <span>The page is taking longer to load.</span>
                <button type="button" id="direk-frame-retry">Retry</button>
                <button type="button" id="direk-frame-open">Open page directly</button>
            </div>
            <iframe id="direk-workspace-frame" name="direk-workspace" title="DIREK workspace content" data-initial-url="{{ request()->fullUrl() }}"></iframe>
            <noscript><a href="{{ request()->fullUrlWithQuery(['_direk_standalone' => 1]) }}">Open the standard workspace</a></noscript>
        </main>
        @include('components.scripts.workspace-navigation')
    @else
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
                {!! $direkSidenavHtml !!}
                <main class="main-content direk-main position-relative border-radius-lg">
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
        @if (!request()->routeIs(['financial-plans.plans', 'work-plans.plans']))
            <script src="/assets/js/plugins/fullcalendar.min.js"></script>
            <script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
            {{-- <script src="/assets/js/plugins/jquery.dataTables.min.js"></script> --}}
            <script src="{{ asset('assets') }}/js/plugins/datatables.min.js?v=1.0.2"></script>
            <script src="/assets/js/plugins/choices.min.js"></script>
            {{-- <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script> --}}
            <script src="/assets/js/plugins/quill.min.js"></script>
            {{-- <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script> --}}
            {{-- <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script> --}}
            <script src="/assets/js/plugins/tom-select.complete.min.js"></script>
        @endif
        <script src="/assets/js/plugins/bootstrap-notify.js?v=1.0.1"></script>
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
        <script src="/assets/js/argon-dashboard.js?v=1.0.4"></script>
        <script>
            function showDirekLoader() {
                document.documentElement.classList.add('direk-loading');
                $('#loader').stop(true, true).show();
            }
            function hideDirekLoader() {
                document.documentElement.classList.remove('direk-loading');
                $('#loader').stop(true, true).fadeOut(150);
            }
            function direkTargetsCurrentPage(element) {
                const target = element.getAttribute('target')
                    || document.querySelector('base[target]')?.getAttribute('target')
                    || '_self';
                return target.toLowerCase() === '_self';
            }
            document.addEventListener('click', function (event) {
                if (event.defaultPrevented || event.button !== 0 || event.ctrlKey
                    || event.metaKey || event.shiftKey || event.altKey) return;
                const link = event.target.closest('a[href]');
                if (!link || link.hasAttribute('download')
                    || link.hasAttribute('data-no-loader')
                    || !direkTargetsCurrentPage(link)) return;
                const rawHref = link.getAttribute('href').trim();
                if (!rawHref || rawHref.startsWith('#')) return;
                const url = new URL(link.href, window.location.href);
                if (!['http:', 'https:'].includes(url.protocol)) return;
                if (/\/(?:pdf|export-pdf)\/?$/i.test(url.pathname)
                    || /\.(?:pdf|xlsx?|csv|zip)$/i.test(url.pathname)) return;
                if (url.origin === window.location.origin
                    && url.pathname === window.location.pathname
                    && url.search === window.location.search) return;
                queueMicrotask(function () {
                    if (!event.defaultPrevented) showDirekLoader();
                });
            });
            document.addEventListener('submit', function (event) {
                const form = event.target;
                const submitter = event.submitter;
                const target = submitter?.getAttribute('formtarget');
                const method = submitter?.getAttribute('formmethod') || form.method;
                if (form.hasAttribute('data-no-loader')
                    || submitter?.hasAttribute('data-no-loader')
                    || (target ? target.toLowerCase() !== '_self' : !direkTargetsCurrentPage(form))
                    || method.toLowerCase() === 'dialog') return;
                queueMicrotask(function () {
                    if (!event.defaultPrevented) showDirekLoader();
                });
            });
            $(window).on('pageshow', hideDirekLoader);
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
                    hideDirekLoader();
                }, 500);
                @if (session()->has('succes'))
                    showToast('success', @json(session()->get('succes')));
                @endif
                @if (session()->has('error'))
                    showToast('warning', @json(session()->get('error')));
                @endif
                @if (session()->has('info'))
                    showToast('info', @json(session()->get('info')));
                @endif
                @if ($errors->any())
                    showToast('warning', @json($errors->first()));
                @endif
            });
        </script>
        @include('components.scripts.workspace-navigation')
    @endif
</body>
</html>

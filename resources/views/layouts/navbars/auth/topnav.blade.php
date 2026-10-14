@php
    $authNavTitle = $title ?? 'Dashboard';
    $authNavCurrent = $subtitle ?? $authNavTitle;
    $authNavLinks = $links ?? [];
    $authNavHasTrail = count($authNavLinks) > 0 || !request()->routeIs('home') || !empty($subtitle);
    $authNavDark = session('user_settings.class_theme', '') === 'dark';
@endphp
<div class="direk-auth-title" data-theme="{{ $authNavDark ? 'dark' : 'light' }}">
    <div class="sidenav-toggler direk-desktop-toggle">
        <button type="button" class="nav-link direk-toolbar-button" aria-label="Toggle sidebar" aria-controls="sidenav-main" title="Toggle sidebar">
            <span class="sidenav-toggler-inner direk-toggle-lines" aria-hidden="true"><i class="sidenav-toggler-line"></i><i class="sidenav-toggler-line"></i><i class="sidenav-toggler-line"></i></span>
        </button>
    </div>
    <div class="direk-title-copy">
        <nav aria-label="Breadcrumb">
            <ol class="direk-breadcrumb">
                <li @if (!$authNavHasTrail) aria-current="page" @endif>
                    @if ($authNavHasTrail)
                        <a href="{{ route('home') }}"><i class="fa fa-home" aria-hidden="true"></i><span>Home</span></a>
                    @else
                        <span class="direk-breadcrumb-current"><i class="fa fa-home" aria-hidden="true"></i><span>Home</span></span>
                    @endif
                </li>
                @foreach ($authNavLinks as $link)
                    <li><i class="fa fa-angle-right direk-breadcrumb-separator" aria-hidden="true"></i>@if (!empty($link['url']))<a href="{{ $link['url'] }}">{{ $link['name'] ?? '' }}</a>@else<span>{{ $link['name'] ?? '' }}</span>@endif</li>
                @endforeach
                @if ($authNavHasTrail)
                    <li aria-current="page"><i class="fa fa-angle-right direk-breadcrumb-separator" aria-hidden="true"></i><span class="direk-breadcrumb-current">{{ $authNavCurrent }}</span></li>
                @endif
            </ol>
        </nav>
        <h1>{{ $authNavTitle }}</h1>
    </div>
</div>
@push('css')
<style>
.navbar-main:has(.direk-auth-title){--toolbar-bg:#fff;--toolbar-border:#e8ebf2;--toolbar-heading:#172033;--toolbar-copy:#637086;--toolbar-subtle:#f6f7fb;--toolbar-accent:#635bff;background:var(--toolbar-bg)!important;border:1px solid var(--toolbar-border)!important;border-radius:16px!important;box-shadow:0 5px 20px rgba(31,35,70,.035)!important;padding:4px 0!important;z-index:1020!important}
.navbar-main:has(.direk-auth-title[data-theme="dark"]){--toolbar-bg:#192130;--toolbar-border:#2c3546;--toolbar-heading:#eef2fb;--toolbar-copy:#aab5c8;--toolbar-subtle:#252d40;--toolbar-accent:#a19cff}
.navbar-main .container-fluid:has(>.direk-auth-title){display:flex!important;align-items:center!important;flex-wrap:wrap!important;gap:12px!important;padding:10px 16px!important}
.direk-auth-title{display:flex;align-items:center;gap:14px;flex:1 1 240px;min-width:0;color:var(--toolbar-heading,#172033);padding:0;background:transparent}
.direk-auth-title .direk-title-copy{flex:1;min-width:0}
.direk-auth-title h1{margin:0!important;margin-top:3px!important;font-size:19px!important;line-height:1.4!important;letter-spacing:-.5px!important;font-weight:650!important;color:var(--toolbar-heading,#172033)!important;overflow-wrap:anywhere}
.direk-auth-title .direk-breadcrumb{display:flex;flex-wrap:wrap;align-items:center;gap:5px 8px;list-style:none;margin:0!important;padding:0!important;font-size:10px;line-height:1.6;color:var(--toolbar-copy,#637086)}
.direk-auth-title .direk-breadcrumb li,.direk-auth-title .direk-breadcrumb a,.direk-auth-title .direk-breadcrumb-current{display:inline-flex;align-items:center;gap:7px;min-width:0;overflow-wrap:anywhere}
.direk-auth-title .direk-breadcrumb a{color:var(--toolbar-copy,#637086);text-decoration:none;border-radius:3px;transition:color .15s}
.direk-auth-title .direk-breadcrumb a:hover{color:var(--toolbar-accent,#635bff)}
.direk-auth-title .direk-breadcrumb-current{color:var(--toolbar-accent,#635bff);font-weight:600}
.direk-auth-title .direk-breadcrumb-separator{font-size:10px;opacity:.6}
.direk-auth-title .direk-desktop-toggle{display:none!important;align-items:center;justify-content:center;flex-shrink:0;width:42px;height:42px;padding:0!important}
.direk-auth-title .direk-toolbar-button{display:grid!important;place-items:center!important;width:42px!important;height:42px!important;margin:0!important;padding:0!important;border:1px solid var(--toolbar-border,#e8ebf2)!important;border-radius:11px!important;background:var(--toolbar-subtle,#f6f7fb)!important;box-shadow:none!important;color:var(--toolbar-copy,#637086)!important;transition:background .15s!important}
.direk-auth-title .direk-toolbar-button:hover{background:var(--toolbar-border,#e8ebf2)!important}
.direk-auth-title .direk-toggle-lines{display:flex!important;flex-direction:column!important;justify-content:space-between!important;width:17px!important;height:12px!important}
.direk-auth-title .direk-toggle-lines .sidenav-toggler-line{display:block!important;width:17px!important;height:2px!important;margin:0!important;background:var(--toolbar-copy,#637086)!important;border-radius:2px!important;transform:none!important;position:static!important}
.direk-auth-title .direk-toolbar-button:focus-visible,.direk-auth-title a:focus-visible{outline:2px solid var(--toolbar-accent,#635bff)!important;outline-offset:3px}
@media(min-width:1200px){.direk-auth-title .direk-desktop-toggle{display:flex!important}}
@media(max-width:760px){.direk-auth-title{flex-basis:160px}.direk-auth-title h1{font-size:17px!important}.navbar-main .container-fluid:has(>.direk-auth-title){gap:8px!important;padding:10px 12px!important}}
@media(max-width:480px){.direk-auth-title{flex-basis:100%}.direk-auth-title h1{font-size:16px!important}}
@media(prefers-reduced-motion:reduce){.direk-auth-title a,.direk-auth-title .direk-toolbar-button{transition:none!important}}

/* Violet depth for the selected DIREK homepage/sidebar combination. */
.navbar-main:has(.direk-auth-title){--toolbar-bg:#f8f6fd;--toolbar-border:#ddd7ec;--toolbar-heading:#302842;--toolbar-copy:#706482;--toolbar-subtle:#ece7f5;--toolbar-accent:#7160ca;--toolbar-edge:#d9d0e8;--toolbar-highlight:rgba(255,255,255,.9);background:linear-gradient(145deg,#ffffff,#f4f0fa)!important;box-shadow:inset 0 1px 0 var(--toolbar-highlight),0 3px 0 var(--toolbar-edge),0 12px 25px rgba(54,39,89,.08)!important;overflow:visible!important;transform:none!important;z-index:1020!important}
.navbar-main:has(.direk-auth-title[data-theme="dark"]){--toolbar-bg:#26213b;--toolbar-border:#514465;--toolbar-heading:#f4f0ff;--toolbar-copy:#c1b6d7;--toolbar-subtle:#39314f;--toolbar-accent:#c2acff;--toolbar-edge:#161126;--toolbar-highlight:rgba(239,221,255,.1);background:linear-gradient(145deg,#302a46,#211d34)!important;box-shadow:inset 0 1px 0 var(--toolbar-highlight),0 3px 0 var(--toolbar-edge),0 12px 25px rgba(0,0,0,.16)!important}
.direk-auth-title .direk-toolbar-button{background:linear-gradient(145deg,var(--toolbar-bg),var(--toolbar-subtle))!important;color:var(--toolbar-accent)!important;box-shadow:inset 0 1px 0 var(--toolbar-highlight),0 3px 0 var(--toolbar-edge)!important;transition:box-shadow .18s ease,background .18s ease!important}
.direk-auth-title .direk-toolbar-button:hover{background:var(--toolbar-subtle)!important;box-shadow:inset 0 1px 0 var(--toolbar-highlight),0 3px 0 var(--toolbar-edge),0 7px 15px rgba(106,80,168,.12)!important}
.direk-auth-title .direk-toggle-lines .sidenav-toggler-line{background:var(--toolbar-accent)!important}
.direk-auth-title h1{letter-spacing:-.45px!important;font-weight:700!important}
.direk-auth-title .direk-breadcrumb{font-size:11px}
.direk-auth-title .direk-breadcrumb-current{color:var(--toolbar-accent)}
@media(prefers-reduced-motion:reduce){.direk-auth-title .direk-toolbar-button{transition:none!important}}

</style>
@endpush

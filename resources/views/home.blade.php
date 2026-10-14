@extends('layouts.app')
@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Workspace'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-home" class="hc-workspace" data-theme="{{ $isDark ? 'dark' : 'light' }}">
    <header class="hc-welcome">
        <div>
            <span class="hc-eyebrow">DIREK / YOUR DAILY WORKSPACE</span>
            <h1>{{ $greeting }}, {{ $firstName }}.</h1>
            <p>Where would you like to begin?</p>
        </div>
        <div class="hc-date">
            <span>{{ $homeNow->format('l, F j, Y') }}</span>
            <time id="dh-live-time" datetime="{{ $homeNow->format('H:i') }}">{{ $homeNow->format('g:i A') }}</time>
        </div>
    </header>
    <section class="sc-hero" data-light aria-labelledby="sc-hero-title">
        <div class="sc-copy">
            <span class="sc-badge"><i aria-hidden="true"></i> YOUR CONNECTED WORKSPACE</span>
            <h2 id="sc-hero-title">Bring your <br> plans <em> to life. </em></h2>
            <p>Shape the budget. Connect the activities. See the path from planning to delivery in one workspace.</p>
            <div class="hc-start-actions">
                @if ($primaryModule)
                    <a class="hc-button hc-button-light" href="{{ route($primaryModule['route']) }}">Continue planning <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                @endif
                <button class="hc-button hc-button-outline" id="dh-find-shortcut" type="button">Explore workspaces <span aria-hidden="true"> / </span></button>
            </div>
            <div class="sc-summary">
                <span><strong> {{ count($modules) }} </strong> available workspaces</span>
                <span><strong> {{ count($steps) }} </strong> planning stages</span>
            </div>
            <button id="dh-motion-toggle" type="button" class="hc-motion" aria-pressed="true" hidden><i class="fa fa-magic" aria-hidden="true"></i> <span> Motion on </span></button>
        </div>
        <div class="sc-scene" aria-hidden="true">
            <div class="sc-glow"></div>
            <div class="sc-ring"></div>
            <div class="sc-ring sc-ring-two"></div>
            <div class="sc-stage">
                <span class="sc-plane"></span>
                <span class="sc-plane"></span>
                <span class="sc-plane"></span>
            </div>
            <div class="sc-cube">
                <span class="sc-face sc-front"><i class="fa fa-cubes"></i></span>
                <span class="sc-face sc-back"></span>
                <span class="sc-face sc-right"></span>
                <span class="sc-face sc-left"></span>
                <span class="sc-face sc-top"></span>
                <span class="sc-face sc-bottom"></span>
            </div>
            <span class="sc-float sc-float-one"><i class="fa fa-money"></i> Financial Plan</span>
            <span class="sc-float sc-float-two"><i class="fa fa-tasks"></i> Work Plan</span>
            <span class="sc-float sc-float-three"><i class="fa fa-calendar"></i> Monthly Disbursement</span>
            <span class="sc-scene-caption">ONE CONNECTED PLANNING FLOW</span>
        </div>
    </section>
    @if ($homeannouncement && $homeannouncement->start_date && $homeannouncement->end_date && \Carbon\Carbon::parse($homeannouncement->start_date)->lte($homeNow) && \Carbon\Carbon::parse($homeannouncement->end_date)->gte($homeNow))
        <div class="hc-announcement">
            @include('components.home-announcement', ['homeannouncement' => $homeannouncement])
        </div>
    @endif
    @if ($birthdate && $birthdate->month === $homeNow->month && $birthdate->day === $homeNow->day)
        <div class="hc-announcement">
            @include('others.hbd')
        </div>
    @endif
    <section id="hc-tools" class="hc-tools" aria-labelledby="hc-tools-title">
        <div class="hc-section-title">
            <div>
                <span class="hc-eyebrow">OPEN A WORKSPACE</span>
                <h2 id="hc-tools-title">Choose your next workspace.</h2>
            </div>
            <span id="dh-result-count" class="hc-count">{{ count($modules) }} {{ count($modules) === 1 ? 'workspace' : 'workspaces' }}</span>
        </div>
        <div class="hc-controls">
            <div class="hc-filters" aria-label="Filter workspaces">
                <button type="button" class="is-active" data-filter="all" aria-pressed="true">All workspaces</button>
                @foreach (collect($modules)->pluck('category')->unique() as $category)
                    <button type="button" data-filter="{{ strtolower($category) }}" aria-pressed="false">{{ $category }}</button>
                @endforeach
            </div>
            <label class="hc-search" for="dh-search-input"><i class="fa fa-search" aria-hidden="true"></i> <span class="hc-sr-only"> Search workspaces </span> <input id="dh-search-input" type="search" placeholder="Search a workspace…" autocomplete="off"> <button id="dh-search-clear" type="button" aria-label="Clear search" hidden> <i class="fa fa-times" aria-hidden="true"></i> </button></label>
        </div>
        <div class="hc-grid">
            @foreach ($modules as $module)
                <a class="hc-module hc-tone-{{ $module['tone'] }}" href="{{ route($module['route']) }}" data-depth data-module data-category="{{ strtolower($module['category']) }}" data-search="{{ strtolower($module['title'] . ' ' . $module['category'] . ' ' . $module['description']) }}">
                    <div class="hc-module-heading">
                        <span class="hc-module-category">
                            {{ $module['category'] }}
                        </span>
                        <span class="hc-module-index">
                            {{ $module['index'] }}
                        </span>
                    </div>
                    <div class="hc-module-art" aria-hidden="true">
                        <span class="hc-module-orbit"></span>
                        <span class="hc-module-floor"></span>
                        <span class="hc-module-icon">
                            <i class="fa {{ $module['icon'] }}"></i>
                        </span>
                    </div>
                    <div class="hc-module-copy">
                        <h3>
                            {{ $module['title'] }}
                        </h3>
                        <p>
                            {{ $module['description'] }}
                        </p>
                    </div>
                    <div class="hc-module-bottom">
                        <span>
                            Open workspace
                        </span>
                        <span class="hc-open-arrow">
                            <i class="fa fa-arrow-up" aria-hidden="true"></i>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
        <div id="dh-empty" class="hc-empty" @if (count($modules)) hidden @endif>
            <i class="fa fa-search" aria-hidden="true"></i>
            <h3>{{ count($modules) ? 'No workspace found' : 'No workspaces available' }}</h3>
            <p>{{ count($modules) ? 'Try another keyword or reset your filters.' : 'Use the sidebar to browse other modules, or ask your administrator about access.' }}</p>
            @if (count($modules))
                <button id="dh-reset" class="hc-button" type="button">Reset search</button>
            @endif
        </div>
        <p id="dh-search-status" class="hc-sr-only" role="status" aria-live="polite"></p>
    </section>
    <section id="hc-journey" class="hc-journey" aria-labelledby="hc-journey-title">
        <div class="hc-section-title">
            <div>
                <span class="hc-eyebrow">YOUR PLANNING JOURNEY</span>
                <h2 id="hc-journey-title">From foundations to delivery.</h2>
            </div>
            <span class="hc-route-badge"><i class="fa fa-link" aria-hidden="true"></i> {{ count($steps) }} connected stages</span>
        </div>
        <ol>
            @foreach ($steps as $step)
                <li class="hc-route-step">
                    <div class="hc-route-top">
                        <span class="hc-route-symbol" aria-hidden="true">
                            <i class="fa {{ ['fa-sliders', 'fa-cubes', 'fa-pencil-square-o', 'fa-line-chart'][$loop->index % 4] }}">
                            </i>
                        </span>
                        <span class="hc-route-number">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                    <strong>
                        {{ $step['label'] }}
                    </strong>
                    <p>
                        {{ $step['detail'] }}
                    </p>
                </li>
            @endforeach
        </ol>
        <div class="hc-route-insight">
            <i class="fa fa-lightbulb-o" aria-hidden="true"></i>
            <p><strong> Connected by design. </strong> Financial Plan schedules feed monthly cash targets into the MDP.</p>
        </div>
    </section>
    <footer class="hc-footer">
        <strong>D.I.R.E.K.</strong>
        <span>DEPDev Integrated Reporting and Executive Kiosk</span>
    </footer>
</div>
@include('layouts.footers.auth.footer')
@endsection
@push('css')
<style>
#direk-home.hc-workspace{--hc-bg:#f4f4ef;--hc-surface:#fff;--hc-text:#233632;--hc-copy:#687871;--hc-border:#dee4dc;--hc-accent:#237c65;--hc-soft:#eaf1e9;--hc-edge:#d7dfd3;padding:26px 32px 36px;color:var(--hc-text);background:var(--hc-bg);border-radius:24px;margin:18px 24px 30px;font-family:inherit}
#direk-home.hc-workspace[data-theme="dark"]{--hc-bg:#151f23;--hc-surface:#1e2c30;--hc-text:#eaf4ed;--hc-copy:#a2b6b0;--hc-border:#34484b;--hc-accent:#87d6b3;--hc-soft:#273a38;--hc-edge:#0c1619}
#direk-home.hc-workspace *{box-sizing:border-box}#direk-home.hc-workspace [hidden]{display:none!important}#direk-home.hc-workspace h1,#direk-home.hc-workspace h2,#direk-home.hc-workspace h3,#direk-home.hc-workspace p{margin:0;color:inherit}#direk-home.hc-workspace a{color:inherit;text-decoration:none}#direk-home.hc-workspace button,#direk-home.hc-workspace input{font:inherit}#direk-home.hc-workspace button{cursor:pointer}#direk-home.hc-workspace a:focus-visible,#direk-home.hc-workspace button:focus-visible,#direk-home.hc-workspace input:focus-visible{outline:2px solid var(--hc-accent);outline-offset:4px}
#direk-home .hc-eyebrow{font-size:9px;font-weight:700;letter-spacing:.14em;color:var(--hc-copy)}#direk-home .hc-welcome{display:flex;justify-content:space-between;align-items:center;gap:20px;margin:0 0 26px}#direk-home .hc-welcome h1{font-size:clamp(25px,2.4vw,34px);letter-spacing:-1px;font-weight:650;margin:8px 0}#direk-home .hc-welcome p{font-size:13px;color:var(--hc-copy)}#direk-home .hc-date{display:flex;flex-direction:column;align-items:flex-end;gap:6px;font-size:11px;color:var(--hc-copy)}#direk-home .hc-date time{font-size:16px;font-weight:600;color:var(--hc-text);font-variant-numeric:tabular-nums}
#direk-home .hc-overview{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(0,1fr) minmax(0,1fr);gap:17px}#direk-home .hc-start,#direk-home .hc-stat,#direk-home .hc-module{transform:perspective(1100px) rotateX(var(--depth-x,0deg)) rotateY(var(--depth-y,0deg));transition:transform .18s ease,box-shadow .18s ease}
#direk-home .hc-start{position:relative;display:flex;flex-direction:column;min-height:300px;padding:26px;border-radius:20px;background:#213d35;color:#fff;box-shadow:0 5px 0 #172d26,0 16px 28px rgba(19,46,35,.14);overflow:hidden}#direk-home .hc-start::after{content:"";position:absolute;width:200px;height:200px;right:-95px;top:40px;border:28px solid rgba(167,208,175,.075);border-radius:50%;pointer-events:none}#direk-home .hc-start-heading{display:flex;justify-content:space-between;align-items:center;gap:12px}#direk-home .hc-start .hc-eyebrow{color:#b5d8c5}#direk-home .hc-start-mark{font-size:23px;color:#b4d9a9}#direk-home .hc-start h2{font-size:clamp(27px,2.7vw,39px);font-weight:600;letter-spacing:-1.2px;line-height:1.12;margin:15px 0;color:#fff}#direk-home .hc-start>p{color:#c2d4ca;font-size:12px;line-height:1.7;max-width:365px}#direk-home .hc-start-actions{display:flex;gap:9px;flex-wrap:wrap;margin:20px 0}#direk-home .hc-button{display:inline-flex;min-height:40px;align-items:center;justify-content:center;gap:10px;padding:10px 14px;border:1px solid var(--hc-border);border-radius:9px;background:var(--hc-surface);color:var(--hc-text);font-size:11px;font-weight:600}#direk-home .hc-button-light{background:#dceecf;border-color:#dceecf;color:#233a2a!important}#direk-home .hc-button-outline{border-color:#526e60;background:transparent;color:#e7f2eb}#direk-home .hc-start-bottom{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:auto;padding-top:13px;border-top:1px solid rgba(255,255,255,.15);font-size:9px;color:#b5cec0}#direk-home .hc-motion{border:0;background:transparent;color:#c9ddce;display:inline-flex;align-items:center;gap:6px;font-size:9px;padding:0;min-height:26px}#direk-home .hc-motion:disabled{cursor:default;opacity:.65}
#direk-home .hc-stat{display:flex;flex-direction:column;border:1px solid var(--hc-border);background:var(--hc-surface);padding:25px 22px;border-radius:20px;box-shadow:0 4px 0 var(--hc-edge)}#direk-home .hc-stat-icon{font-size:20px;color:var(--hc-accent);margin-bottom:24px}#direk-home .hc-stat>strong{font-size:58px;line-height:1;letter-spacing:-3px;font-weight:600;margin:14px 0;color:var(--hc-text)}#direk-home .hc-stat h3{font-size:13px;font-weight:650}#direk-home .hc-stat p{font-size:11px;line-height:1.7;color:var(--hc-copy);margin-top:9px}#direk-home .hc-stat>a{display:flex;align-items:center;gap:8px;font-size:10px;font-weight:600;color:var(--hc-accent);margin-top:auto;padding-top:19px}#direk-home .hc-stat-path{background:var(--hc-soft)}
#direk-home .hc-announcement{margin-top:25px}#direk-home .hc-tools{margin-top:36px;scroll-margin-top:100px}#direk-home .hc-section-title{display:flex;align-items:center;justify-content:space-between;gap:20px}#direk-home .hc-section-title h2{font-size:23px;font-weight:650;letter-spacing:-.65px;margin-top:7px}#direk-home .hc-count{font-size:10px;color:var(--hc-copy);white-space:nowrap}#direk-home .hc-controls{display:flex;justify-content:space-between;align-items:center;gap:18px;margin:23px 0 18px}#direk-home .hc-filters{display:flex;gap:5px;flex-wrap:wrap}#direk-home .hc-filters button{padding:9px 12px;background:transparent;border:1px solid transparent;border-radius:8px;color:var(--hc-copy);font-size:11px;min-height:36px}#direk-home .hc-filters button.is-active{background:var(--hc-surface);border-color:var(--hc-border);color:var(--hc-text);box-shadow:0 2px 0 var(--hc-edge)}#direk-home .hc-search{display:flex;align-items:center;gap:10px;border:1px solid var(--hc-border);background:var(--hc-surface);border-radius:9px;padding:0 12px;width:260px;min-height:40px;color:var(--hc-copy)}#direk-home .hc-search input{width:100%;min-width:0;border:0;padding:10px 0;background:transparent;font-size:11px;color:var(--hc-text);outline:none}#direk-home .hc-search input::placeholder{color:var(--hc-copy)}#direk-home .hc-search button{border:0;background:transparent;color:var(--hc-copy);padding:4px}#direk-home .hc-search:focus-within{border-color:var(--hc-accent)}
#direk-home .hc-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}#direk-home .hc-module{--module-color:var(--hc-accent);display:flex;flex-direction:column;min-height:220px;padding:22px;border:1px solid var(--hc-border);border-radius:17px;background:var(--hc-surface);box-shadow:0 3px 0 var(--hc-edge)}#direk-home .hc-module:hover{border-color:var(--hc-accent);box-shadow:0 5px 0 var(--hc-edge),0 14px 23px rgba(19,40,31,.09)}#direk-home .hc-module-top{display:flex;align-items:center;gap:10px}#direk-home .hc-module-icon{font-size:20px;color:var(--module-color)}#direk-home .hc-module-category{font-size:9px;color:var(--hc-copy)}#direk-home .hc-module-index{margin-left:auto;font-size:9px;color:var(--hc-copy)}#direk-home .hc-module h3{font-size:18px;line-height:1.3;letter-spacing:-.4px;font-weight:650;margin-top:20px}#direk-home .hc-module p{font-size:11px;line-height:1.7;color:var(--hc-copy);margin:9px 0 19px}#direk-home .hc-module-bottom{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:auto;font-size:10px;font-weight:600;color:var(--hc-accent)}#direk-home .hc-open-arrow{display:grid;place-items:center;width:29px;height:29px;border-radius:50%;border:1px solid var(--hc-border);transform:rotate(45deg)}#direk-home .hc-tone-amber{--module-color:#ba812c}#direk-home .hc-tone-violet{--module-color:#997bc1}
#direk-home .hc-empty{text-align:center;background:var(--hc-surface);border:1px dashed var(--hc-border);border-radius:17px;padding:40px 20px}#direk-home .hc-empty>i{color:var(--hc-accent);font-size:25px}#direk-home .hc-empty h3{font-size:18px;margin-top:14px}#direk-home .hc-empty p{color:var(--hc-copy);font-size:12px;margin:10px 0 15px}
#direk-home .hc-journey{padding:25px;margin-top:34px;background:var(--hc-soft);border:1px solid var(--hc-border);border-radius:18px;scroll-margin-top:100px}#direk-home .hc-journey h2{font-size:20px}#direk-home .hc-path-note{max-width:265px;font-size:10px;line-height:1.6;color:var(--hc-copy)}#direk-home .hc-journey ol{display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:20px;list-style:none;padding:0;margin:25px 0 0}#direk-home .hc-journey li{border-top:1px solid var(--hc-border);padding-top:15px}#direk-home .hc-journey li>span{display:block;font-size:11px;color:var(--hc-accent);font-weight:700;margin-bottom:10px}#direk-home .hc-journey strong{font-size:12px;display:block}#direk-home .hc-journey p{font-size:10px;line-height:1.6;color:var(--hc-copy);margin-top:5px}#direk-home .hc-footer{display:flex;align-items:center;gap:14px;padding-top:25px;font-size:9px;color:var(--hc-copy)}#direk-home .hc-footer strong{font-size:10px;letter-spacing:1px;color:var(--hc-text)}
#direk-home .hc-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}#direk-home[data-motion="off"] [data-depth]{transform:none!important}
@media(max-width:1150px){#direk-home .hc-overview{grid-template-columns:2fr 1fr}#direk-home .hc-start{grid-row:span 2}#direk-home .hc-stat{padding:18px;min-height:175px}#direk-home .hc-stat-icon{margin-bottom:12px}#direk-home .hc-stat>strong{font-size:42px;margin:10px 0}#direk-home .hc-stat p{display:none}#direk-home .hc-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:760px){#direk-home.hc-workspace{margin:12px 15px;padding:22px 18px}#direk-home .hc-welcome{align-items:flex-start;flex-direction:column;gap:12px}#direk-home .hc-date{align-items:flex-start;flex-direction:row;gap:12px}#direk-home .hc-date time{font-size:11px}#direk-home .hc-overview{grid-template-columns:repeat(2,minmax(0,1fr))}#direk-home .hc-start{grid-column:1/-1;grid-row:auto}#direk-home .hc-stat{min-height:185px}#direk-home .hc-controls{flex-direction:column;align-items:stretch;gap:14px}#direk-home .hc-search{width:100%}#direk-home .hc-section-title{align-items:flex-start}#direk-home .hc-journey .hc-section-title{flex-direction:column}#direk-home .hc-path-note{max-width:none}}
@media(max-width:480px){#direk-home.hc-workspace{margin:10px;padding:18px 14px}#direk-home .hc-grid{grid-template-columns:1fr}#direk-home .hc-start{padding:22px}#direk-home .hc-start h2{font-size:34px}#direk-home .hc-stat h3{font-size:12px}#direk-home .hc-stat>a{font-size:9px}#direk-home .hc-start-bottom{flex-wrap:wrap}#direk-home .hc-footer{align-items:flex-start;flex-direction:column;gap:8px}#direk-home .hc-section-title h2{font-size:21px}#direk-home .hc-count{padding-top:4px}}
@media(prefers-reduced-motion:reduce){#direk-home.hc-workspace *{transition:none!important;animation:none!important}#direk-home [data-depth]{transform:none!important}}
/* Immersive depth layer: scoped to the homepage. */
#direk-home.hc-workspace{--hc-bg:#edf0fb;--hc-surface:#fff;--hc-text:#232943;--hc-copy:#6c7493;--hc-border:#dce1f2;--hc-accent:#7563ed;--hc-soft:#e9eafa;--hc-edge:#d0d7ed;position:relative;isolation:isolate;background:radial-gradient(ellipse at 5% 0%,rgba(181,140,255,.19),transparent 35%),radial-gradient(ellipse at 95% 35%,rgba(84,201,240,.12),transparent 38%),#f0f2fb;border:1px solid var(--hc-border);border-radius:28px;padding:30px}
#direk-home.hc-workspace[data-theme="dark"]{--hc-bg:#101326;--hc-surface:#1c2038;--hc-text:#f1f1ff;--hc-copy:#a5adcc;--hc-border:#343854;--hc-accent:#b7a5ff;--hc-soft:#232741;--hc-edge:#111528;background:radial-gradient(ellipse at 5% 0%,rgba(130,69,216,.19),transparent 36%),radial-gradient(ellipse at 95% 35%,rgba(52,147,206,.11),transparent 40%),#13162a}
#direk-home .hc-welcome{margin-bottom:25px}#direk-home .hc-welcome h1{font-size:29px}#direk-home .hc-date{padding:11px 15px;border:1px solid var(--hc-border);border-radius:13px;background:var(--hc-surface)}
#direk-home .sc-hero{position:relative;display:grid;grid-template-columns:minmax(0,1fr) minmax(330px,.9fr);min-height:450px;border:1px solid #6750a3;border-radius:25px;background:radial-gradient(ellipse at 82% 46%,rgba(132,75,255,.28),transparent 42%),linear-gradient(120deg,#20213c,#29204c 60%,#29244e);overflow:hidden;box-shadow:inset 0 1px 0 rgba(224,206,255,.22),0 6px 0 #282141,0 24px 45px rgba(37,24,73,.2);transform:perspective(1400px) rotateX(var(--depth-x,0deg)) rotateY(var(--depth-y,0deg));transition:transform .2s ease;color:#fff}
#direk-home .sc-hero::before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(189,167,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(189,167,255,.035) 1px,transparent 1px);background-size:35px 35px;pointer-events:none;mask-image:linear-gradient(90deg,transparent,#000)}
#direk-home .sc-copy{position:relative;z-index:2;padding:45px 0 35px 38px;align-self:center}#direk-home .sc-badge{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(220,194,255,.25);background:rgba(182,140,255,.09);border-radius:99px;padding:7px 11px;color:#e3d5ff;font-size:9px;letter-spacing:1.2px;font-weight:700}#direk-home .sc-badge i{width:6px;height:6px;background:#6de3d5;border-radius:50%;box-shadow:0 0 12px #6de3d5}
#direk-home .sc-copy h2{font-size:clamp(37px,4vw,59px);line-height:1.06;letter-spacing:-2.2px;font-weight:700;color:#fff;margin:22px 0 18px}#direk-home .sc-copy h2 em{font-style:normal;background:linear-gradient(100deg,#c6a4ff,#9edcff);color:#c6a4ff;background-clip:text;-webkit-background-clip:text;-webkit-text-fill-color:transparent}#direk-home .sc-copy>p{font-size:13px;line-height:1.85;color:#bfc1de;max-width:360px}#direk-home .sc-copy .hc-start-actions{margin:25px 0 22px}#direk-home .sc-copy .hc-button-light{background:#e2d4ff;border-color:#e2d4ff;color:#38225e!important;box-shadow:0 4px 0 #aa95d3,0 9px 20px rgba(170,114,255,.17)}#direk-home .sc-copy .hc-button-outline{color:#eee7ff;border-color:#64517e;background:rgba(255,255,255,.035)}#direk-home .sc-summary{display:flex;flex-wrap:wrap;gap:17px;margin-top:23px}#direk-home .sc-summary span{font-size:10px;color:#b2b6d5}#direk-home .sc-summary strong{font-size:15px;color:#e9e3ff;margin-right:5px}#direk-home .sc-copy .hc-motion{margin-top:16px;color:#c2b5e4}
#direk-home .sc-scene{position:relative;min-height:450px;perspective:800px;pointer-events:none;align-self:center;overflow:hidden}#direk-home .sc-glow{position:absolute;inset:12% 2%;border-radius:50%;background:radial-gradient(circle,rgba(143,89,255,.23),transparent 63%);filter:blur(12px)}
#direk-home .sc-stage{position:absolute;top:50%;left:50%;width:230px;height:230px;margin:-115px;transform-style:preserve-3d;transform:rotateX(61deg) rotateZ(-34deg);animation:sc-stage-drift 12s ease-in-out infinite alternate}
#direk-home .sc-plane{position:absolute;inset:0;border:1px solid rgba(189,156,255,.7);border-radius:24px;background:linear-gradient(130deg,rgba(192,159,255,.18),rgba(113,65,201,.13));box-shadow:0 0 35px rgba(133,72,255,.18),inset 0 0 20px rgba(173,129,255,.12);transform:translateZ(-55px)}#direk-home .sc-plane:nth-child(2){inset:18px;transform:translateZ(-25px);border-color:rgba(132,204,255,.7);background:rgba(106,189,250,.05)}#direk-home .sc-plane:nth-child(3){inset:36px;transform:translateZ(5px);background:rgba(147,91,240,.22);border-color:#b792ff}
#direk-home .sc-cube{position:absolute;left:50%;top:50%;width:92px;height:92px;margin:-46px;transform-style:preserve-3d;transform:rotateX(-20deg) rotateY(-35deg);animation:sc-core-float 6s ease-in-out infinite alternate}#direk-home .sc-face{position:absolute;width:92px;height:92px;border:1px solid rgba(226,193,255,.8);border-radius:10px;background:linear-gradient(145deg,rgba(186,126,255,.55),rgba(101,68,206,.58));box-shadow:inset 0 0 25px rgba(193,145,255,.22);backface-visibility:hidden;-webkit-backface-visibility:hidden}#direk-home .sc-front{transform:translateZ(46px);display:grid;place-items:center;color:#eee0ff;font-size:31px;text-shadow:0 0 20px #bca4ff}#direk-home .sc-back{transform:rotateY(180deg) translateZ(46px)}#direk-home .sc-right{transform:rotateY(90deg) translateZ(46px)}#direk-home .sc-left{transform:rotateY(-90deg) translateZ(46px)}#direk-home .sc-top{transform:rotateX(90deg) translateZ(46px);background:linear-gradient(145deg,rgba(235,190,255,.6),rgba(113,149,255,.5))}#direk-home .sc-bottom{transform:rotateX(-90deg) translateZ(46px)}
#direk-home .sc-ring{position:absolute;left:50%;top:50%;width:310px;height:310px;margin:-155px;border:1px solid rgba(183,146,255,.35);border-radius:50%;transform:rotateX(58deg) rotateZ(-25deg);animation:sc-ring-spin 22s linear infinite}#direk-home .sc-ring::before{content:"";position:absolute;top:36px;left:30px;width:9px;height:9px;background:#acdfff;border-radius:50%;box-shadow:0 0 20px #70c9fa}#direk-home .sc-ring-two{width:360px;height:360px;margin:-180px;border-style:dashed;opacity:.5;animation-direction:reverse;animation-duration:33s}
#direk-home .sc-float{position:absolute;z-index:3;display:flex;align-items:center;gap:9px;padding:12px 14px;border:1px solid #675588;border-radius:12px;background:linear-gradient(140deg,rgba(69,54,103,.96),rgba(42,39,73,.96));box-shadow:0 5px 0 rgba(20,16,38,.5),0 15px 23px rgba(9,6,21,.2);font-size:11px;font-weight:600;color:#e9e0fc;animation:sc-panel-float 6s ease-in-out infinite alternate}#direk-home .sc-float i{color:#c1adff;font-size:16px}#direk-home .sc-float-one{left:6%;top:19%;animation-delay:-2s}#direk-home .sc-float-two{right:4%;top:51%;animation-delay:-4s}#direk-home .sc-float-three{left:12%;bottom:15%;animation-delay:-1s}#direk-home .sc-scene-caption{position:absolute;bottom:30px;left:0;right:0;text-align:center;color:#9e96bf;letter-spacing:2px;font-size:8px}
#direk-home .hc-tools{margin-top:40px}#direk-home .hc-section-title h2{font-size:25px}#direk-home .hc-count{border:1px solid var(--hc-border);background:var(--hc-surface);border-radius:8px;padding:7px 10px}#direk-home .hc-filters button.is-active{background:var(--hc-accent);color:#fff;border-color:var(--hc-accent)}#direk-home .hc-module{position:relative;border-radius:20px;background:linear-gradient(140deg,var(--hc-surface),var(--hc-soft));border:1px solid var(--hc-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.12),0 4px 0 var(--hc-edge),0 12px 24px rgba(36,28,67,.06);overflow:hidden}#direk-home .hc-module::before{content:"";position:absolute;left:0;right:0;top:0;height:2px;background:linear-gradient(90deg,var(--module-color),transparent);opacity:.55}#direk-home .hc-module:hover{box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 7px 0 var(--hc-edge),0 18px 35px rgba(75,51,137,.16)}#direk-home .hc-module-icon{font-size:22px}#direk-home .hc-module h3{font-size:19px}#direk-home .hc-open-arrow{background:var(--hc-soft);border-color:var(--hc-border)}#direk-home .hc-module-category{font-size:8px;letter-spacing:.04em}#direk-home .hc-tone-emerald{--module-color:#329b8b}#direk-home[data-theme="dark"] .hc-tone-emerald{--module-color:#68dcc6}#direk-home[data-theme="dark"] .hc-tone-amber{--module-color:#f2cc81}#direk-home[data-theme="dark"] .hc-tone-violet{--module-color:#c7a2ff}
#direk-home .hc-journey{background:linear-gradient(135deg,var(--hc-surface),var(--hc-soft));box-shadow:0 4px 0 var(--hc-edge);border-radius:20px}#direk-home .hc-journey li>span{display:grid;place-items:center;width:30px;height:30px;border:1px solid var(--hc-border);border-radius:9px;background:var(--hc-surface);box-shadow:0 3px 0 var(--hc-edge)}
@keyframes sc-panel-float{from{transform:translateY(-4px)}to{transform:translateY(5px)}}@keyframes sc-core-float{from{transform:translateY(-8px) rotateX(-20deg) rotateY(-35deg)}to{transform:translateY(5px) rotateX(-15deg) rotateY(-28deg)}}@keyframes sc-stage-drift{from{transform:rotateX(61deg) rotateZ(-34deg)}to{transform:rotateX(65deg) rotateZ(-28deg)}}@keyframes sc-ring-spin{from{transform:rotateX(58deg) rotateZ(0deg)}to{transform:rotateX(58deg) rotateZ(360deg)}}
#direk-home[data-motion="off"] .sc-scene *,#direk-home[data-motion="off"] .sc-scene *::before{animation:none!important}
@media(max-width:1000px){#direk-home .sc-hero{grid-template-columns:1fr 330px}#direk-home .sc-copy{padding:35px 0 30px 28px}#direk-home .sc-copy h2{font-size:42px}#direk-home .sc-float{padding:10px;font-size:10px}#direk-home .sc-float-one{left:2%}#direk-home .sc-float-two{right:2%}}
@media(max-width:760px){#direk-home.hc-workspace{padding:22px 18px}#direk-home .sc-hero{grid-template-columns:1fr;min-height:0}#direk-home .sc-copy{padding:30px}#direk-home .sc-copy h2{font-size:44px}#direk-home .sc-scene{min-height:330px;max-width:430px;width:100%;justify-self:center;margin-top:-15px}#direk-home .sc-float-one{top:12%}#direk-home .sc-float-three{bottom:14%}#direk-home .sc-scene-caption{bottom:15px}#direk-home .hc-welcome h1{font-size:27px}}
@media(max-width:480px){#direk-home.hc-workspace{padding:18px 14px}#direk-home .sc-copy{padding:27px 23px}#direk-home .sc-copy h2{font-size:38px;letter-spacing:-1.5px}#direk-home .sc-copy .hc-start-actions{flex-direction:column}#direk-home .sc-scene{min-height:295px}#direk-home .sc-stage{width:190px;height:190px;margin:-95px}#direk-home .sc-ring{width:270px;height:270px;margin:-135px}#direk-home .sc-ring-two{width:300px;height:300px;margin:-150px}#direk-home .sc-float{font-size:9px;padding:9px}#direk-home .sc-float-three{left:6%}#direk-home .hc-section-title h2{font-size:22px}}
@media(prefers-reduced-motion:reduce){#direk-home .sc-scene *,#direk-home .sc-scene *::before{animation:none!important}#direk-home .sc-hero{transform:none!important;transition:none!important}}
/* Avoid combining an outer hover transform with independently animated 3D surfaces. */
#direk-home .sc-hero{transform:none!important;transition:box-shadow .2s ease}
#direk-home .sc-glow{background:radial-gradient(circle at var(--light-x,50%) var(--light-y,50%),rgba(143,89,255,.26),transparent 63%)}
#direk-home .sc-face{background:linear-gradient(145deg,#a77ae2,#7954c2);backface-visibility:hidden;-webkit-backface-visibility:hidden;backdrop-filter:none;-webkit-backdrop-filter:none}
#direk-home .sc-top{background:linear-gradient(145deg,#c8a4f0,#9b82e0)}
#direk-home .sc-right{background:linear-gradient(145deg,#8d63cc,#6747ab)}
#direk-home .sc-left{background:linear-gradient(145deg,#926bd0,#6f4bad)}
/* Workspace launchpads complement the hero without changing it. */
#direk-home .hc-tools{margin-top:38px}#direk-home .hc-controls{padding:12px 14px;border:1px solid var(--hc-border);border-radius:15px;background:var(--hc-surface);box-shadow:0 3px 0 var(--hc-edge);gap:14px;margin:23px 0}#direk-home .hc-filters{gap:7px}#direk-home .hc-filters button{font-size:11px;font-weight:600;border-radius:9px;padding:9px 13px}#direk-home .hc-search{background:var(--hc-soft);border-color:transparent;min-height:42px;width:275px}#direk-home .hc-search input{font-size:12px}
#direk-home .hc-grid{grid-template-columns:repeat(auto-fit,minmax(min(100%,230px),1fr));gap:19px}#direk-home .hc-module{--module-color:#7b65e8;--module-glow:rgba(134,104,239,.17);--module-floor:#d7cbf8;padding:22px;min-height:305px;background:linear-gradient(155deg,var(--hc-surface) 35%,var(--hc-soft));border-radius:22px;box-shadow:inset 0 1px 0 rgba(255,255,255,.35),0 5px 0 var(--hc-edge),0 15px 25px rgba(60,48,104,.06)}#direk-home .hc-module::before{height:2px;opacity:.8}#direk-home .hc-module:hover{border-color:var(--module-color);box-shadow:inset 0 1px 0 rgba(255,255,255,.3),0 7px 0 var(--hc-edge),0 22px 35px rgba(60,48,104,.14)}
#direk-home .hc-tone-emerald{--module-color:#299c91;--module-glow:rgba(58,195,178,.17);--module-floor:#bae7dd}#direk-home .hc-tone-violet{--module-color:#ab7ada;--module-glow:rgba(181,136,229,.2);--module-floor:#e2cdf4}#direk-home .hc-tone-amber{--module-color:#bd8b34;--module-glow:rgba(217,169,74,.17);--module-floor:#f2ddb3}
#direk-home .hc-module-heading{display:flex;align-items:center;justify-content:space-between;gap:10px}#direk-home .hc-module-category{font-size:10px;font-weight:600;color:var(--module-color);letter-spacing:.02em}#direk-home .hc-module-index{font-size:10px;margin-left:0;color:var(--hc-copy);opacity:.7}
#direk-home .hc-module-art{position:relative;height:115px;margin:5px 0 10px;background:radial-gradient(ellipse at center,var(--module-glow),transparent 65%);pointer-events:none;isolation:isolate}#direk-home .hc-module-orbit{position:absolute;left:50%;top:50%;width:125px;height:50px;margin:-25px -62.5px;border:1px solid var(--module-color);opacity:.23;border-radius:50%;transform:rotate(-15deg)}#direk-home .hc-module-orbit::after{content:"";position:absolute;left:14px;top:2px;width:5px;height:5px;border-radius:50%;background:var(--module-color)}#direk-home .hc-module-floor{position:absolute;left:50%;top:65%;width:64px;height:64px;margin:-32px;background:linear-gradient(145deg,var(--module-floor),var(--hc-soft));border:1px solid var(--module-color);border-radius:13px;transform:rotateX(60deg) rotateZ(-35deg);box-shadow:0 5px 0 var(--hc-edge);opacity:.65}#direk-home .hc-module-icon{position:absolute;left:50%;top:39%;width:56px;height:56px;margin:-28px;display:grid;place-items:center;color:var(--module-color);font-size:33px;filter:drop-shadow(0 7px 5px var(--module-glow));transform:translateY(0);transition:transform .2s ease}#direk-home .hc-module:hover .hc-module-icon{transform:translateY(-5px)}
#direk-home .hc-module-copy{position:relative;flex:1}#direk-home .hc-module h3{font-size:19px;font-weight:700;line-height:1.3;margin:0;letter-spacing:-.4px}#direk-home .hc-module p{font-size:12px;line-height:1.75;margin:10px 0 20px;color:var(--hc-copy)}#direk-home .hc-module-bottom{padding-top:15px;border-top:1px solid var(--hc-border);font-size:11px;font-weight:600;color:var(--module-color)}#direk-home .hc-open-arrow{width:31px;height:31px;background:transparent;border-color:var(--hc-border);color:var(--module-color);transition:background .2s,border-color .2s}#direk-home .hc-module:hover .hc-open-arrow{background:var(--module-glow);border-color:var(--module-color)}
#direk-home[data-theme="dark"] .hc-module{--module-floor:#494069}#direk-home[data-theme="dark"] .hc-tone-emerald{--module-color:#6ddccb;--module-floor:#2e635d}#direk-home[data-theme="dark"] .hc-tone-violet{--module-color:#cca5f2;--module-floor:#57426e}#direk-home[data-theme="dark"] .hc-tone-amber{--module-color:#edc77f;--module-floor:#65533a}
#direk-home .hc-journey{position:relative;padding:28px;margin-top:32px;border-radius:22px;box-shadow:inset 0 1px 0 rgba(255,255,255,.16),0 4px 0 var(--hc-edge);background:linear-gradient(130deg,var(--hc-surface),var(--hc-soft))}#direk-home .hc-journey ol{gap:22px;margin-top:28px}#direk-home .hc-journey li{position:relative;border:0;padding-top:0}#direk-home .hc-journey li:not(:last-child)::after{content:"";position:absolute;top:19px;left:56px;right:-9px;height:1px;background:linear-gradient(90deg,var(--hc-accent),var(--hc-border));opacity:.35}#direk-home .hc-journey li>span{width:39px;height:39px;margin-bottom:16px;border-radius:12px;font-size:11px;position:relative;z-index:1;background:var(--hc-surface);box-shadow:0 3px 0 var(--hc-edge),0 6px 15px rgba(75,51,137,.08)}#direk-home .hc-journey strong{font-size:13px;font-weight:650}#direk-home .hc-journey p{font-size:11px;margin-top:7px;max-width:220px;line-height:1.7}#direk-home .hc-path-note{font-size:11px;max-width:290px;line-height:1.7}
#direk-home[data-motion="off"] .hc-module-icon{transform:none!important}
@media(max-width:1100px){#direk-home .hc-grid{grid-template-columns:repeat(2,minmax(0,1fr))}#direk-home .hc-module{min-height:285px}#direk-home .hc-journey ol{grid-template-columns:repeat(2,minmax(0,1fr));gap:25px}#direk-home .hc-journey li::after{display:none}}
@media(max-width:760px){#direk-home .hc-controls{padding:13px;gap:13px}#direk-home .hc-search{width:100%}#direk-home .hc-filters{gap:4px}#direk-home .hc-filters button{font-size:10px;padding:8px 10px}#direk-home .hc-grid{gap:16px}#direk-home .hc-module{padding:20px}#direk-home .hc-journey{padding:23px}}
@media(max-width:520px){#direk-home .hc-grid{grid-template-columns:1fr}#direk-home .hc-module{min-height:0;display:grid;grid-template-columns:80px minmax(0,1fr);column-gap:17px;padding:20px}#direk-home .hc-module-heading{grid-column:1/-1;margin-bottom:13px}#direk-home .hc-module-art{height:95px;margin:0}#direk-home .hc-module-orbit{width:85px;height:38px;margin:-19px -42.5px}#direk-home .hc-module-icon{font-size:28px}#direk-home .hc-module-floor{width:52px;height:52px;margin:-26px}#direk-home .hc-module-copy{align-self:center}#direk-home .hc-module h3{font-size:18px}#direk-home .hc-module p{font-size:11px;margin-bottom:15px}#direk-home .hc-module-bottom{grid-column:1/-1;margin-top:8px}#direk-home .hc-journey ol{grid-template-columns:1fr}#direk-home .hc-journey li{padding-left:58px;min-height:58px}#direk-home .hc-journey li>span{position:absolute;left:0;top:0}#direk-home .hc-journey li:not(:last-child)::after{display:block;left:19px;top:47px;right:auto;bottom:-17px;width:1px;height:auto}#direk-home .hc-journey p{max-width:none}}
@media(prefers-reduced-motion:reduce){#direk-home .hc-module-icon{transform:none!important;transition:none!important}}
/* Final planning journey: a visual sequence, not a completion indicator. */
#direk-home .hc-journey{padding:28px;background:radial-gradient(ellipse at 85% 0%,rgba(133,104,226,.19),transparent 60%),linear-gradient(120deg,#24233e,#302746);border:1px solid #615177;border-radius:23px;box-shadow:inset 0 1px 0 rgba(255,255,255,.16),0 5px 0 #201b33,0 18px 30px rgba(42,29,78,.11);color:#f2edff}
#direk-home .hc-journey .hc-eyebrow{color:#bda9e5}#direk-home .hc-journey .hc-section-title h2{font-size:23px;color:#f5f1ff;margin-top:8px;letter-spacing:-.6px}#direk-home .hc-route-badge{display:inline-flex;align-items:center;gap:7px;flex-shrink:0;padding:8px 11px;border:1px solid rgba(221,195,255,.2);border-radius:9px;font-size:10px;background:rgba(220,197,255,.05);color:#dbceef}
#direk-home .hc-journey ol{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:21px;margin:25px 0 0;list-style:none;padding:0}
#direk-home .hc-journey .hc-route-step{--route-tint:#c2a5f9;position:relative;min-height:170px;padding:20px;border:1px solid rgba(219,191,255,.2);border-radius:16px;background:linear-gradient(145deg,rgba(215,191,255,.09),rgba(160,126,215,.025));box-shadow:inset 0 1px 0 rgba(255,255,255,.09),0 4px 0 rgba(13,10,26,.28);transition:transform .18s ease,border-color .18s ease}
#direk-home .hc-journey .hc-route-step:nth-child(4n+2){--route-tint:#9fdcd1}#direk-home .hc-journey .hc-route-step:nth-child(4n+3){--route-tint:#a7c9f3}#direk-home .hc-journey .hc-route-step:nth-child(4n){--route-tint:#e8c68f}
#direk-home .hc-journey .hc-route-step::after{display:none!important}#direk-home .hc-route-step:not(:last-child)::before{content:"\2192";position:absolute;right:-17px;top:48%;z-index:2;color:#b2a1d1;font-size:15px;line-height:1;pointer-events:none}
#direk-home .hc-route-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}#direk-home .hc-route-symbol{display:grid;place-items:center;width:36px;height:36px;color:var(--route-tint);font-size:21px;filter:drop-shadow(0 4px 4px rgba(0,0,0,.17))}#direk-home .hc-route-number{font-size:11px;letter-spacing:.08em;color:var(--route-tint);opacity:.8}
#direk-home .hc-journey .hc-route-step>strong{display:block;font-size:14px;font-weight:650;line-height:1.45;color:#f0e9ff}#direk-home .hc-journey .hc-route-step>p{max-width:none;font-size:11px;line-height:1.75;margin:8px 0 0;color:#bdb2d1}
#direk-home .hc-route-insight{display:flex;align-items:center;gap:11px;padding:15px 0 0;margin-top:22px;border-top:1px solid rgba(223,202,255,.15)}#direk-home .hc-route-insight>i{color:#bda9e5;font-size:15px}#direk-home .hc-route-insight p{font-size:11px;color:#bfb3d2;line-height:1.7;margin:0}#direk-home .hc-route-insight strong{display:inline;font-size:11px;color:#e9ddfa;margin-right:5px}
@media(hover:hover) and (pointer:fine){#direk-home .hc-journey .hc-route-step:hover{transform:translateY(-2px);border-color:rgba(219,191,255,.4)}}
#direk-home[data-motion="off"] .hc-route-step:hover{transform:none!important}
@media(max-width:1100px){#direk-home .hc-journey ol{grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}#direk-home .hc-route-step::before{display:none}}
@media(max-width:760px){#direk-home .hc-journey{padding:24px}#direk-home .hc-journey .hc-section-title{gap:13px}#direk-home .hc-journey .hc-section-title h2{font-size:22px}#direk-home .hc-route-step{padding:18px}}
@media(max-width:520px){#direk-home .hc-journey{padding:22px 19px}#direk-home .hc-journey ol{grid-template-columns:1fr;gap:18px}#direk-home .hc-journey .hc-route-step{min-height:0;padding:18px 18px 18px 70px}#direk-home .hc-route-top{position:absolute;left:17px;top:17px;display:flex;flex-direction:column;gap:5px;margin:0}#direk-home .hc-route-symbol{width:34px;height:28px;font-size:21px}#direk-home .hc-route-number{font-size:9px}#direk-home .hc-route-step:not(:last-child)::before{display:block;content:"\2193";left:32px;right:auto;top:auto;bottom:-16px;font-size:13px}#direk-home .hc-route-insight{align-items:flex-start}}
@media(prefers-reduced-motion:reduce){#direk-home .hc-route-step:hover{transform:none!important;transition:none!important}}
</style>
@endpush
@push('js')
<script>
(function () {
    const root = document.getElementById('direk-home');
    if (!root) return;
    const input = root.querySelector('#dh-search-input');
    const clear = root.querySelector('#dh-search-clear');
    const reset = root.querySelector('#dh-reset');
    const finder = root.querySelector('#dh-find-shortcut');
    const empty = root.querySelector('#dh-empty');
    const count = root.querySelector('#dh-result-count');
    const status = root.querySelector('#dh-search-status');
    const filters = Array.from(root.querySelectorAll('[data-filter]'));
    const cards = Array.from(root.querySelectorAll('[data-module]'), function (card) { return { element: card, search: card.dataset.search, category: card.dataset.category }; });
    let activeFilter = 'all';
    function filterCards() {
        const query = input.value.trim().toLocaleLowerCase();
        let visible = 0;
        cards.forEach(function (card) {
            const matches = card.search.includes(query) && (activeFilter === 'all' || card.category === activeFilter);
            card.element.hidden = !matches;
            if (matches) visible++;
        });
        const label = visible + ' ' + (visible === 1 ? 'workspace' : 'workspaces');
        empty.hidden = visible !== 0;
        clear.hidden = query.length === 0;
        count.textContent = label;
        status.textContent = label + ' found.';
    }
    function focusSearch() { input.focus(); input.scrollIntoView({ behavior: root.dataset.motion === 'off' || window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' }); }
    input.addEventListener('input', filterCards);
    clear.addEventListener('click', function () { input.value = ''; filterCards(); input.focus(); });
    if (reset) reset.addEventListener('click', function () {
        input.value = ''; activeFilter = 'all';
        filters.forEach(function (button) { const selected = button.dataset.filter === 'all'; button.classList.toggle('is-active', selected); button.setAttribute('aria-pressed', selected ? 'true' : 'false'); });
        filterCards();
    });
    finder.addEventListener('click', focusSearch);
    filters.forEach(function (button) { button.addEventListener('click', function () {
        activeFilter = button.dataset.filter;
        filters.forEach(function (item) { const selected = item === button; item.classList.toggle('is-active', selected); item.setAttribute('aria-pressed', selected ? 'true' : 'false'); });
        filterCards();
    }); });
    document.addEventListener('keydown', function (event) {
        if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey && !document.activeElement.isContentEditable && !/input|textarea|select/i.test(document.activeElement.tagName)) { event.preventDefault(); focusSearch(); }
        if (event.key === 'Escape' && document.activeElement === input) { input.value = ''; filterCards(); input.blur(); }
    });
    // Optional motion never controls access, counts, or navigation.
    const motionButton = root.querySelector('#dh-motion-toggle');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
    const depthLayers = Array.from(root.querySelectorAll('[data-depth], [data-light]'));
    const cleanups = [];
    let motionPreference = true;
    try { motionPreference = window.localStorage.getItem('direk.home.motion') !== 'off'; } catch (_) {}
    let motionEnabled = false;
    function syncMotion() {
        motionEnabled = motionPreference && !reduceMotion.matches;
        root.dataset.motion = motionEnabled ? 'on' : 'off';
        cleanups.forEach(function (resetDepth) { resetDepth(); });
        if (motionButton) {
            motionButton.hidden = false;
            motionButton.disabled = reduceMotion.matches;
            motionButton.setAttribute('aria-pressed', String(motionEnabled));
            motionButton.querySelector('span').textContent = reduceMotion.matches ? 'Reduced motion' : (motionEnabled ? 'Motion on' : 'Motion off');
        }
    }
    depthLayers.forEach(function (layer) {
        let frame = 0;
        function resetDepth() {
            window.cancelAnimationFrame(frame);
            frame = 0;
            ['--depth-x','--depth-y','--light-x','--light-y'].forEach(function (property) { layer.style.removeProperty(property); });
        }
        cleanups.push(resetDepth);
        layer.addEventListener('pointermove', function (event) {
            if (!motionEnabled || !finePointer.matches || event.pointerType === 'touch' || layer.hidden) return;
            const bounds = layer.getBoundingClientRect();
            if (!bounds.width || !bounds.height) return;
            const x = Math.max(0, Math.min(1, (event.clientX - bounds.left) / bounds.width));
            const y = Math.max(0, Math.min(1, (event.clientY - bounds.top) / bounds.height));
            window.cancelAnimationFrame(frame);
            frame = window.requestAnimationFrame(function () {
                if (!motionEnabled || layer.hidden) return;
                if (layer.hasAttribute('data-depth')) {
                    layer.style.setProperty('--depth-x', ((0.5 - y) * 4).toFixed(2) + 'deg');
                    layer.style.setProperty('--depth-y', ((x - 0.5) * 4).toFixed(2) + 'deg');
                }
                layer.style.setProperty('--light-x', (x * 100).toFixed(1) + '%');
                layer.style.setProperty('--light-y', (y * 100).toFixed(1) + '%');
            });
        });
        layer.addEventListener('pointerleave', resetDepth);
        layer.addEventListener('blur', resetDepth);
    });
    if (motionButton) motionButton.addEventListener('click', function () {
        motionPreference = !motionPreference;
        try { window.localStorage.setItem('direk.home.motion', motionPreference ? 'on' : 'off'); } catch (_) {}
        syncMotion();
    });
    if (reduceMotion.addEventListener) reduceMotion.addEventListener('change', syncMotion);
    if (finePointer.addEventListener) finePointer.addEventListener('change', syncMotion);
    window.addEventListener('blur', function () { cleanups.forEach(function (resetDepth) { resetDepth(); }); });
    syncMotion();
    const clock = root.querySelector('#dh-live-time');
    function updateClock() { const now = new Date(); clock.dateTime = now.toTimeString().slice(0, 5); clock.textContent = now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }); }
    updateClock();
    window.setInterval(updateClock, 60000);
})();
</script>
@endpush

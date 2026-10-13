@extends('layouts.app')
@section('content')
@php
    $homeNow = now();
    $homeUser = auth()->user();
    $isDark = session('user_settings.class_theme', '') === 'dark';
    $announcement = $homeannouncement ?? null;
    $birthday = $homeUser?->birthday;
    $birthdate = $birthday ? \Carbon\Carbon::parse($birthday) : null;
    $firstName = trim((string) ($homeUser?->firstname ?? '')) ?: 'there';
    $greeting = $homeNow->hour < 12 ? 'Good morning' : ($homeNow->hour < 18 ? 'Good afternoon' : 'Good evening');
    $canViewFinancialPlan = $homeUser && $homeUser->can('viewAny', \App\Models\FinancialPlan::class);
    $canViewWorkPlan = $homeUser && $homeUser->can('viewAny', \App\Models\WorkPlan::class);
    $modules = [];
    if ($canViewFinancialPlan && \Illuminate\Support\Facades\Route::has('financial-plans.plans')) {
        $modules[] = ['title' => 'Financial plans', 'description' => 'Build your budget, schedule financial targets, and review allocation availability.', 'route' => 'financial-plans.plans', 'icon' => 'fa-money', 'category' => 'Budget & resources', 'color' => 'indigo'];
    }
    if ($canViewWorkPlan && \Illuminate\Support\Facades\Route::has('work-plans.plans')) {
        $modules[] = ['title' => 'Work plans', 'description' => 'Turn planned activities into measurable outputs with clear delivery months.', 'route' => 'work-plans.plans', 'icon' => 'fa-tasks', 'category' => 'Activities & outputs', 'color' => 'teal'];
    }
    if ($canViewFinancialPlan && $canViewWorkPlan && \Illuminate\Support\Facades\Route::has('mdp.index')) {
        $modules[] = ['title' => 'Monthly disbursement', 'description' => 'See monthly cash requirements from linked Financial Plan schedules and export your MDP.', 'route' => 'mdp.index', 'icon' => 'fa-calendar', 'category' => 'Cash requirements', 'color' => 'violet'];
    }
    if (\Illuminate\Support\Facades\Route::has('user-profile')) {
        $modules[] = ['title' => 'My profile', 'description' => 'Update your account details, security preferences, and trusted devices.', 'route' => 'user-profile', 'icon' => 'fa-user', 'category' => 'Account & security', 'color' => 'amber'];
    }
    $primaryModule = $modules[0] ?? null;
    $steps = ['Fiscal year', 'Allocation level', 'Expense types', 'Allocations', 'Financial plan', 'Work plan', 'MDP'];
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Workspace'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div id="direk-home-modern" data-theme="{{ $isDark ? 'dark' : 'light' }}" class="!px-4 !pb-8 !pt-4 md:!px-6">
    <div class="home-shell">
        <div class="home-intro">
            <div>
                <p class="home-eyebrow">YOUR WORKSPACE</p>
                <h1 class="home-greeting">{{ $greeting }}, {{ $firstName }}<span class="home-greeting-dot">.</span></h1>
                <p class="home-secondary">A little more clarity. A lot more forward momentum.</p>
            </div>
            <div class="home-date"><i class="fa fa-calendar-o" aria-hidden="true"></i><span>{{ $homeNow->format('D, M j, Y') }}</span></div>
        </div>
        <section class="home-hero" aria-labelledby="home-hero-title">
            <div class="home-hero-content">
                <span class="home-hero-badge"><span aria-hidden="true"></span>D.I.R.E.K. / Planning workspace</span>
                <h2 id="home-hero-title">Make room for<br><span>your next big move.</span></h2>
                <p>Bring your budgets, activities, and monthly cash requirements into focus. Start with the work that matters today.</p>
                <div class="home-hero-actions">
                    @if ($primaryModule)
                        <a href="{{ route($primaryModule['route']) }}" class="home-primary-action">Open {{ strtolower($primaryModule['title']) }}<i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                    @endif
                    <a href="#home-modules" class="home-secondary-action">Explore workspace<i class="fa fa-arrow-down" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="home-hero-visual" aria-hidden="true">
                <div class="home-orbit home-orbit-one"></div><div class="home-orbit home-orbit-two"></div>
                <div class="home-floating-label"><span class="home-label-mark"></span>A connected perspective</div>
                <div class="home-visual-card"><span class="home-visual-kicker">FROM INTENT TO ACTION</span><strong>Plan.<br>Coordinate.<br><span>Deliver.</span></strong><div class="home-visual-rule"></div><span class="home-visual-caption">One workspace for the bigger picture.</span></div>
            </div>
        </section>
        @if ($announcement && $announcement->start_date && $announcement->end_date && \Carbon\Carbon::parse($announcement->start_date)->lte($homeNow) && \Carbon\Carbon::parse($announcement->end_date)->gte($homeNow))
            <div class="!mt-6">@include('components.home-announcement', ['homeannouncement' => $announcement])</div>
        @endif
        @if ($birthdate && $birthdate->month === $homeNow->month && $birthdate->day === $homeNow->day)
            <div class="!mt-6">@include('others.hbd')</div>
        @endif
        <div class="home-main-grid">
            <section id="home-modules" class="home-modules-section" aria-labelledby="home-modules-title">
                <div class="home-section-heading">
                    <div><p class="home-eyebrow">PICK UP YOUR WORK</p><h2 id="home-modules-title">Where would you like to start?</h2></div>
                    <span class="home-module-count">{{ count($modules) }} shortcuts</span>
                </div>
                <label class="home-search" for="home-module-search"><i class="fa fa-search" aria-hidden="true"></i><span class="sr-only">Search workspace shortcuts</span><input id="home-module-search" type="search" placeholder="Find a workspace shortcut..." autocomplete="off" aria-controls="home-module-grid"></label>
                <div id="home-module-grid" class="home-module-grid">
                    @foreach ($modules as $module)
                        <a href="{{ route($module['route']) }}" class="home-module-card" data-home-module data-search="{{ $module['title'] . ' ' . $module['category'] . ' ' . $module['description'] }}">
                            <div class="home-module-top"><span class="home-module-icon home-tone-{{ $module['color'] }}"><i class="fa {{ $module['icon'] }}" aria-hidden="true"></i></span><span class="home-module-arrow"><i class="fa fa-arrow-right" aria-hidden="true"></i></span></div>
                            <p class="home-card-category">{{ $module['category'] }}</p><h3>{{ $module['title'] }}</h3><p class="home-card-description">{{ $module['description'] }}</p><span class="home-card-action">Open workspace<span aria-hidden="true">↗</span></span>
                        </a>
                    @endforeach
                </div>
                <div id="home-module-empty" class="home-empty" @if (count($modules)) hidden @endif><i class="fa fa-search" aria-hidden="true"></i><h3>No matching shortcuts</h3><p>Try another search or use the sidebar to browse your available modules.</p></div>
                <p id="home-search-status" class="sr-only" role="status" aria-live="polite"></p>
            </section>
            <aside class="home-context" aria-label="Workspace guide">
                <section class="home-guide">
                    <span class="home-guide-icon"><i class="fa fa-compass" aria-hidden="true"></i></span><p class="home-eyebrow">THE PATH FORWARD</p><h2>Good plans<br>stay connected.</h2><p class="home-secondary">Follow the planning sequence to keep resources, outputs, and cash schedules aligned.</p>
                    <ol class="home-steps">
                        @foreach ($steps as $step)<li><span class="home-step-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span>{{ $step }}</span>@if (!$loop->last)<i class="fa fa-angle-down" aria-hidden="true"></i>@endif</li>@endforeach
                    </ol>
                    <div class="home-guide-note"><i class="fa fa-lightbulb-o" aria-hidden="true"></i><p>MDP reads monthly Financial Plan targets. Work Plan months describe when outputs are expected.</p></div>
                </section>
                <section class="home-brand-note"><span class="home-brand-symbol" aria-hidden="true">D</span><div><strong>Built for a clearer view.</strong><p>DEPDev Integrated Reporting and Executive Kiosk</p></div></section>
            </aside>
        </div>
        <div class="home-bottom-note"><span>One workspace. Connected decisions.</span><span>Use the sidebar for all modules available to your account.</span></div>
    </div>
</div>
@include('layouts.footers.auth.footer')
@endsection
@push('css')
<style>
#direk-home-modern{--home-surface:#fff;--home-subtle:#f6f7fb;--home-border:#e8ebf2;--home-heading:#172033;--home-copy:#637086;--home-muted:#69758b;--home-accent:#635bff;--home-ring:rgba(99,91,255,.2);color:var(--home-heading);font-family:inherit}
#direk-home-modern[data-theme="dark"]{--home-surface:#192130;--home-subtle:#151c29;--home-border:#2c3546;--home-heading:#eef2fb;--home-copy:#aab5c8;--home-muted:#8290a8;--home-accent:#a19cff;--home-ring:rgba(161,156,255,.25)}
#direk-home-modern *,#direk-home-modern *::before,#direk-home-modern *::after{box-sizing:border-box}
#direk-home-modern [hidden]{display:none!important}
#direk-home-modern h1,#direk-home-modern h2,#direk-home-modern h3,#direk-home-modern p{margin:0;color:inherit}
#direk-home-modern a{text-decoration:none;color:inherit}
#direk-home-modern a:focus-visible,#direk-home-modern input:focus-visible{outline:3px solid var(--home-accent);outline-offset:4px}
#direk-home-modern .home-shell{max-width:1600px;margin:0 auto;background:var(--home-subtle);border:1px solid var(--home-border);border-radius:24px;padding:28px}
#direk-home-modern .home-intro{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:26px}
#direk-home-modern .home-eyebrow{font-size:10px;font-weight:700;letter-spacing:.14em;color:var(--home-muted);line-height:1.5}
#direk-home-modern .home-greeting{font-size:clamp(25px,2.6vw,36px);font-weight:650;letter-spacing:-1.2px;line-height:1.25;margin-top:8px;overflow-wrap:anywhere}
#direk-home-modern .home-greeting-dot{color:var(--home-accent)}
#direk-home-modern .home-secondary{font-size:13px;line-height:1.8;color:var(--home-copy);margin-top:8px}
#direk-home-modern .home-date{display:flex;align-items:center;gap:10px;white-space:nowrap;padding:11px 14px;background:var(--home-surface);border:1px solid var(--home-border);border-radius:12px;font-size:12px;color:var(--home-copy)}
#direk-home-modern .home-hero{position:relative;display:grid;grid-template-columns:minmax(0,1.25fr) minmax(0,.75fr);overflow:hidden;background:linear-gradient(115deg,#171a32 0%,#292452 58%,#49419e 100%);border-radius:20px;color:#fff;min-height:330px}
#direk-home-modern .home-hero-content{position:relative;z-index:2;padding:38px 40px}
#direk-home-modern .home-hero-badge{display:inline-flex;align-items:center;gap:9px;color:#dedaff;font-size:10px;font-weight:600;letter-spacing:.07em;border:1px solid rgba(255,255,255,.16);border-radius:999px;padding:7px 11px}
#direk-home-modern .home-hero-badge>span{width:6px;height:6px;background:#b9b1ff;border-radius:50%}
#direk-home-modern .home-hero h2{font-size:clamp(30px,3vw,46px);line-height:1.12;letter-spacing:-1.5px;font-weight:650;margin-top:24px;color:#fff}
#direk-home-modern .home-hero h2 span{color:#c8c1ff}
#direk-home-modern .home-hero-content>p{max-width:450px;color:#cbcbe1;font-size:13px;line-height:1.85;margin-top:18px}
#direk-home-modern .home-hero-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:24px}
#direk-home-modern .home-primary-action,#direk-home-modern .home-secondary-action{display:inline-flex;align-items:center;justify-content:center;gap:14px;min-height:44px;border-radius:10px;padding:12px 16px;font-size:12px;font-weight:600;transition:background .2s,transform .2s}
#direk-home-modern .home-primary-action{background:#fff;color:#292452;box-shadow:0 6px 18px rgba(0,0,0,.12)}
#direk-home-modern .home-primary-action:hover{background:#efecff;transform:translateY(-2px)}
#direk-home-modern .home-secondary-action{color:#eeeaff;border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.04)}
#direk-home-modern .home-secondary-action:hover{background:rgba(255,255,255,.12)}
#direk-home-modern .home-hero-visual{position:relative;display:grid;place-items:center;padding:35px 30px 25px}
#direk-home-modern .home-orbit{position:absolute;width:300px;height:300px;border:1px solid rgba(210,200,255,.2);border-radius:50%;right:-40px;top:10px}
#direk-home-modern .home-orbit-two{width:410px;height:410px;top:-45px;right:-95px;opacity:.6}
#direk-home-modern .home-floating-label{position:absolute;top:28px;right:28px;display:flex;align-items:center;gap:7px;color:#e2dcff;font-size:10px}
#direk-home-modern .home-label-mark{width:6px;height:6px;border-radius:50%;background:#c7b9ff}
#direk-home-modern .home-visual-card{position:relative;width:245px;max-width:100%;padding:25px;border:1px solid rgba(255,255,255,.22);border-radius:18px;background:linear-gradient(135deg,rgba(255,255,255,.13),rgba(255,255,255,.04));transform:rotate(-5deg);box-shadow:0 24px 55px rgba(11,7,45,.2)}
#direk-home-modern .home-visual-kicker{font-size:8px;letter-spacing:.12em;color:#c8c2ea}
#direk-home-modern .home-visual-card strong{display:block;font-size:34px;font-weight:600;line-height:1.15;letter-spacing:-1px;margin-top:18px;color:#fff}
#direk-home-modern .home-visual-card strong span{color:#c1b7ff}
#direk-home-modern .home-visual-rule{height:1px;background:rgba(255,255,255,.18);margin:20px 0 12px}
#direk-home-modern .home-visual-caption{font-size:9px;color:#d6d0f0}
#direk-home-modern .home-main-grid{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:24px;margin-top:30px}
#direk-home-modern .home-section-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px}
#direk-home-modern .home-section-heading h2{font-size:20px;line-height:1.3;letter-spacing:-.5px;font-weight:650;margin-top:6px}
#direk-home-modern .home-module-count{white-space:nowrap;font-size:10px;color:var(--home-copy);background:var(--home-surface);border:1px solid var(--home-border);padding:6px 9px;border-radius:999px}
#direk-home-modern .home-search{display:flex;align-items:center;gap:10px;min-height:44px;background:var(--home-surface);border:1px solid var(--home-border);border-radius:11px;padding:0 14px;margin-bottom:16px;color:var(--home-muted)}
#direk-home-modern .home-search:focus-within{border-color:var(--home-accent);box-shadow:0 0 0 3px var(--home-ring)}
#direk-home-modern .home-search input{width:100%;min-width:0;border:0;background:transparent;color:var(--home-heading);font:inherit;font-size:12px;padding:12px 0;outline:none!important;box-shadow:none}
#direk-home-modern .home-search input::placeholder{color:var(--home-muted)}
#direk-home-modern .home-module-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
#direk-home-modern .home-module-card{display:block;min-width:0;padding:23px;border:1px solid var(--home-border);border-radius:16px;background:var(--home-surface);transition:transform .2s,border-color .2s,box-shadow .2s}
#direk-home-modern .home-module-card:hover{transform:translateY(-3px);border-color:var(--home-accent);box-shadow:0 10px 26px rgba(30,32,70,.08)}
#direk-home-modern .home-module-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
#direk-home-modern .home-module-icon{display:grid;place-items:center;width:42px;height:42px;border-radius:12px;font-size:17px}
#direk-home-modern .home-tone-indigo{background:#eeecff;color:#6558d9}
#direk-home-modern .home-tone-teal{background:#e3f5f0;color:#188b75}
#direk-home-modern .home-tone-violet{background:#f3ebff;color:#9863d5}
#direk-home-modern .home-tone-amber{background:#fff2df;color:#bb8733}
#direk-home-modern[data-theme="dark"] .home-module-icon{background:#2a3043}
#direk-home-modern .home-module-arrow{color:var(--home-muted);font-size:11px;transition:transform .2s}
#direk-home-modern .home-module-card:hover .home-module-arrow{transform:translateX(3px);color:var(--home-accent)}
#direk-home-modern .home-card-category{font-size:10px;color:var(--home-muted);margin-bottom:7px}
#direk-home-modern .home-module-card h3{font-size:18px;font-weight:650;letter-spacing:-.4px;line-height:1.3}
#direk-home-modern .home-card-description{font-size:12px;line-height:1.85;color:var(--home-copy);margin-top:10px;min-height:66px}
#direk-home-modern .home-card-action{display:flex;justify-content:space-between;align-items:center;font-size:11px;font-weight:600;color:var(--home-accent);border-top:1px solid var(--home-border);padding-top:15px;margin-top:17px}
#direk-home-modern .home-context{padding-top:1px}
#direk-home-modern .home-guide{background:var(--home-surface);border:1px solid var(--home-border);border-radius:16px;padding:24px}
#direk-home-modern .home-guide-icon{display:grid;place-items:center;width:34px;height:34px;border-radius:50%;background:var(--home-subtle);color:var(--home-accent);margin-bottom:22px;font-size:17px}
#direk-home-modern .home-guide h2{font-size:25px;line-height:1.2;letter-spacing:-.8px;margin-top:9px;font-weight:650}
#direk-home-modern .home-steps{list-style:none;margin:22px 0;padding:0}
#direk-home-modern .home-steps li{position:relative;display:flex;align-items:center;gap:12px;min-height:49px;font-size:12px;font-weight:500}
#direk-home-modern .home-step-number{display:grid;place-items:center;width:28px;height:28px;background:var(--home-subtle);border:1px solid var(--home-border);border-radius:9px;font-size:9px;color:var(--home-muted);font-variant-numeric:tabular-nums}
#direk-home-modern .home-steps li>i{position:absolute;left:11px;bottom:-3px;font-size:10px;color:var(--home-muted)}
#direk-home-modern .home-guide-note{display:flex;align-items:flex-start;gap:10px;padding-top:16px;border-top:1px solid var(--home-border);color:var(--home-copy)}
#direk-home-modern .home-guide-note i{margin-top:2px;color:var(--home-accent);font-size:16px}
#direk-home-modern .home-guide-note p{font-size:10px;line-height:1.8}
#direk-home-modern .home-brand-note{display:flex;align-items:flex-start;gap:12px;margin-top:17px;padding:18px 10px;color:var(--home-copy)}
#direk-home-modern .home-brand-symbol{display:grid;place-items:center;width:30px;height:30px;border-radius:9px;background:var(--home-surface);border:1px solid var(--home-border);color:var(--home-accent);font-size:14px;font-weight:700;flex-shrink:0}
#direk-home-modern .home-brand-note strong{display:block;font-size:11px;font-weight:600;color:var(--home-heading)}
#direk-home-modern .home-brand-note p{font-size:10px;line-height:1.7;margin-top:5px}
#direk-home-modern .home-empty{border:1px dashed var(--home-border);border-radius:16px;padding:35px 20px;text-align:center;color:var(--home-copy)}
#direk-home-modern .home-empty>i{font-size:22px;color:var(--home-muted)}
#direk-home-modern .home-empty h3{font-size:16px;margin-top:15px}
#direk-home-modern .home-empty p{font-size:12px;line-height:1.8;margin-top:8px}
#direk-home-modern .home-bottom-note{display:flex;justify-content:space-between;gap:14px;border-top:1px solid var(--home-border);padding-top:20px;margin-top:26px;font-size:10px;color:var(--home-muted)}
@media(max-width:1100px){#direk-home-modern .home-main-grid{grid-template-columns:minmax(0,1fr)}#direk-home-modern .home-context{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,.65fr);gap:20px}#direk-home-modern .home-brand-note{align-self:center}#direk-home-modern .home-steps{display:flex;flex-wrap:wrap;gap:8px}#direk-home-modern .home-steps li{min-height:40px;padding-right:8px}#direk-home-modern .home-steps li>i{display:none}}
@media(max-width:760px){#direk-home-modern .home-shell{padding:18px;border-radius:18px}#direk-home-modern .home-intro{align-items:flex-start;flex-direction:column;gap:14px}#direk-home-modern .home-hero{grid-template-columns:minmax(0,1fr)}#direk-home-modern .home-hero-content{padding:28px}#direk-home-modern .home-hero-visual{display:none}#direk-home-modern .home-context{grid-template-columns:minmax(0,1fr)}#direk-home-modern .home-brand-note{margin:0}#direk-home-modern .home-bottom-note{flex-direction:column;gap:7px}}
@media(max-width:520px){#direk-home-modern .home-module-grid{grid-template-columns:minmax(0,1fr)}#direk-home-modern .home-hero-content{padding:24px}#direk-home-modern .home-section-heading{align-items:flex-start}#direk-home-modern .home-section-heading h2{font-size:18px}#direk-home-modern .home-card-description{min-height:0}#direk-home-modern .home-hero-actions>a{width:100%}}
@media(prefers-reduced-motion:reduce){#direk-home-modern a,#direk-home-modern .home-module-arrow{transition:none!important;transform:none!important}}
</style>
@endpush
@push('js')
<script>
(function () {
    const root = document.getElementById('direk-home-modern');
    if (!root) return;
    const search = root.querySelector('#home-module-search');
    const cards = Array.from(root.querySelectorAll('[data-home-module]'));
    const empty = root.querySelector('#home-module-empty');
    const status = root.querySelector('#home-search-status');
    search.addEventListener('input', function () {
        const query = search.value.trim().toLocaleLowerCase();
        let visible = 0;
        cards.forEach(function (card) {
            const matches = card.dataset.search.toLocaleLowerCase().includes(query);
            card.hidden = !matches;
            if (matches) visible++;
        });
        empty.hidden = visible !== 0;
        status.textContent = visible + ' shortcut' + (visible === 1 ? '' : 's') + ' found.';
    });
})();
</script>
@endpush

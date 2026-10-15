<script>
(function () {
    'use strict';
    const parameter = '_direk_frame';
    function publicUrl(value) {
        const url = new URL(value, window.location.href);
        url.searchParams.delete(parameter);
        return url;
    }
    function eligibleLink(event, documentRoot) {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return null;
        const link = event.target.closest && event.target.closest('a[href]');
        if (!link || link.hasAttribute('download') || link.hasAttribute('data-no-workspace')) return null;
        const target = link.getAttribute('target') || documentRoot.querySelector('base[target]')?.getAttribute('target') || '_self';
        if (target.toLowerCase() !== '_self') return null;
        const href = link.getAttribute('href').trim();
        if (!href || href.startsWith('#')) return null;
        const url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin || !['http:', 'https:'].includes(url.protocol)) return null;
        if (/\/(?:pdf|export-pdf|logout|azure|azurecallback)\/?$/i.test(url.pathname) || /\.(?:pdf|xlsx?|csv|zip)$/i.test(url.pathname)) return null;
        return { link, url: publicUrl(url.href) };
    }
    const frame = document.getElementById('direk-workspace-frame');
    if (frame && window.self === window.top) {
        if (window.DirekWorkspace) return;
        const sidebar = document.getElementById('sidenav-main');
        const main = frame.closest('main');
        const progress = document.getElementById('direk-frame-progress');
        const errorBox = document.getElementById('direk-frame-error');
        let pending = null;
        let timeout = null;
        let committedUrl = publicUrl(window.location.href).href;
        function setBusy(busy) {
            main.setAttribute('aria-busy', String(busy));
            progress.hidden = !busy;
            clearTimeout(timeout);
            if (busy) timeout = setTimeout(function () { errorBox.hidden = false; }, 20000);
            else errorBox.hidden = true;
        }
        function toggleSidebar(forceOpen) {
            if (window.matchMedia('(min-width: 1200px)').matches) {
                const hide = forceOpen === true ? false : !document.body.classList.contains('g-sidenav-hidden');
                document.body.classList.toggle('g-sidenav-hidden', hide);
            } else {
                const open = forceOpen === true || !document.body.classList.contains('g-sidenav-pinned');
                document.body.classList.toggle('g-sidenav-pinned', open);
            }
        }
        function syncSidebar(route) {
            if (!sidebar) return;
            sidebar.querySelectorAll('a.nav-link[data-direk-route-family]').forEach(function (link) {
                const family = link.dataset.direkRouteFamily;
                const selected = route === family || route.startsWith(family + '.');
                link.classList.toggle('direk-selected', selected);
                link.classList.toggle('direk-rest', !selected);
                link.classList.toggle('active', selected);
                if (selected) link.setAttribute('aria-current', 'page');
                else link.removeAttribute('aria-current');
            });
            sidebar.querySelectorAll('button[aria-controls]').forEach(function (toggle) {
                const menu = document.getElementById(toggle.getAttribute('aria-controls'));
                if (!menu || !sidebar.contains(menu)) return;
                const selected = !!menu.querySelector('a[aria-current="page"]');
                toggle.classList.toggle('direk-selected', selected);
                toggle.classList.toggle('direk-rest', !selected);
                if (selected) {
                    menu.hidden = false;
                    toggle.setAttribute('aria-expanded', 'true');
                    const icon = toggle.querySelector('.fa-angle-down');
                    if (icon) icon.classList.add('!rotate-180');
                }
            });
        }
        function navigate(value, mode) {
            const url = publicUrl(value);
            if (url.origin !== window.location.origin) { window.location.assign(url.href); return; }
            const historyMode = mode || 'push';
            if (url.href === committedUrl && !pending && historyMode === 'push') return;
            pending = { url: url.href, mode: historyMode };
            setBusy(true);
            const embedded = new URL(url.href);
            embedded.searchParams.set(parameter, '1');
            if (frame.contentWindow && frame.getAttribute('src')) frame.contentWindow.location.replace(embedded.href);
            else frame.src = embedded.href;
        }
        function syncFromFrame(mode) {
            let child, doc, url;
            try {
                child = frame.contentWindow;
                if (!child || child.location.href === 'about:blank') return;
                doc = child.document;
                url = publicUrl(child.location.href);
                if (doc.body?.dataset.direkShell !== 'true') {
                    window.location.replace(url.href);
                    return;
                }
            } catch (error) {
                setBusy(false);
                errorBox.hidden = false;
                return;
            }
            const historyMode = mode || pending?.mode || 'replace';
            const state = { ...(window.history.state || {}), direkWorkspace: true, url: url.href };
            if (historyMode === 'push' && url.href !== committedUrl) window.history.pushState(state, '', url.href);
            else window.history.replaceState(state, '', url.href);
            committedUrl = url.href;
            document.title = doc.title || 'DIREK App';
            frame.title = (doc.querySelector('h1')?.textContent.trim() || document.title) + ' - workspace content';
            document.body.dataset.direkTheme = doc.body.dataset.direkTheme || 'light';
            document.body.classList.toggle('dark-version', doc.body.classList.contains('dark-version'));
            sidebar?.setAttribute('data-theme', document.body.dataset.direkTheme);
            syncSidebar(doc.body.dataset.direkCurrentRoute || '');
            pending = null;
            frame.dataset.ready = 'true';
            setBusy(false);
        }
        window.DirekWorkspace = { navigate, toggleSidebar, syncFromFrame };
        window.history.replaceState({ ...(window.history.state || {}), direkWorkspace: true, url: committedUrl }, '', committedUrl);
        frame.addEventListener('load', function () { syncFromFrame(); });
        frame.addEventListener('error', function () { setBusy(false); errorBox.hidden = false; });
        sidebar?.addEventListener('click', function (event) {
            const match = eligibleLink(event, document);
            if (!match) return;
            event.preventDefault();
            navigate(match.url.href);
            if (!window.matchMedia('(min-width: 1200px)').matches) document.body.classList.remove('g-sidenav-pinned');
        });
        document.getElementById('iconSidenav')?.addEventListener('click', function () { document.body.classList.remove('g-sidenav-pinned'); });
        window.addEventListener('popstate', function () {
            const url = publicUrl(window.location.href).href;
            if (url !== committedUrl) navigate(url, 'history');
        });
        document.getElementById('direk-frame-retry').addEventListener('click', function () { navigate(pending?.url || committedUrl, pending?.mode || 'replace'); });
        document.getElementById('direk-frame-open').addEventListener('click', function () {
            const url = new URL(pending?.url || committedUrl);
            url.searchParams.set('_direk_standalone', '1');
            window.location.assign(url.href);
        });
        navigate(frame.dataset.initialUrl || window.location.href, 'replace');
        return;
    }
    let owner;
    try {
        owner = window.parent !== window && window.parent.DirekWorkspace;
        if (!owner || window.parent.document.getElementById('direk-workspace-frame')?.contentWindow !== window) return;
    } catch (error) { return; }
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest && event.target.closest('.sidenav-toggler, #iconNavbarSidenav');
        if (toggle) {
            event.preventDefault();
            event.stopImmediatePropagation();
            owner.toggleSidebar();
            return;
        }
        const link = event.target.closest && event.target.closest('a[href]');
        if (link && /\/(?:login|logout)(?:\/|$)/i.test(new URL(link.href, window.location.href).pathname)) {
            link.target = '_top';
        }
        const profile = window.parent.document.getElementById('profile-menu');
        const profileToggle = window.parent.document.getElementById('profile-menu-toggle');
        if (profile) profile.hidden = true;
        profileToggle?.setAttribute('aria-expanded', 'false');
        window.parent.document.getElementById('profile-menu-icon')?.classList.remove('!rotate-180');
    }, true);
    document.addEventListener('click', function (event) {
        const match = eligibleLink(event, document);
        if (!match) return;
        event.preventDefault();
        owner.navigate(match.url.href);
    });
    document.addEventListener('submit', function (event) {
        const form = event.target;
        const action = new URL(event.submitter?.formAction || form.action, window.location.href);
        if (/\/(?:logout|login)(?:\/|$)/i.test(action.pathname) || action.origin !== window.location.origin) form.target = '_top';
    }, true);
    // Keep new-tab links free of the internal frame marker.
    document.querySelectorAll('a[href]').forEach(function (link) {
        try {
            const url = new URL(link.href, window.location.href);
            if (url.origin === window.location.origin && url.searchParams.has(parameter)) link.href = publicUrl(url.href).href;
        } catch (error) { /* Non-navigation anchors remain untouched. */ }
    });
    ['pushState', 'replaceState'].forEach(function (method) {
        const original = window.history[method].bind(window.history);
        window.history[method] = function () {
            const result = original.apply(null, arguments);
            owner.syncFromFrame('replace');
            return result;
        };
    });
    window.addEventListener('popstate', function () { owner.syncFromFrame('replace'); });
    window.addEventListener('hashchange', function () { owner.syncFromFrame('replace'); });
})();
</script>

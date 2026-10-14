@php $authActionsDark = session('user_settings.class_theme', '') === 'dark'; @endphp
<div class="direk-auth-actions" id="navbar" data-theme="{{ $authActionsDark ? 'dark' : 'light' }}">
    <div class="direk-clock" aria-label="Philippine date and time">
        <span class="direk-clock-icon"><i class="fa fa-clock-o" aria-hidden="true"></i></span>
        <div><p id="direk-date"></p><p id="pst-time"></p></div>
    </div>
    <ul class="direk-toolbar-actions">
        <li class="direk-mobile-toggle">
            <button type="button" class="direk-action-button" id="iconNavbarSidenav" aria-label="Toggle sidebar" aria-controls="sidenav-main" title="Toggle sidebar">
                <span class="sidenav-toggler-inner direk-mobile-lines" aria-hidden="true"><i class="sidenav-toggler-line"></i><i class="sidenav-toggler-line"></i><i class="sidenav-toggler-line"></i></span>
            </button>
        </li>
        <li class="nav-item dropdown direk-notification-control">
            <button type="button" class="nav-link direk-action-button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications" title="Notifications"><i class="fa fa-bell-o" aria-hidden="true"></i><span id="spanAlertCounter"></span></button>
            <ul class="dropdown-menu dropdown-menu-end direk-notification-menu" aria-labelledby="dropdownMenuButton">
                <li class="direk-dropdown-heading"><div><strong>Notifications</strong><p>Financial Plan activity</p></div><span><i class="fa fa-bell-o" aria-hidden="true"></i></span></li>
                <li class="direk-notification-list"><ul id="divAlerts"></ul></li>
                <li id="divAlertsNA" class="direk-notification-empty"><span><i class="fa fa-bell-slash-o" aria-hidden="true"></i></span><strong>No notifications</strong><p>You're all caught up.</p></li>
            </ul>
        </li>
        <li class="nav-item dropdown direk-account-control">
            <button type="button" class="nav-link direk-account-button" id="profileMenuButton" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="Profile photo" width="34" height="34">
                <span class="direk-account-label"><strong>{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</strong><span>{{ optional(auth()->user()->role)->name ?? 'User' }}</span></span><i class="fa fa-angle-down" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end direk-account-menu" aria-labelledby="profileMenuButton">
                <li class="direk-account-summary"><strong>{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</strong><span>{{ auth()->user()->email }}</span></li>
                <li><a class="dropdown-item direk-account-item" href="{{ route('user-profile') }}"><span class="direk-account-item-icon"><i class="fa fa-user" aria-hidden="true"></i></span><span><strong>My profile</strong><small>Account details and preferences</small></span><i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
                <li><form role="form" method="POST" action="{{ route('logout') }}" class="direk-logout-form">@csrf<button type="submit" class="dropdown-item direk-account-item direk-logout-button"><span class="direk-account-item-icon"><i class="fa fa-power-off" aria-hidden="true"></i></span><span><strong>Log out</strong><small>End your DIREK session</small></span></button></form></li>
            </ul>
        </li>
    </ul>
</div>
@push('css')
<style>
#navbar.direk-auth-actions{--actions-bg:#fff;--actions-border:#e8ebf2;--actions-heading:#172033;--actions-copy:#637086;--actions-subtle:#f6f7fb;--actions-accent:#635bff;display:flex!important;align-items:center;justify-content:flex-end;gap:18px;flex:0 1 auto;min-width:0;margin-left:auto;color:var(--actions-heading);font-family:inherit}
#navbar.direk-auth-actions[data-theme="dark"]{--actions-bg:#192130;--actions-border:#2c3546;--actions-heading:#eef2fb;--actions-copy:#aab5c8;--actions-subtle:#252d40;--actions-accent:#a19cff}
#navbar.direk-auth-actions p{margin:0!important;color:inherit}
#navbar.direk-auth-actions [hidden]{display:none!important}
#navbar.direk-auth-actions .direk-clock{display:flex;align-items:center;gap:9px;padding-right:16px;border-right:1px solid var(--actions-border);white-space:nowrap}
#navbar.direk-auth-actions .direk-clock-icon{font-size:17px;color:var(--actions-copy)}
#navbar.direk-auth-actions #direk-date{font-size:9px;color:var(--actions-copy);line-height:1.6}
#navbar.direk-auth-actions #pst-time{font-size:12px;font-weight:600;font-variant-numeric:tabular-nums;line-height:1.6;color:var(--actions-heading)}
#navbar.direk-auth-actions .direk-toolbar-actions{display:flex;align-items:center;gap:7px;list-style:none;margin:0!important;padding:0!important}
#navbar.direk-auth-actions .direk-toolbar-actions>li{position:relative;margin:0;padding:0}
#navbar.direk-auth-actions .direk-action-button{position:relative;display:grid!important;place-items:center!important;min-width:40px;width:40px;height:40px;margin:0!important;padding:0!important;background:transparent!important;border:1px solid var(--actions-border)!important;border-radius:11px!important;box-shadow:none!important;color:var(--actions-copy)!important;font-size:16px!important;cursor:pointer;transition:background .15s,color .15s}
#navbar.direk-auth-actions .direk-action-button:hover{background:var(--actions-subtle)!important;color:var(--actions-accent)!important}
#navbar.direk-auth-actions .direk-action-button::after,#navbar.direk-auth-actions .direk-account-button::after{display:none!important}
#navbar.direk-auth-actions .direk-mobile-lines{display:flex!important;flex-direction:column!important;justify-content:space-between!important;width:17px!important;height:12px!important}
#navbar.direk-auth-actions .direk-mobile-lines i{display:block!important;width:17px!important;height:2px!important;margin:0!important;border-radius:2px!important;background:var(--actions-copy)!important;transform:none!important;position:static!important}
#navbar.direk-auth-actions #spanAlertCounter{position:absolute;top:-5px;right:-5px;display:grid;place-items:center;min-width:17px;height:17px;padding:0 4px;background:#ef5a70;color:#fff;border:2px solid var(--actions-bg);border-radius:999px;font-size:8px;font-weight:700;line-height:1}
#navbar.direk-auth-actions #spanAlertCounter:empty{display:none}
#navbar.direk-auth-actions .direk-account-button{display:flex!important;align-items:center;gap:9px;border:0!important;border-radius:11px!important;padding:4px 7px!important;margin:0!important;background:transparent!important;color:var(--actions-heading)!important;box-shadow:none!important;cursor:pointer;transition:background .15s}
#navbar.direk-auth-actions .direk-account-button:hover{background:var(--actions-subtle)!important}
#navbar.direk-auth-actions .direk-account-button>img{width:34px!important;height:34px!important;max-width:none;border-radius:10px;object-fit:cover;border:1px solid var(--actions-border)}
#navbar.direk-auth-actions .direk-account-label{display:flex;flex-direction:column;gap:2px;text-align:left;max-width:135px;min-width:0}
#navbar.direk-auth-actions .direk-account-label strong{font-size:11px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#navbar.direk-auth-actions .direk-account-label>span{font-size:9px;color:var(--actions-copy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#navbar.direk-auth-actions .direk-account-button>.fa-angle-down{font-size:10px;color:var(--actions-copy)}
#navbar.direk-auth-actions .dropdown-menu{background:var(--actions-bg)!important;border:1px solid var(--actions-border)!important;border-radius:14px!important;color:var(--actions-heading)!important;box-shadow:0 14px 45px rgba(24,27,58,.13)!important;padding:0!important;overflow:hidden;max-width:calc(100vw - 30px);margin-top:9px!important}
#navbar.direk-auth-actions .direk-notification-menu{width:360px}
#navbar.direk-auth-actions .direk-dropdown-heading{display:flex;align-items:center;justify-content:space-between;padding:17px 18px;border-bottom:1px solid var(--actions-border)}
#navbar.direk-auth-actions .direk-dropdown-heading strong{font-size:13px;font-weight:650}
#navbar.direk-auth-actions .direk-dropdown-heading p{font-size:10px;color:var(--actions-copy);margin-top:4px!important}
#navbar.direk-auth-actions .direk-dropdown-heading>span{display:grid;place-items:center;width:32px;height:32px;border-radius:10px;background:var(--actions-subtle);color:var(--actions-accent)}
#navbar.direk-auth-actions #divAlerts{max-height:380px;overflow-y:auto;list-style:none;margin:0;padding:0}
#navbar.direk-auth-actions #divAlerts>li{border-color:var(--actions-border)!important}
#navbar.direk-auth-actions .submission-notification-item{white-space:normal!important;background:transparent;color:var(--actions-heading)!important;padding:15px 18px!important}
#navbar.direk-auth-actions .submission-notification-item:hover,#navbar.direk-auth-actions .submission-notification-item.bg-slate-50{background:var(--actions-subtle)!important}
#navbar.direk-auth-actions .submission-notification-item p{color:var(--actions-copy)!important;overflow-wrap:anywhere}
#navbar.direk-auth-actions .submission-notification-item p.font-semibold{color:var(--actions-heading)!important;font-size:12px!important}
#navbar.direk-auth-actions .submission-notification-item .bg-sky-50{background:var(--actions-subtle)!important;color:var(--actions-accent)!important;border:1px solid var(--actions-border)}
#navbar.direk-auth-actions .direk-notification-empty{text-align:center;padding:30px 22px}
#navbar.direk-auth-actions .direk-notification-empty>span{display:grid;place-items:center;width:42px;height:42px;margin:0 auto 13px;background:var(--actions-subtle);color:var(--actions-copy);border-radius:13px}
#navbar.direk-auth-actions .direk-notification-empty strong{display:block;font-size:12px}
#navbar.direk-auth-actions .direk-notification-empty p{font-size:10px;color:var(--actions-copy);margin-top:5px!important}
#navbar.direk-auth-actions .direk-account-menu{width:285px}
#navbar.direk-auth-actions .direk-account-summary{padding:16px 18px;border-bottom:1px solid var(--actions-border)}
#navbar.direk-auth-actions .direk-account-summary strong,#navbar.direk-auth-actions .direk-account-summary>span{display:block;overflow-wrap:anywhere}
#navbar.direk-auth-actions .direk-account-summary strong{font-size:12px;font-weight:600}
#navbar.direk-auth-actions .direk-account-summary>span{font-size:10px;color:var(--actions-copy);margin-top:4px}
#navbar.direk-auth-actions .direk-account-item{display:flex!important;align-items:center;gap:11px;padding:13px 16px!important;background:transparent!important;color:var(--actions-heading)!important;border:0!important;width:100%;text-align:left;white-space:normal!important}
#navbar.direk-auth-actions .direk-account-item:hover{background:var(--actions-subtle)!important}
#navbar.direk-auth-actions .direk-account-item-icon{display:grid;place-items:center;width:32px;height:32px;border-radius:10px;background:var(--actions-subtle);color:var(--actions-accent);flex-shrink:0}
#navbar.direk-auth-actions .direk-account-item strong{display:block;font-size:11px;font-weight:600}
#navbar.direk-auth-actions .direk-account-item small{display:block;font-size:9px;color:var(--actions-copy);margin-top:4px}
#navbar.direk-auth-actions .direk-account-item>.fa-angle-right{margin-left:auto;color:var(--actions-copy);font-size:11px}
#navbar.direk-auth-actions .direk-logout-form{margin:0!important;border-top:1px solid var(--actions-border)}
#navbar.direk-auth-actions .direk-logout-button strong,#navbar.direk-auth-actions .direk-logout-button .direk-account-item-icon{color:#e84e69}
#navbar.direk-auth-actions button:focus-visible,#navbar.direk-auth-actions a:focus-visible{outline:2px solid var(--actions-accent)!important;outline-offset:2px}
@media(min-width:1200px){#navbar.direk-auth-actions .direk-mobile-toggle{display:none}}
@media(max-width:1050px){#navbar.direk-auth-actions .direk-account-label,#navbar.direk-auth-actions .direk-account-button>.fa-angle-down{display:none}#navbar.direk-auth-actions{gap:12px}}
@media(max-width:760px){#navbar.direk-auth-actions .direk-clock{display:none}#navbar.direk-auth-actions{gap:8px}#navbar.direk-auth-actions .direk-toolbar-actions{gap:5px}}
@media(max-width:480px){#navbar.direk-auth-actions{width:100%;justify-content:flex-end;border-top:1px solid var(--actions-border);padding-top:9px}#navbar.direk-auth-actions .direk-mobile-toggle{margin-right:auto}#navbar.direk-auth-actions .direk-toolbar-actions{width:100%}}
@media(prefers-reduced-motion:reduce){#navbar.direk-auth-actions button{transition:none!important}}

/* Share the same surface, accent and edge palette as the title partial. */
#navbar.direk-auth-actions{--actions-bg:#f8f6fd;--actions-border:#ddd7ec;--actions-heading:#302842;--actions-copy:#706482;--actions-subtle:#ece7f5;--actions-accent:#7160ca;--actions-edge:#d9d0e8;--actions-highlight:rgba(255,255,255,.9)}
#navbar.direk-auth-actions[data-theme="dark"]{--actions-bg:#26213b;--actions-border:#514465;--actions-heading:#f4f0ff;--actions-copy:#c1b6d7;--actions-subtle:#39314f;--actions-accent:#c2acff;--actions-edge:#161126;--actions-highlight:rgba(239,221,255,.1)}
#navbar.direk-auth-actions .direk-clock{padding:9px 13px;border:1px solid var(--actions-border);border-radius:12px;background:linear-gradient(145deg,var(--actions-bg),var(--actions-subtle));box-shadow:inset 0 1px 0 var(--actions-highlight),0 3px 0 var(--actions-edge);gap:10px}
#navbar.direk-auth-actions .direk-clock-icon{color:var(--actions-accent);background:transparent;box-shadow:none;border:0}
#navbar.direk-auth-actions #direk-date{font-size:10px;line-height:1.5}
#navbar.direk-auth-actions #pst-time{font-size:13px;letter-spacing:.03em}
#navbar.direk-auth-actions .direk-action-button{color:var(--actions-accent)!important;background:linear-gradient(145deg,var(--actions-bg),var(--actions-subtle))!important;box-shadow:inset 0 1px 0 var(--actions-highlight),0 3px 0 var(--actions-edge)!important}
#navbar.direk-auth-actions .direk-action-button:hover{background:var(--actions-subtle)!important;box-shadow:inset 0 1px 0 var(--actions-highlight),0 3px 0 var(--actions-edge),0 7px 15px rgba(106,80,168,.12)!important}
#navbar.direk-auth-actions .direk-mobile-lines i{background:var(--actions-accent)!important}
#navbar.direk-auth-actions .direk-account-button{padding:6px 9px!important;border:1px solid var(--actions-border)!important;background:linear-gradient(145deg,var(--actions-bg),var(--actions-subtle))!important;box-shadow:inset 0 1px 0 var(--actions-highlight),0 3px 0 var(--actions-edge)!important}
#navbar.direk-auth-actions .direk-account-button:hover{background:var(--actions-subtle)!important}
#navbar.direk-auth-actions .direk-account-button>img{border-radius:10px;border-color:var(--actions-border)}
#navbar.direk-auth-actions .dropdown-menu{box-shadow:inset 0 1px 0 var(--actions-highlight),0 4px 0 var(--actions-edge),0 18px 45px rgba(24,16,46,.22)!important}
#navbar.direk-auth-actions .direk-account-summary,#navbar.direk-auth-actions .direk-dropdown-heading{background:linear-gradient(145deg,var(--actions-bg),var(--actions-subtle))}
#navbar.direk-auth-actions .direk-account-item-icon,#navbar.direk-auth-actions .direk-dropdown-heading>span,#navbar.direk-auth-actions .direk-notification-empty>span{background:transparent!important;border:0!important;box-shadow:none!important;color:var(--actions-accent)}
#navbar.direk-auth-actions .direk-logout-button .direk-account-item-icon{color:#e84e69!important}
#navbar.direk-auth-actions .submission-notification-item .bg-sky-50{background:transparent!important;border:0!important;box-shadow:none!important}
#navbar.direk-auth-actions .direk-account-label strong{max-width:140px}
@media(max-width:760px){#navbar.direk-auth-actions .direk-clock{display:none}}
@media(max-width:480px){#navbar.direk-auth-actions .direk-account-button{padding:4px 6px!important}#navbar.direk-auth-actions .dropdown-menu{max-width:calc(100vw - 24px)}}

</style>
@endpush
@push('js')
<script>
    $(document).ready(function () {
        updateDirekClock();
        loadSubmissionNotifications();
        setInterval(updateDirekClock, 1000);
        setInterval(loadSubmissionNotifications, 30000);
    });
    // Display current Philippine date and time.
    function updateDirekClock() {
        var now = new Date();
        var dateElement = document.getElementById('direk-date');
        var timeElement = document.getElementById('pst-time');
        if (!dateElement || !timeElement) {
            return;
        }
        try {
            dateElement.textContent = new Intl.DateTimeFormat(
                'en-PH',
                {
                    timeZone: 'Asia/Manila',
                    weekday: 'short',
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                }
            ).format(now);
            timeElement.textContent = new Intl.DateTimeFormat(
                'en-PH',
                {
                    timeZone: 'Asia/Manila',
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                }
            ).format(now);
        } catch (error) {
            timeElement.textContent = now.toLocaleTimeString();
        }
    }
    // Load Financial Plan submission notifications.
    function loadSubmissionNotifications() {
        $.get('{{ route('notifications.submissions.index') }}')
            .done(function (response) {
                var notifications = response.notifications || [];
                var unreadCount = response.unread_count || 0;
                $('#spanAlertCounter').text(
                    unreadCount > 0 ? unreadCount : ''
                );
                $('#divAlerts').empty();
                if (notifications.length === 0) {
                    $('#divAlertsNA').removeAttr('hidden');
                    return;
                }
                $('#divAlertsNA').attr('hidden', true);
                $.each(
                    notifications,
                    function (index, notification) {
                        $('#divAlerts').append(
                            submissionNotificationHtml(notification)
                        );
                    }
                );
            })
            .fail(function () {
    // Keep navbar usable if notification loading fails.
                $('#spanAlertCounter').text('');
            });
    }
    // Build a Financial Plan notification item.
    function submissionNotificationHtml(notification) {
        var unreadClass = notification.is_read
            ? ''
            : ' bg-slate-50';
        var url = notification.url || 'javascript:;';
        var remarks = notification.remarks
            ? '<p class="mb-0 mt-1 text-xs text-slate-600">' +
                '<strong>Remarks:</strong> ' +
                notificationEscape(notification.remarks) +
              '</p>'
            : '';
        return '' +
            '<li class="border-bottom">' +
                '<a ' +
                    'class="dropdown-item submission-notification-item' +
                        unreadClass +
                        ' px-4 py-3" ' +
                    'href="' + notificationEscape(url) + '" ' +
                    'data-notification-id="' +
                        notificationEscape(notification.id) +
                    '">' +
                    '<div class="flex items-start gap-3">' +
                        '<div class="flex h-9 w-9 shrink-0 ' +
                            'items-center justify-center rounded-lg ' +
                            'bg-sky-50 text-sky-600">' +
                            '<i class="fa fa-file-text-o"></i>' +
                        '</div>' +
                        '<div class="min-w-0 flex-1">' +
                            '<p class="mb-0 text-sm font-semibold ' +
                                'text-slate-800 text-wrap">' +
                                notificationEscape(
                                    notification.title
                                ) +
                            '</p>' +
                            '<p class="mb-0 mt-1 text-xs ' +
                                'leading-5 text-slate-500 text-wrap">' +
                                notificationEscape(
                                    notification.message
                                ) +
                            '</p>' +
                            remarks +
                            '<p class="mb-0 mt-2 text-[10px] ' +
                                'text-slate-400">' +
                                '<i class="fa fa-clock-o me-1"></i>' +
                                notificationEscape(
                                    notification.created_at
                                ) +
                            '</p>' +
                        '</div>' +
                    '</div>' +
                '</a>' +
            '</li>';
    }
    // Escape values before adding notification content to HTML.
    function notificationEscape(value) {
        return $('<div/>')
            .text(value == null ? '' : String(value))
            .html();
}
    // Mark notification as read before navigation.
    $(document).on(
        'click',
        '.submission-notification-item',
        function () {
            var notificationId = $(this).data('notification-id');
            if (!notificationId) {
                return;
            }
            var readUrl =
                '{{ route(
                    'notifications.submissions.read',
                    ['notification' => '__NOTIFICATION_ID__']
                ) }}';
            $.post(
                readUrl.replace(
                    '__NOTIFICATION_ID__',
                    notificationId
                ),
                {
                    _token: '{{ csrf_token() }}'
                }
            );
        }
    );
</script>
@endpush

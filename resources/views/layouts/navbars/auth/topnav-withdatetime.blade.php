{{-- DIREK Authenticated Navbar Actions --}}

<div
    class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4"
    id="navbar"
>
    {{-- Philippine Date / Time --}}
    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
        <div class="hidden text-right sm:block">
            <p
                id="direk-date"
                class="mb-0 text-[10px] font-semibold uppercase
                       tracking-wide text-white/60"
            ></p>

            <p
                id="pst-time"
                class="mb-0 mt-0.5 text-sm font-semibold text-white"
            ></p>
        </div>
    </div>

    <ul class="navbar-nav align-items-center justify-content-end gap-1">

        {{-- Mobile Sidebar Toggle --}}
        <li class="nav-item d-xl-none d-flex align-items-center px-2">
            <a
                href="javascript:;"
                class="nav-link p-0 text-white"
                id="iconNavbarSidenav"
                aria-label="Toggle sidebar"
                title="Toggle sidebar"
            >
                <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line bg-white"></i>
                    <i class="sidenav-toggler-line bg-white"></i>
                    <i class="sidenav-toggler-line bg-white"></i>
                </div>
            </a>
        </li>

        {{-- Notifications --}}
        <li class="nav-item position-relative d-flex align-items-center px-2">
            <a
                href="javascript:;"
                class="nav-link position-relative p-0 text-white"
                id="dropdownMenuButton"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Notifications"
                title="Notifications"
            >
                <span
                    class="inline-flex h-9 w-9 items-center
                           justify-center rounded-xl text-white
                           transition hover:bg-white/10"
                >
                    <i
                        class="fa fa-bell-o text-base"
                        aria-hidden="true"
                    ></i>
                </span>

                <span
                    id="spanAlertCounter"
                    class="position-absolute
                           inline-flex min-w-[17px] items-center
                           justify-center rounded-full bg-rose-500
                           px-1 text-[9px] font-bold leading-[17px]
                           text-white shadow-sm"
                    style="top: -3px; right: -4px;"
                ></span>
            </a>

            <ul
                class="dropdown-menu dropdown-menu-end
                       overflow-hidden border-0 p-0 shadow-lg
                       {{ isset($class_theme) && $class_theme == 'dark'
                            ? 'bg-default'
                            : '' }}"
                aria-labelledby="dropdownMenuButton"
                style="width: 360px; max-width: calc(100vw - 30px);"
            >
                {{-- Notification Header --}}
                <li>
                    <div
                        class="flex items-center justify-between
                               border-bottom px-4 py-3"
                    >
                        <div>
                            <p
                                class="mb-0 text-sm font-bold
                                       {{ isset($class_theme) && $class_theme == 'dark'
                                            ? 'text-white'
                                            : 'text-slate-800' }}"
                            >
                                Notifications
                            </p>

                            <p
                                class="mb-0 mt-0.5 text-[11px]
                                       {{ isset($class_theme) && $class_theme == 'dark'
                                            ? 'text-white/60'
                                            : 'text-slate-500' }}"
                            >
                                Financial Plan activity
                            </p>
                        </div>

                        <span
                            class="inline-flex h-8 w-8 items-center
                                   justify-center rounded-lg
                                   bg-sky-50 text-sky-600"
                        >
                            <i
                                class="fa fa-bell-o"
                                aria-hidden="true"
                            ></i>
                        </span>
                    </div>
                </li>

                {{-- Notification Items --}}
                <div
                    id="divAlerts"
                    style="max-height: 380px; overflow-y: auto;"
                ></div>

                {{-- Empty State --}}
                <div
                    id="divAlertsNA"
                    class="px-5 py-8 text-center"
                >
                    <div
                        class="mx-auto flex h-11 w-11 items-center
                               justify-center rounded-xl bg-slate-100
                               text-slate-400"
                    >
                        <i
                            class="fa fa-bell-slash-o"
                            aria-hidden="true"
                        ></i>
                    </div>

                    <p
                        class="mb-0 mt-3 text-sm font-semibold
                               {{ isset($class_theme) && $class_theme == 'dark'
                                    ? 'text-white'
                                    : 'text-slate-700' }}"
                    >
                        No Notifications
                    </p>

                    <p
                        class="mb-0 mt-1 text-xs
                               {{ isset($class_theme) && $class_theme == 'dark'
                                    ? 'text-white/60'
                                    : 'text-slate-500' }}"
                    >
                        You're all caught up.
                    </p>
                </div>
            </ul>
        </li>

        {{-- User Profile --}}
        <li class="nav-item position-relative d-flex align-items-center ps-1">
            <a
                href="javascript:;"
                class="nav-link p-0 text-white"
                id="profileMenuButton"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Account menu"
            >
                <div
                    class="flex items-center gap-2 rounded-xl
                           px-1.5 py-1 transition hover:bg-white/10"
                >
                    <img
                        src="{{ auth()->user()->avatarUrl() }}"
                        class="h-8 w-8 rounded-lg border
                               border-white/30 object-cover"
                        alt="Profile photo"
                    >

                    <div class="hidden text-left lg:block">
                        <p
                            class="mb-0 max-w-[130px] truncate
                                   text-xs font-semibold text-white"
                        >
                            {{ auth()->user()->firstname }}
                            {{ auth()->user()->lastname }}
                        </p>

                        <p
                            class="mb-0 max-w-[130px] truncate
                                   text-[10px] text-white/60"
                        >
                            {{ optional(auth()->user()->role)->name ?? 'User' }}
                        </p>
                    </div>

                    <i
                        class="fa fa-angle-down hidden text-xs
                               text-white/60 lg:inline"
                        aria-hidden="true"
                    ></i>
                </div>
            </a>

            <ul
                class="dropdown-menu dropdown-menu-end
                       overflow-hidden border-0 p-2 shadow-lg
                       {{ isset($class_theme) && $class_theme == 'dark'
                            ? 'bg-default'
                            : '' }}"
                aria-labelledby="profileMenuButton"
                style="min-width: 220px;"
            >
                {{-- Profile Summary --}}
                <li>
                    <div class="border-bottom px-3 py-2">
                        <p
                            class="mb-0 truncate text-sm font-semibold
                                   {{ isset($class_theme) && $class_theme == 'dark'
                                        ? 'text-white'
                                        : 'text-slate-800' }}"
                        >
                            {{ auth()->user()->firstname }}
                            {{ auth()->user()->lastname }}
                        </p>

                        <p
                            class="mb-0 mt-0.5 truncate text-[11px]
                                   {{ isset($class_theme) && $class_theme == 'dark'
                                        ? 'text-white/60'
                                        : 'text-slate-500' }}"
                        >
                            {{ auth()->user()->email }}
                        </p>
                    </div>
                </li>

                {{-- My Profile --}}
                <li class="mt-2">
                    <a
                        class="dropdown-item border-radius-md
                               flex items-center gap-3 px-3 py-2"
                        href="{{ route('user-profile') }}"
                    >
                        <span
                            class="inline-flex h-8 w-8 shrink-0
                                   items-center justify-center
                                   rounded-lg bg-sky-50 text-sky-600"
                        >
                            <i
                                class="fa fa-user"
                                aria-hidden="true"
                            ></i>
                        </span>

                        <div>
                            <p
                                class="mb-0 text-sm font-semibold
                                       {{ isset($class_theme) && $class_theme == 'dark'
                                            ? 'text-white'
                                            : 'text-slate-700' }}"
                            >
                                My Profile
                            </p>

                            <p
                                class="mb-0 text-[10px]
                                       {{ isset($class_theme) && $class_theme == 'dark'
                                            ? 'text-white/60'
                                            : 'text-slate-500' }}"
                            >
                                Account & security
                            </p>
                        </div>
                    </a>
                </li>

                {{-- Logout --}}
                <li>
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        id="logout-form"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="dropdown-item border-0
                                   border-radius-md flex w-full
                                   items-center gap-3 bg-transparent
                                   px-3 py-2 text-left"
                        >
                            <span
                                class="inline-flex h-8 w-8 shrink-0
                                       items-center justify-center
                                       rounded-lg bg-rose-50 text-rose-600"
                            >
                                <i
                                    class="fa fa-power-off"
                                    aria-hidden="true"
                                ></i>
                            </span>

                            <div>
                                <p
                                    class="mb-0 text-sm font-semibold
                                           text-rose-600"
                                >
                                    Log out
                                </p>

                                <p
                                    class="mb-0 text-[10px]
                                           {{ isset($class_theme) && $class_theme == 'dark'
                                                ? 'text-white/60'
                                                : 'text-slate-500' }}"
                                >
                                    End your DIREK session
                                </p>
                            </div>
                        </button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</div>

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

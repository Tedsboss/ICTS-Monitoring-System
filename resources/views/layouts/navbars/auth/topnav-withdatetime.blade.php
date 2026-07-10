
<div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
  <div class="ms-md-auto pe-md-3 d-flex align-items-center">
      <span class="" id="pst-time" style="font-size: 0.90rem; color: white;"></span>
  </div>
  <ul class="navbar-nav justify-content-end">
    <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
      <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
        <div class="sidenav-toggler-inner">
          <i class="sidenav-toggler-line bg-white"></i>
          <i class="sidenav-toggler-line bg-white"></i>
          <i class="sidenav-toggler-line bg-white"></i>
        </div>
      </a>
    </li>
    <li class="nav-item position-relative pe-2 d-flex align-items-center px-3">
      <a href="javascript:;" class="nav-link text-white p-0" id="profileMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa cursor-pointer">
          <img src="{{ auth()->user()->avatarUrl() }}" class="avatar rounded-circle avatar-sm" style="object-fit: cover !important;" alt="...">
        </i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end px-2 py-2 me-sm-n4 shadow-lg {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}" aria-labelledby="profileMenuButton">
        <li class="mb-2 align-middle">
          <a class="dropdown-item border-radius-md" href="{{ route('user-profile') }}">
            {{-- <div class="col-sm-12"> --}}
              <div class="row">
                <div class="col-4 pe-0">
                  <i class="fa fa-user fixed-plugin-button-nav cursor-pointer align-middle"></i>
                </div>
                <div class="col-8 ps-0">
                  <h6 class="text-sm font-weight-normal mb-1 ">
                    <span class="font-weight-bold align-middle">My Profile</span> 
                  </h6>
                </div>
              </div>
            {{-- </div> --}}
          </a>
        </li>
        <li class="mb-2 align-middle">
          <form role="form" method="post" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <a class="dropdown-item border-radius-md" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link text-white font-weight-bold px-0">
              {{-- <div class="col-sm-12"> --}}
                <div class="row">
                  <div class="col-4 pe-0">
                    <i class="fa fa-power-off fixed-plugin-button-nav cursor-pointer align-middle"></i>
                  </div>
                  <div class="col-8 ps-0">
                    <h6 class="text-sm font-weight-normal mb-1 ">
                      <span class="font-weight-bold align-middle">Log out</span> 
                    </h6>
                  </div>
                </div>
              {{-- </div> --}}
            </a>
          </form>
        </li>
      </ul>
    </li>


    {{-- <li class="nav-item position-relative pe-2 d-flex align-items-center px-3">
        <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-bell fa-lg cursor-pointer"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4 shadow-sm" aria-labelledby="dropdownMenuButton">
            <li class="mb-2">
                <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                        <div class="my-auto">
                            <img src="/assets/img/team-2.jpg" class="avatar avatar-sm  me-3 "
                                alt="user image">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="text-sm font-weight-normal mb-1">
                                <span class="font-weight-bold">New message</span> from Laur
                            </h6>
                            <p class="text-xs text-secondary mb-0">
                                <i class="fa fa-clock me-1"></i>
                                13 minutes ago
                            </p>
                        </div>
                    </div>
                </a>
            </li>
            <li class="mb-2">
                <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                        <div class="my-auto">
                            <img src="/assets/img/small-logos/logo-spotify.svg"
                                class="avatar avatar-sm bg-gradient-dark  me-3 " alt="logo spotify">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="text-sm font-weight-normal mb-1">
                                <span class="font-weight-bold">New album</span> by Travis Scott
                            </h6>
                            <p class="text-xs text-secondary mb-0">
                                <i class="fa fa-clock me-1"></i>
                                1 day
                            </p>
                        </div>
                    </div>
                </a>
            </li>
            <li>
                <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                        <div class="avatar avatar-sm bg-gradient-secondary  me-3  my-auto">
                            <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1"
                                xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>credit-card</title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF"
                                        fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g transform="translate(453.000000, 454.000000)">
                                                <path class="color-background"
                                                    d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z"
                                                    opacity="0.593633743"></path>
                                                <path class="color-background"
                                                    d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="text-sm font-weight-normal mb-1">
                                Payment successfully completed
                            </h6>
                            <p class="text-xs text-secondary mb-0">
                                <i class="fa fa-clock me-1"></i>
                                2 days
                            </p>
                        </div>
                    </div>
                </a>
            </li>
        </ul>
    </li> --}}


    @php
      $alertsCount = 0;
      $inquiryCount = 0;
      $newRegistrationCount = 0;
      $inquiryTimeStamp = null;
      $newRegistrationTimeStamp = null;
    @endphp

    <li class="nav-item position-relative pe-2 d-flex align-items-center px-3">
      <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-bell fa-lg cursor-pointer"></i>
        <span class="badge badge-sm bg-danger" id="spanAlertCounter"></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end px-2 pt-3 pb-1 me-sm-n4 shadow-lg {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}" aria-labelledby="dropdownMenuButton">
        <div id="divAlerts">
          @if ($inquiryCount > 0)
            @include('alerts.new-inquiries', ['inquiryCount' => $inquiryCount, 'inquiryTimeStamp' => $inquiryTimeStamp])
          @endif
          {{-- @if ($newUserCount > 0)
            @include('alerts.new-users', ['newUserCount' => $newUserCount, 'newUserTimeStamp' => $newUserTimeStamp])
          @endif --}}
          @if ($newRegistrationCount > 0)
            @include('alerts.new-registration')
          @endif
        </div>
        <div id="divAlertsNA" class="text-center" @if($alertsCount > 0) hidden @endif>
          <p class="text-xs text-secondary mb-2">
            No Available Notification
          </p>
        </div>
      </ul>
    </li>
  </ul>
</div>

@push('js')
  <script>
    $(document).ready(function () {
      loadSubmissionNotifications();
      setInterval(loadSubmissionNotifications, 30000);
    });

    function loadSubmissionNotifications() {
      $.get('{{ route('notifications.submissions.index') }}')
        .done(function (response) {
          var notifications = response.notifications || [];
          var unreadCount = response.unread_count || 0;

          $('#spanAlertCounter').text(unreadCount > 0 ? unreadCount : '');
          $('#divAlerts').empty();

          if (notifications.length == 0) {
            $('#divAlertsNA').removeAttr('hidden');
            return;
          }

          $('#divAlertsNA').attr('hidden', true);

          $.each(notifications, function (index, notification) {
            $('#divAlerts').append(submissionNotificationHtml(notification));
          });
        });
    }

    function submissionNotificationHtml(notification) {
      var readClass = notification.is_read ? '' : ' bg-gray-100';
      var url = notification.url || 'javascript:;';

      return '' +
        '<li class="mb-2">' +
          '<a class="dropdown-item border-radius-md submission-notification-item' + readClass + '" href="' + url + '" data-notification-id="' + notification.id + '">' +
            '<div class="d-flex py-1">' +
              '<div class="my-auto">' +
                '<div class="avatar avatar-sm bg-gradient-info me-3 d-flex align-items-center justify-content-center">' +
                  '<i class="fa fa-file-text text-white"></i>' +
                '</div>' +
              '</div>' +
              '<div class="d-flex flex-column justify-content-center" style="min-width: 220px; max-width: 280px;">' +
                '<h6 class="text-sm font-weight-normal mb-1 text-wrap">' +
                  '<span class="font-weight-bold">' + notificationEscape(notification.title) + '</span>' +
                '</h6>' +
                '<p class="text-xs text-secondary mb-1 text-wrap">' + notificationEscape(notification.message) + '</p>' +
                (notification.remarks ? '<p class="text-xs text-dark mb-1 text-wrap"><strong>Remarks:</strong> ' + notificationEscape(notification.remarks) + '</p>' : '') +
                '<p class="text-xs text-secondary mb-0">' +
                  '<i class="fa fa-clock me-1"></i>' + notificationEscape(notification.created_at) +
                '</p>' +
              '</div>' +
            '</div>' +
          '</a>' +
        '</li>';
    }

    function notificationEscape(value) {
      return $('<div/>').text(value || '').html();
    }

    $(document).on('click', '.submission-notification-item', function () {
      var notificationId = $(this).data('notification-id');

      if (!notificationId) {
        return;
      }

      $.post('{{ route('notifications.submissions.read', ['notification' => '__NOTIFICATION_ID__']) }}'.replace('__NOTIFICATION_ID__', notificationId), {
        _token: '{{ csrf_token() }}'
      });
    });
  </script>
@endpush

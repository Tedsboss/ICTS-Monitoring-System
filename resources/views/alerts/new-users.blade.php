<li class="mb-2" id="alert_newuser">
  <a class="dropdown-item border-radius-md" href="{{ route('userapprovals.index') }}">
    <div class="d-flex py-1">
      <div class="my-auto">
        <i class="fa fa-user-circle-o fa-2x align-middle me-3"></i>
      </div>
      <div class="d-flex flex-column justify-content-center w-100">
        <div class="row">
          <h6 class="text-sm font-weight-normal mb-1">
            <span class="font-weight-bold">You have <span id="alert_newuser_count" class="text-danger">{{ $newUserCount }}</span> new <span id="alert_newuser_noun">{{ $newUserCount > 1 ? 'users' : 'user' }}</span> for approval</span>
          </h6>
        </div>
        <div class="row">
          <div class="col-6">
            <p class="text-xs text-secondary fst-italic mb-0 opacity-3">
              Click for more
            </p>
          </div>
          <div class="col-6 justify-content-end text-end">
            <p class="text-xs text-secondary mb-0">
              <i class="fa fa-clock-o me-1"></i>
              <span id="alert_newuser_timestamp">{{ Carbon\Carbon::parse($newUserTimeStamp)->diffForHumans() }}</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </a>
</li>
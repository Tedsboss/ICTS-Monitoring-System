<li class="mb-2" id="alert_comment">
  <a class="dropdown-item border-radius-md" href="{{ $newCommentUrl }}">
    <div class="d-flex py-1">
      <div class="my-auto">
        <i class="fa fa-commenting-o fa-2x align-middle me-3"></i>
      </div>
      <div class="d-flex flex-column justify-content-center w-100">
        <div class="row">
          <p class="text-sm font-weight-normal mb-1">
            <span class="font-weight-bold">You have <span id="alert_comment_count" class="text-danger">{{ $newCommentCount }}</span> unread <span id="alert_comment_noun">{{ $newCommentCount > 1 ? 'comments' : 'comment' }}</span></span>
          </p>
        </div>
        <div class="row">
          <div class="col-6">
            <p class="text-xs text-secondary mb-0 pe-4">
              {{ $newCommentRefId }}
            </p>
          </div>
          <div class="col-6 justify-content-end text-end">
            <p class="text-xs text-secondary mb-0">
              <i class="fa fa-clock-o me-1"></i>
              <span id="alert_comment_timestamp">{{ Carbon\Carbon::parse($newCommentTimeStamp)->diffForHumans() }}</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </a>
</li>
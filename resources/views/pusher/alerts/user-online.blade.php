<li class="mb-2" id="{{ $element_id }}">
  <a class="dropdown-item border-radius-md" href="{{ isset($ticket_id) ? route('icts.edit', $ticket_id) : 'javascript:;' }}">
    <div class="d-flex py-1">
      <div class="my-auto">
        <i class="fa fa-user-circle-o fa-2x align-middle me-3"></i>
      </div>
      <div class="d-flex flex-column justify-content-center w-100">
        <div class="row">
          <h6 class="text-sm font-weight-normal mb-1">
            <span class="font-weight-bold">User is currently online</span> - {{ $description }}
          </h6>
        </div>
        <div class="row">
          <div class="col-6">
            <p class="text-xs text-secondary mb-0">
              {{ $ticket_code }}
            </p>
          </div>
          <div class="col-6 justify-content-end text-end">
            <p class="text-xs text-secondary mb-0">
              <i class="fa fa-clock-o me-1"></i>
              {{ $timestamp }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </a>
</li>
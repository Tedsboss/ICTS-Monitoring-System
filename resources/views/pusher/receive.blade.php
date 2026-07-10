<div class="d-flex m-2">
  <div class="flex-shrink-0">
    <a href="javascript:;">
      <div class="position-relative avatar">
        <img src="{{ $image }}" class="img-fluid rounded-circle" alt="">
        <span class="position-absolute bottom-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle" {{ $hidden }}>
          <span class="visually-hidden">New alerts</span>
        </span>
      </div>
    </a>
  </div>
  <div class="flex-grow-1 ms-2 mb-2">
    <div class="card card-text d-inline-block text-sm p-2 shadow-sm me-6 bg-chat-from">{!! $message !!}</div>
    <div class="d-flex justify-content-start mt-2 ms-2">
      <span class="text-xs text-light2">{{ $timestamp }}</span>
    </div>
  </div>
</div>
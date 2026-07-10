<div class="d-flex m-2">
  <div class="flex-grow-1 me-2 mb-2 text-end">
    <div class="card card-text d-inline-block text-sm p-2 text-start shadow-sm ms-6 bg-chat-to">{{ $message }}</div>
    <div class="d-flex justify-content-end mt-2 me-2">
      <span class="text-xs text-light2">{{ $timestamp }}</span>
    </div>
  </div>
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
</div>
<div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none pe-5">
  <a href="javascript:;" class="nav-link p-0">
      <div class="sidenav-toggler-inner">
          <i class="sidenav-toggler-line bg-white"></i>
          <i class="sidenav-toggler-line bg-white"></i>
          <i class="sidenav-toggler-line bg-white"></i>
      </div>
  </a>
</div>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm">
            <a class="text-white" href="{{ route('home') }}">
                <i class="fa fa-home"></i>
            </a>
        </li>

        @foreach (isset($links) ? $links : [] as $link)
            <li class="breadcrumb-item text-sm text-white"><a class="opacity-5 text-white" href="{{ $link['url'] }}">{{ $link['name'] }}</a></li>
        @endforeach

        {{-- <li class="breadcrumb-item text-sm text-white"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li> --}}
        <li class="breadcrumb-item text-sm text-white active" aria-current="page">{{ isset($subtitle) ? $subtitle : $title }}</li>
    </ol>
    <h6 class="font-weight-bolder mb-0 text-white">{{ $title }}</h6>
</nav>

@extends('layouts.app', ['class' => 'error-page'])

@section('content')
<div class="container position-sticky z-index-sticky top-0">
    <div class="row">
        <div class="col-12">
            @include('layouts.navbars.guest.topnav', [
                'classes' => 'mt-4 blur border-radius-lg top-0 z-index-3 shadow py-2 start-0 end-0 mx-4',
            ])
        </div>
    </div>
</div>
<main class="main-content  mt-0">
    <div class="page-header min-vh-100" style="background-image: url('/assets/img/neda/header.jpg');">
        <span class="mask bg-gradient-light opacity-9"></span>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-md-7 mx-auto text-center">
                    <h1 class="display-1 text-bolder text-primary">User not authorized</h1>
                    <h2>The client does not have access to the system</h2>
                    <p class="lead">Please contact system administrator to create/allow your account.</p>
                    <a href="{{ route('login') }}" class="btn bg-gradient-dark mt-4">Go back to Login page</a>
                </div>
            </div>
        </div>
    </div>
</main>
@include('layouts.footers.auth.desc-footer')
@endsection
@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  @include('errors.layout', [
    'error_code' => '419', 
    'error_name' => 'Page Expired', 
    'error_description' => 'Your session has expired or the security token is invalid. Please refresh the page or log in again. If you think this is an error, please contact us.'
  ])
@endsection
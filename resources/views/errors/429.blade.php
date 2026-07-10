@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  @include('errors.layout', [
    'error_code' => '429', 
    'error_name' => 'Too Many Requests', 
    'error_description' => 'You have sent too many requests in a short period of time. Please slow down and try again later. If you think this is an error, please contact us.'
  ])
@endsection
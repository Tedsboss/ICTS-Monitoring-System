@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  @include('errors.layout', [
    'error_code' => '503', 
    'error_name' => 'Service Unavailable', 
    'error_description' => 'The server is temporarily unavailable due to maintenance or high load. Please try again in a few moments.',
  ])
@endsection
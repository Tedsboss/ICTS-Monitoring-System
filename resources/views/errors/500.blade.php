@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  @include('errors.layout', [
    'error_code' => '500', 
    'error_name' => 'Internal Server Error', 
    'error_description' => 'An unexpected error occurred on the server. Please try again later. If you think this is an error, please contact us.'
  ])
@endsection
@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  @include('errors.layout', [
    'error_code' => '404', 
    'error_name' => 'Not Found', 
    'error_description' => 'The requested resource could not be found. If you think this is an error, please contact us.'
  ])
@endsection
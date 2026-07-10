@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  @include('errors.layout', [
    'error_code' => '403', 
    'error_name' => 'Forbidden', 
    'error_description' => 'You do not have permission to access this resource. If you think this is an error, please contact us.'
  ])
@endsection
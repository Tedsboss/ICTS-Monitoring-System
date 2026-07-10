@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  @include('errors.layout', [
    'error_code' => '402', 
    'error_name' => 'Payment Required', 
    'error_description' => 'Your subscription has expired or is inactive. If you think this is an error, please contact us.'
  ])
@endsection
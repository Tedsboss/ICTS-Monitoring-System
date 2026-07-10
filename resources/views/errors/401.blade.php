@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  @include('errors.layout', [
    'error_code' => '401', 
    'error_name' => 'Unauthorized', 
    'error_description' => 'The request requires valid authentication credentials. If you think this is an error, please contact us.'
  ])
@endsection
@extends('layouts.app')

@section('content')
@include('allocations.form', ['editing' => true])
@endsection

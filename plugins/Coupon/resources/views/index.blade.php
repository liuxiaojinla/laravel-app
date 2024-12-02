@extends('activity::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('activity.name') !!}</p>
@endsection

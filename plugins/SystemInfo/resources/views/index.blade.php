@extends('systeminfo::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('systeminfo.name') !!}</p>
@endsection

@extends('vcard::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('vcard.name') !!}</p>
@endsection

@extends('errors.status')
@section('title', 'Form expired — PortGen')
@section('heading', 'This form has expired')
@section('message', 'Your session expired. Log in again, then return to the page you were using. Your saved draft may still be available in this browser.')
@section('action')
    <a class="button-primary" href="{{ route('login', ['intended' => request()->getRequestUri()]) }}">Log in and continue</a>
@endsection

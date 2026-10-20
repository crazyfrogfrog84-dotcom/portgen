@extends('errors.status')
@section('title', 'Private link required — PortGen')
@section('heading', 'This private link is not valid')
@section('message', 'This private link is invalid or the portfolio now belongs to another account. If you own the account, log in to manage it.')
@section('action')
    <a class="button-primary" href="{{ route('login') }}">Log in</a>
@endsection

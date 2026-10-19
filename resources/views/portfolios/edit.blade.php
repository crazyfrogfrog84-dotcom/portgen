@extends('layouts.app')

@section('title', 'Edit your portfolio — PortGen')

@section('content')
    <section class="mx-auto max-w-3xl space-y-6">
        <p class="text-sm font-semibold uppercase tracking-wide">Private portfolio management</p>
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Edit your portfolio</h1>
        @include('portfolios.form', ['action' => $updateUrl, 'formMethod' => 'PUT', 'backUrl' => URL::signedRoute('manage.show', ['portfolio' => $portfolio]), 'submitLabel' => 'Save changes'])
    </section>
@endsection

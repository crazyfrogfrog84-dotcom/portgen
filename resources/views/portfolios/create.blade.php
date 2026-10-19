@extends('layouts.app')

@section('title', 'Create your portfolio — PortGen')

@section('content')
    <section class="mx-auto max-w-3xl space-y-6">
        <p class="text-sm font-semibold uppercase tracking-wide">Step 1 of 3 · Your information</p>
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Create your portfolio</h1>
        <p class="max-w-2xl text-lg text-[var(--simple-muted)]">Add the details you want people to see. You can leave education, experience, projects, and links empty.</p>
        @include('portfolios.form', ['portfolio' => null, 'action' => route('portfolios.store'), 'formMethod' => 'POST', 'backUrl' => route('portfolios.create'), 'submitLabel' => 'Next: choose a template'])
    </section>
@endsection

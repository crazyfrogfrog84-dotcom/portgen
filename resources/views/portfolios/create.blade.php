@extends('layouts.app')

@section('title', 'Create your portfolio — PortGen')

@section('content')
    <section class="mx-auto max-w-3xl space-y-6">
        @include('portfolios.stepper', ['active' => 1])
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Create your portfolio</h1>
        <p class="text-muted max-w-2xl text-lg">Add the details you want people to see. You can leave education, experience, projects, and links empty. Open one section at a time and follow the completion labels.</p>
        @include('portfolios.form', ['portfolio' => null, 'action' => route('portfolios.store'), 'formMethod' => 'POST', 'backUrl' => route('portfolios.create'), 'submitLabel' => 'Next: choose a template'])
    </section>

    <dialog id="draft-restore-dialog" aria-labelledby="draft-restore-title" data-draft-restore-dialog>
        <h2 id="draft-restore-title" class="text-2xl font-bold">Restore your saved draft?</h2>
        <p class="mt-2">A draft from this browser is available. Restore it to continue where you left off.</p>
        <div class="mt-5 flex flex-wrap gap-3">
            <button class="button-primary" type="button" data-restore-draft>Restore draft</button>
            <button class="button-secondary" type="button" data-discard-draft>Start a new portfolio</button>
        </div>
    </dialog>
@endsection

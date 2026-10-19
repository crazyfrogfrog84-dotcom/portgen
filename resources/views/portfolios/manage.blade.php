@extends('layouts.app')

@section('title', 'Manage your portfolio — PortGen')

@section('content')
    <section class="mx-auto max-w-3xl space-y-6">
        <p class="text-sm font-semibold uppercase tracking-wide">Private portfolio management</p>
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Manage portfolio for {{ $portfolio->full_name }}</h1>
        <p>This private link lets you edit or delete this portfolio. Save it somewhere private; anyone with the link can manage it.</p>
        <p><a class="button-secondary" href="{{ $publicUrl }}">View the public portfolio</a></p>
        <div class="flex flex-wrap gap-3">
            <a class="button-primary" href="{{ $editUrl }}">Edit portfolio</a>
            <a class="button-secondary" href="{{ $changeTemplateUrl }}">Change template</a>
            <form method="POST" action="{{ $deleteUrl }}" data-delete-form>
                @csrf
                @method('DELETE')
                <button class="button-danger" type="submit">Delete portfolio</button>
            </form>
        </div>
    </section>

    <dialog id="delete-confirmation" aria-labelledby="delete-confirmation-title" data-delete-confirmation>
        <h2 id="delete-confirmation-title" class="text-2xl font-bold">Delete {{ $portfolio->full_name }}'s portfolio?</h2>
        <p class="mt-2">This removes the public page. You will have 10 seconds to undo before the portfolio is permanently deleted.</p>
        <div class="mt-5 flex flex-wrap gap-3">
            <button class="button-secondary" type="button" data-cancel-delete>Keep portfolio</button>
            <button class="button-danger" type="button" data-confirm-delete>Yes, delete this portfolio</button>
        </div>
    </dialog>
@endsection

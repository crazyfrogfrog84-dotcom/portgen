@extends('layouts.app')

@section('title', 'Portfolio deleted — PortGen')

@section('content')
    <section class="surface mx-auto max-w-2xl space-y-5 rounded-2xl border p-6" data-deleted-screen data-purge-url="{{ $purgeUrl }}" data-undo-seconds="{{ $undoSecondsLeft }}">
        <p class="text-sm font-semibold uppercase tracking-wide">Portfolio deletion</p>
        <h1 class="text-3xl font-bold">{{ $portfolio->full_name }}'s portfolio is deleted</h1>
        @if ($undoUrl)
            <p>You have <span data-undo-countdown>{{ $undoSecondsLeft }}</span> seconds to undo. After that, the portfolio will be permanently removed.</p>
            <form method="POST" action="{{ $undoUrl }}" data-undo-form>
                @csrf
                <button class="button-primary" type="submit">Undo deletion</button>
            </form>
            <p data-delete-status role="status" aria-live="polite"></p>
        @else
            <p>The undo period has ended. This portfolio will be permanently removed.</p>
        @endif
        <a class="min-h-11 inline-flex items-center underline" href="{{ route('portfolios.create') }}">Create a different portfolio</a>
    </section>
@endsection

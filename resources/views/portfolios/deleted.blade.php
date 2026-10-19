@extends('layouts.app')

@section('title', 'Portfolio deleted — PortGen')

@section('content')
    <section class="mx-auto max-w-2xl space-y-5 rounded-2xl border border-slate-400 p-6" data-purge-url="{{ $purgeUrl }}" data-undo-seconds="{{ $undoSecondsLeft }}">
        <p class="text-sm font-semibold uppercase tracking-wide">Portfolio deletion</p>
        <h1 class="text-3xl font-bold">{{ $portfolio->full_name }}'s portfolio is deleted</h1>
        @if ($undoUrl)
            <p>You have {{ $undoSecondsLeft }} seconds to undo. After that, the portfolio will be permanently removed.</p>
            <form method="POST" action="{{ $undoUrl }}" data-undo-form>
                @csrf
                <button class="min-h-11 rounded-lg bg-slate-900 px-5 py-3 font-semibold text-white dark:bg-white dark:text-slate-900" type="submit">Undo deletion</button>
            </form>
            <p data-delete-status role="status" aria-live="polite"></p>
        @else
            <p>The undo period has ended. This portfolio will be permanently removed.</p>
        @endif
        <a class="min-h-11 inline-flex items-center underline" href="{{ route('portfolios.create') }}">Create a different portfolio</a>
    </section>
@endsection

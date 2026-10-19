@extends('layouts.app')

@section('title', $portfolio->full_name.' — PortGen portfolio')

@section('content')
    @if ($ownerManageUrl)
        <aside class="mx-auto mb-6 max-w-5xl rounded-xl border border-indigo-700 p-4" aria-labelledby="manage-link-heading">
            <p class="text-sm font-semibold uppercase tracking-wide">Step 3 of 3 · Done and share</p>
            <h2 id="manage-link-heading" class="mt-1 text-xl font-semibold">Your portfolio is ready</h2>
            <p class="mt-1">Copy the private manage link if you want to edit or delete your portfolio later. Anyone with that private link can manage it.</p>
            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                <input id="owner-manage-link" type="url" readonly value="{{ $ownerManageUrl }}" aria-label="Private manage link" class="min-h-11 min-w-0 flex-1 rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white">
                <button type="button" class="min-h-11 rounded-lg border border-current px-4 py-2 font-semibold" data-copy-target="owner-manage-link">Copy private link</button>
            </div>
            <p class="mt-2 text-sm" data-copy-status role="status" aria-live="polite"></p>
            <p class="mt-3">Public page: <a href="{{ route('portfolio.show', ['portfolio' => $portfolio]) }}">{{ route('portfolio.show', ['portfolio' => $portfolio]) }}</a></p>
        </aside>
    @endif

    @include('portfolios.templates.'.$template, ['portfolio' => $portfolio])
@endsection

@extends('layouts.app')

@section('title', 'Manage your portfolio — PortGen')

@section('content')
    <section class="mx-auto max-w-3xl space-y-6">
        <p class="text-sm font-semibold uppercase tracking-wide">Private portfolio management</p>
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Manage {{ $portfolio->full_name }}'s portfolio</h1>
        <p>This private link lets you edit or delete this portfolio. Save it somewhere private; anyone with the link can manage it.</p>
        <p><a class="min-h-11 inline-flex items-center font-semibold" href="{{ $publicUrl }}">View the public portfolio</a></p>
        <div class="flex flex-wrap gap-3">
            <a class="min-h-11 inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white dark:bg-white dark:text-slate-900" href="{{ $editUrl }}">Edit portfolio</a>
            <form method="POST" action="{{ $deleteUrl }}" data-delete-form>
                @csrf
                @method('DELETE')
                <button class="min-h-11 rounded-lg border border-red-800 px-4 py-2 font-semibold text-red-900 dark:text-red-200" type="submit">Delete portfolio</button>
            </form>
        </div>
    </section>
@endsection

@extends('layouts.app')

@section('title', 'My Portfolios — PortGen')

@section('content')
    <section class="space-y-6" data-dashboard>
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="text-sm font-semibold uppercase tracking-wide">Your account</p><h1 class="text-3xl font-bold tracking-tight sm:text-4xl">My Portfolios</h1><p class="mt-2 text-muted">Manage portfolios linked to {{ auth()->user()->email }}.</p></div>
            <a class="button-primary" href="{{ route('portfolios.create') }}">Create a portfolio</a>
        </header>

        @if ($portfolios->isEmpty())
            <div class="surface grid justify-items-start gap-4 rounded-2xl border p-8">
                <h2 class="text-2xl font-bold">Your portfolio space is ready</h2>
                <p>Create your first portfolio. You can also keep using PortGen as a guest whenever you like.</p>
                <a class="button-primary" href="{{ route('portfolios.create') }}">Create your first portfolio</a>
            </div>
        @else
            <ul class="grid list-none gap-4 p-0 md:grid-cols-2">
                @foreach ($portfolios as $portfolio)
                    <li class="surface grid content-start gap-4 rounded-2xl border p-5" data-dashboard-portfolio>
                        <div class="min-w-0"><h2 class="break-words text-xl font-bold">{{ $portfolio->full_name }}</h2><p class="mt-1 text-muted">{{ $portfolio->template_choice ?? 'No template chosen' }} · Updated {{ $portfolio->updated_at->diffForHumans() }}</p></div>
                        <div class="flex flex-wrap gap-2">
                            @if ($portfolio->template_choice)<a class="button-secondary" href="{{ route('portfolio.show', ['portfolio' => $portfolio]) }}">View</a>@endif
                            <a class="button-secondary" href="{{ route('manage.edit', ['portfolio' => $portfolio]) }}">Edit</a>
                            <a class="button-secondary" href="{{ route('portfolio.select-template', ['portfolio' => $portfolio, 'from_manage' => 1]) }}">Change template</a>
                            <form method="POST" action="{{ route('manage.destroy', ['portfolio' => $portfolio]) }}" data-delete-form>
                                @csrf @method('DELETE')
                                <button class="button-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
            @if ($portfolios instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <nav aria-label="Portfolio pages" class="mt-6">{{ $portfolios->links() }}</nav>
            @endif
        @endif
    </section>

    <dialog id="delete-confirmation" aria-labelledby="delete-confirmation-title" data-delete-confirmation>
        <h2 id="delete-confirmation-title" class="text-2xl font-bold">Delete this portfolio?</h2>
        <p class="mt-2">The public page will be removed. You will have 10 seconds to undo before permanent deletion.</p>
        <div class="mt-5 flex flex-wrap gap-3"><button class="button-secondary" type="button" data-cancel-delete>Keep portfolio</button><button class="button-danger" type="button" data-confirm-delete>Yes, delete this portfolio</button></div>
    </dialog>
@endsection

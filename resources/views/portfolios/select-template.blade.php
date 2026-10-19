@extends('layouts.app')

@section('title', 'Choose a template — PortGen')

@section('content')
    <section class="mx-auto max-w-5xl space-y-6">
        <p class="text-sm font-semibold uppercase tracking-wide">Step 2 of 3 · Choose a template</p>
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Choose the look that fits your work</h1>
        <p class="max-w-3xl text-lg text-[var(--simple-muted)]">Each preview uses your name, headline, skills, and project information. You can change this choice later.</p>

        <form method="POST" action="{{ route('portfolio.save-template', ['portfolio' => $portfolio]) }}" class="grid gap-5 lg:grid-cols-3">
            @csrf
            @foreach (['Simple' => ['A calm, monochromatic layout with generous space.', 'theme-simple'], 'Modern' => ['A focused, cool palette with clear section cards.', 'theme-modern'], 'Creative' => ['A warm, expressive layout with a bold accent.', 'theme-creative']] as $choice => [$description, $theme])
                <label class="block cursor-pointer rounded-xl border border-slate-400 p-4 has-[:checked]:border-indigo-700 has-[:checked]:ring-2 has-[:checked]:ring-indigo-700">
                    <span class="flex items-start gap-3">
                        <input class="mt-1 min-h-5 min-w-5" type="radio" name="template_choice" value="{{ $choice }}" @checked(old('template_choice', $portfolio->template_choice) === $choice) required>
                        <span><span class="block text-xl font-semibold">{{ $choice }}</span><span class="mt-1 block text-sm text-[var(--simple-muted)]">{{ $description }}</span></span>
                    </span>
                    <span class="{{ $theme }} mt-4 block rounded-lg border border-current p-4">
                        <span class="block text-xs uppercase tracking-wide">Portfolio preview</span>
                        <span class="mt-2 block text-2xl font-bold">{{ $portfolio->full_name }}</span>
                        <span class="mt-1 block">{{ $portfolio->headline }}</span>
                        <span class="mt-4 block border-t border-current/30 pt-3">{{ collect($portfolio->skills)->take(3)->join(' · ') }}</span>
                        @if (! empty($portfolio->projects[0]['name']))
                            <span class="mt-3 block font-semibold">{{ $portfolio->projects[0]['name'] }}</span>
                        @endif
                    </span>
                    <span class="mt-3 block text-sm">Preview of your own portfolio information.</span>
                </label>
            @endforeach
            @error('template_choice')<p class="lg:col-span-3 font-medium text-red-800 dark:text-red-200" role="alert">{{ $message }}</p>@enderror
            <div class="flex flex-wrap items-center justify-between gap-3 lg:col-span-3">
                <a class="min-h-11 inline-flex items-center rounded-lg border border-current px-4 py-2 font-medium" href="{{ $backUrl }}">Back to your details</a>
                <button class="min-h-11 rounded-lg bg-slate-900 px-5 py-3 font-semibold text-white dark:bg-white dark:text-slate-900" type="submit" data-submit-button>Next: preview and share</button>
            </div>
        </form>
    </section>
@endsection

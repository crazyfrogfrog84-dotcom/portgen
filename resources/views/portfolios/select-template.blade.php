@extends('layouts.app')

@section('title', 'Choose a template — PortGen')

@section('content')
    <section class="mx-auto max-w-5xl space-y-6">
        @include('portfolios.stepper', ['active' => 2])
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Choose the look that fits your work</h1>
        <p class="text-muted max-w-3xl text-lg">Each preview uses your name, headline, skills, and project information. You can change this choice later.</p>

        <form method="POST" action="{{ $saveTemplateUrl }}" class="grid gap-5 lg:grid-cols-3" data-template-form>
            @csrf
            @foreach (['Simple' => ['A calm, monochromatic layout with generous space.', 'theme-simple'], 'Modern' => ['A focused, cool palette with clear section cards.', 'theme-modern'], 'Creative' => ['A tactile paper layout with playful icons and cobalt details.', 'theme-creative']] as $choice => [$description, $theme])
                <label class="template-card">
                    <span class="flex items-start gap-3">
                        <input class="mt-1 min-h-5 min-w-5" type="radio" name="template_choice" value="{{ $choice }}" @checked(old('template_choice', $portfolio->template_choice) === $choice) required>
                        <span><span class="block text-xl font-semibold">{{ $choice }}</span><span class="text-muted mt-1 block text-sm">{{ $description }}</span></span>
                    </span>
                    <span class="template-preview {{ $theme }}">
                        <span class="block text-xs uppercase tracking-wide">Portfolio preview</span>
                        <span class="mt-2 block text-2xl font-bold">{{ $portfolio->full_name }}</span>
                        <span class="mt-1 block">{{ $portfolio->headline }}</span>
                        <span class="mt-4 block border-t border-current/30 pt-3">{{ collect($portfolio->skills)->take(3)->join(' · ') }}</span>
                        @if (! empty($portfolio->projects[0]['name']))
                            <span class="preview-project block font-semibold">{{ $portfolio->projects[0]['name'] }}</span>
                        @endif
                    </span>
                    <span class="mt-3 block text-sm">Preview of your own portfolio information.</span>
                </label>
            @endforeach
            @error('template_choice')<p class="status-error lg:col-span-3" role="alert">{{ $message }}</p>@enderror
            <div class="flex flex-wrap items-center justify-between gap-3 lg:col-span-3">
                <a class="button-secondary" href="{{ $backUrl }}" data-step-back>Back to your details</a>
                <button class="button-primary" type="submit" data-submit-button>Next: preview and share</button>
            </div>
        </form>
    </section>
@endsection

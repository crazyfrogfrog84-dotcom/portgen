@props([
    'name',
    'headline' => '',
    'summary' => '',
    'primaryHref' => '#projects',
    'primaryLabel' => 'View projects',
    'secondaryHref' => '#contact',
    'secondaryLabel' => 'Contact me',
    'imageUrl' => null,
    'imageAlt' => '',
])

<header class="pf-hero">
    <div class="pf-hero__copy">
        <p class="pf-hero__eyebrow">Hi, I’m</p>
        <h1>{{ $name }}</h1>
        @if ($headline !== '')
            <p class="pf-hero__headline">{{ $headline }}</p>
        @endif
        @if ($summary !== '')
            <p class="pf-hero__summary">{{ $summary }}</p>
        @endif
        <div class="pf-hero__actions">
            <a class="button-primary" href="{{ $primaryHref }}">{{ $primaryLabel }}</a>
            <a class="button-secondary" href="{{ $secondaryHref }}">{{ $secondaryLabel }}</a>
        </div>
    </div>
    <x-pf.avatar :name="$name" :image-url="$imageUrl" :alt="$imageAlt" />
</header>

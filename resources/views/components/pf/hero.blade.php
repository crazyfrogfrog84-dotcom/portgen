@props([
    'name',
    'headline' => '',
    'summary' => '',
    'primaryHref' => null,
    'primaryLabel' => 'View projects',
    'secondaryHref' => null,
    'secondaryLabel' => 'Contact me',
    'imageUrl' => null,
    'imageAlt' => '',
    'material' => null,
])

<section id="home" class="pf-hero {{ $material === 'raised' ? 'surface-raised' : '' }}" aria-label="Introduction">
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
            @if ($primaryHref)<a class="button-primary {{ $material === 'raised' ? 'surface-raised' : '' }}" href="{{ $primaryHref }}">{{ $primaryLabel }}</a>@endif
            @if ($secondaryHref)<a class="button-secondary {{ $material === 'raised' ? 'surface-raised' : '' }}" href="{{ $secondaryHref }}">{{ $secondaryLabel }}</a>@endif
        </div>
    </div>
    <x-pf.avatar :name="$name" :image-url="$imageUrl" :alt="$imageAlt" />
</section>

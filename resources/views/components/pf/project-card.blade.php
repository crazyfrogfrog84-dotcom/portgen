@props([
    'title',
    'description' => '',
    'url' => null,
    'tags' => [],
    'imageUrl' => null,
    'imageAlt' => '',
    'imageWidth' => null,
    'imageHeight' => null,
    'simple' => false,
    'material' => null,
])

<article class="pf-project-card {{ $simple || $material === 'raised' ? 'surface-raised' : '' }} {{ $simple ? 'pf-project-card--simple' : '' }}">
    @if ($imageUrl)
        <img class="pf-project-card__image" src="{{ $imageUrl }}" alt="{{ $imageAlt }}" width="{{ $imageWidth }}" height="{{ $imageHeight }}" loading="lazy">
    @else
        <div class="pf-project-card__cover" role="img" aria-label="Generated cover for {{ $title }}">{{ mb_substr(trim($title), 0, 1) }}</div>
    @endif
    <h3>{{ $title }}</h3>
    @if ($description !== '')
        <p class="pf-project-card__description">{{ $description }}</p>
    @endif
    @if (count($tags))
        <ul class="pf-project-card__tags" aria-label="Project technologies">
            @foreach ($tags as $tag)
                <li><x-pf.skill-chip :label="$tag" :material="$simple || $material === 'raised' ? 'raised' : null" /></li>
            @endforeach
        </ul>
    @endif
    @if ($url)
        <a class="pf-project-card__link pf-external-link" href="{{ $url }}" rel="noopener noreferrer" @if ($simple) aria-label="View project: {{ $title }}" data-stretched-link @endif>View project <span class="pf-external-link__icon" aria-hidden="true">↗</span><span class="sr-only"> (external link)</span></a>
    @endif
</article>

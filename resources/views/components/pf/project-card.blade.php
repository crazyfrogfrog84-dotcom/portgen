@props(['title', 'description' => '', 'url' => null, 'tags' => []])

<article class="pf-project-card">
    <div class="pf-project-card__cover" aria-hidden="true">{{ mb_substr(trim($title), 0, 1) }}</div>
    <h3>{{ $title }}</h3>
    @if ($description !== '')
        <p>{{ $description }}</p>
    @endif
    @if (count($tags))
        <ul class="pf-project-card__tags" aria-label="Project technologies">
            @foreach ($tags as $tag)
                <li><x-pf.skill-chip :label="$tag" /></li>
            @endforeach
        </ul>
    @endif
    @if ($url)
        <a class="pf-external-link" href="{{ $url }}" rel="noopener noreferrer">View project <span class="sr-only">(external link)</span></a>
    @endif
</article>

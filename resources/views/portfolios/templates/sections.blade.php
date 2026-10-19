@php($projectImages = $projectImages ?? collect())
<div class="mt-8 space-y-8">
    <section aria-labelledby="about-heading">
        <h2 id="about-heading" class="text-xl font-semibold">About me</h2>
        <p class="mt-2 max-w-3xl whitespace-pre-line">{{ $portfolio->about_me }}</p>
        @if ($portfolio->availability || $portfolio->motto)
            <p class="mt-3">{{ $portfolio->availability ?: $portfolio->motto }}</p>
        @endif
        @if ($portfolio->location || $portfolio->interests)
            <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-2">
                @if ($portfolio->location)<div><dt class="inline font-semibold">Location: </dt><dd class="inline">{{ $portfolio->location }}</dd></div>@endif
                @if ($portfolio->interests)<div><dt class="inline font-semibold">Interests: </dt><dd class="inline">{{ implode(', ', $portfolio->interests) }}</dd></div>@endif
            </dl>
        @endif
    </section>

    @if (count($portfolio->education))
        <section aria-labelledby="education-heading">
            <h2 id="education-heading" class="text-xl font-semibold">Education</h2>
            <ul class="mt-3 space-y-3">
                @foreach ($portfolio->education as $education)
                    <li class="rounded-lg border border-current/20 p-4"><h3 class="font-semibold">{{ $education['institution'] ?? '' }}</h3><p>{{ $education['qualification'] ?? '' }}@if (! empty($education['year'])) · {{ $education['year'] }}@endif</p></li>
                @endforeach
            </ul>
        </section>
    @endif

    @if (count($portfolio->work_experience))
        <section aria-labelledby="experience-heading">
            <h2 id="experience-heading" class="text-xl font-semibold">Experience</h2>
            <ul class="mt-3 space-y-3">
                @foreach ($portfolio->work_experience as $experience)
                    <li class="rounded-lg border border-current/20 p-4"><h3 class="font-semibold">{{ $experience['role'] ?? '' }}@if (! empty($experience['company'])) · {{ $experience['company'] }}@endif</h3>@if (! empty($experience['description']))<p class="mt-2 whitespace-pre-line">{{ $experience['description'] }}</p>@endif</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section aria-labelledby="skills-heading">
        <h2 id="skills-heading" class="text-xl font-semibold">Skills</h2>
        <ul class="mt-3 flex flex-wrap gap-2">
            @foreach ($portfolio->skills as $skill)
                <li class="rounded-full border border-current/30 px-3 py-1">{{ $skill }}</li>
            @endforeach
        </ul>
    </section>

    <section aria-labelledby="projects-heading">
        <h2 id="projects-heading" class="text-xl font-semibold">Projects</h2>
        @if (count($portfolio->projects))
            <ul class="mt-3 grid gap-4 sm:grid-cols-2">
                @foreach ($portfolio->projects as $index => $project)
                    <li class="rounded-xl border border-current/20 p-4">
                        @if ($projectImages->has($index))
                            <img class="pf-project-image" src="{{ route('portfolio.image', ['portfolio' => $portfolio, 'image' => $projectImages[$index]]) }}" alt="{{ $projectImages[$index]->alt }}" width="{{ $projectImages[$index]->width }}" height="{{ $projectImages[$index]->height }}" loading="lazy">
                        @else
                            <div class="pf-project-card__cover" role="img" aria-label="Generated cover for {{ $project['name'] ?? 'project' }}">{{ mb_substr($project['name'] ?? 'P', 0, 1) }}</div>
                        @endif
                        <h3 class="font-semibold">{{ $project['name'] ?? '' }}</h3>@if (! empty($project['description']))<p class="mt-2 whitespace-pre-line">{{ $project['description'] }}</p>@endif
                        @if (! empty($project['tags']))<ul class="mt-2 flex flex-wrap gap-2">@foreach ($project['tags'] as $tag)<li class="rounded-full border border-current/30 px-3 py-1 text-sm">{{ $tag }}</li>@endforeach</ul>@endif
                        @if (! empty($project['url']))<a class="mt-3 inline-flex min-h-11 items-center" href="{{ $project['url'] }}">View project</a>@endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-2">No projects shared yet.</p>
        @endif
    </section>

    @if (count($portfolio->social_links))
        <section aria-labelledby="links-heading">
            <h2 id="links-heading" class="text-xl font-semibold">Find me online</h2>
            <ul class="mt-3 flex flex-wrap gap-4">
                @foreach ($portfolio->social_links as $link)
                    @if (! empty($link['label']) && ! empty($link['url']))
                        <li><a class="min-h-11 inline-flex items-center" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">{{ $link['label'] }} <span class="sr-only">(opens in a new tab)</span></a></li>
                    @endif
                @endforeach
            </ul>
        </section>
    @endif

    @if ($portfolio->contact_email)
        <section aria-labelledby="contact-heading">
            <h2 id="contact-heading" class="text-xl font-semibold">Contact</h2>
            @if ($portfolio->contact_email)<p class="mt-2"><a href="mailto:{{ $portfolio->contact_email }}">{{ $portfolio->contact_email }}</a></p>@endif
        </section>
    @endif
</div>

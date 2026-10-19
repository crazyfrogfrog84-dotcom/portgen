<div class="mt-8 space-y-8">
    <section aria-labelledby="about-heading">
        <h2 id="about-heading" class="text-xl font-semibold">About me</h2>
        <p class="mt-2 max-w-3xl whitespace-pre-line">{{ $portfolio->about_me }}</p>
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
                @foreach ($portfolio->projects as $project)
                    <li class="rounded-xl border border-current/20 p-4"><h3 class="font-semibold">{{ $project['name'] ?? '' }}</h3>@if (! empty($project['description']))<p class="mt-2 whitespace-pre-line">{{ $project['description'] }}</p>@endif @if (! empty($project['url']))<a class="mt-3 inline-flex min-h-11 items-center" href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer">View project <span class="sr-only">(opens in a new tab)</span></a>@endif</li>
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
</div>

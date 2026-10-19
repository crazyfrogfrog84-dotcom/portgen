@php
    $projectImages = $projectImages ?? collect();
    $focusItems = count($portfolio->skills ?? [])
        ? array_slice($portfolio->skills, 0, 3)
        : array_slice($portfolio->interests ?? [], 0, 3);
    $hasContact = filled($portfolio->contact_email) || count($portfolio->social_links ?? []) > 0;
@endphp

<div class="pf-sections">
    <x-pf.section id="about" title="About me" class="pf-about">
        <div class="pf-about__grid">
            <div class="pf-about__copy">
                @if (filled($portfolio->about_me))
                    <p class="pf-about__text">{{ $portfolio->about_me }}</p>
                @endif
                @if (count($focusItems))
                    <ul class="pf-focus-list" aria-label="Areas of focus">
                        @foreach ($focusItems as $focusItem)
                            <li><x-pf.skill-chip :label="$focusItem" /></li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <dl class="pf-facts">
                <div class="pf-facts__row"><dt>Name</dt><dd>{{ $portfolio->full_name }}</dd></div>
                @if (filled($portfolio->location))
                    <div class="pf-facts__row"><dt>Location</dt><dd>{{ $portfolio->location }}</dd></div>
                @endif
                @if (count($portfolio->education ?? []))
                    @php $education = $portfolio->education[0]; @endphp
                    <div class="pf-facts__row"><dt>Education</dt><dd>{{ $education['qualification'] ?? $education['institution'] ?? '' }}</dd></div>
                @endif
                @if (count($portfolio->interests ?? []))
                    <div class="pf-facts__row"><dt>Interests</dt><dd>{{ implode(', ', $portfolio->interests) }}</dd></div>
                @endif
            </dl>
        </div>
    </x-pf.section>

    @if (count($portfolio->skills ?? []))
        <x-pf.section id="skills" title="Skills" class="pf-skills">
            <ul class="pf-skills-grid">
                @foreach ($portfolio->skills as $skill)
                    <li><x-pf.skill-chip :label="$skill" /></li>
                @endforeach
            </ul>
        </x-pf.section>
    @endif

    @if (count($portfolio->projects ?? []))
        <x-pf.section id="projects" title="Projects" class="pf-projects">
            <ul class="pf-project-grid">
                @foreach ($portfolio->projects as $index => $project)
                    <li>
                        <x-pf.project-card
                            :title="$project['name'] ?? ''"
                            :description="$project['description'] ?? ''"
                            :url="$project['url'] ?? null"
                            :tags="$project['tags'] ?? []"
                            :image-url="$projectImages->has($index) ? route('portfolio.image', ['portfolio' => $portfolio, 'image' => $projectImages[$index]]) : null"
                            :image-alt="$projectImages->has($index) ? $projectImages[$index]->alt : ''"
                            :image-width="$projectImages->has($index) ? $projectImages[$index]->width : null"
                            :image-height="$projectImages->has($index) ? $projectImages[$index]->height : null"
                        />
                    </li>
                @endforeach
            </ul>
        </x-pf.section>
    @endif

    @if (count($portfolio->work_experience ?? []))
        <x-pf.section id="experience" title="Experience" class="pf-experience">
            <ol class="pf-timeline">
                @foreach ($portfolio->work_experience as $experience)
                    @php
                        $period = trim(implode(' – ', array_filter([
                            $experience['start_date'] ?? '',
                            $experience['end_date'] ?? '',
                        ])));
                    @endphp
                    <x-pf.timeline-item
                        :title="$experience['role'] ?? ''"
                        :organization="$experience['company'] ?? ''"
                        :period="$period"
                        :description="$experience['description'] ?? ''"
                    />
                @endforeach
            </ol>
        </x-pf.section>
    @endif

    @if (count($portfolio->education ?? []))
        <x-pf.section id="education" title="Education" class="pf-education">
            <ol class="pf-timeline">
                @foreach ($portfolio->education as $education)
                    <x-pf.timeline-item
                        :title="$education['qualification'] ?? ''"
                        :organization="$education['institution'] ?? ''"
                        :period="$education['year'] ?? ''"
                    />
                @endforeach
            </ol>
        </x-pf.section>
    @endif

    @if ($hasContact)
        <x-pf.section id="contact" title="Contact" class="pf-contact">
            <x-pf.contact-links :links="$portfolio->social_links ?? []" :email="$portfolio->contact_email" />
        </x-pf.section>
    @endif
</div>

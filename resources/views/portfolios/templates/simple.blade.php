@php
    $about = trim((string) $portfolio->about_me);
    $aboutExcerptText = trim(preg_replace('/\s+/u', ' ', $about) ?? '');
    $headline = trim((string) $portfolio->headline);
    $contactCandidate = preg_replace('/^(?:e-?mail|phone|telephone|contact(?:\s+me)?|call|text)(?:\s+(?:me\s+)?(?:at|on))?\s*[:\-]?\s*/iu', '', $aboutExcerptText) ?? $aboutExcerptText;
    $isContactOnly = preg_match('/^[\w.+-]+@[\w.-]+\.[a-z]{2,}$/iu', $contactCandidate)
        || preg_match('/^\+?[\d\s().-]{7,}$/u', $contactCandidate);
    $aboutExcerpt = $aboutExcerptText !== ''
        && mb_strtolower($aboutExcerptText) !== mb_strtolower($headline)
        && ! $isContactOnly
            ? \Illuminate\Support\Str::limit($aboutExcerptText, 140, '…', true)
            : '';
    $hasProjects = count($portfolio->projects ?? []) > 0;
    $hasContact = filled($portfolio->contact_email) || count($portfolio->social_links ?? []) > 0;
    $latestEducation = collect($portfolio->education ?? [])->sortByDesc(function ($item) {
        preg_match('/(?:19|20)\d{2}/', (string) ($item['year'] ?? ''), $matches);

        return $matches[0] ?? '0';
    })->first() ?? collect($portfolio->education ?? [])->last();
    $aboutFacts = array_filter([
        'Location' => $portfolio->location,
        'Education' => $latestEducation['qualification'] ?? $latestEducation['institution'] ?? null,
        'Availability' => $portfolio->availability,
        'Interests' => count($portfolio->interests ?? []) ? implode(', ', $portfolio->interests) : null,
        'Email' => $portfolio->contact_email,
    ], fn ($value) => filled($value));
    $hasAbout = filled($about) || count($aboutFacts) > 0;
    $uniqueSkills = collect($portfolio->skills ?? [])->filter(fn ($skill) => filled($skill))
        ->unique(fn ($skill) => mb_strtolower(trim($skill)))->values();
    $primaryHref = $hasProjects ? '#projects' : ($hasContact ? '#contact' : null);
    $secondaryHref = $hasProjects && $hasContact ? '#contact' : ($hasAbout ? '#about' : null);
    $formatDate = function ($date) {
        $date = trim((string) $date);
        if (preg_match('/^(\d{4})-(\d{2})$/', $date, $matches)) {
            return \Illuminate\Support\Carbon::createFromFormat('!Y-m', $date)->format('M Y');
        }

        return $date;
    };
@endphp

<article class="theme-simple pf-template-content pf-template-content--simple" aria-label="Simple portfolio template">
    <x-pf.hero
        :name="$portfolio->full_name"
        :headline="$headline"
        :summary="$aboutExcerpt"
        :primary-href="$primaryHref"
        :primary-label="$hasProjects ? 'View my projects' : 'Contact me'"
        :secondary-href="$secondaryHref"
        :secondary-label="$hasProjects && $hasContact ? 'Contact me' : 'About me'"
        :image-url="$profileImage ? route('portfolio.image', ['portfolio' => $portfolio, 'image' => $profileImage]) : null"
        :image-alt="$profileImage?->alt ?? ''"
        material="raised"
    />

    <div class="pf-sections pf-sections--simple">
        @if ($hasAbout)
            <x-pf.section id="about" title="About me" class="pf-about surface-raised">
                <div class="pf-about__grid {{ count($aboutFacts) ? '' : 'pf-about__grid--single' }}">
                    @if (filled($about))
                        <div class="pf-about__copy">
                            @foreach (preg_split('/\R\s*\R/u', $about, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $paragraph)
                                <p class="pf-about__text">{{ trim($paragraph) }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if (count($aboutFacts))
                        <div class="pf-quick-facts surface-inset">
                            <p class="pf-quick-facts__heading">Quick facts</p>
                            <dl class="pf-facts">
                                @foreach ($aboutFacts as $label => $value)
                                    <div class="pf-facts__row">
                                        <dt>{{ $label }}</dt>
                                        <dd>@if ($label === 'Email')<a class="surface-raised" href="mailto:{{ $value }}">{{ $value }}</a>@else{{ $value }}@endif</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif
                </div>
            </x-pf.section>
        @endif

        @if ($uniqueSkills->isNotEmpty())
            <x-pf.section id="skills" title="Skills" class="pf-skills surface-raised">
                <ul class="pf-skills-grid pf-skills-grid--simple {{ $uniqueSkills->count() > 12 ? 'pf-skills-grid--dense' : '' }}">
                    @foreach ($uniqueSkills as $skill)
                        <li><x-pf.skill-chip :label="$skill" material="raised" /></li>
                    @endforeach
                </ul>
            </x-pf.section>
        @endif

        @if ($hasProjects)
            <x-pf.section id="projects" title="Projects" class="pf-projects surface-raised">
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
                                :simple="true"
                            />
                        </li>
                    @endforeach
                </ul>
            </x-pf.section>
        @endif

        @if (count($portfolio->work_experience ?? []))
            <x-pf.section id="experience" title="Experience" class="pf-experience surface-raised">
                <ol class="pf-timeline">
                    @foreach ($portfolio->work_experience as $experience)
                        @php
                            $period = trim(implode(' – ', array_filter([
                                $formatDate($experience['start_date'] ?? ''),
                                ($experience['end_date'] ?? '') === 'Present' ? 'Present' : $formatDate($experience['end_date'] ?? ''),
                            ], fn ($value) => $value !== '')));
                            $period = str_replace(' – ', ' - ', $period);
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
            <x-pf.section id="education" title="Education" class="pf-education surface-raised">
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
            <x-pf.section id="contact" title="Get in touch" class="pf-contact surface-raised">
                <p class="pf-contact__intro">For enquiries, use one of the options below.</p>
                <x-pf.contact-links :links="$portfolio->social_links ?? []" :email="$portfolio->contact_email" :simple="true" />
            </x-pf.section>
        @endif
    </div>
</article>

@php
    $projectImages = $projectImages ?? collect();
    $focusItems = count($portfolio->skills ?? [])
        ? array_slice($portfolio->skills, 0, 3)
        : array_slice($portfolio->interests ?? [], 0, 3);
    $hasContact = filled($portfolio->contact_email) || count($portfolio->social_links ?? []) > 0;
    $modernMaterial = ($templateName ?? null) === 'modern';
    $creativeMaterial = ($templateName ?? null) === 'creative';
    $skillMarks = [
        'css' => '3', 'css3' => '3', 'figma' => 'F', 'git' => '◇', 'html' => '5', 'html5' => '5',
        'javascript' => 'JS', 'js' => 'JS', 'laravel' => 'L', 'node.js' => 'N', 'php' => 'PHP',
        'python' => 'Py', 'react' => '⚛', 'tailwind css' => '≈', 'typescript' => 'TS', 'vue' => 'V',
    ];
    $skillIcons = [
        'css' => 'css3', 'css3' => 'css3', 'figma' => 'figma', 'git' => 'git', 'html' => 'html5', 'html5' => 'html5',
        'javascript' => 'javascript', 'js' => 'javascript', 'react' => 'react', 'tailwind css' => 'tailwind',
        'accessibility' => 'heart', 'content design' => 'folder', 'design systems' => 'code', 'information architecture' => 'folder',
        'prototyping' => 'link', 'research' => 'user', 'service design' => 'sparkle', 'usability testing' => 'heart', 'workshop facilitation' => 'briefcase',
    ];
    $factIcons = ['Location' => 'pin', 'Education' => 'graduation', 'Availability' => 'heart', 'Interests' => 'sparkle', 'Email' => 'mail'];
    $aboutFacts = array_filter([
        'Location' => $portfolio->location,
        'Education' => $portfolio->education[0]['qualification'] ?? $portfolio->education[0]['institution'] ?? null,
        'Availability' => $portfolio->availability,
        'Interests' => count($portfolio->interests ?? []) ? implode(', ', $portfolio->interests) : null,
        'Email' => $portfolio->contact_email,
    ], fn ($value) => filled($value));
    $hasAbout = filled($portfolio->about_me) || count($aboutFacts) > 0;
    $displaySkills = $creativeMaterial
        ? collect($portfolio->skills ?? [])->filter(fn ($skill) => filled($skill))->unique(fn ($skill) => mb_strtolower(trim($skill)))->values()
        : collect($portfolio->skills ?? []);
@endphp

<div class="pf-sections">
    @if ($hasAbout)
        <x-pf.section id="about" title="About me" class="pf-about {{ $modernMaterial || $creativeMaterial ? 'surface-raised' : '' }} {{ $creativeMaterial ? 'pf-creative-card' : '' }}">
            <div class="pf-about__grid">
                <div class="pf-about__copy">
                    @if (filled($portfolio->about_me) && $creativeMaterial)
                        @foreach (preg_split('/\R\s*\R/u', trim($portfolio->about_me), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $paragraph)
                            <p class="pf-about__text">{{ trim($paragraph) }}</p>
                        @endforeach
                    @elseif (filled($portfolio->about_me))
                        <p class="pf-about__text">{{ $portfolio->about_me }}</p>
                    @endif
                    @if (count($focusItems))
                        <ul class="pf-focus-list" aria-label="Areas of focus">
                            @foreach ($focusItems as $focusItem)
                                @php
                                    $focusMark = $skillMarks[mb_strtolower(trim($focusItem))] ?? null;
                                @endphp
                                <li><x-pf.skill-chip :label="$focusItem" :icon="$modernMaterial ? $focusMark : null" :icon-name="$creativeMaterial ? ($skillIcons[mb_strtolower(trim($focusItem))] ?? null) : null" :material="$modernMaterial || $creativeMaterial ? 'raised' : null" /></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                @if (count($aboutFacts))
                    @if ($creativeMaterial)
                        <div class="pf-quick-facts surface-inset">
                            <p class="pf-quick-facts__heading">Quick facts</p>
                    @endif
                    <dl class="pf-facts {{ $modernMaterial ? 'surface-inset' : '' }} {{ $creativeMaterial ? 'pf-facts--creative' : '' }}">
                        @foreach ($aboutFacts as $label => $value)
                            <div class="pf-facts__row"><dt>@if ($creativeMaterial)<x-pf.icon :name="$factIcons[$label] ?? 'link'" class="pf-facts__icon" />@endif<span>{{ $label }}</span></dt><dd>{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                    @if ($creativeMaterial)
                        </div>
                    @endif
                @endif
            </div>
        </x-pf.section>
    @endif

    @if ($displaySkills->isNotEmpty())
        <x-pf.section id="skills" title="Skills" class="pf-skills {{ $creativeMaterial ? 'surface-raised pf-creative-card' : '' }}">
            <ul class="pf-skills-grid {{ $displaySkills->count() > 12 ? 'pf-skills-grid--dense' : '' }} {{ $creativeMaterial ? 'pf-skills-grid--creative' : '' }}">
                @foreach ($displaySkills as $skill)
                    @php
                        $skillMark = $skillMarks[mb_strtolower(trim($skill))] ?? null;
                        $skillIcon = $skillIcons[mb_strtolower(trim($skill))] ?? null;
                    @endphp
                    <li><x-pf.skill-chip :label="$skill" :icon="$modernMaterial ? $skillMark : null" :icon-name="$creativeMaterial ? $skillIcon : null" :material="$modernMaterial || $creativeMaterial ? 'raised' : null" /></li>
                @endforeach
            </ul>
        </x-pf.section>
    @endif

    @if (count($portfolio->projects ?? []))
        <x-pf.section id="projects" title="Projects" class="pf-projects {{ $creativeMaterial ? 'surface-raised pf-creative-card' : '' }}">
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
                            :creative="$creativeMaterial"
                            :material="$modernMaterial ? 'raised' : null"
                        />
                    </li>
                @endforeach
            </ul>
        </x-pf.section>
    @endif

    @if (count($portfolio->work_experience ?? []))
        <x-pf.section id="experience" title="Experience" class="pf-experience {{ $creativeMaterial ? 'surface-raised pf-creative-card' : '' }}">
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
        <x-pf.section id="education" title="Education" class="pf-education {{ $creativeMaterial ? 'surface-raised pf-creative-card' : '' }}">
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
        <x-pf.section id="contact" title="Contact" class="pf-contact {{ $modernMaterial || $creativeMaterial ? 'surface-raised' : '' }} {{ $creativeMaterial ? 'pf-creative-card' : '' }}">
            @if ($creativeMaterial)<p class="pf-contact__intro">Have a project in mind? Reach out using one of the options below.</p>@endif
            <x-pf.contact-links :links="$portfolio->social_links ?? []" :email="$portfolio->contact_email" :creative="$creativeMaterial" />
        </x-pf.section>
    @endif
</div>

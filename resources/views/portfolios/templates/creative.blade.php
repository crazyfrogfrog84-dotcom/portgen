@php
    $profileImage = $profileImage ?? null;
    $headline = trim((string) $portfolio->headline);
    $about = trim((string) $portfolio->about_me);
    $summaryText = trim(preg_replace('/\s+/u', ' ', $about) ?? '');
    $summary = $summaryText !== '' && mb_strtolower($summaryText) !== mb_strtolower($headline)
        ? \Illuminate\Support\Str::limit($summaryText, 180, '…', true)
        : '';
    $hasProjects = count($portfolio->projects ?? []) > 0;
    $hasContact = filled($portfolio->contact_email) || count($portfolio->social_links ?? []) > 0;
    $hasAbout = filled($about) || filled($portfolio->location) || filled($portfolio->availability)
        || count($portfolio->education ?? []) > 0 || count($portfolio->interests ?? []) > 0 || filled($portfolio->contact_email);
    $primaryHref = $hasProjects ? '#projects' : ($hasContact ? '#contact' : null);
    $secondaryHref = $hasProjects && $hasContact ? '#contact' : ($hasAbout ? '#about' : null);
@endphp

<article class="theme-creative pf-template-content pf-template-content--creative" aria-label="Creative portfolio template">
    <section id="home" class="pf-hero pf-creative-hero surface-raised" aria-label="Introduction">
        <div class="pf-hero__copy pf-creative-hero__copy">
            <p class="pf-hero__eyebrow pf-creative-hero__eyebrow"><span class="pf-creative-hero__eyebrow-dot" aria-hidden="true"></span>Hi, I’m</p>
            <h1>{{ $portfolio->full_name }}</h1>
            @if ($headline !== '')
                <p class="pf-hero__headline">{{ $headline }}</p>
            @endif
            @if ($summary !== '')
                <p class="pf-hero__summary">{{ $summary }}</p>
            @endif
            <div class="pf-hero__actions">
                @if ($primaryHref)
                    <a class="button-primary" href="{{ $primaryHref }}">{{ $hasProjects ? 'View my projects' : 'Contact me' }}<span aria-hidden="true">→</span></a>
                @endif
                @if ($secondaryHref)
                    <a class="button-secondary surface-raised" href="{{ $secondaryHref }}">{{ $hasProjects && $hasContact ? 'Contact me' : 'About me' }}</a>
                @endif
            </div>
        </div>
        <div class="pf-creative-hero__visual">
            <span class="pf-creative-hero__backplate" aria-hidden="true"></span>
            <x-pf.avatar :name="$portfolio->full_name" :image-url="$profileImage ? route('portfolio.image', ['portfolio' => $portfolio, 'image' => $profileImage]) : null" :alt="$profileImage?->alt ?? ''" />
            <span class="pf-creative-hero__sparkle" aria-hidden="true"><x-pf.icon name="sparkle" /></span>
        </div>
    </section>

    @include('portfolios.templates.sections', ['portfolio' => $portfolio, 'projectImages' => $projectImages, 'templateName' => 'creative'])
</article>

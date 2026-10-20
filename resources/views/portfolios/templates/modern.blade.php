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
    $hasAbout = filled($about);
    $primaryHref = $hasProjects ? '#projects' : ($hasContact ? '#contact' : null);
    $secondaryHref = $hasProjects && $hasContact ? '#contact' : ($hasAbout ? '#about' : null);
@endphp

<article class="theme-modern pf-template-content pf-template-content--modern" aria-label="Modern portfolio template">
    <section id="home" class="pf-hero pf-modern-hero" aria-label="Introduction">
        <div class="pf-hero__copy pf-modern-hero__copy">
            <p class="pf-hero__eyebrow"><span class="pf-modern-hero__eyebrow-mark" aria-hidden="true"></span>Hello, I’m</p>
            <h1>{{ $portfolio->full_name }}</h1>
            @if ($headline !== '')
                <p class="pf-hero__headline">{{ $headline }}</p>
            @endif
            @if ($summary !== '')
                <p class="pf-hero__summary">{{ $summary }}</p>
            @endif
            <div class="pf-hero__actions">
                @if ($primaryHref)
                    <a class="button-primary" href="{{ $primaryHref }}">{{ $hasProjects ? 'View my work' : 'Contact me' }}<span aria-hidden="true">→</span></a>
                @endif
                @if ($secondaryHref)
                    <a class="button-secondary" href="{{ $secondaryHref }}">{{ $hasProjects && $hasContact ? 'Contact me' : 'About me' }}</a>
                @endif
            </div>
        </div>
        <div class="pf-modern-hero__visual" aria-hidden="false">
            <span class="pf-modern-hero__glow" aria-hidden="true"></span>
            <x-pf.avatar :name="$portfolio->full_name" :image-url="$profileImage ? route('portfolio.image', ['portfolio' => $portfolio, 'image' => $profileImage]) : null" :alt="$profileImage?->alt ?? ''" />
        </div>
    </section>
    @include('portfolios.templates.sections', ['portfolio' => $portfolio, 'projectImages' => $projectImages, 'templateName' => 'modern'])
</article>

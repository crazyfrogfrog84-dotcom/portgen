@php
    $aboutExcerpt = trim((string) $portfolio->about_me);
    $hasContact = filled($portfolio->contact_email) || count($portfolio->social_links ?? []) > 0;
@endphp

<article class="theme-simple pf-template-content pf-template-content--simple" aria-label="Simple portfolio template">
    <x-pf.hero
        :name="$portfolio->full_name"
        :headline="$portfolio->headline ?? ''"
        :summary="$aboutExcerpt"
        :primary-href="count($portfolio->projects ?? []) ? '#projects' : null"
        primary-label="View my projects"
        :secondary-href="$hasContact ? '#contact' : null"
        secondary-label="Contact me"
        :image-url="$profileImage ? route('portfolio.image', ['portfolio' => $portfolio, 'image' => $profileImage]) : null"
        :image-alt="$profileImage?->alt ?? ''"
    />

    @include('portfolios.templates.sections', ['portfolio' => $portfolio, 'projectImages' => $projectImages])
</article>

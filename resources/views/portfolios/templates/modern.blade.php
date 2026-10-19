@php($profileImage = $profileImage ?? null)
<article class="theme-modern pf-template-content pf-template-content--modern" aria-label="Modern portfolio template">
    <section id="home" class="pf-legacy-hero rounded-2xl border border-current/20 p-6 sm:p-10" aria-label="Introduction">
        <p class="text-sm font-semibold uppercase tracking-widest">Portfolio</p>
        <div class="pf-hero">
            <div><h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-6xl">{{ $portfolio->full_name }}</h1><p class="mt-3 max-w-2xl text-xl">{{ $portfolio->headline }}</p></div>
            <x-pf.avatar :name="$portfolio->full_name" :image-url="$profileImage ? route('portfolio.image', ['portfolio' => $portfolio, 'image' => $profileImage]) : null" :alt="$profileImage?->alt ?? ''" />
        </div>
    </section>
    @include('portfolios.templates.sections', ['portfolio' => $portfolio, 'projectImages' => $projectImages])
</article>

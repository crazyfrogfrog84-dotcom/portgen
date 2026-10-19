@php($profileImage = $profileImage ?? null)
<article class="theme-simple pf-portfolio mx-auto max-w-5xl" data-template="simple" data-theme="system" aria-label="Simple portfolio template">
    <header class="max-w-3xl border-b border-current/20 pb-6">
        <x-pf.theme-toggle template="simple" />
        <p class="text-sm font-semibold uppercase tracking-widest">Portfolio</p>
        <div class="pf-hero">
            <div><h1 class="mt-2 text-4xl font-bold tracking-tight sm:text-6xl">{{ $portfolio->full_name }}</h1><p class="mt-3 text-xl">{{ $portfolio->headline }}</p></div>
            <x-pf.avatar :name="$portfolio->full_name" :image-url="$profileImage ? route('portfolio.image', ['portfolio' => $portfolio, 'image' => $profileImage]) : null" :alt="$profileImage?->alt ?? ''" />
        </div>
    </header>
    @include('portfolios.templates.sections', ['portfolio' => $portfolio])
</article>

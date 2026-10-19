@php($profileImage = $profileImage ?? null)
<article class="theme-modern pf-portfolio mx-auto max-w-6xl" data-template="modern" data-theme="system" aria-label="Modern portfolio template">
    <header class="rounded-2xl border border-current/20 p-6 sm:p-10">
        <x-pf.theme-toggle template="modern" />
        <p class="text-sm font-semibold uppercase tracking-widest">Selected work · Portfolio</p>
        <div class="pf-hero">
            <div><h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-6xl">{{ $portfolio->full_name }}</h1><p class="mt-3 max-w-2xl text-xl">{{ $portfolio->headline }}</p></div>
            <x-pf.avatar :name="$portfolio->full_name" :image-url="$profileImage ? route('portfolio.image', ['portfolio' => $portfolio, 'image' => $profileImage]) : null" :alt="$profileImage?->alt ?? ''" />
        </div>
        <p class="mt-5 inline-flex min-h-11 items-center rounded-full border border-current/30 px-4">{{ count($portfolio->projects) }} projects · {{ count($portfolio->skills) }} skills</p>
    </header>
    @include('portfolios.templates.sections', ['portfolio' => $portfolio])
</article>

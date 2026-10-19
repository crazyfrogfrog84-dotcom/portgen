@php($profileImage = $profileImage ?? null)
<article class="theme-creative pf-portfolio mx-auto max-w-5xl" data-template="creative" data-theme="system" aria-label="Creative portfolio template">
    <header class="rounded-2xl border-2 border-current p-6 sm:p-10">
        <x-pf.theme-toggle template="creative" />
        <p class="text-sm font-bold uppercase tracking-widest">Hello, I'm</p>
        <div class="pf-hero">
            <div><h1 class="mt-2 text-5xl font-black tracking-tight sm:text-7xl">{{ $portfolio->full_name }}</h1><p class="mt-4 max-w-2xl text-2xl font-semibold">{{ $portfolio->headline }}</p></div>
            <x-pf.avatar :name="$portfolio->full_name" :image-url="$profileImage ? route('portfolio.image', ['portfolio' => $portfolio, 'image' => $profileImage]) : null" :alt="$profileImage?->alt ?? ''" />
        </div>
    </header>
    @include('portfolios.templates.sections', ['portfolio' => $portfolio])
</article>

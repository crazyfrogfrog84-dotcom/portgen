<article class="theme-simple mx-auto max-w-5xl rounded-2xl border border-current/15 p-6 sm:p-10" aria-label="Simple portfolio template">
    <header class="max-w-3xl border-b border-current/20 pb-6">
        <p class="text-sm font-semibold uppercase tracking-widest">Portfolio</p>
        <h1 class="mt-2 text-4xl font-bold tracking-tight sm:text-6xl">{{ $portfolio->full_name }}</h1>
        <p class="mt-3 text-xl">{{ $portfolio->headline }}</p>
    </header>
    @include('portfolios.templates.sections', ['portfolio' => $portfolio])
</article>

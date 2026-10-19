<article class="theme-modern mx-auto max-w-6xl rounded-2xl border border-current/15 p-6 sm:p-10" aria-label="Modern portfolio template">
    <header class="rounded-2xl border border-current/20 p-6 sm:p-10">
        <p class="text-sm font-semibold uppercase tracking-widest">Selected work · Portfolio</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-6xl">{{ $portfolio->full_name }}</h1>
        <p class="mt-3 max-w-2xl text-xl">{{ $portfolio->headline }}</p>
        <p class="mt-5 inline-flex min-h-11 items-center rounded-full border border-current/30 px-4">{{ count($portfolio->projects) }} projects · {{ count($portfolio->skills) }} skills</p>
    </header>
    @include('portfolios.templates.sections', ['portfolio' => $portfolio])
</article>

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="description" content="Create a clear, shareable portfolio with PortGen.">
    <title>@yield('title', 'PortGen — your portfolio, ready to share')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--simple-background)] text-[var(--simple-foreground)]">
    <a class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-4 focus:py-3 focus:text-black" href="#main">Skip to main content</a>
    <header class="border-b border-slate-300/50 px-4 py-4 dark:border-slate-700">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
            <a class="min-h-11 inline-flex items-center font-semibold" href="{{ route('home') }}">PortGen</a>
            <a class="min-h-11 inline-flex items-center rounded px-3 underline" href="{{ route('portfolios.create') }}">Create a portfolio</a>
        </div>
    </header>
    @if (session('status'))
        <div class="mx-auto max-w-6xl px-4 pt-4" role="status" aria-live="polite">
            <p class="rounded-lg border border-emerald-700 px-4 py-3">{{ session('status') }}</p>
        </div>
    @endif
    <main id="main" class="mx-auto max-w-6xl px-4 py-8" tabindex="-1">
        @yield('content')
    </main>
    <footer class="mx-auto max-w-6xl px-4 pb-8 text-sm">
        <p>Made with PortGen. Your work, presented clearly.</p>
    </footer>
</body>
</html>

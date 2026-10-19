<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Create a clear, shareable portfolio with PortGen.">
    <title>@yield('title', 'PortGen — your portfolio, ready to share')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen" @if (request()->routeIs('portfolio.select-template') && session()->has('ownerManageUrl')) data-clear-draft="true" @endif>
    <a class="skip-link sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:px-4 focus:py-3" href="#main">Skip to main content</a>
    <header class="site-header border-b border-current/20 px-4 py-4">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4">
            <a class="min-h-11 inline-flex items-center font-semibold" href="{{ route('home') }}">PortGen</a>
            <nav aria-label="Main navigation" class="flex flex-wrap items-center gap-2">
                <a class="button-text" href="{{ route('help') }}">Help</a>
                <button class="button-text" type="button" data-dialog-open="help-dialog" aria-haspopup="dialog">Shortcuts <kbd>?</kbd></button>
                <a class="button-secondary" href="{{ route('portfolios.create') }}">Create a portfolio</a>
            </nav>
        </div>
    </header>
    @if (session('status'))
        <div class="mx-auto max-w-6xl px-4 pt-4" role="status" aria-live="polite" data-session-status>
            <p class="status-success">{{ session('status') }}</p>
        </div>
    @endif
    <main id="main" class="mx-auto max-w-6xl px-4 py-8" tabindex="-1">
        @yield('content')
    </main>
    <footer class="site-footer mx-auto max-w-6xl px-4 py-6 text-sm">
        <p>Made with PortGen. Your work, presented clearly.</p>
    </footer>
    <div class="status-neutral" role="status" aria-live="polite" aria-atomic="true" data-toast hidden></div>

    <dialog id="help-dialog" aria-labelledby="help-dialog-title" data-help-dialog>
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide">Keyboard help</p>
                <h2 id="help-dialog-title" class="mt-1 text-2xl font-bold">Shortcuts</h2>
            </div>
            <button class="button-secondary" type="button" data-dialog-close aria-label="Close keyboard help">Close</button>
        </div>
        <dl class="mt-4 grid gap-3">
            <div><dt class="font-semibold"><kbd>Ctrl</kbd> or <kbd>⌘</kbd> + <kbd>S</kbd></dt><dd>Save your draft or submit the current step.</dd></div>
            <div><dt class="font-semibold"><kbd>Alt</kbd> + <kbd>→</kbd></dt><dd>Continue to the next step.</dd></div>
            <div><dt class="font-semibold"><kbd>Alt</kbd> + <kbd>←</kbd></dt><dd>Go back to the previous step.</dd></div>
            <div><dt class="font-semibold"><kbd>?</kbd></dt><dd>Open this keyboard help.</dd></div>
        </dl>
        <p class="mt-4"><a href="{{ route('help') }}">Read the help and FAQ</a></p>
    </dialog>
</body>
</html>

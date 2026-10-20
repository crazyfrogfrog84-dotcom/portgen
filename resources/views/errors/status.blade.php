<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-app-theme="system">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'PortGen — something went wrong')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <header class="site-header px-4 py-3">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
            <a class="min-h-11 inline-flex items-center font-semibold" href="{{ route('home') }}">PortGen</a>
            <x-pf.theme-toggle template="error" scope="app" />
        </div>
    </header>
    <main id="main" class="mx-auto max-w-2xl px-4 py-16" aria-labelledby="error-title">
        <h1 id="error-title">@yield('heading', 'We could not complete that request')</h1>
        <p>@yield('message', 'Please try again, or return to the start of the portfolio form.')</p>
        @hasSection('action')<p>@yield('action')</p>@endif
        <p><a href="{{ route('portfolios.create') }}">Return to PortGen</a></p>
    </main>
</body>
</html>

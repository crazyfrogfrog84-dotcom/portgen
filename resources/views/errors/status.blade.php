<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'PortGen — something went wrong')</title>
</head>
<body>
    <main id="main" aria-labelledby="error-title">
        <p>PortGen</p>
        <h1 id="error-title">@yield('heading', 'We could not complete that request')</h1>
        <p>@yield('message', 'Please try again, or return to the start of the portfolio form.')</p>
        <p><a href="{{ route('portfolios.create') }}">Return to PortGen</a></p>
    </main>
</body>
</html>

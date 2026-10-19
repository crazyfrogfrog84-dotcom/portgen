<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="description" content="{{ $portfolio->headline ?: 'A portfolio by '.$portfolio->full_name }}">
    <title>@yield('title', $portfolio->full_name.' — Portfolio')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main">Skip to main content</a>
    @yield('content')
</body>
</html>

@extends('layouts.app')

@section('title', 'Log in — PortGen')

@section('content')
    <section class="mx-auto max-w-lg space-y-6">
        <header class="space-y-2">
            <p class="text-sm font-semibold uppercase tracking-wide">Your PortGen account</p>
            <h1 class="text-3xl font-bold tracking-tight">Log in</h1>
            <p>Accounts keep your portfolios together. You can also create and manage a portfolio as a guest.</p>
        </header>

        <form class="form-card grid gap-5 rounded-2xl p-6" method="POST" action="{{ route('login.store') }}" data-loading-form>
            @csrf
            @if ($errors->any())
                <div class="status-error" role="alert" aria-live="assertive" tabindex="-1" data-auth-error-summary>
                    <p class="font-bold">We could not log you in.</p>
                    <ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                </div>
            @endif
            @if (session('lockoutSeconds'))
                <p class="status-error" role="status" data-lockout-countdown data-seconds="{{ session('lockoutSeconds') }}">Try again in <span>{{ session('lockoutSeconds') }}</span> seconds.</p>
            @endif

            <div class="grid gap-2">
                <label class="font-semibold" for="email">Email address</label>
                <input class="form-control" id="email" name="email" type="email" autocomplete="username" inputmode="email" maxlength="190" required autofocus value="{{ old('email') }}" @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                @error('email')<span class="text-sm font-semibold" id="email-error" data-inline-error>{{ $message }}</span>@enderror
            </div>

            <div class="grid gap-2">
                <label class="font-semibold" for="password">Password</label>
                <div class="flex gap-2">
                    <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required data-caps-lock-input>
                    <button class="button-secondary shrink-0" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false">Show</button>
                </div>
                <p class="text-sm text-muted" data-caps-lock-warning hidden>Caps Lock is on.</p>
            </div>

            <label class="flex min-h-11 items-center gap-3 font-semibold">
                <input name="remember" type="checkbox" value="1" @checked(old('remember'))>
                Remember me on this device
            </label>

            <button class="button-primary w-full" type="submit" data-loading-label="Logging in...">Log in</button>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('register') }}">Create an account</a>
            </div>
        </form>

        <aside class="surface grid gap-3 rounded-xl border p-5">
            <h2 class="text-xl font-bold">Keep going as a guest</h2>
            <p>You do not need an account to build or share a portfolio. A private link lets you manage it later.</p>
            <a class="button-secondary w-fit" href="{{ route('portfolios.create') }}">Continue as guest</a>
        </aside>
    </section>
@endsection

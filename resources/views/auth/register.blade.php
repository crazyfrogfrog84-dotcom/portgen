@extends('layouts.app')

@section('title', 'Create an account — PortGen')

@section('content')
    <section class="mx-auto max-w-lg space-y-6">
        <header class="space-y-2">
            <p class="text-sm font-semibold uppercase tracking-wide">Optional account</p>
            <h1 class="text-3xl font-bold tracking-tight">Create an account</h1>
            <p>An account keeps your portfolios in one place so you can manage them without saving a private link. Guests can still create portfolios without registering.</p>
            @if ($claimUrl)<p class="status-neutral">The portfolio you just made will be added to this account.</p>@endif
        </header>

        <form class="form-card grid gap-5 rounded-2xl p-6" method="POST" action="{{ route('register.store') }}" data-loading-form>
            @csrf
            @if ($claimUrl)<input type="hidden" name="claim_url" value="{{ $claimUrl }}">@endif
            @if ($errors->any())
                <div class="status-error" role="alert" aria-live="assertive" tabindex="-1" data-auth-error-summary>
                    <p class="font-bold">Review the details below and try again.</p>
                    <ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="grid gap-2">
                <label class="font-semibold" for="name">Your name</label>
                <input class="form-control" id="name" name="name" type="text" autocomplete="name" maxlength="120" required autofocus value="{{ old('name') }}" @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
                @error('name')<span class="text-sm font-semibold" id="name-error" data-inline-error>{{ $message }}</span>@enderror
            </div>
            <div class="grid gap-2">
                <label class="font-semibold" for="email">Email address</label>
                <input class="form-control" id="email" name="email" type="email" autocomplete="username" inputmode="email" maxlength="190" required value="{{ old('email') }}" @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                @error('email')<span class="text-sm font-semibold" id="email-error" data-inline-error>{{ $message }}</span>@enderror
            </div>
            <div class="grid gap-2">
                <label class="font-semibold" for="password">Password</label>
                <div class="flex gap-2">
                    <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required aria-describedby="password-requirement @if ($errors->has('password'))password-error @endif" @if ($errors->has('password')) aria-invalid="true" @endif data-password-requirement-input>
                    <button class="button-secondary shrink-0" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false">Show</button>
                </div>
                <p class="text-sm text-muted" id="password-requirement" data-password-requirement aria-live="polite">Use at least 8 characters.</p>
                @error('password')<span class="text-sm font-semibold" id="password-error" data-inline-error>{{ $message }}</span>@enderror
            </div>
            <div class="grid gap-2">
                <label class="font-semibold" for="password_confirmation">Confirm password</label>
                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
            </div>
            <button class="button-primary w-full" type="submit" data-loading-label="Creating account...">Create account</button>
            <p>Already have an account? <a href="{{ route('login') }}">Log in</a></p>
            <p><a href="{{ route('portfolios.create') }}">Continue as guest</a></p>
        </form>
    </section>
@endsection

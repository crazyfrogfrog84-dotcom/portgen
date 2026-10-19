@extends('layouts.portfolio')

@section('title', $portfolio->full_name.' — PortGen portfolio')

@section('content')
    @php
        $nameParts = preg_split('/\s+/u', trim($portfolio->full_name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $brandMark = collect(array_slice($nameParts, 0, 2))->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
    @endphp
    <div class="pf-page" data-template="{{ $template }}" data-theme="system">
        <header class="pf-site-header">
            <div class="pf-site-header__inner">
                <a class="pf-brand" href="#home">
                    <span class="pf-brand__mark" aria-hidden="true">{{ $brandMark }}</span>
                    <span class="pf-brand__name">{{ $portfolio->full_name }}</span>
                </a>
                <x-pf.nav :items="$navItems" />
                <x-pf.theme-toggle :template="$template" />
            </div>
        </header>
        <main id="main" class="pf-main" tabindex="-1">
            @include('portfolios.templates.'.$template, ['portfolio' => $portfolio, 'profileImage' => $profileImage, 'projectImages' => $projectImages])
        </main>
        @if ($ownerManageUrl)
            <aside class="pf-owner-note" aria-labelledby="manage-link-heading">
                @include('portfolios.stepper', ['active' => 3])
                <h2 id="manage-link-heading">Your portfolio is ready</h2>
                <p>Keep this private link to manage your portfolio later. Anyone with the link can manage it.</p>
                <div class="pf-owner-note__actions">
                    <input id="owner-manage-link" type="url" readonly value="{{ $ownerManageUrl }}" aria-label="Private manage link" class="form-control">
                    <button type="button" class="button-secondary" data-copy-target="owner-manage-link">Copy private link</button>
                </div>
                <p data-copy-status role="status" aria-live="polite"></p>
                <p>Public page: <a href="{{ route('portfolio.show', ['portfolio' => $portfolio]) }}">{{ route('portfolio.show', ['portfolio' => $portfolio]) }}</a></p>
            </aside>
        @endif
        <x-pf.footer :name="$portfolio->full_name" :links="$portfolio->social_links ?? []" :email="$portfolio->contact_email" />
    </div>
@endsection

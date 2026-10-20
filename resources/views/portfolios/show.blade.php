@extends('layouts.portfolio')

@section('title', $portfolio->full_name.' — PortGen portfolio')

@section('content')
    @php
        $nameParts = preg_split('/\s+/u', trim($portfolio->full_name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $brandMark = collect(array_slice($nameParts, 0, 2))->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
    @endphp
    <div class="pf-page" data-template="{{ $template }}" data-theme="system" data-portfolio-slug="{{ $portfolio->slug }}">
        @if ($showReadyBanner)
            <aside class="pf-ready-banner surface-raised" aria-label="Portfolio ready" data-ready-banner data-copy-context>
                <p>Your portfolio is ready.</p>
                @if ($isAccountOwner)
                    <a class="button-secondary" href="{{ route('dashboard') }}">Open My portfolios</a>
                @else
                    <span>Copy private link.</span>
                    <button class="button-secondary surface-raised" type="button" data-copy-link="{{ $ownerManageUrl }}">Copy private link</button>
                @endif
                <span class="pf-copy-status" data-copy-status role="status" aria-live="polite"></span>
                <button class="button-text" type="button" aria-label="Dismiss portfolio ready message" data-dismiss-ready>Dismiss</button>
            </aside>
        @endif
        <header class="pf-site-header">
            <div class="pf-site-header__inner">
                <a class="pf-brand" href="#home">
                    <span class="pf-brand__mark" aria-hidden="true">{{ $brandMark }}</span>
                    <span class="pf-brand__name">{{ $portfolio->full_name }}</span>
                </a>
                <x-pf.nav :items="$navItems" />
                <div class="pf-site-header__tools">
                    @if ($isPortfolioOwner)
                        <x-pf.owner-toolbar
                            :portfolio="$portfolio"
                            :selected-template="$selectedTemplate"
                            :is-preview="$isTemplatePreview"
                            :is-account-owner="$isAccountOwner"
                            :owner-urls="$ownerUrls"
                            :private-link="$ownerManageUrl"
                            :show-saved="$showTemplateSaved"
                        />
                    @endif
                    <x-pf.theme-toggle :template="$template" />
                </div>
            </div>
        </header>
        <main id="main" class="pf-main" tabindex="-1">
            @include('portfolios.templates.'.$template, ['portfolio' => $portfolio, 'profileImage' => $profileImage, 'projectImages' => $projectImages])
        </main>
        <x-pf.footer :name="$portfolio->full_name" :links="$portfolio->social_links ?? []" :email="$portfolio->contact_email" :simple="$template === 'simple'" />
    </div>
@endsection

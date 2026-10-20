@props([
    'portfolio',
    'selectedTemplate',
    'isPreview' => false,
    'isAccountOwner' => false,
    'ownerUrls' => [],
    'privateLink' => null,
    'showSaved' => false,
])

@php
    $templates = ['Simple', 'Modern', 'Creative'];
    $publicUrl = route('portfolio.show', ['portfolio' => $portfolio]);
    $selectedKey = strtolower($selectedTemplate);
    $popoverId = 'template-options-'.$portfolio->id;
    $manageId = 'manage-options-'.$portfolio->id;
@endphp

<div class="pf-owner-toolbar" aria-label="Portfolio owner toolbar" data-owner-toolbar>
    <div class="pf-template-control" data-template-control>
        <div class="pf-template-control__desktop">
            <span class="pf-template-control__label" id="template-label-{{ $portfolio->id }}">Template</span>
            <div class="pf-template-control__options surface" role="radiogroup" aria-labelledby="template-label-{{ $portfolio->id }}" data-template-radiogroup>
                @foreach ($templates as $choice)
                    @php($isSelected = strtolower($choice) === $selectedKey)
                    <a class="pf-template-control__option surface-raised" role="radio" aria-checked="{{ $isSelected ? 'true' : 'false' }}" href="{{ route('portfolio.show', ['portfolio' => $portfolio, 'preview' => $choice]) }}" data-template-option>
                        <span>{{ $choice }}</span>
                        <span class="pf-template-control__check" aria-hidden="true">✓</span>
                    </a>
                @endforeach
            </div>
        </div>

        <details class="pf-template-control__mobile" data-owner-disclosure data-template-popover>
            <summary class="pf-owner-summary surface-raised" aria-controls="{{ $popoverId }}" aria-expanded="false" data-disclosure-button>Template: {{ $selectedTemplate }}</summary>
            <div class="pf-template-control__popover surface" id="{{ $popoverId }}" role="radiogroup" aria-label="Template" data-template-radiogroup>
                @foreach ($templates as $choice)
                    @php($isSelected = strtolower($choice) === $selectedKey)
                    <a class="pf-template-control__option surface-raised" role="radio" aria-checked="{{ $isSelected ? 'true' : 'false' }}" href="{{ route('portfolio.show', ['portfolio' => $portfolio, 'preview' => $choice]) }}" data-template-option>
                        <span>{{ $choice }}</span>
                        <span class="pf-template-control__check" aria-hidden="true">✓</span>
                    </a>
                @endforeach
            </div>
        </details>
    </div>

    @if ($isPreview)
        <div class="pf-template-preview-status" aria-live="polite" data-template-preview-status>
            <span>Previewing {{ $selectedTemplate }}. Not saved yet</span>
            <form method="POST" action="{{ $ownerUrls['saveTemplate'] }}">
                @csrf
                <input type="hidden" name="template_choice" value="{{ $selectedTemplate }}">
                <button class="button-primary surface-raised" type="submit">Save template</button>
            </form>
            <a class="button-secondary surface-raised" href="{{ $publicUrl }}">Cancel</a>
        </div>
    @elseif ($showSaved)
        <p class="pf-template-saved" role="status" aria-live="polite">Template saved</p>
    @endif

    <details class="pf-manage-control" data-owner-disclosure>
        <summary class="pf-owner-summary surface-raised" aria-controls="{{ $manageId }}" aria-expanded="false" data-disclosure-button>Manage</summary>
        <div class="pf-manage-control__popover surface" id="{{ $manageId }}" data-copy-context>
            <ul>
                <li><a class="surface-raised" href="{{ $ownerUrls['edit'] }}">Edit portfolio</a></li>
                <li><a class="surface-raised" href="{{ $ownerUrls['manage'] }}">Manage page</a></li>
                @if ($isAccountOwner)
                    <li><a class="surface-raised" href="{{ route('dashboard') }}">My portfolios</a></li>
                @elseif ($privateLink)
                    <li><button class="pf-manage-control__copy surface-raised" type="button" data-copy-link="{{ $privateLink }}">Copy private link</button></li>
                @endif
            </ul>
            @unless ($isAccountOwner)
                <p class="pf-manage-control__note">Anyone with this link can manage a guest portfolio.</p>
                <p class="pf-copy-status" data-copy-status role="status" aria-live="polite"></p>
            @endunless
        </div>
    </details>
</div>

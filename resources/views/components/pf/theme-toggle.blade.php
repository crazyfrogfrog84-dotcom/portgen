@props(['template' => 'portfolio'])

<fieldset class="pf-theme-toggle" data-theme-toggle>
    <legend class="sr-only">Portfolio color theme</legend>
    @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'] as $value => $label)
        <label class="pf-theme-option" for="theme-{{ $template }}-{{ $value }}">
            <input class="sr-only" id="theme-{{ $template }}-{{ $value }}" type="radio" name="portfolio-theme" value="{{ $value }}" aria-label="Use {{ strtolower($label) }} theme" @checked($value === 'system')>
            <span class="pf-theme-option__icon" aria-hidden="true">
                @if ($value === 'light')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4" /><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" /></svg>
                @elseif ($value === 'dark')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.6 14.2A8.8 8.8 0 0 1 9.8 3.4 8.8 8.8 0 1 0 20.6 14.2Z" /></svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2" /><path d="M8 22h8m-4-4v4" /></svg>
                @endif
            </span>
        </label>
    @endforeach
</fieldset>

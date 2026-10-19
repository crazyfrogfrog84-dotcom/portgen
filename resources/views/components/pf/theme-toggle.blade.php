@props(['template' => 'portfolio'])

<fieldset class="pf-theme-toggle" data-theme-toggle>
    <legend>Theme</legend>
    @foreach (['system' => 'System', 'light' => 'Light', 'dark' => 'Dark'] as $value => $label)
        <label class="pf-theme-option" for="theme-{{ $template }}-{{ $value }}">
            <input id="theme-{{ $template }}-{{ $value }}" type="radio" name="portfolio-theme" value="{{ $value }}" @checked($value === 'system')>
            <span>{{ $label }}</span>
        </label>
    @endforeach
</fieldset>

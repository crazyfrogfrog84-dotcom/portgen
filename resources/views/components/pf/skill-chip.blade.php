@props(['label', 'icon' => null])

<span class="pf-skill-chip">
    @if ($icon)
        <span class="pf-skill-chip__icon" aria-hidden="true">{{ $icon }}</span>
    @endif
    <span>{{ $label }}</span>
</span>

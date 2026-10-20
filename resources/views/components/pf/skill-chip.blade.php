@props(['label', 'icon' => null, 'material' => null])

<span class="pf-skill-chip {{ $material === 'raised' ? 'surface-raised' : '' }}">
    @if ($icon)
        <span class="pf-skill-chip__icon" aria-hidden="true">{{ $icon }}</span>
    @endif
    <span>{{ $label }}</span>
</span>

@props(['label', 'icon' => null, 'iconName' => null, 'material' => null])

<span class="pf-skill-chip {{ $material === 'raised' ? 'surface-raised' : '' }}">
    @if ($icon)
        <span class="pf-skill-chip__icon" aria-hidden="true">{{ $icon }}</span>
    @endif
    @if ($iconName)
        <x-pf.icon :name="$iconName" class="pf-skill-chip__svg" />
    @endif
    <span>{{ $label }}</span>
</span>

@props(['title', 'organization' => '', 'period' => '', 'description' => ''])

<li class="pf-timeline-item">
    @if ($title !== '')<h3>{{ $title }}</h3>@endif
    @if ($organization !== '')
        <p class="pf-timeline-item__organization">{{ $organization }}</p>
    @endif
    @if ($period !== '')
        <p class="pf-timeline-item__period">{{ $period }}</p>
    @endif
    @if ($description !== '')
        <p>{{ $description }}</p>
    @endif
</li>

@props(['name', 'imageUrl' => null, 'alt' => ''])

@if ($imageUrl)
    <img class="pf-avatar" src="{{ $imageUrl }}" alt="{{ $alt }}" width="144" height="144" loading="lazy">
@else
    @php
        $nameParts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $monogram = collect(array_slice($nameParts, 0, 2))->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
    @endphp
    <span class="pf-avatar" role="img" aria-label="Monogram for {{ $name }}">{{ $monogram }}</span>
@endif

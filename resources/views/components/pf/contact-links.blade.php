@props(['links' => []])

@if (count($links))
    <ul class="pf-contact-links">
        @foreach ($links as $link)
            @if (! empty($link['label']) && ! empty($link['url']))
                <li>
                    <a class="pf-external-link" href="{{ $link['url'] }}" rel="noopener noreferrer">
                        {{ $link['label'] }} <span class="sr-only">(external link)</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
@endif

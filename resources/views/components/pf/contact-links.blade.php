@props(['links' => [], 'email' => null])

@if (count($links) || $email)
    <ul class="pf-contact-links">
        @if ($email)
            <li><a href="mailto:{{ $email }}"><span>Email: {{ $email }}</span><span class="pf-external-link__icon" aria-hidden="true">✉</span></a></li>
        @endif
        @foreach ($links as $link)
            @if (! empty($link['label']) && ! empty($link['url']))
                <li>
                    <a class="pf-external-link" href="{{ $link['url'] }}" rel="noopener noreferrer">
                        {{ $link['label'] }} <span class="pf-external-link__icon" aria-hidden="true">↗</span><span class="sr-only"> (external link)</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
@endif

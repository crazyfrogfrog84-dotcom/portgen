@props(['links' => [], 'email' => null, 'simple' => false, 'creative' => false])

@if (count($links) || $email)
    <ul class="pf-contact-links">
        @if ($email)
            <li><a class="{{ $simple || $creative ? 'button-primary surface-raised' : '' }}" href="mailto:{{ $email }}"><span>{{ $simple ? $email : 'Email: '.$email }}</span>@if ($creative)<x-pf.icon name="mail" class="pf-external-link__svg" />@else<span class="pf-external-link__icon" aria-hidden="true">✉</span>@endif</a></li>
        @endif
        @foreach ($links as $link)
            @if (! empty($link['label']) && ! empty($link['url']))
                <li>
                    <a class="pf-external-link {{ $simple || $creative ? 'button-secondary surface-raised' : '' }}" href="{{ $link['url'] }}" rel="noopener noreferrer">
                        {{ $link['label'] }} @if ($creative)<x-pf.icon name="external" class="pf-external-link__svg" />@else<span class="pf-external-link__icon" aria-hidden="true">↗</span>@endif<span class="sr-only"> (external link)</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
@endif

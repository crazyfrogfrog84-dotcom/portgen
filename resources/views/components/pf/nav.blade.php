@props(['items' => [], 'label' => 'Portfolio sections', 'variant' => 'horizontal'])

@if (count($items))
    <nav aria-label="{{ $label }}" class="pf-nav pf-nav--{{ $variant }}" data-section-nav>
        <ul class="pf-nav__list">
            @foreach ($items as $item)
                <li>
                    <a class="pf-nav__link" href="{{ $item['href'] }}" @if (! empty($item['current'])) aria-current="true" @endif>
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif

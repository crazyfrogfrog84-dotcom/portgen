@props(['items' => [], 'label' => 'Portfolio sections', 'template' => null])

@php($icons = ['Home' => 'home', 'About' => 'user', 'Skills' => 'code', 'Projects' => 'folder', 'Experience' => 'briefcase', 'Education' => 'graduation', 'Contact' => 'mail'])

@if (count($items))
    <nav aria-label="{{ $label }}" class="pf-nav" data-section-nav>
        <ul class="pf-nav__list">
            @foreach ($items as $item)
                <li>
                    <a class="pf-nav__link {{ ! empty($item['current']) ? 'surface-inset' : '' }}" href="{{ $item['href'] }}" @if (! empty($item['current'])) aria-current="true" @endif>
                        @if ($template === 'creative')<x-pf.icon :name="$icons[$item['label']] ?? 'link'" class="pf-nav__icon" />@endif
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif

@props(['id', 'title', 'class' => ''])

<section id="{{ $id }}" aria-labelledby="{{ $id }}-heading" class="pf-section {{ $class }}">
    <h2 id="{{ $id }}-heading">{{ $title }}</h2>
    <div class="pf-section__content">
        {{ $slot }}
    </div>
</section>

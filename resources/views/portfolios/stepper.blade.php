@php($steps = ['Your info', 'Choose template', 'Done and share'])

<ol class="stepper" aria-label="Portfolio creation progress" data-stepper>
    @foreach ($steps as $index => $label)
        @php($number = $index + 1)
        <li class="step-item" @if ($active === $number) aria-current="step" @endif>
            <span class="step-number" aria-hidden="true">{{ $number < $active ? '✓' : $number }}</span>
            <span>{{ $label }}@if ($number < $active) <span class="visually-hidden">(complete)</span>@endif</span>
        </li>
    @endforeach
</ol>

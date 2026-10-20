@props(['portfolio', 'selectedTemplate', 'isPreview' => false])

<aside class="pf-owner-toolbar" aria-label="Portfolio owner toolbar" data-owner-toolbar>
    <div class="pf-owner-toolbar__inner">
        <div class="pf-owner-toolbar__preview">
            <p class="pf-owner-toolbar__label">{{ $isPreview ? 'Previewing '.$selectedTemplate : 'Portfolio owner tools' }}</p>
            <nav class="pf-owner-toolbar__templates" aria-label="Preview a portfolio template">
                @foreach (['Simple', 'Modern', 'Creative'] as $choice)
                    <a class="button-secondary" href="{{ route('portfolio.show', ['portfolio' => $portfolio, 'preview' => $choice]) }}" @if ($selectedTemplate === $choice) aria-current="page" @endif>{{ $choice }}</a>
                @endforeach
            </nav>
        </div>
        <div class="pf-owner-toolbar__actions">
            <form method="POST" action="{{ route('portfolio.save-template', ['portfolio' => $portfolio]) }}">
                @csrf
                <input type="hidden" name="template_choice" value="{{ $selectedTemplate }}">
                <button class="button-primary" type="submit">Save</button>
            </form>
            <a class="button-secondary" href="{{ route('portfolio.show', ['portfolio' => $portfolio]) }}">Cancel</a>
        </div>
    </div>
</aside>

@extends('layouts.app')

@section('title', 'Help and common questions — PortGen')

@section('content')
    <article class="mx-auto max-w-3xl space-y-7">
        <p class="text-sm font-semibold uppercase tracking-wide">Help</p>
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Create and manage your portfolio</h1>
        <p class="text-muted text-lg">Add the information you want to share, choose a template, then copy your public link.</p>

        <section aria-labelledby="help-steps-heading">
            <h2 id="help-steps-heading" class="text-2xl font-semibold">How it works</h2>
            <ol class="mt-3 list-inside list-decimal space-y-2">
                <li>Fill in your name, headline, introduction, and at least one skill. Location, availability, contact email, photos, education, experience, projects, interests, personal notes, and links are optional.</li>
                <li>Choose Simple, Modern, or Creative. Each preview shows your information.</li>
                <li>Copy the public portfolio link to share. Copy and save your private manage link somewhere safe.</li>
            </ol>
        </section>

        <section aria-labelledby="help-faq-heading">
            <h2 id="help-faq-heading" class="text-2xl font-semibold">Common questions</h2>
            <div class="mt-3 space-y-3">
                <details class="form-section">
                    <summary>What is the difference between my public and private links?</summary>
                    <div class="section-body"><p>The public link shows your portfolio. The private manage link lets anyone who has it edit or delete your portfolio. Keep the private link somewhere only you can access.</p></div>
                </details>
                <details class="form-section">
                    <summary>Can I update my portfolio later?</summary>
                    <div class="section-body"><p>Yes. Open your private manage link and choose Edit portfolio or Change template.</p></div>
                </details>
                <details class="form-section">
                    <summary>What happens if I delete my portfolio?</summary>
                    <div class="section-body"><p>The public page disappears right away. You have 10 seconds to undo. After that, the portfolio is permanently removed.</p></div>
                </details>
                <details class="form-section">
                    <summary>What if I refresh while filling in the form?</summary>
                    <div class="section-body"><p>Before you submit, the draft stays in this browser on this device. After you submit, your portfolio is saved on the server. Do not use a shared browser for private information unless you clear its saved draft.</p></div>
                </details>
            </div>
        </section>

        <section aria-labelledby="shortcuts-heading">
            <h2 id="shortcuts-heading" class="text-2xl font-semibold">Keyboard shortcuts</h2>
            <dl class="mt-3 grid gap-3 sm:grid-cols-2">
                <div class="surface rounded-lg p-4"><dt class="font-semibold"><kbd>Ctrl</kbd> or <kbd>⌘</kbd> + <kbd>S</kbd></dt><dd>Save your draft or submit this step.</dd></div>
                <div class="surface rounded-lg p-4"><dt class="font-semibold"><kbd>Alt</kbd> + <kbd>→</kbd></dt><dd>Continue to the next step.</dd></div>
                <div class="surface rounded-lg p-4"><dt class="font-semibold"><kbd>Alt</kbd> + <kbd>←</kbd></dt><dd>Go back to the previous step.</dd></div>
                <div class="surface rounded-lg p-4"><dt class="font-semibold"><kbd>?</kbd></dt><dd>Open the shortcut dialog.</dd></div>
            </dl>
        </section>
    </article>
@endsection

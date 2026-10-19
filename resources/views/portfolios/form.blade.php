@php
    $educationRows = old('education', $portfolio?->education ?: [['institution' => '', 'qualification' => '', 'year' => '']]);
    $experienceRows = old('work_experience', $portfolio?->work_experience ?: [['company' => '', 'role' => '', 'description' => '', 'start_date' => '', 'end_date' => '']]);
    $skillRows = old('skills', $portfolio?->skills ?: ['']);
    $projectRows = old('projects', $portfolio?->projects ?: [['name' => '', 'description' => '', 'url' => '']]);
    $linkRows = old('social_links', $portfolio?->social_links ?: [['label' => '', 'url' => '']]);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" data-portfolio-form>
    @csrf
    @if ($formMethod === 'PUT')
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-700 p-4" role="alert" tabindex="-1">
            <h2 class="font-semibold">Please check these details</h2>
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="space-y-4 rounded-xl border border-slate-300/60 p-5 dark:border-slate-700" aria-labelledby="basics-heading">
        <h2 id="basics-heading" class="text-xl font-semibold">Basics</h2>
        <div>
            <label for="full_name" class="block font-medium">Your name</label>
            <p id="full_name-hint" class="text-sm text-[var(--simple-muted)]">Use the name people know you by.</p>
            <input id="full_name" name="full_name" value="{{ old('full_name', $portfolio?->full_name) }}" required maxlength="120" autocomplete="name" aria-describedby="full_name-hint @error('full_name') full_name-error @enderror" @error('full_name') aria-invalid="true" @enderror class="mt-1 w-full rounded-lg border border-slate-500 bg-white px-3 py-2 text-slate-950 dark:bg-slate-900 dark:text-white">
            @error('full_name')<p id="full_name-error" class="mt-1 font-medium text-red-800 dark:text-red-200">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="headline" class="block font-medium">Headline</label>
            <p id="headline-hint" class="text-sm text-[var(--simple-muted)]">Example: Product designer focused on accessible tools.</p>
            <input id="headline" name="headline" value="{{ old('headline', $portfolio?->headline) }}" required maxlength="180" aria-describedby="headline-hint @error('headline') headline-error @enderror" @error('headline') aria-invalid="true" @enderror class="mt-1 w-full rounded-lg border border-slate-500 bg-white px-3 py-2 text-slate-950 dark:bg-slate-900 dark:text-white">
            @error('headline')<p id="headline-error" class="mt-1 font-medium text-red-800 dark:text-red-200">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="about_me" class="block font-medium">About me</label>
            <p id="about_me-hint" class="text-sm text-[var(--simple-muted)]">Share what you do and the kind of work you enjoy.</p>
            <textarea id="about_me" name="about_me" rows="5" required maxlength="2000" aria-describedby="about_me-hint @error('about_me') about_me-error @enderror" @error('about_me') aria-invalid="true" @enderror class="mt-1 w-full rounded-lg border border-slate-500 bg-white px-3 py-2 text-slate-950 dark:bg-slate-900 dark:text-white">{{ old('about_me', $portfolio?->about_me) }}</textarea>
            @error('about_me')<p id="about_me-error" class="mt-1 font-medium text-red-800 dark:text-red-200">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="space-y-4 rounded-xl border border-slate-300/60 p-5 dark:border-slate-700" aria-labelledby="education-heading">
        <h2 id="education-heading" class="text-xl font-semibold">Education <span class="text-sm font-normal">(optional)</span></h2>
        @foreach ($educationRows as $index => $education)
            <fieldset class="grid gap-4 rounded-lg bg-slate-100 p-4 dark:bg-slate-800 sm:grid-cols-3">
                <legend class="sr-only">Education item {{ $index + 1 }}</legend>
                <div><label for="education-{{ $index }}-institution" class="block font-medium">School</label><input id="education-{{ $index }}-institution" name="education[{{ $index }}][institution]" value="{{ $education['institution'] ?? '' }}" maxlength="120" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
                <div><label for="education-{{ $index }}-qualification" class="block font-medium">Qualification</label><input id="education-{{ $index }}-qualification" name="education[{{ $index }}][qualification]" value="{{ $education['qualification'] ?? '' }}" maxlength="120" placeholder="Example: B.A. Design" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
                <div><label for="education-{{ $index }}-year" class="block font-medium">Year</label><input id="education-{{ $index }}-year" name="education[{{ $index }}][year]" value="{{ $education['year'] ?? '' }}" maxlength="20" placeholder="Example: 2025" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
            </fieldset>
        @endforeach
    </section>

    <section class="space-y-4 rounded-xl border border-slate-300/60 p-5 dark:border-slate-700" aria-labelledby="experience-heading">
        <h2 id="experience-heading" class="text-xl font-semibold">Experience <span class="text-sm font-normal">(optional)</span></h2>
        @foreach ($experienceRows as $index => $experience)
            <fieldset class="space-y-3 rounded-lg bg-slate-100 p-4 dark:bg-slate-800">
                <legend class="sr-only">Experience item {{ $index + 1 }}</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label for="experience-{{ $index }}-role" class="block font-medium">Role</label><input id="experience-{{ $index }}-role" name="work_experience[{{ $index }}][role]" value="{{ $experience['role'] ?? '' }}" maxlength="120" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
                    <div><label for="experience-{{ $index }}-company" class="block font-medium">Company</label><input id="experience-{{ $index }}-company" name="work_experience[{{ $index }}][company]" value="{{ $experience['company'] ?? '' }}" maxlength="120" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
                </div>
                <div><label for="experience-{{ $index }}-description" class="block font-medium">What you did</label><textarea id="experience-{{ $index }}-description" name="work_experience[{{ $index }}][description]" rows="3" maxlength="2000" class="mt-1 w-full rounded border border-slate-500 bg-white px-3 py-2 text-slate-950 dark:bg-slate-900 dark:text-white">{{ $experience['description'] ?? '' }}</textarea></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label for="experience-{{ $index }}-start" class="block font-medium">Start date</label><input id="experience-{{ $index }}-start" type="month" name="work_experience[{{ $index }}][start_date]" value="{{ $experience['start_date'] ?? '' }}" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
                    <div><label for="experience-{{ $index }}-end" class="block font-medium">End date</label><input id="experience-{{ $index }}-end" type="month" name="work_experience[{{ $index }}][end_date]" value="{{ $experience['end_date'] ?? '' }}" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
                </div>
            </fieldset>
        @endforeach
    </section>

    <section class="space-y-4 rounded-xl border border-slate-300/60 p-5 dark:border-slate-700" aria-labelledby="skills-heading">
        <h2 id="skills-heading" class="text-xl font-semibold">Skills</h2>
        <p id="skills-hint" class="text-sm text-[var(--simple-muted)]">Add at least one skill. Example: Research, Figma, or Python.</p>
        @foreach ($skillRows as $index => $skill)
            <div><label for="skill-{{ $index }}" class="block font-medium">Skill {{ $index + 1 }}</label><input id="skill-{{ $index }}" name="skills[]" value="{{ $skill }}" required maxlength="50" aria-describedby="skills-hint @error('skills') skills-error @enderror" @error('skills') aria-invalid="true" @enderror class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
        @endforeach
        @error('skills')<p id="skills-error" class="font-medium text-red-800 dark:text-red-200">{{ $message }}</p>@enderror
    </section>

    <section class="space-y-4 rounded-xl border border-slate-300/60 p-5 dark:border-slate-700" aria-labelledby="projects-heading">
        <h2 id="projects-heading" class="text-xl font-semibold">Projects <span class="text-sm font-normal">(optional)</span></h2>
        @foreach ($projectRows as $index => $project)
            <fieldset class="space-y-3 rounded-lg bg-slate-100 p-4 dark:bg-slate-800">
                <legend class="sr-only">Project item {{ $index + 1 }}</legend>
                <div><label for="project-{{ $index }}-name" class="block font-medium">Project name</label><input id="project-{{ $index }}-name" name="projects[{{ $index }}][name]" value="{{ $project['name'] ?? '' }}" maxlength="120" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
                <div><label for="project-{{ $index }}-description" class="block font-medium">Description</label><textarea id="project-{{ $index }}-description" name="projects[{{ $index }}][description]" rows="3" maxlength="2000" class="mt-1 w-full rounded border border-slate-500 bg-white px-3 py-2 text-slate-950 dark:bg-slate-900 dark:text-white">{{ $project['description'] ?? '' }}</textarea></div>
                <div><label for="project-{{ $index }}-url" class="block font-medium">Project link</label><p id="project-{{ $index }}-url-hint" class="text-sm text-[var(--simple-muted)]">Use a complete address starting with https://.</p><input id="project-{{ $index }}-url" type="url" name="projects[{{ $index }}][url]" value="{{ $project['url'] ?? '' }}" maxlength="2048" placeholder="https://example.com" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
            </fieldset>
        @endforeach
    </section>

    <section class="space-y-4 rounded-xl border border-slate-300/60 p-5 dark:border-slate-700" aria-labelledby="links-heading">
        <h2 id="links-heading" class="text-xl font-semibold">Links <span class="text-sm font-normal">(optional)</span></h2>
        @foreach ($linkRows as $index => $link)
            <fieldset class="grid gap-4 rounded-lg bg-slate-100 p-4 dark:bg-slate-800 sm:grid-cols-2">
                <legend class="sr-only">Social link {{ $index + 1 }}</legend>
                <div><label for="social-{{ $index }}-label" class="block font-medium">Link name</label><input id="social-{{ $index }}-label" name="social_links[{{ $index }}][label]" value="{{ $link['label'] ?? '' }}" maxlength="40" placeholder="LinkedIn" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
                <div><label for="social-{{ $index }}-url" class="block font-medium">Web address</label><input id="social-{{ $index }}-url" type="url" name="social_links[{{ $index }}][url]" value="{{ $link['url'] ?? '' }}" maxlength="2048" placeholder="https://example.com" class="mt-1 min-h-11 w-full rounded border border-slate-500 bg-white px-3 text-slate-950 dark:bg-slate-900 dark:text-white"></div>
            </fieldset>
        @endforeach
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a class="min-h-11 inline-flex items-center rounded-lg border border-current px-4 py-2 font-medium" href="{{ $backUrl }}">Back</a>
        <button class="min-h-11 rounded-lg bg-slate-900 px-5 py-3 font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900" type="submit" data-submit-button>{{ $submitLabel }}</button>
    </div>
</form>

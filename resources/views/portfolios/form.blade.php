@php
    $educationRows = old('education', $portfolio?->education ?: [['institution' => '', 'qualification' => '', 'year' => '']]);
    $experienceRows = old('work_experience', $portfolio?->work_experience ?: [['company' => '', 'role' => '', 'description' => '', 'start_date' => '', 'end_date' => '']]);
    $skillRows = old('skills', $portfolio?->skills ?: ['']);
    $projectRows = old('projects', $portfolio?->projects ?: [['name' => '', 'description' => '', 'url' => '']]);
    $linkRows = old('social_links', $portfolio?->social_links ?: [['label' => '', 'url' => '']]);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-4" data-portfolio-form @if ($formMethod === 'POST') data-draft-form data-draft-key="portgen:portfolio-draft" @endif>
    @csrf
    @if ($formMethod === 'PUT')
        @method('PUT')
    @endif

    @if ($formMethod === 'POST')
        <p class="status-neutral" role="status" aria-live="polite" data-draft-status>Drafts save on this device while you type.</p>
    @endif

    @if ($errors->any())
        <div class="status-error" role="alert" tabindex="-1" data-error-summary>
            <h2 class="font-semibold">Please check these details</h2>
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <details class="form-section" data-section data-section-required open>
        <summary><span>Basics</span><span class="section-completion" data-section-completion>In progress</span></summary>
        <div class="section-body">
            <div>
                <label for="full_name" class="block font-medium">Your name</label>
                <p id="full_name-hint" class="text-muted text-sm">Use the name people know you by.</p>
                <input id="full_name" name="full_name" value="{{ old('full_name', $portfolio?->full_name) }}" required maxlength="120" autocomplete="name" data-error-message="Enter your name." aria-describedby="full_name-hint @error('full_name') full_name-error @enderror" @error('full_name') aria-invalid="true" @enderror class="form-control mt-1">
                @error('full_name')<p id="full_name-error" class="mt-1" data-inline-error>{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="headline" class="block font-medium">Headline</label>
                <p id="headline-hint" class="text-muted text-sm">Example: Product designer focused on accessible tools.</p>
                <input id="headline" name="headline" value="{{ old('headline', $portfolio?->headline) }}" required maxlength="180" data-error-message="Add a short headline." aria-describedby="headline-hint @error('headline') headline-error @enderror" @error('headline') aria-invalid="true" @enderror class="form-control mt-1">
                @error('headline')<p id="headline-error" class="mt-1" data-inline-error>{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="about_me" class="block font-medium">About me</label>
                <p id="about_me-hint" class="text-muted text-sm">Share what you do and the kind of work you enjoy.</p>
                <textarea id="about_me" name="about_me" rows="5" required maxlength="2000" data-error-message="Add a short introduction." aria-describedby="about_me-hint @error('about_me') about_me-error @enderror" @error('about_me') aria-invalid="true" @enderror class="form-control mt-1">{{ old('about_me', $portfolio?->about_me) }}</textarea>
                @error('about_me')<p id="about_me-error" class="mt-1" data-inline-error>{{ $message }}</p>@enderror
            </div>
        </div>
    </details>

    <details class="form-section" data-section data-section-optional>
        <summary><span>Education <span class="font-normal">(optional)</span></span><span class="section-completion" data-section-completion>Not started</span></summary>
        <div class="section-body">
            <p class="text-muted text-sm">Example: Northstar University · B.A. Design · 2025</p>
            <div class="grid gap-3" data-repeat-group="education">
                @foreach ($educationRows as $index => $education)
                    <fieldset class="form-card grid gap-3 rounded-xl p-4 sm:grid-cols-3" data-repeat-row>
                        <legend class="sr-only">Education item {{ $index + 1 }}</legend>
                        <div><label for="education-{{ $index }}-institution" class="block font-medium">School</label><input id="education-{{ $index }}-institution" name="education[{{ $index }}][institution]" value="{{ $education['institution'] ?? '' }}" maxlength="120" class="form-control mt-1"></div>
                        <div><label for="education-{{ $index }}-qualification" class="block font-medium">Qualification</label><input id="education-{{ $index }}-qualification" name="education[{{ $index }}][qualification]" value="{{ $education['qualification'] ?? '' }}" maxlength="120" placeholder="Example: B.A. Design" class="form-control mt-1"></div>
                        <div><label for="education-{{ $index }}-year" class="block font-medium">Year</label><input id="education-{{ $index }}-year" name="education[{{ $index }}][year]" value="{{ $education['year'] ?? '' }}" maxlength="20" placeholder="Example: 2025" class="form-control mt-1"></div>
                        <button class="button-text sm:col-span-3" type="button" data-remove-row hidden>Remove this education item</button>
                    </fieldset>
                @endforeach
            </div>
            <button class="button-secondary" type="button" data-add-row="education">Add education</button>
            <template data-row-template="education">
                <fieldset class="form-card grid gap-3 rounded-xl p-4 sm:grid-cols-3" data-repeat-row>
                    <legend class="sr-only">Education item __INDEX_LABEL__</legend>
                    <div><label for="education-__INDEX__-institution" class="block font-medium">School</label><input id="education-__INDEX__-institution" name="education[__INDEX__][institution]" maxlength="120" class="form-control mt-1"></div>
                    <div><label for="education-__INDEX__-qualification" class="block font-medium">Qualification</label><input id="education-__INDEX__-qualification" name="education[__INDEX__][qualification]" maxlength="120" placeholder="Example: B.A. Design" class="form-control mt-1"></div>
                    <div><label for="education-__INDEX__-year" class="block font-medium">Year</label><input id="education-__INDEX__-year" name="education[__INDEX__][year]" maxlength="20" placeholder="Example: 2025" class="form-control mt-1"></div>
                    <button class="button-text sm:col-span-3" type="button" data-remove-row>Remove this education item</button>
                </fieldset>
            </template>
        </div>
    </details>

    <details class="form-section" data-section data-section-optional>
        <summary><span>Experience <span class="font-normal">(optional)</span></span><span class="section-completion" data-section-completion>Not started</span></summary>
        <div class="section-body">
            <p class="text-muted text-sm">Include paid work, volunteering, internships, or community work.</p>
            <div class="grid gap-3" data-repeat-group="work_experience">
                @foreach ($experienceRows as $index => $experience)
                    <fieldset class="form-card grid gap-3 rounded-xl p-4" data-repeat-row>
                        <legend class="sr-only">Experience item {{ $index + 1 }}</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div><label for="experience-{{ $index }}-role" class="block font-medium">Role</label><input id="experience-{{ $index }}-role" name="work_experience[{{ $index }}][role]" value="{{ $experience['role'] ?? '' }}" maxlength="120" placeholder="Example: Design intern" class="form-control mt-1"></div>
                            <div><label for="experience-{{ $index }}-company" class="block font-medium">Company or group</label><input id="experience-{{ $index }}-company" name="work_experience[{{ $index }}][company]" value="{{ $experience['company'] ?? '' }}" maxlength="120" class="form-control mt-1"></div>
                        </div>
                        <div><label for="experience-{{ $index }}-description" class="block font-medium">What you did</label><textarea id="experience-{{ $index }}-description" name="work_experience[{{ $index }}][description]" rows="3" maxlength="2000" class="form-control mt-1">{{ $experience['description'] ?? '' }}</textarea></div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div><label for="experience-{{ $index }}-start" class="block font-medium">Start date</label><input id="experience-{{ $index }}-start" type="month" name="work_experience[{{ $index }}][start_date]" value="{{ $experience['start_date'] ?? '' }}" class="form-control mt-1"></div>
                            <div><label for="experience-{{ $index }}-end" class="block font-medium">End date</label><input id="experience-{{ $index }}-end" type="month" name="work_experience[{{ $index }}][end_date]" value="{{ $experience['end_date'] ?? '' }}" class="form-control mt-1"></div>
                        </div>
                        <button class="button-text" type="button" data-remove-row hidden>Remove this experience</button>
                    </fieldset>
                @endforeach
            </div>
            <button class="button-secondary" type="button" data-add-row="work_experience">Add experience</button>
            <template data-row-template="work_experience">
                <fieldset class="form-card grid gap-3 rounded-xl p-4" data-repeat-row>
                    <legend class="sr-only">Experience item __INDEX_LABEL__</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div><label for="experience-__INDEX__-role" class="block font-medium">Role</label><input id="experience-__INDEX__-role" name="work_experience[__INDEX__][role]" maxlength="120" placeholder="Example: Design intern" class="form-control mt-1"></div>
                        <div><label for="experience-__INDEX__-company" class="block font-medium">Company or group</label><input id="experience-__INDEX__-company" name="work_experience[__INDEX__][company]" maxlength="120" class="form-control mt-1"></div>
                    </div>
                    <div><label for="experience-__INDEX__-description" class="block font-medium">What you did</label><textarea id="experience-__INDEX__-description" name="work_experience[__INDEX__][description]" rows="3" maxlength="2000" class="form-control mt-1"></textarea></div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div><label for="experience-__INDEX__-start" class="block font-medium">Start date</label><input id="experience-__INDEX__-start" type="month" name="work_experience[__INDEX__][start_date]" class="form-control mt-1"></div>
                        <div><label for="experience-__INDEX__-end" class="block font-medium">End date</label><input id="experience-__INDEX__-end" type="month" name="work_experience[__INDEX__][end_date]" class="form-control mt-1"></div>
                    </div>
                    <button class="button-text" type="button" data-remove-row>Remove this experience</button>
                </fieldset>
            </template>
        </div>
    </details>

    <details class="form-section" data-section data-section-required>
        <summary><span>Skills</span><span class="section-completion" data-section-completion>In progress</span></summary>
        <div class="section-body">
            <p id="skills-hint" class="text-muted text-sm">Add at least one skill. Examples: Research, Figma, or Python.</p>
            <div class="grid gap-3" data-repeat-group="skills">
                @foreach ($skillRows as $index => $skill)
                    <div class="grid gap-2 sm:grid-cols-[1fr_auto] sm:items-end" data-repeat-row>
                        <div><label for="skill-{{ $index }}" class="block font-medium">Skill {{ $index + 1 }}</label><input id="skill-{{ $index }}" name="skills[]" value="{{ $skill }}" @if ($index === 0) data-error-message="Add at least one skill." @endif maxlength="50" aria-describedby="skills-hint @error('skills') skills-error @enderror" @error('skills') aria-invalid="true" @enderror class="form-control mt-1"></div>
                        <button class="button-text" type="button" data-remove-row hidden>Remove this skill</button>
                    </div>
                @endforeach
            </div>
            <button class="button-secondary" type="button" data-add-row="skills">Add another skill</button>
            <template data-row-template="skills">
                <div class="grid gap-2 sm:grid-cols-[1fr_auto] sm:items-end" data-repeat-row>
                    <div><label for="skill-__INDEX__" class="block font-medium">Skill __INDEX_LABEL__</label><input id="skill-__INDEX__" name="skills[]" maxlength="50" class="form-control mt-1"></div>
                    <button class="button-text" type="button" data-remove-row>Remove this skill</button>
                </div>
            </template>
            @error('skills')<p id="skills-error" class="status-error" data-inline-error>{{ $message }}</p>@enderror
        </div>
    </details>

    <details class="form-section" data-section data-section-optional>
        <summary><span>Projects <span class="font-normal">(optional)</span></span><span class="section-completion" data-section-completion>Not started</span></summary>
        <div class="section-body">
            <p class="text-muted text-sm">Share personal, class, community, or client work. Link addresses must start with http:// or https://.</p>
            <div class="grid gap-3" data-repeat-group="projects">
                @foreach ($projectRows as $index => $project)
                    <fieldset class="form-card grid gap-3 rounded-xl p-4" data-repeat-row>
                        <legend class="sr-only">Project item {{ $index + 1 }}</legend>
                        <div><label for="project-{{ $index }}-name" class="block font-medium">Project name</label><input id="project-{{ $index }}-name" name="projects[{{ $index }}][name]" value="{{ $project['name'] ?? '' }}" maxlength="120" class="form-control mt-1"></div>
                        <div><label for="project-{{ $index }}-description" class="block font-medium">Description</label><textarea id="project-{{ $index }}-description" name="projects[{{ $index }}][description]" rows="3" maxlength="2000" class="form-control mt-1">{{ $project['description'] ?? '' }}</textarea></div>
                        <div><label for="project-{{ $index }}-url" class="block font-medium">Project link</label><input id="project-{{ $index }}-url" type="url" name="projects[{{ $index }}][url]" value="{{ $project['url'] ?? '' }}" maxlength="2048" data-http-url data-error-message="Use a complete http or https project link." placeholder="https://example.com" class="form-control mt-1"></div>
                        <button class="button-text" type="button" data-remove-row hidden>Remove this project</button>
                    </fieldset>
                @endforeach
            </div>
            <button class="button-secondary" type="button" data-add-row="projects">Add a project</button>
            <template data-row-template="projects">
                <fieldset class="form-card grid gap-3 rounded-xl p-4" data-repeat-row>
                    <legend class="sr-only">Project item __INDEX_LABEL__</legend>
                    <div><label for="project-__INDEX__-name" class="block font-medium">Project name</label><input id="project-__INDEX__-name" name="projects[__INDEX__][name]" maxlength="120" class="form-control mt-1"></div>
                    <div><label for="project-__INDEX__-description" class="block font-medium">Description</label><textarea id="project-__INDEX__-description" name="projects[__INDEX__][description]" rows="3" maxlength="2000" class="form-control mt-1"></textarea></div>
                    <div><label for="project-__INDEX__-url" class="block font-medium">Project link</label><input id="project-__INDEX__-url" type="url" name="projects[__INDEX__][url]" maxlength="2048" data-http-url data-error-message="Use a complete http or https project link." placeholder="https://example.com" class="form-control mt-1"></div>
                    <button class="button-text" type="button" data-remove-row>Remove this project</button>
                </fieldset>
            </template>
        </div>
    </details>

    <details class="form-section" data-section data-section-optional>
        <summary><span>Links <span class="font-normal">(optional)</span></span><span class="section-completion" data-section-completion>Not started</span></summary>
        <div class="section-body">
            <p class="text-muted text-sm">Add a profile or website people can visit. Use a complete http:// or https:// address.</p>
            <div class="grid gap-3" data-repeat-group="social_links">
                @foreach ($linkRows as $index => $link)
                    <fieldset class="form-card grid gap-3 rounded-xl p-4 sm:grid-cols-2" data-repeat-row>
                        <legend class="sr-only">Social link {{ $index + 1 }}</legend>
                        <div><label for="social-{{ $index }}-label" class="block font-medium">Link name</label><input id="social-{{ $index }}-label" name="social_links[{{ $index }}][label]" value="{{ $link['label'] ?? '' }}" maxlength="40" placeholder="LinkedIn" class="form-control mt-1"></div>
                        <div><label for="social-{{ $index }}-url" class="block font-medium">Web address</label><input id="social-{{ $index }}-url" type="url" name="social_links[{{ $index }}][url]" value="{{ $link['url'] ?? '' }}" maxlength="2048" data-http-url data-error-message="Use a complete http or https social link." placeholder="https://example.com" class="form-control mt-1"></div>
                        <button class="button-text sm:col-span-2" type="button" data-remove-row hidden>Remove this link</button>
                    </fieldset>
                @endforeach
            </div>
            <button class="button-secondary" type="button" data-add-row="social_links">Add a link</button>
            <template data-row-template="social_links">
                <fieldset class="form-card grid gap-3 rounded-xl p-4 sm:grid-cols-2" data-repeat-row>
                    <legend class="sr-only">Social link __INDEX_LABEL__</legend>
                    <div><label for="social-__INDEX__-label" class="block font-medium">Link name</label><input id="social-__INDEX__-label" name="social_links[__INDEX__][label]" maxlength="40" placeholder="LinkedIn" class="form-control mt-1"></div>
                    <div><label for="social-__INDEX__-url" class="block font-medium">Web address</label><input id="social-__INDEX__-url" type="url" name="social_links[__INDEX__][url]" maxlength="2048" data-http-url data-error-message="Use a complete http or https social link." placeholder="https://example.com" class="form-control mt-1"></div>
                    <button class="button-text sm:col-span-2" type="button" data-remove-row>Remove this link</button>
                </fieldset>
            </template>
        </div>
    </details>

    <div class="flex flex-wrap items-center justify-between gap-3 py-2">
        @if ($formMethod === 'PUT')
            <a class="button-secondary" href="{{ $backUrl }}" data-step-back>Back</a>
        @endif
        <button class="button-primary" type="submit" data-submit-button>{{ $submitLabel }}</button>
    </div>
</form>

@php
    $educationRows = old('education', $portfolio?->education ?: [['institution' => '', 'qualification' => '', 'year' => '']]);
    $experienceRows = old('work_experience', $portfolio?->work_experience ?: [['company' => '', 'role' => '', 'description' => '', 'start_date' => '', 'end_date' => '']]);
    $skillRows = old('skills', $portfolio?->skills ?: ['']);
    $projectRows = old('projects', $portfolio?->projects ?: [['name' => '', 'description' => '', 'url' => '']]);
    $linkRows = old('social_links', $portfolio?->social_links ?: [['label' => '', 'url' => '']]);
    $profileImage = $portfolio?->images?->firstWhere('kind', 'profile');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-4" data-portfolio-form @if ($formMethod === 'POST') data-draft-form data-draft-key="portgen:portfolio-draft" @endif>
    @csrf
    @if ($formMethod === 'PUT')
        @method('PUT')
    @endif
    <input type="hidden" name="project_image_controls_present" value="1">

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
            <div class="grid gap-3 sm:grid-cols-2">
                <div><label for="location" class="block font-medium">Location <span class="font-normal">(optional)</span></label><p id="location-hint" class="text-muted text-sm">Example: Taipei, Taiwan.</p><input id="location" name="location" value="{{ old('location', $portfolio?->location) }}" maxlength="120" aria-describedby="location-hint" class="form-control mt-1"></div>
                <div><label for="availability" class="block font-medium">Availability <span class="font-normal">(optional)</span></label><p id="availability-hint" class="text-muted text-sm">Example: Open to internships.</p><input id="availability" name="availability" value="{{ old('availability', $portfolio?->availability) }}" maxlength="80" aria-describedby="availability-hint" class="form-control mt-1"></div>
            </div>
            <div><label for="contact_email" class="block font-medium">Contact email <span class="font-normal">(optional)</span></label><p id="contact_email-hint" class="text-muted text-sm">This address will be visible on your public portfolio. Example: name@example.com.</p><input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $portfolio?->contact_email) }}" maxlength="190" autocomplete="email" aria-describedby="contact_email-hint @error('contact_email') contact_email-error @enderror" @error('contact_email') aria-invalid="true" @enderror class="form-control mt-1">@error('contact_email')<p id="contact_email-error" class="mt-1" data-inline-error>{{ $message }}</p>@enderror</div>
            <div>
                <label for="about_me" class="block font-medium">About me</label>
                <p id="about_me-hint" class="text-muted text-sm">Share what you do and the kind of work you enjoy.</p>
                <textarea id="about_me" name="about_me" rows="5" required maxlength="2000" data-error-message="Add a short introduction." aria-describedby="about_me-hint @error('about_me') about_me-error @enderror" @error('about_me') aria-invalid="true" @enderror class="form-control mt-1">{{ old('about_me', $portfolio?->about_me) }}</textarea>
                @error('about_me')<p id="about_me-error" class="mt-1" data-inline-error>{{ $message }}</p>@enderror
            </div>
        </div>
    </details>

    <details class="form-section" data-section data-section-optional>
        <summary><span>Photo <span class="font-normal">(optional)</span></span><span class="section-completion" data-section-completion>Optional</span></summary>
        <div class="section-body">
            <p class="text-muted text-sm">A clear portrait works well. JPEG, PNG, or WebP, up to 2 MB. This photo will be public on your portfolio.</p>
            @if ($profileImage)<img class="pf-avatar" src="{{ route('portfolio.image', ['portfolio' => $portfolio, 'image' => $profileImage]) }}" alt="{{ $profileImage->alt }}" width="{{ $profileImage->width }}" height="{{ $profileImage->height }}">@endif
            <div><label for="profile_image" class="block font-medium">Upload profile photo</label><input id="profile_image" name="profile_image" type="file" accept="image/jpeg,image/png,image/webp" class="form-control mt-1">@error('profile_image')<p class="mt-1" data-inline-error>{{ $message }}</p>@enderror</div>
            <div><label for="profile_image_alt" class="block font-medium">Photo description (alt text)</label><p id="profile_image_alt-hint" class="text-muted text-sm">Describe what the photo shows. Example: Photo of {{ old('full_name', $portfolio?->full_name ?: 'your name') }}.</p><input id="profile_image_alt" name="profile_image_alt" value="{{ old('profile_image_alt', $profileImage?->alt ?? ('Photo of '.old('full_name', $portfolio?->full_name ?: 'your name'))) }}" maxlength="200" aria-describedby="profile_image_alt-hint @error('profile_image_alt') profile_image_alt-error @enderror" @error('profile_image_alt') aria-invalid="true" @enderror class="form-control mt-1">@error('profile_image_alt')<p id="profile_image_alt-error" class="mt-1" data-inline-error>{{ $message }}</p>@enderror</div>
            @if ($profileImage)<label class="inline-flex min-h-11 items-center gap-2"><input type="checkbox" name="profile_image_remove" value="1"> Remove the current profile photo</label>@endif
        </div>
    </details>

    <details class="form-section" data-section data-section-optional>
        <summary><span>Interests <span class="font-normal">(optional)</span></span><span class="section-completion" data-section-completion>Optional</span></summary>
        <div class="section-body">
            <label for="interests_input" class="block font-medium">Interests</label><p id="interests-hint" class="text-muted text-sm">List up to 8 interests separated by commas. Example: Coding, hiking, music.</p>
            <input id="interests_input" name="interests_input" value="{{ old('interests_input', implode(', ', old('interests', $portfolio?->interests ?? []))) }}" maxlength="334" aria-describedby="interests-hint @error('interests_input') interests-error @enderror" class="form-control mt-1">
            @error('interests_input')<p id="interests-error" class="mt-1" data-inline-error>{{ $message }}</p>@enderror
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
                        <div><label for="project-{{ $index }}-tags" class="block font-medium">Technology tags <span class="font-normal">(optional)</span></label><p id="project-{{ $index }}-tags-hint" class="text-muted text-sm">Up to 8, separated by commas. Example: Laravel, PostgreSQL.</p><input id="project-{{ $index }}-tags" name="projects[{{ $index }}][tags_input]" value="{{ old("projects.{$index}.tags_input", implode(', ', $project['tags'] ?? [])) }}" maxlength="254" aria-describedby="project-{{ $index }}-tags-hint @error("projects.{$index}.tags_input") project-{{ $index }}-tags-error @enderror" @error("projects.{$index}.tags_input") aria-invalid="true" @enderror class="form-control mt-1">@error("projects.{$index}.tags_input")<p id="project-{{ $index }}-tags-error" class="mt-1" data-inline-error>{{ $message }}</p>@enderror</div>
                        @php($projectImage = $portfolio?->images?->first(fn ($image) => $image->kind === 'project' && (int) $image->project_index === (int) $index))
                        @if ($projectImage)<img class="pf-project-image" src="{{ route('portfolio.image', ['portfolio' => $portfolio, 'image' => $projectImage]) }}" alt="{{ $projectImage->alt }}" width="{{ $projectImage->width }}" height="{{ $projectImage->height }}"><input type="hidden" name="project_image_ids[{{ $index }}]" value="{{ $projectImage->id }}">@endif
                        <div><label for="project-{{ $index }}-image" class="block font-medium">Project image <span class="font-normal">(optional)</span></label><p id="project-{{ $index }}-image-hint" class="text-muted text-sm">JPEG, PNG, or WebP, up to 2 MB. This photo will be public on your portfolio.</p><input id="project-{{ $index }}-image" name="project_images[{{ $index }}]" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="project-{{ $index }}-image-hint" class="form-control mt-1">@error("project_images.{$index}")<p class="mt-1" data-inline-error>{{ $message }}</p>@enderror</div>
                        <div><label for="project-{{ $index }}-image-alt" class="block font-medium">Project image description (alt text)</label><p id="project-{{ $index }}-image-alt-hint" class="text-muted text-sm">Describe the project image. Example: Preview of {{ $project['name'] ?? 'the project' }}.</p><input id="project-{{ $index }}-image-alt" name="project_image_alts[{{ $index }}]" value="{{ old("project_image_alts.{$index}", $projectImage?->alt ?? '') }}" maxlength="200" aria-describedby="project-{{ $index }}-image-alt-hint" class="form-control mt-1">@error("project_image_alts.{$index}")<p class="mt-1" data-inline-error>{{ $message }}</p>@enderror</div>
                        @if ($projectImage)<label class="inline-flex min-h-11 items-center gap-2"><input type="checkbox" name="project_image_remove[{{ $index }}]" value="1"> Remove this project image</label>@endif
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
                    <div><label for="project-__INDEX__-tags" class="block font-medium">Technology tags <span class="font-normal">(optional)</span></label><p id="project-__INDEX__-tags-hint" class="text-muted text-sm">Up to 8, separated by commas. Example: Laravel, PostgreSQL.</p><input id="project-__INDEX__-tags" name="projects[__INDEX__][tags_input]" maxlength="254" aria-describedby="project-__INDEX__-tags-hint" class="form-control mt-1"></div>
                    <div><label for="project-__INDEX__-image" class="block font-medium">Project image <span class="font-normal">(optional)</span></label><p id="project-__INDEX__-image-hint" class="text-muted text-sm">JPEG, PNG, or WebP, up to 2 MB. This photo will be public on your portfolio.</p><input id="project-__INDEX__-image" name="project_images[__INDEX__]" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="project-__INDEX__-image-hint" class="form-control mt-1"></div>
                    <div><label for="project-__INDEX__-image-alt" class="block font-medium">Project image description (alt text)</label><p id="project-__INDEX__-image-alt-hint" class="text-muted text-sm">Describe the project image. Example: Preview of your project.</p><input id="project-__INDEX__-image-alt" name="project_image_alts[__INDEX__]" maxlength="200" aria-describedby="project-__INDEX__-image-alt-hint" class="form-control mt-1"></div>
                    <button class="button-text" type="button" data-remove-row>Remove this project</button>
                </fieldset>
            </template>
        </div>
    </details>

    <details class="form-section" data-section data-section-optional>
        <summary><span>Personal note <span class="font-normal">(optional)</span></span><span class="section-completion" data-section-completion>Optional</span></summary>
        <div class="section-body">
            <label for="motto" class="block font-medium">Short note or motto</label><p id="motto-hint" class="text-muted text-sm">Use your own words, up to 60 characters. Example: I like turning ideas into useful tools.</p><input id="motto" name="motto" value="{{ old('motto', $portfolio?->motto) }}" maxlength="60" aria-describedby="motto-hint" class="form-control mt-1">
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

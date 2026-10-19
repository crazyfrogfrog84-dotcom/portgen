<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

trait HasPortfolioRules
{
    protected function portfolioRules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:180'],
            'about_me' => ['required', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:120'],
            'availability' => ['nullable', 'string', 'max:80'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'motto' => ['nullable', 'string', 'max:60'],
            'interests' => ['nullable', 'array', 'max:8'],
            'interests.*' => ['required', 'string', 'max:40'],
            'interests_input' => ['nullable', 'string', 'max:334'],
            'profile_image' => ['nullable', 'file', 'max:2048'],
            'profile_image_alt' => ['nullable', 'string', 'max:200'],
            'profile_image_remove' => ['nullable', 'boolean'],
            'education' => ['nullable', 'array', 'max:10'],
            'education.*.institution' => ['nullable', 'string', 'max:120'],
            'education.*.qualification' => ['nullable', 'string', 'max:120'],
            'education.*.year' => ['nullable', 'string', 'max:20'],
            'work_experience' => ['nullable', 'array', 'max:10'],
            'work_experience.*.company' => ['nullable', 'string', 'max:120'],
            'work_experience.*.role' => ['nullable', 'string', 'max:120'],
            'work_experience.*.description' => ['nullable', 'string', 'max:2000'],
            'work_experience.*.start_date' => ['nullable', 'date_format:Y-m'],
            'work_experience.*.end_date' => ['nullable', 'date_format:Y-m'],
            'skills' => ['required', 'array', 'min:1', 'max:30'],
            'skills.*' => ['required', 'string', 'max:50'],
            'projects' => ['nullable', 'array', 'max:20'],
            'projects.*.name' => ['nullable', 'string', 'max:120'],
            'projects.*.description' => ['nullable', 'string', 'max:2000'],
            'projects.*.url' => ['nullable', 'url:http,https', 'max:2048'],
            'projects.*.tags' => ['nullable', 'array', 'max:8'],
            'projects.*.tags.*' => ['required', 'string', 'max:30'],
            'projects.*.tags_input' => ['nullable', 'string', 'max:254'],
            'project_images' => ['nullable', 'array', 'max:20'],
            'project_images.*' => ['nullable', 'file', 'max:2048'],
            'project_image_alts' => ['nullable', 'array', 'max:20'],
            'project_image_alts.*' => ['nullable', 'string', 'max:200'],
            'project_image_ids' => ['nullable', 'array', 'max:20'],
            'project_image_ids.*' => ['nullable', 'uuid'],
            'project_image_remove' => ['nullable', 'array', 'max:20'],
            'project_image_remove.*' => ['nullable', 'boolean'],
            'social_links' => ['nullable', 'array', 'max:20'],
            'social_links.*.label' => ['nullable', 'string', 'max:40'],
            'social_links.*.url' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateDelimitedFields($validator);
            $this->validateImageUploads($validator);
            $this->validateImageAltText($validator);
            $this->validateSubmittedImageOwnership($validator);
        });
    }

    private function validateDelimitedFields(Validator $validator): void
    {
        $groups = [
            'interests_input' => ['limit' => 8, 'length' => 40, 'label' => 'interest'],
        ];
        $projects = $this->input('projects', []);
        foreach (is_array($projects) ? $projects : [] as $index => $project) {
            $groups["projects.{$index}.tags_input"] = ['limit' => 8, 'length' => 30, 'label' => 'project tag'];
        }

        foreach ($groups as $field => $limits) {
            $value = $this->input($field);
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $items = array_values(array_filter(array_map('trim', explode(',', $value)), fn (string $item) => $item !== ''));
            if (count($items) > $limits['limit']) {
                $validator->errors()->add($field, 'Add no more than '.$limits['limit'].' '.$limits['label'].'s.');
            } elseif (collect($items)->contains(fn (string $item) => mb_strlen($item) > $limits['length'])) {
                $validator->errors()->add($field, 'Each '.$limits['label'].' must be '.$limits['length'].' characters or fewer.');
            }
        }
    }

    private function validateImageUploads(Validator $validator): void
    {
        foreach (['profile_image' => $this->file('profile_image')] as $field => $upload) {
            if ($upload instanceof UploadedFile) {
                $this->validateImageContent($validator, $field, $upload);
            }
        }

        foreach ($this->file('project_images', []) as $index => $upload) {
            if ($upload instanceof UploadedFile) {
                $this->validateImageContent($validator, "project_images.{$index}", $upload);
            }
        }
    }

    private function validateImageContent(Validator $validator, string $field, UploadedFile $upload): void
    {
        $path = $upload->getRealPath();
        if (! $path || ! is_file($path)) {
            $validator->errors()->add($field, 'Choose a readable JPEG, PNG, or WebP image.');

            return;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $info = @getimagesize($path);
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || ! is_array($info) || ($info['mime'] ?? null) !== $mime) {
            $validator->errors()->add($field, 'Choose a real JPEG, PNG, or WebP image. File extensions alone are not accepted.');
        }
    }

    private function validateImageAltText(Validator $validator): void
    {
        if ($this->hasFile('profile_image') && trim((string) $this->input('profile_image_alt')) === '') {
            $validator->errors()->add('profile_image_alt', 'Describe the profile photo so it has useful alternative text.');
        }

        foreach ($this->file('project_images', []) as $index => $upload) {
            if ($upload instanceof UploadedFile && trim((string) $this->input("project_image_alts.{$index}")) === '') {
                $validator->errors()->add("project_image_alts.{$index}", 'Describe this project image so it has useful alternative text.');
            }
        }
    }

    private function validateSubmittedImageOwnership(Validator $validator): void
    {
        $ids = array_values(array_filter($this->input('project_image_ids', [])));
        if ($ids === []) {
            return;
        }

        $portfolio = $this->route('portfolio');
        if (! $portfolio || ! method_exists($portfolio, 'images')) {
            $validator->errors()->add('project_image_ids', 'Project images can only be changed from the private edit link.');

            return;
        }

        $ownedIds = $portfolio->images()->where('kind', 'project')->whereIn('id', $ids)->pluck('id')->all();
        if (count(array_unique($ids)) !== count($ids) || count($ownedIds) !== count($ids)) {
            $validator->errors()->add('project_image_ids', 'One or more project images do not belong to this portfolio.');
        }
    }

    public function messages(): array
    {
        return [
            'skills.required' => 'Add at least one skill.',
            'skills.min' => 'Add at least one skill.',
            'projects.*.url.url' => 'Use a complete http or https project link.',
            'social_links.*.url.url' => 'Use a complete http or https social link.',
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'your name',
            'headline' => 'your headline',
            'about_me' => 'your about me',
            'contact_email' => 'your contact email',
            'profile_image_alt' => 'profile photo description',
        ];
    }
}

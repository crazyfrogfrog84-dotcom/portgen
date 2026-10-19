<?php

namespace App\Http\Requests\Concerns;

trait HasPortfolioRules
{
    protected function portfolioRules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:180'],
            'about_me' => ['required', 'string', 'max:2000'],
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
            'social_links' => ['nullable', 'array', 'max:20'],
            'social_links.*.label' => ['nullable', 'string', 'max:40'],
            'social_links.*.url' => ['nullable', 'url:http,https', 'max:2048'],
        ];
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
        ];
    }
}

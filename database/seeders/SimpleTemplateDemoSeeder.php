<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use Illuminate\Database\Seeder;

class SimpleTemplateDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->upsert('demo-min', [
            'full_name' => 'Morgan Lin',
            'headline' => 'Junior front-end developer',
            'about_me' => 'I build clear, accessible interfaces and enjoy turning small ideas into useful tools.',
            'location' => null,
            'availability' => null,
            'contact_email' => null,
            'motto' => null,
            'interests' => [],
            'education' => [],
            'work_experience' => [],
            'skills' => ['HTML and CSS'],
            'projects' => [],
            'social_links' => [],
        ]);

        $this->upsert('demo-rich', [
            'full_name' => 'Avery Chen',
            'headline' => 'Product designer building civic tools',
            'about_me' => "I design helpful digital services for people navigating everyday systems.\n\nMy work combines research, clear writing, and careful interface design. I care about making complex tasks feel understandable.",
            'location' => 'Taipei, Taiwan',
            'availability' => 'Open to freelance projects',
            'contact_email' => 'avery.chen@example.test',
            'motto' => null,
            'interests' => ['Accessibility', 'Public transit', 'Service design'],
            'education' => [
                ['institution' => 'National Taiwan University', 'qualification' => 'MDes, Interaction Design', 'year' => '2025'],
            ],
            'work_experience' => [
                ['company' => 'Civic Lab', 'role' => 'Product designer', 'description' => 'Designed clear service flows with community partners and engineering teams.', 'start_date' => '2024-01', 'end_date' => 'Present'],
                ['company' => 'Studio North', 'role' => 'Design researcher', 'description' => 'Planned interviews and turned findings into practical prototypes.', 'start_date' => '2022-06', 'end_date' => '2023-12'],
            ],
            'skills' => ['Service design', 'Accessibility', 'Figma', 'Prototyping', 'Research', 'Content design'],
            'projects' => [
                ['name' => 'Transit Access Guide', 'description' => 'A route planning concept that makes step-free access information easier to compare before a trip.', 'url' => 'https://example.test/transit-access', 'tags' => ['Research', 'Accessibility']],
                ['name' => 'Community Services Map', 'description' => 'A searchable directory that helps residents find nearby services and understand what to expect.', 'url' => 'https://example.test/community-map', 'tags' => ['Mapping', 'Service design']],
                ['name' => 'Clear Forms Study', 'description' => 'A set of form patterns that reduce uncertainty when people apply for local support.', 'url' => 'https://example.test/clear-forms', 'tags' => ['Forms', 'Content design']],
                ['name' => 'Open Data Notes', 'description' => 'A small interface for exploring public datasets through plain language summaries.', 'url' => 'https://example.test/open-data', 'tags' => ['Data', 'Prototyping']],
            ],
            'social_links' => [
                ['label' => 'LinkedIn', 'url' => 'https://example.test/avery-linkedin'],
                ['label' => 'GitHub', 'url' => 'https://example.test/avery-github'],
            ],
        ]);
    }

    private function upsert(string $slug, array $data): void
    {
        $portfolio = Portfolio::withTrashed()->firstOrNew(['slug' => $slug]);
        if ($portfolio->trashed()) {
            $portfolio->restore();
        }
        $portfolio->fill($data);
        $portfolio->slug = $slug;
        $portfolio->user_id = null;
        $portfolio->template_choice = 'Simple';
        $portfolio->save();
    }
}

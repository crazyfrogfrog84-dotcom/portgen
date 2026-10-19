<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $demo = Portfolio::withTrashed()->firstOrNew(['slug' => 'portgen001']);
        if ($demo->trashed()) {
            $demo->restore();
        }

        $demo->fill([
            'full_name' => 'Jordan Sample',
            'headline' => 'Designer making everyday tools clearer',
            'about_me' => 'I turn complex ideas into useful, accessible experiences. This fictional profile demonstrates the public portfolio page.',
            'location' => null,
            'availability' => null,
            'contact_email' => null,
            'motto' => null,
            'interests' => [],
            'education' => [
                ['institution' => 'Northstar University', 'qualification' => 'B.A. Communication Design', 'year' => '2025'],
            ],
            'work_experience' => [
                ['company' => 'Civic Studio', 'role' => 'Design volunteer', 'description' => 'Improved the readability of community service information.', 'start_date' => '2024-06', 'end_date' => null],
            ],
            'skills' => ['Accessibility', 'Research', 'Visual design'],
            'projects' => [
                ['name' => 'Neighborhood Guide', 'description' => 'A clear guide to local services and community resources.', 'url' => 'https://example.com/neighborhood-guide', 'tags' => ['Accessibility', 'Research']],
            ],
            'social_links' => [
                ['label' => 'Personal website', 'url' => 'https://example.com'],
            ],
            'template_choice' => 'Modern',
        ]);
        $demo->slug = 'portgen001';
        $demo->save();
    }
}

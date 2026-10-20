<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            'skills' => ['HTML and CSS'],
            'education' => [],
            'work_experience' => [],
            'projects' => [],
            'social_links' => [],
        ]);

        $rich = $this->upsert('demo-rich', [
            'full_name' => 'Avery Chen',
            'headline' => 'Product designer building clearer civic services',
            'about_me' => "I design digital services that help people move through everyday tasks with less uncertainty.\n\nMy work combines research, accessible interaction patterns, and clear writing. I enjoy working with teams to turn a complicated process into something people can understand and use.",
            'location' => 'Taipei, Taiwan',
            'availability' => 'Open to selected freelance work',
            'contact_email' => 'avery.chen@example.test',
            'motto' => null,
            'interests' => ['Accessible services', 'Urban gardens', 'Public transport'],
            'education' => [
                ['institution' => 'East Harbor University', 'qualification' => 'MDes, Interaction Design', 'year' => '2025'],
                ['institution' => 'North Coast College', 'qualification' => 'BA, Communication Design', 'year' => '2022'],
                ['institution' => 'Civic Design Institute', 'qualification' => 'Certificate, Service Research', 'year' => '2021'],
            ],
            'work_experience' => [
                ['company' => 'Common Ground Lab', 'role' => 'Product designer', 'description' => 'Worked with residents and engineers to make public-service tasks easier to understand.', 'start_date' => '2024-01', 'end_date' => 'Present'],
                ['company' => 'Fieldnote Studio', 'role' => 'Design researcher', 'description' => 'Planned interviews and translated research into practical interface patterns.', 'start_date' => '2022-06', 'end_date' => '2023-12'],
                ['company' => 'Open City Project', 'role' => 'Junior designer', 'description' => 'Prepared maps and content for a neighborhood information service.', 'start_date' => '2021-02', 'end_date' => '2022-05'],
            ],
            'skills' => ['Service design', 'Accessibility', 'Figma', 'Prototyping', 'Research', 'Content design', 'Workshop facilitation', 'Information architecture', 'HTML', 'CSS', 'Usability testing', 'Design systems'],
            'projects' => [
                ['name' => 'Step-free City Guide', 'description' => 'A route guide that helps people compare step-free access information before choosing a journey.', 'url' => 'https://example.test/step-free-guide', 'tags' => ['Accessibility', 'Mapping']],
                ['name' => 'Neighborhood Service Map', 'description' => 'A searchable directory that helps residents find local services and understand what to expect.', 'url' => 'https://example.test/service-map', 'tags' => ['Research', 'Public data']],
                ['name' => 'Clear Forms Toolkit', 'description' => 'A set of form patterns that explain requirements and next steps in plain language.', 'url' => 'https://example.test/forms-toolkit', 'tags' => ['Content design', 'Forms']],
                ['name' => 'Community Garden Planner', 'description' => 'A planning board for neighbors coordinating shared garden beds and seasonal tasks.', 'url' => 'https://example.test/garden-planner', 'tags' => ['Prototyping', 'Community']],
                ['name' => 'Library Visit Notes', 'description' => 'A small research project about how visitors find quiet spaces and support at a library.', 'url' => 'https://example.test/library-notes', 'tags' => ['Field research', 'Service design']],
                ['name' => 'Transit Delay Explainer', 'description' => 'An interface concept for showing why a transit update changed and what riders can do.', 'url' => 'https://example.test/transit-explainer', 'tags' => ['Information design', 'Prototyping']],
            ],
            'social_links' => [
                ['label' => 'LinkedIn', 'url' => 'https://example.test/avery-linkedin'],
                ['label' => 'GitHub', 'url' => 'https://example.test/avery-github'],
            ],
        ]);

        $this->seedImages($rich);

        $stressName = Str::substr(str_repeat('Rin Liu ', 18), 0, 120);
        $stressHeadline = Str::substr(str_repeat('Accessible product designer shaping thoughtful public tools · ', 5), 0, 180);
        $stressAboutSuffix = ' Unicode: café, 東京, مرحباً بالعالم, שלום עולם. Escaping: <script>alert("not executable")</script> & <b>plain text</b>.';
        $stressAbout = Str::substr(str_repeat("I make digital services easier to understand, navigate, and trust. This line checks long-form wrapping across compact and wide screens.\n\n", 17), 0, 2000 - Str::length($stressAboutSuffix))
            .$stressAboutSuffix;

        $this->upsert('demo-stress', [
            'full_name' => $stressName,
            'headline' => $stressHeadline,
            'about_me' => $stressAbout,
            'location' => null,
            'availability' => null,
            'contact_email' => null,
            'motto' => null,
            'interests' => [],
            'education' => [],
            'work_experience' => [],
            'skills' => array_map(fn (int $index) => 'Skill '.$index.' · '.($index % 2 ? 'Unicode 字' : 'Test'), range(1, 30)),
            'projects' => array_map(fn (int $index) => [
                'name' => 'Project '.$index.' · '.($index % 2 ? 'RTL مرحباً' : 'Café'),
                'description' => 'A long project description to check how the card behaves at several viewport widths without hiding its meaning.',
                'url' => 'https://example.test/stress-'.$index,
                'tags' => ['Design '.$index, 'QA'],
            ], range(1, 20)),
            'social_links' => [],
        ]);
    }

    private function upsert(string $slug, array $data): Portfolio
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

        return $portfolio;
    }

    private function seedImages(Portfolio $portfolio): void
    {
        $portfolio->images()->delete();

        $images = [
            ['file' => 'simple-portrait.png', 'kind' => 'profile', 'project_index' => null, 'width' => 480, 'height' => 640, 'alt' => 'Illustrated portrait for the fictional Avery Chen demo profile.'],
        ];
        foreach (range(0, 5) as $index) {
            $images[] = ['file' => 'simple-cover-'.($index + 1).'.png', 'kind' => 'project', 'project_index' => $index, 'width' => 960, 'height' => 540, 'alt' => 'Generated geometric cover for demo project '.($index + 1).'.'];
        }

        foreach ($images as $image) {
            $path = database_path('seeders/fixtures/'.$image['file']);
            if (! is_file($path)) {
                throw new \RuntimeException('A Simple visual fixture image is missing.');
            }

            $bytes = file_get_contents($path);
            if ($bytes === false) {
                throw new \RuntimeException('A Simple visual fixture image could not be read.');
            }

            $portfolio->images()->create([
                'kind' => $image['kind'],
                'project_index' => $image['project_index'],
                'mime' => 'image/png',
                'bytes' => $bytes,
                'width' => $image['width'],
                'height' => $image['height'],
                'alt' => $image['alt'],
            ]);
        }
    }
}

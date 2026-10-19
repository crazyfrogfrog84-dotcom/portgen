<?php

namespace Database\Factories;

use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Portfolio> */
class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'headline' => fake()->jobTitle(),
            'about_me' => fake()->paragraph(),
            'location' => fake()->city().', '.fake()->country(),
            'availability' => null,
            'contact_email' => fake()->safeEmail(),
            'motto' => null,
            'interests' => [fake()->word(), fake()->word()],
            'education' => [
                ['institution' => fake()->company().' University', 'qualification' => 'Bachelor of Arts', 'year' => (string) fake()->year()],
            ],
            'work_experience' => [
                ['company' => fake()->company(), 'role' => fake()->jobTitle(), 'description' => fake()->sentence(), 'start_date' => '2023-01', 'end_date' => null],
            ],
            'skills' => [fake()->word(), fake()->word(), fake()->word()],
            'projects' => [
                ['name' => fake()->catchPhrase(), 'description' => fake()->sentence(), 'url' => fake()->url(), 'tags' => [fake()->word(), fake()->word()]],
            ],
            'social_links' => [
                ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/'.fake()->userName()],
            ],
            'template_choice' => null,
        ];
    }

    public function withTemplate(string $template): static
    {
        return $this->state(fn () => ['template_choice' => $template]);
    }
}

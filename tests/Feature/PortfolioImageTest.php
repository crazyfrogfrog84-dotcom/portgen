<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PortfolioImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_and_project_images_are_reencoded_and_served_with_cache_headers(): void
    {
        $data = $this->validPortfolioData([
            'location' => 'Taipei',
            'availability' => 'Open to internships',
            'contact_email' => 'avery@example.test',
            'motto' => 'Build useful things',
            'interests_input' => 'Design, Music',
            'projects' => [[
                'name' => 'Transit Map',
                'description' => 'A public transit guide.',
                'url' => 'https://example.test/project',
                'tags_input' => 'Laravel, PostgreSQL',
            ]],
            'profile_image' => $this->imageUpload('profile.png', $this->pngBytes()),
            'profile_image_alt' => 'Avery at a desk',
            'project_images' => [0 => $this->imageUpload('project.png', $this->pngBytes())],
            'project_image_alts' => [0 => 'Preview of Transit Map'],
        ]);

        $this->post(route('portfolios.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
        $portfolio = Portfolio::query()->firstOrFail();
        $portfolio->update(['template_choice' => 'Simple']);
        $this->assertSame(['Design', 'Music'], $portfolio->interests);
        $this->assertSame(['Laravel', 'PostgreSQL'], $portfolio->projects[0]['tags']);

        $images = $portfolio->images()->orderBy('kind')->get();
        $this->assertCount(2, $images);
        $profile = $images->firstWhere('kind', 'profile');
        $project = $images->firstWhere('kind', 'project');
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $profile->id);
        $this->assertSame('Avery at a desk', $profile->alt);
        $this->assertSame(0, (int) $project->project_index);
        $this->assertLessThanOrEqual(307_200, strlen($profile->bytes));
        $this->assertSame(24, $profile->width);
        $this->assertSame(24, $profile->height);

        $imageUrl = route('portfolio.image', ['portfolio' => $portfolio, 'image' => $profile]);
        $response = $this->get($imageUrl)->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=31536000', $cacheControl);
        $this->assertStringContainsString('immutable', $cacheControl);
        $etag = $response->headers->get('ETag');
        $this->assertNotEmpty($etag);
        $this->assertSame($profile->bytes, $response->getContent());
        $this->withHeaders(['If-None-Match' => $etag])->get($imageUrl)->assertStatus(304)->assertHeader('ETag', $etag);

        $this->get(route('portfolio.show', ['portfolio' => $portfolio]))
            ->assertOk()
            ->assertSee('Avery at a desk', false)
            ->assertSee('Laravel')
            ->assertSee('mailto:avery@example.test', false);
    }

    public function test_project_image_index_tracks_the_saved_project_after_empty_rows_are_removed(): void
    {
        $this->post(route('portfolios.store'), $this->validPortfolioData([
            'projects' => [
                ['name' => '', 'description' => '', 'url' => '', 'tags_input' => ''],
                ['name' => 'Transit Map', 'description' => 'A public transit guide.', 'url' => '', 'tags_input' => 'Maps'],
            ],
            'project_images' => [1 => $this->imageUpload('project.png', $this->pngBytes())],
            'project_image_alts' => [1 => 'Transit Map route preview'],
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $portfolio = Portfolio::query()->firstOrFail();
        $image = $portfolio->images()->where('kind', 'project')->firstOrFail();
        $this->assertSame('Transit Map', $portfolio->projects[0]['name']);
        $this->assertSame(0, (int) $image->project_index);
    }

    public function test_image_uploads_reject_disguised_files_missing_alt_text_and_oversized_uploads(): void
    {
        $this->post(route('portfolios.store'), $this->validPortfolioData([
            'profile_image' => $this->imageUpload('disguised.jpg', '<script>alert(1)</script>'),
            'profile_image_alt' => 'A portrait',
        ]), ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors(['profile_image']);

        $this->post(route('portfolios.store'), $this->validPortfolioData([
            'profile_image' => $this->imageUpload('profile.png', $this->pngBytes()),
            'profile_image_alt' => '',
        ]), ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors(['profile_image_alt']);

        $this->post(route('portfolios.store'), $this->validPortfolioData([
            'profile_image' => $this->imageUpload('large.png', str_repeat('x', 2_097_153)),
            'profile_image_alt' => 'A portrait',
        ]), ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors(['profile_image']);

        $this->assertDatabaseCount('portfolios', 0);
    }

    public function test_interest_and_project_tag_limits_are_enforced(): void
    {
        $tooManyTags = implode(', ', array_fill(0, 9, 'Technology'));
        $this->postJson(route('portfolios.store'), $this->validPortfolioData([
            'interests_input' => implode(', ', array_fill(0, 9, 'Interest')),
            'projects' => [['name' => 'A project', 'description' => '', 'url' => '', 'tags_input' => $tooManyTags]],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['interests_input', 'projects.0.tags_input']);
    }

    public function test_image_alt_text_is_escaped_in_public_markup(): void
    {
        $this->post(route('portfolios.store'), $this->validPortfolioData([
            'profile_image' => $this->imageUpload('profile.png', $this->pngBytes()),
            'profile_image_alt' => '<script>alert(1)</script>',
        ]))->assertRedirect();

        $portfolio = Portfolio::query()->firstOrFail();
        $portfolio->update(['template_choice' => 'Simple']);
        $this->get(route('portfolio.show', ['portfolio' => $portfolio]))
            ->assertOk()
            ->assertSee('alt="&lt;script&gt;alert(1)&lt;/script&gt;"', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_gd_reencoding_removes_exif_and_gps_metadata_and_caps_dimensions(): void
    {
        $image = imagecreatetruecolor(1600, 800);
        ob_start();
        imagejpeg($image, null, 96);
        $jpeg = ob_get_clean();
        imagedestroy($image);

        $exifPayload = "Exif\0\0GPSLatitude\0GPSLongitude\0Private camera note";
        $segment = "\xFF\xE1".pack('n', strlen($exifPayload) + 2).$exifPayload;
        $sourceWithMetadata = substr($jpeg, 0, 2).$segment.substr($jpeg, 2);
        $this->assertStringContainsString('GPSLatitude', $sourceWithMetadata);

        $this->post(route('portfolios.store'), $this->validPortfolioData([
            'profile_image' => $this->imageUpload('portrait.jpg', $sourceWithMetadata),
            'profile_image_alt' => 'Portrait with a neutral background',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $stored = PortfolioImage::query()->where('kind', 'profile')->firstOrFail();
        $this->assertSame('image/jpeg', $stored->mime);
        $this->assertLessThanOrEqual(1200, max($stored->width, $stored->height));
        $this->assertLessThanOrEqual(307_200, strlen($stored->bytes));
        $this->assertStringNotContainsString('Exif', $stored->bytes);
        $this->assertStringNotContainsString('GPSLatitude', $stored->bytes);
        $this->assertStringNotContainsString('GPSLongitude', $stored->bytes);
        $this->assertSame(1200, $stored->width);
        $this->assertSame(600, $stored->height);
    }

    public function test_images_remain_during_undo_and_are_deleted_when_the_portfolio_is_purged(): void
    {
        $this->post(route('portfolios.store'), $this->validPortfolioData([
            'profile_image' => $this->imageUpload('profile.png', $this->pngBytes()),
            'profile_image_alt' => 'A portrait',
        ]))->assertRedirect();
        $portfolio = Portfolio::query()->firstOrFail();
        $portfolio->update(['template_choice' => 'Simple']);
        $imageId = $portfolio->images()->firstOrFail()->id;

        $deleted = $this->deleteJson(URL::signedRoute('manage.destroy', ['portfolio' => $portfolio]))->assertOk();
        $this->assertDatabaseHas('portfolio_images', ['id' => $imageId]);
        $this->get(route('portfolio.image', ['portfolio' => $portfolio, 'image' => $imageId]))->assertNotFound();

        $this->travel(11)->seconds();
        $this->deleteJson($deleted->json('purgeUrl'))->assertOk();
        $this->assertDatabaseMissing('portfolio_images', ['id' => $imageId]);
    }

    public function test_project_image_ids_cannot_be_used_to_read_or_edit_another_portfolio_image(): void
    {
        $first = Portfolio::factory()->withTemplate('Simple')->create();
        $image = $first->images()->create([
            'kind' => 'profile',
            'mime' => 'image/png',
            'bytes' => $this->pngBytes(),
            'width' => 24,
            'height' => 24,
            'alt' => 'A portrait',
        ]);
        $second = Portfolio::factory()->withTemplate('Modern')->create();

        $this->get(route('portfolio.image', ['portfolio' => $second, 'image' => $image]))->assertNotFound();
        $this->get(route('portfolio.image', ['portfolio' => $first, 'image' => $image]))->assertOk();
        $this->putJson(URL::signedRoute('manage.update', ['portfolio' => $second]), $this->validPortfolioData([
            'project_image_ids' => [0 => $image->id],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['project_image_ids']);
    }

    public function test_postgres_hex_encoded_bytea_values_are_returned_as_image_bytes(): void
    {
        $image = new PortfolioImage;
        $image->setRawAttributes(['bytes' => '\\x89504e470d0a1a0a'], true);

        $this->assertSame(hex2bin('89504e470d0a1a0a'), $image->bytes);
    }

    private function validPortfolioData(array $overrides = []): array
    {
        return array_replace([
            'full_name' => 'Avery Example',
            'headline' => 'Designer building useful tools',
            'about_me' => 'I make thoughtful digital products.',
            'education' => [],
            'work_experience' => [],
            'skills' => ['Research', 'Design'],
            'projects' => [],
            'social_links' => [],
        ], $overrides);
    }

    private function imageUpload(string $filename, string $bytes): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($filename, $bytes);
    }

    private function pngBytes(): string
    {
        $image = imagecreatetruecolor(24, 24);
        $background = imagecolorallocate($image, 64, 128, 96);
        imagefill($image, 0, 0, $background);
        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }
}

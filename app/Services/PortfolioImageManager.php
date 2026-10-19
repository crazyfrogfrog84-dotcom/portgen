<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\PortfolioImage;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PortfolioImageManager
{
    public function __construct(private readonly PortfolioImageProcessor $processor) {}

    /** @param array<int, int> $projectIndexMap Maps submitted row indexes to saved project indexes. */
    public function sync(Portfolio $portfolio, Request $request, array $projectIndexMap): void
    {
        $this->syncProfile($portfolio, $request);
        $this->syncProjects($portfolio, $request, $projectIndexMap);
    }

    private function syncProfile(Portfolio $portfolio, Request $request): void
    {
        $current = $portfolio->images()->where('kind', 'profile')->first();

        if ($request->boolean('profile_image_remove')) {
            $current?->delete();

            return;
        }

        if ($request->hasFile('profile_image')) {
            $current?->delete();
            $this->save($portfolio, $request->file('profile_image'), 'profile', null, $request->string('profile_image_alt')->toString(), 'profile_image');

            return;
        }

        if ($current && $request->filled('profile_image_alt')) {
            $current->update(['alt' => $request->string('profile_image_alt')->toString()]);
        }
    }

    /** @param array<int, int> $projectIndexMap */
    private function syncProjects(Portfolio $portfolio, Request $request, array $projectIndexMap): void
    {
        $submittedIds = Arr::wrap($request->input('project_image_ids', []));
        $manageRowsExplicitly = $request->boolean('project_image_controls_present') || $request->exists('project_image_ids');
        $existingImages = $portfolio->images()->where('kind', 'project')->get();

        foreach ($existingImages as $image) {
            $submittedIndex = array_search($image->id, $submittedIds, true);
            if (! $manageRowsExplicitly && $submittedIndex === false) {
                $submittedIndex = array_search((int) $image->project_index, $projectIndexMap, true);
            }
            if ($submittedIndex === false || $request->boolean("project_image_remove.{$submittedIndex}") || ! array_key_exists($submittedIndex, $projectIndexMap)) {
                $image->delete();

                continue;
            }

            $image->project_index = $projectIndexMap[$submittedIndex];
            $alt = $request->input("project_image_alts.{$submittedIndex}");
            if (is_string($alt) && trim($alt) !== '') {
                $image->alt = trim($alt);
            }
            $image->save();
        }

        foreach ($request->file('project_images', []) ?? [] as $submittedIndex => $upload) {
            if (! $upload || ! array_key_exists($submittedIndex, $projectIndexMap)) {
                continue;
            }

            $portfolio->images()->where('kind', 'project')->where('project_index', $projectIndexMap[$submittedIndex])->delete();
            $alt = $request->input("project_image_alts.{$submittedIndex}", 'Project image');
            $this->save($portfolio, $upload, 'project', $projectIndexMap[$submittedIndex], trim((string) $alt), "project_images.{$submittedIndex}");
        }
    }

    private function save(Portfolio $portfolio, $upload, string $kind, ?int $projectIndex, string $alt, string $errorField): PortfolioImage
    {
        $processed = $this->processor->process($upload, $errorField);

        return $portfolio->images()->create([
            'kind' => $kind,
            'project_index' => $projectIndex,
            ...$processed,
            'alt' => $alt,
        ]);
    }
}

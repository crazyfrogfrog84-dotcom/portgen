<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTemplateRequest;
use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use App\Models\Portfolio;
use App\Services\PortfolioImageManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    private const UNDO_SECONDS = 10;

    public function create(): View
    {
        return view('portfolios.create');
    }

    public function store(StorePortfolioRequest $request, PortfolioImageManager $imageManager): RedirectResponse
    {
        [$data, $projectIndexMap] = $this->structuredData($request->validated(), $request);
        $portfolio = DB::transaction(function () use ($data, $projectIndexMap, $request, $imageManager) {
            $portfolio = Portfolio::create($data);
            $imageManager->sync($portfolio, $request, $projectIndexMap);

            return $portfolio;
        });
        $ownerManageUrl = URL::signedRoute('manage.show', ['portfolio' => $portfolio]);

        return redirect(URL::signedRoute('portfolio.select-template', ['portfolio' => $portfolio]))
            ->with('status', 'Your portfolio draft is saved. Choose a template to continue.')
            ->with('ownerManageUrl', $ownerManageUrl);
    }

    public function selectTemplate(Request $request, Portfolio $portfolio): View
    {
        $backUrl = route('portfolios.create');
        $fromManage = $request->boolean('from_manage');
        if ($fromManage) {
            $backUrl = URL::signedRoute('manage.show', ['portfolio' => $portfolio]);
        } elseif (session()->has('ownerManageUrl')) {
            session()->keep(['ownerManageUrl']);
            $backUrl = URL::signedRoute('manage.edit', ['portfolio' => $portfolio]);
        }
        $saveTemplateUrl = URL::signedRoute('portfolio.save-template', array_filter([
            'portfolio' => $portfolio,
            'from_manage' => $fromManage ? 1 : null,
        ], fn ($value) => $value !== null));

        return view('portfolios.select-template', compact('portfolio', 'backUrl', 'saveTemplateUrl', 'fromManage'));
    }

    public function saveTemplate(SaveTemplateRequest $request, Portfolio $portfolio): RedirectResponse
    {
        $ownerManageUrl = session('ownerManageUrl');
        $portfolio->template_choice = $request->validated('template_choice');
        $portfolio->save();

        if ($request->boolean('from_manage')) {
            return redirect(URL::signedRoute('manage.show', ['portfolio' => $portfolio]))
                ->with('status', 'Your template has been changed.');
        }

        return redirect()
            ->route('portfolio.show', ['portfolio' => $portfolio])
            ->with('status', 'Your portfolio is ready to share.')
            ->with('ownerManageUrl', $ownerManageUrl);
    }

    public function show(Portfolio $portfolio): View|RedirectResponse
    {
        if (! $portfolio->template_choice) {
            abort(404);
        }

        $template = strtolower($portfolio->template_choice);

        $portfolio->load('images');
        $profileImage = $portfolio->images->firstWhere('kind', 'profile');
        $projectImages = $portfolio->images->where('kind', 'project')->keyBy('project_index');
        $hasContact = filled($portfolio->contact_email) || count($portfolio->social_links ?? []) > 0;
        $navItems = array_values(array_filter([
            ['label' => 'Home', 'href' => '#home', 'current' => true],
            ['label' => 'About', 'href' => '#about'],
            count($portfolio->skills ?? []) ? ['label' => 'Skills', 'href' => '#skills'] : null,
            count($portfolio->projects ?? []) ? ['label' => 'Projects', 'href' => '#projects'] : null,
            count($portfolio->work_experience ?? []) ? ['label' => 'Experience', 'href' => '#experience'] : null,
            count($portfolio->education ?? []) ? ['label' => 'Education', 'href' => '#education'] : null,
            $hasContact ? ['label' => 'Contact', 'href' => '#contact'] : null,
        ]));

        return view('portfolios.show', [
            'portfolio' => $portfolio,
            'template' => $template,
            'ownerManageUrl' => session('ownerManageUrl'),
            'profileImage' => $profileImage,
            'projectImages' => $projectImages,
            'navItems' => $navItems,
        ]);
    }

    public function manage(Portfolio $portfolio): View
    {
        return view('portfolios.manage', [
            'portfolio' => $portfolio,
            'publicUrl' => route('portfolio.show', ['portfolio' => $portfolio]),
            'editUrl' => URL::signedRoute('manage.edit', ['portfolio' => $portfolio]),
            'deleteUrl' => URL::signedRoute('manage.destroy', ['portfolio' => $portfolio]),
            'changeTemplateUrl' => URL::signedRoute('portfolio.select-template', ['portfolio' => $portfolio, 'from_manage' => 1]),
        ]);
    }

    public function edit(Portfolio $portfolio): View
    {
        $portfolio->load('images');

        return view('portfolios.edit', [
            'portfolio' => $portfolio,
            'updateUrl' => URL::signedRoute('manage.update', ['portfolio' => $portfolio]),
        ]);
    }

    public function update(UpdatePortfolioRequest $request, Portfolio $portfolio, PortfolioImageManager $imageManager): RedirectResponse
    {
        [$data, $projectIndexMap] = $this->structuredData($request->validated(), $request);
        DB::transaction(function () use ($portfolio, $data, $projectIndexMap, $request, $imageManager) {
            $portfolio->fill($data);
            $portfolio->save();
            $imageManager->sync($portfolio, $request, $projectIndexMap);
        });

        return redirect(URL::signedRoute('manage.show', ['portfolio' => $portfolio]))
            ->with('status', 'Your changes have been saved.');
    }

    public function destroy(Request $request, Portfolio $portfolio): JsonResponse|RedirectResponse
    {
        $portfolio->undo_until = now()->addSeconds(self::UNDO_SECONDS);
        $portfolio->save();
        $portfolio->delete();

        $undoUrl = URL::signedRoute('manage.undo', [
            'portfolio' => $portfolio->slug,
            'undo_until' => $portfolio->undo_until->timestamp,
        ]);
        $purgeUrl = URL::signedRoute('manage.purge', [
            'portfolio' => $portfolio->slug,
            'undo_until' => $portfolio->undo_until->timestamp,
        ]);
        $deletedUrl = URL::signedRoute('manage.deleted', ['portfolio' => $portfolio->slug]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Portfolio deleted. You have 10 seconds to undo.',
                'undoUrl' => $undoUrl,
                'purgeUrl' => $purgeUrl,
                'deletedUrl' => $deletedUrl,
                'undoUntil' => $portfolio->undo_until->toIso8601String(),
            ]);
        }

        return redirect($deletedUrl)->with('status', 'Portfolio deleted. You have 10 seconds to undo.');
    }

    public function deleted(Portfolio $portfolio): View|RedirectResponse
    {
        if (! $portfolio->trashed()) {
            return redirect(URL::signedRoute('manage.show', ['portfolio' => $portfolio]));
        }

        $undoOpen = $portfolio->undo_until && now()->lessThan($portfolio->undo_until);
        $undoUrl = $undoOpen ? URL::signedRoute('manage.undo', [
            'portfolio' => $portfolio->slug,
            'undo_until' => $portfolio->undo_until->timestamp,
        ]) : null;
        $purgeUrl = URL::signedRoute('manage.purge', [
            'portfolio' => $portfolio->slug,
            'undo_until' => $portfolio->undo_until?->timestamp,
        ]);

        return view('portfolios.deleted', [
            'portfolio' => $portfolio,
            'undoUrl' => $undoUrl,
            'purgeUrl' => $purgeUrl,
            'undoSecondsLeft' => $undoOpen ? max(0, now()->diffInSeconds($portfolio->undo_until, false)) : 0,
        ]);
    }

    public function undo(Request $request, Portfolio $portfolio): JsonResponse|RedirectResponse
    {
        abort_unless($portfolio->trashed(), 409, 'This portfolio is not waiting for deletion.');
        abort_if(! $portfolio->undo_until || now()->greaterThanOrEqualTo($portfolio->undo_until), 410, 'The 10-second undo period has ended.');

        $portfolio->restore();
        $portfolio->undo_until = null;
        $portfolio->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Deletion undone. Your portfolio is restored.',
                'manageUrl' => URL::signedRoute('manage.show', ['portfolio' => $portfolio]),
            ]);
        }

        return redirect(URL::signedRoute('manage.show', ['portfolio' => $portfolio]))
            ->with('status', 'Deletion undone. Your portfolio is restored.');
    }

    public function purge(Request $request, Portfolio $portfolio): JsonResponse|RedirectResponse
    {
        abort_unless($portfolio->trashed(), 409, 'This portfolio is not waiting for deletion.');
        abort_if($portfolio->undo_until && now()->lessThan($portfolio->undo_until), 409, 'The undo period is still open.');

        $portfolio->forceDelete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Portfolio permanently deleted.']);
        }

        return redirect()->route('portfolios.create')->with('status', 'Portfolio permanently deleted.');
    }

    /** @return array{0: array, 1: array<int, int>} */
    private function structuredData(array $data, Request $request): array
    {
        if (isset($data['interests_input'])) {
            $data['interests'] = array_values(array_filter(array_map('trim', explode(',', $data['interests_input'])), fn (string $value) => $value !== ''));
        }
        unset($data['interests_input']);

        $projectIndexMap = [];
        $projects = [];
        foreach ($data['projects'] ?? [] as $submittedIndex => $project) {
            if (isset($project['tags_input'])) {
                $project['tags'] = array_values(array_filter(array_map('trim', explode(',', $project['tags_input'])), fn (string $value) => $value !== ''));
            }
            unset($project['tags_input']);
            $project['tags'] = array_values(array_filter(array_map('trim', $project['tags'] ?? []), fn (string $value) => $value !== ''));

            $hasText = count(array_filter($project, fn ($value) => $value !== null && $value !== '' && $value !== [])) > 0;
            $hasImage = $request->hasFile("project_images.{$submittedIndex}")
                || (! empty($request->input("project_image_ids.{$submittedIndex}")) && ! $request->boolean("project_image_remove.{$submittedIndex}"));
            if (! $hasText && ! $hasImage) {
                continue;
            }

            $projectIndexMap[$submittedIndex] = count($projects);
            $projects[] = $project;
        }
        $data['projects'] = $projects;

        foreach (['education', 'work_experience', 'skills', 'social_links'] as $field) {
            $items = $data[$field] ?? [];
            if (! in_array($field, ['skills'], true)) {
                $items = array_filter($items, function ($item) {
                    return ! is_array($item) || count(array_filter($item, fn ($value) => $value !== null && $value !== '')) > 0;
                });
            }
            $data[$field] = array_values($items);
        }

        foreach (['profile_image', 'profile_image_alt', 'profile_image_remove', 'project_images', 'project_image_alts', 'project_image_ids', 'project_image_remove'] as $field) {
            unset($data[$field]);
        }

        return [$data, $projectIndexMap];
    }
}

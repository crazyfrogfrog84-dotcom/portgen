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
use Illuminate\Support\Facades\Auth;
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
            if ($request->user()) {
                $portfolio->user_id = $request->user()->id;
                $portfolio->save();
            }
            $imageManager->sync($portfolio, $request, $projectIndexMap);

            return $portfolio;
        });
        if ($portfolio->user_id !== null) {
            return redirect()->route('portfolio.select-template', ['portfolio' => $portfolio])
                ->with('status', 'Your portfolio draft is saved. Choose a template to continue.');
        }

        $ownerManageUrl = URL::signedRoute('manage.show', ['portfolio' => $portfolio]);

        return redirect(URL::signedRoute('portfolio.select-template', ['portfolio' => $portfolio]))
            ->with('status', 'Your portfolio draft is saved. Choose a template to continue.')
            ->with('ownerManageUrl', $ownerManageUrl);
    }

    public function selectTemplate(Request $request, Portfolio $portfolio): View
    {
        $this->authorize('manage', $portfolio);
        $backUrl = route('portfolios.create');
        $fromManage = $request->boolean('from_manage');
        if ($fromManage) {
            $backUrl = URL::signedRoute('manage.show', ['portfolio' => $portfolio]);
        } elseif (Auth::id() === $portfolio->user_id && $portfolio->user_id !== null) {
            $backUrl = route('manage.edit', ['portfolio' => $portfolio]);
        } elseif (session()->has('ownerManageUrl')) {
            session()->keep(['ownerManageUrl']);
            $backUrl = URL::signedRoute('manage.edit', ['portfolio' => $portfolio]);
        }
        $saveTemplateParameters = array_filter([
            'portfolio' => $portfolio,
            'from_manage' => $fromManage ? 1 : null,
        ], fn ($value) => $value !== null);
        $saveTemplateUrl = $this->ownerUrl('portfolio.save-template', $portfolio, $saveTemplateParameters);

        return view('portfolios.select-template', compact('portfolio', 'backUrl', 'saveTemplateUrl', 'fromManage'));
    }

    public function saveTemplate(SaveTemplateRequest $request, Portfolio $portfolio): RedirectResponse
    {
        $this->authorize('manage', $portfolio);
        $ownerManageUrl = session('ownerManageUrl');
        $portfolio->template_choice = $request->validated('template_choice');
        $portfolio->save();

        if ($request->boolean('from_manage')) {
            return redirect($this->ownerUrl('manage.show', $portfolio))
                ->with('status', 'Your template has been changed.');
        }

        $response = redirect()->route('portfolio.show', ['portfolio' => $portfolio])
            ->with('status', 'Your portfolio is ready to share.');

        return $portfolio->user_id === null ? $response->with('ownerManageUrl', $ownerManageUrl) : $response;
    }

    public function show(Request $request, Portfolio $portfolio): View|RedirectResponse
    {
        if (! $portfolio->template_choice) {
            abort(404);
        }

        $storedTemplate = strtolower($portfolio->template_choice);
        $isAccountOwner = $portfolio->user_id !== null && (int) Auth::id() === (int) $portfolio->user_id;
        $requestedPreview = $request->query('preview');
        $requestedPreview = is_string($requestedPreview) ? strtolower($requestedPreview) : '';
        $previewChoices = ['simple', 'modern', 'creative'];
        $template = $isAccountOwner && in_array($requestedPreview, $previewChoices, true)
            ? $requestedPreview
            : $storedTemplate;

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
            'selectedTemplate' => ucfirst($template),
            'isTemplatePreview' => $isAccountOwner && in_array($requestedPreview, $previewChoices, true),
            'ownerManageUrl' => session('ownerManageUrl'),
            'isAccountOwner' => $isAccountOwner,
            'profileImage' => $profileImage,
            'projectImages' => $projectImages,
            'navItems' => $navItems,
        ]);
    }

    public function manage(Portfolio $portfolio): View
    {
        $this->authorize('manage', $portfolio);

        return view('portfolios.manage', [
            'portfolio' => $portfolio,
            'publicUrl' => route('portfolio.show', ['portfolio' => $portfolio]),
            'editUrl' => $this->ownerUrl('manage.edit', $portfolio),
            'deleteUrl' => $this->ownerUrl('manage.destroy', $portfolio),
            'changeTemplateUrl' => $this->ownerUrl('portfolio.select-template', $portfolio, ['from_manage' => 1]),
            'claimUrl' => $portfolio->user_id === null && Auth::check()
                ? URL::signedRoute('manage.claim', ['portfolio' => $portfolio])
                : null,
        ]);
    }

    public function edit(Portfolio $portfolio): View
    {
        $this->authorize('manage', $portfolio);
        $portfolio->load('images');

        return view('portfolios.edit', [
            'portfolio' => $portfolio,
            'updateUrl' => $this->ownerUrl('manage.update', $portfolio),
        ]);
    }

    public function update(UpdatePortfolioRequest $request, Portfolio $portfolio, PortfolioImageManager $imageManager): RedirectResponse
    {
        $this->authorize('manage', $portfolio);
        [$data, $projectIndexMap] = $this->structuredData($request->validated(), $request);
        DB::transaction(function () use ($portfolio, $data, $projectIndexMap, $request, $imageManager) {
            $portfolio->fill($data);
            $portfolio->save();
            $imageManager->sync($portfolio, $request, $projectIndexMap);
        });

        return redirect($this->ownerUrl('manage.show', $portfolio))
            ->with('status', 'Your changes have been saved.');
    }

    public function claim(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $this->authorize('claim', $portfolio);
        $portfolio->user_id = $request->user()->id;
        $portfolio->save();

        return redirect()->route('manage.show', ['portfolio' => $portfolio])
            ->with('status', 'This portfolio is now part of your account. Its old private link no longer grants access.');
    }

    public function destroy(Request $request, Portfolio $portfolio): JsonResponse|RedirectResponse
    {
        $this->authorize('manage', $portfolio);
        $portfolio->undo_until = now()->addSeconds(self::UNDO_SECONDS);
        $portfolio->save();
        $portfolio->delete();

        $undoUrl = $this->ownerUrl('manage.undo', $portfolio, [
            'portfolio' => $portfolio->slug,
            'undo_until' => $portfolio->undo_until->timestamp,
        ]);
        $purgeUrl = $this->ownerUrl('manage.purge', $portfolio, [
            'portfolio' => $portfolio->slug,
            'undo_until' => $portfolio->undo_until->timestamp,
        ]);
        $deletedUrl = $this->ownerUrl('manage.deleted', $portfolio, []);

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
        $this->authorize('manage', $portfolio);
        if (! $portfolio->trashed()) {
            return redirect($this->ownerUrl('manage.show', $portfolio));
        }

        $undoOpen = $portfolio->undo_until && now()->lessThan($portfolio->undo_until);
        $undoUrl = $undoOpen ? $this->ownerUrl('manage.undo', $portfolio, [
            'portfolio' => $portfolio->slug,
            'undo_until' => $portfolio->undo_until->timestamp,
        ]) : null;
        $purgeUrl = $this->ownerUrl('manage.purge', $portfolio, [
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
        $this->authorize('manage', $portfolio);
        abort_unless($portfolio->trashed(), 409, 'This portfolio is not waiting for deletion.');
        abort_if(! $portfolio->undo_until || now()->greaterThanOrEqualTo($portfolio->undo_until), 410, 'The 10-second undo period has ended.');

        $portfolio->restore();
        $portfolio->undo_until = null;
        $portfolio->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Deletion undone. Your portfolio is restored.',
                'manageUrl' => $this->ownerUrl('manage.show', $portfolio),
            ]);
        }

        return redirect($this->ownerUrl('manage.show', $portfolio))
            ->with('status', 'Deletion undone. Your portfolio is restored.');
    }

    public function purge(Request $request, Portfolio $portfolio): JsonResponse|RedirectResponse
    {
        $this->authorize('manage', $portfolio);
        abort_unless($portfolio->trashed(), 409, 'This portfolio is not waiting for deletion.');
        abort_if($portfolio->undo_until && now()->lessThan($portfolio->undo_until), 409, 'The undo period is still open.');

        $portfolio->forceDelete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Portfolio permanently deleted.']);
        }

        return redirect($portfolio->user_id ? route('dashboard') : route('portfolios.create'))->with('status', 'Portfolio permanently deleted.');
    }

    private function ownerUrl(string $routeName, Portfolio $portfolio, array $parameters = []): string
    {
        $parameters = array_merge(['portfolio' => $portfolio], $parameters);
        if ($portfolio->user_id !== null && Auth::id() === $portfolio->user_id) {
            return route($routeName, $parameters);
        }

        return URL::signedRoute($routeName, $parameters);
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

<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTemplateRequest;
use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    private const UNDO_SECONDS = 10;

    public function create(): View
    {
        return view('portfolios.create');
    }

    public function store(StorePortfolioRequest $request): RedirectResponse
    {
        $portfolio = Portfolio::create($this->structuredData($request->validated()));
        $ownerManageUrl = URL::signedRoute('manage.show', ['portfolio' => $portfolio]);

        return redirect()
            ->route('portfolio.select-template', ['portfolio' => $portfolio])
            ->with('status', 'Your portfolio draft is saved. Choose a template to continue.')
            ->with('ownerManageUrl', $ownerManageUrl);
    }

    public function selectTemplate(Portfolio $portfolio): View
    {
        $backUrl = route('portfolios.create');
        if (session()->has('ownerManageUrl')) {
            session()->keep(['ownerManageUrl']);
            $backUrl = URL::signedRoute('manage.edit', ['portfolio' => $portfolio]);
        }

        return view('portfolios.select-template', compact('portfolio', 'backUrl'));
    }

    public function saveTemplate(SaveTemplateRequest $request, Portfolio $portfolio): RedirectResponse
    {
        $ownerManageUrl = session('ownerManageUrl');
        $portfolio->template_choice = $request->validated('template_choice');
        $portfolio->save();

        return redirect()
            ->route('portfolio.show', ['portfolio' => $portfolio])
            ->with('status', 'Your portfolio is ready to share.')
            ->with('ownerManageUrl', $ownerManageUrl);
    }

    public function show(Portfolio $portfolio): View|RedirectResponse
    {
        if (! $portfolio->template_choice) {
            return redirect()->route('portfolio.select-template', ['portfolio' => $portfolio]);
        }

        $template = strtolower($portfolio->template_choice);

        return view('portfolios.show', [
            'portfolio' => $portfolio,
            'template' => $template,
            'ownerManageUrl' => session('ownerManageUrl'),
        ]);
    }

    public function manage(Portfolio $portfolio): View
    {
        return view('portfolios.manage', [
            'portfolio' => $portfolio,
            'publicUrl' => route('portfolio.show', ['portfolio' => $portfolio]),
            'editUrl' => URL::signedRoute('manage.edit', ['portfolio' => $portfolio]),
            'deleteUrl' => URL::signedRoute('manage.destroy', ['portfolio' => $portfolio]),
        ]);
    }

    public function edit(Portfolio $portfolio): View
    {
        return view('portfolios.edit', [
            'portfolio' => $portfolio,
            'updateUrl' => URL::signedRoute('manage.update', ['portfolio' => $portfolio]),
        ]);
    }

    public function update(UpdatePortfolioRequest $request, Portfolio $portfolio): RedirectResponse
    {
        $portfolio->fill($this->structuredData($request->validated()));
        $portfolio->save();

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
            return response()->json(['message' => 'Deletion undone. Your portfolio is restored.']);
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

    private function structuredData(array $data): array
    {
        foreach (['education', 'work_experience', 'skills', 'projects', 'social_links'] as $field) {
            $items = $data[$field] ?? [];
            if ($field !== 'skills') {
                $items = array_filter($items, function ($item) {
                    return ! is_array($item) || count(array_filter($item, fn ($value) => $value !== null && $value !== '')) > 0;
                });
            }
            $data[$field] = array_values($items);
        }

        return $data;
    }
}

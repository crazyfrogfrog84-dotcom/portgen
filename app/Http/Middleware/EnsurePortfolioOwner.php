<?php

namespace App\Http\Middleware;

use App\Models\Portfolio;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortfolioOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $portfolio = $request->route('portfolio');

        abort_unless($portfolio instanceof Portfolio, 404);

        if (Auth::check() && $portfolio->user_id !== null && (int) $portfolio->user_id === (int) Auth::id()) {
            return $next($request);
        }

        if ($portfolio->user_id === null && URL::hasValidSignature($request)) {
            $request->session()->put('guest_portfolio_owners.'.$portfolio->id, true);

            return $next($request);
        }

        abort(403);
    }
}

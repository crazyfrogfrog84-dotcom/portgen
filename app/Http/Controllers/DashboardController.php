<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = $request->user()->portfolios()->latest('updated_at');
        $portfolios = (clone $query)->count() > 12
            ? $query->paginate(12)
            : $query->get();

        return view('auth.dashboard', compact('portfolios'));
    }
}

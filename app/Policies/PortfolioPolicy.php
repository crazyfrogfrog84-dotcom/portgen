<?php

namespace App\Policies;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class PortfolioPolicy
{
    public function manage(?User $user, Portfolio $portfolio): bool
    {
        if ($portfolio->user_id !== null) {
            return $user !== null && (int) $portfolio->user_id === (int) $user->id;
        }

        return URL::hasValidSignature(request());
    }

    public function claim(User $user, Portfolio $portfolio): bool
    {
        return $portfolio->user_id === null;
    }
}

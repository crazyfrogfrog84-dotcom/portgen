<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasPortfolioRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePortfolioRequest extends FormRequest
{
    use HasPortfolioRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->portfolioRules();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Portfolio extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'full_name',
        'headline',
        'about_me',
        'education',
        'work_experience',
        'skills',
        'projects',
        'social_links',
        'template_choice',
    ];

    protected function casts(): array
    {
        return [
            'education' => 'array',
            'work_experience' => 'array',
            'skills' => 'array',
            'projects' => 'array',
            'social_links' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Portfolio $portfolio) {
            $portfolio->id ??= (string) Str::uuid();
            $portfolio->slug ??= Str::random(10);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

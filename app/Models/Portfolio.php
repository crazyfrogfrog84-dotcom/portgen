<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Portfolio extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'full_name',
        'headline',
        'about_me',
        'location',
        'availability',
        'contact_email',
        'motto',
        'interests',
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
            'interests' => 'array',
            'undo_until' => 'datetime',
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

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImage::class);
    }
}

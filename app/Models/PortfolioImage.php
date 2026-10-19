<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PortfolioImage extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'kind',
        'project_index',
        'mime',
        'bytes',
        'width',
        'height',
        'alt',
    ];

    protected static function booted(): void
    {
        static::creating(function (PortfolioImage $image) {
            $image->id ??= (string) Str::uuid();
        });
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function getBytesAttribute(mixed $value): mixed
    {
        if (is_resource($value)) {
            $metadata = stream_get_meta_data($value);
            if ($metadata['seekable'] ?? false) {
                rewind($value);
            }
            $bytes = stream_get_contents($value);
            if ($metadata['seekable'] ?? false) {
                rewind($value);
            }

            return $bytes;
        }

        if (is_string($value) && preg_match('/^\\\\x([a-f0-9]+)$/i', $value, $matches) && strlen($matches[1]) % 2 === 0) {
            return hex2bin($matches[1]);
        }

        return $value;
    }

    public function setBytesAttribute(string $value): void
    {
        if (DB::connection()->getDriverName() === 'pgsql' && ! preg_match('/^\\\\x([a-f0-9]+)$/i', $value)) {
            $value = '\\x'.bin2hex($value);
        }

        $this->attributes['bytes'] = $value;
    }
}

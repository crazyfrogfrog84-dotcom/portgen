<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class PortfolioImageProcessor
{
    public const MAX_UPLOAD_BYTES = 2_097_152;

    public const MAX_STORED_BYTES = 307_200;

    public const MAX_EDGE = 1200;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /** @return array{mime: string, bytes: string, width: int, height: int} */
    public function process(UploadedFile $upload, string $errorField = 'profile_image'): array
    {
        $path = $upload->getRealPath();
        $size = $upload->getSize();
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if (! $path || ! $size || $size > self::MAX_UPLOAD_BYTES || ! in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            throw ValidationException::withMessages([$errorField => 'Choose a JPEG, PNG, or WebP image smaller than 2 MB.']);
        }

        $sourceBytes = file_get_contents($path);
        $info = $sourceBytes === false ? false : @getimagesizefromstring($sourceBytes);
        if (! is_array($info) || ($info['mime'] ?? null) !== $mime) {
            throw ValidationException::withMessages([$errorField => 'The uploaded file is not a readable JPEG, PNG, or WebP image.']);
        }

        [$sourceWidth, $sourceHeight] = [(int) $info[0], (int) $info[1]];
        if ($sourceWidth < 1 || $sourceHeight < 1 || $sourceWidth * $sourceHeight > 25_000_000) {
            throw ValidationException::withMessages([$errorField => 'This image is too large to process safely.']);
        }

        if (! function_exists('imagecreatefromstring')) {
            throw new \RuntimeException('The GD extension is required to process portfolio images.');
        }

        $source = @imagecreatefromstring($sourceBytes);
        if (! $source) {
            throw ValidationException::withMessages([$errorField => 'The uploaded image could not be decoded.']);
        }

        try {
            $edge = min(self::MAX_EDGE, max($sourceWidth, $sourceHeight));
            $quality = 88;

            while (true) {
                $ratio = min(1, $edge / max($sourceWidth, $sourceHeight));
                $width = max(1, (int) round($sourceWidth * $ratio));
                $height = max(1, (int) round($sourceHeight * $ratio));
                $canvas = imagecreatetruecolor($width, $height);

                if ($mime === 'image/png') {
                    imagealphablending($canvas, false);
                    imagesavealpha($canvas, true);
                    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                    imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
                }

                imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
                $optimized = $this->encode($canvas, $mime, $quality);
                imagedestroy($canvas);

                if (strlen($optimized) <= self::MAX_STORED_BYTES) {
                    return ['mime' => $mime, 'bytes' => $optimized, 'width' => $width, 'height' => $height];
                }

                if ($mime !== 'image/png' && $quality > 48) {
                    $quality -= 10;
                } elseif ($edge > 240) {
                    $edge = max(240, (int) floor($edge * 0.82));
                    $quality = 88;
                } else {
                    break;
                }
            }
        } finally {
            imagedestroy($source);
        }

        throw ValidationException::withMessages([$errorField => 'This image could not be reduced below 300 KB. Try a simpler or smaller image.']);
    }

    private function encode(\GdImage $image, string $mime, int $quality): string
    {
        ob_start();
        $written = match ($mime) {
            'image/jpeg' => imagejpeg($image, null, $quality),
            'image/png' => imagepng($image, null, 9),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, null, $quality) : false,
            default => false,
        };
        $bytes = ob_get_clean();

        if (! $written || ! is_string($bytes)) {
            throw new \RuntimeException('The image could not be re-encoded.');
        }

        return $bytes;
    }
}

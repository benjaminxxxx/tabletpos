<?php
// app/Services/ImageProcessor.php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Illuminate\Http\UploadedFile;

class ImageProcessor
{
    const THUMB_WIDTH = 150;
    const FULL_WIDTH  = 1200;

    private ImageManager $manager;

    public function __construct()
    {
        // v4: ImageManager::usingDriver() en lugar de new ImageManager(new Driver())
        $this->manager = ImageManager::usingDriver(Driver::class);
    }

    public function process(UploadedFile $file, string $folder): array
    {
        $baseName = uniqid() . '_' . time();
        $diskRoot = config('filesystems.disks.public.root');

        $thumbPath = "{$folder}/thumbs/{$baseName}_thumb.webp";
        $fullPath  = "{$folder}/full/{$baseName}_full.webp";

        $this->ensureDir("{$diskRoot}/{$folder}/thumbs");
        $this->ensureDir("{$diskRoot}/{$folder}/full");

        // v4: decodePath() en lugar de read()
        $image = $this->manager->decodePath($file->getRealPath());

        // Thumbnail — 150px ancho, proporcional
        $image->scaleDown(width: self::THUMB_WIDTH)
              ->encodeUsingFormat(Format::WEBP, quality: 75)
              ->save("{$diskRoot}/{$thumbPath}");

        // Full — 1200px ancho máximo
        // Re-leer porque encodeUsingFormat retorna un objeto encoded, no la imagen
        $this->manager->decodePath($file->getRealPath())
             ->scaleDown(width: self::FULL_WIDTH)
             ->encodeUsingFormat(Format::WEBP, quality: 85)
             ->save("{$diskRoot}/{$fullPath}");

        return [
            'path_thumb' => $thumbPath,
            'path_full'  => $fullPath,
            'mime_type'  => 'image/webp',
        ];
    }

    private function ensureDir(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Copies committed seed images from the public assets folder into the
 * application's public storage disk, where the models expect to find them.
 */
trait PublishesSeedImages
{
    /**
     * Publishes a seed image once per target directory and returns the stored
     * file name, or null when the record ships without a photograph.
     *
     * @param  array<int, string>  $targetDirectories
     */
    private function publishSeedImage(?string $fileName, string $sourceDirectory, array $targetDirectories): ?string
    {
        if (! $fileName) {
            return null;
        }

        $sourcePath = public_path($sourceDirectory.'/'.$fileName);

        if (! File::exists($sourcePath)) {
            throw new RuntimeException('Missing seed image: '.$sourcePath);
        }

        $disk = Storage::disk('public');

        foreach ($targetDirectories as $directory) {
            if (! $disk->exists($directory.$fileName)) {
                $disk->put($directory.$fileName, File::get($sourcePath));
            }
        }

        return $fileName;
    }
}

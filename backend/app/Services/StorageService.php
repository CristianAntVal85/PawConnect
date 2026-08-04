<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StorageService
{
    public function storePublicFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * @param  iterable<string|null>  $paths
     */
    public function deleteManyPublicFiles(iterable $paths): void
    {
        foreach ($paths as $path) {
            $this->deletePublicFile($path);
        }
    }

    public function deletePublicFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

}
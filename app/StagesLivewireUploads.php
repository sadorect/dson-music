<?php

namespace App;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait StagesLivewireUploads
{
    /**
     * Persist a Livewire upload onto a stable disk before Media Library imports it.
     *
     * @param  object  $model
     */
    protected function addStagedMedia($model, TemporaryUploadedFile|UploadedFile $file, string $collection, string $fileName): void
    {
        $extension = strtolower(
            $file->getClientOriginalExtension()
            ?: $file->extension()
            ?: pathinfo($fileName, PATHINFO_EXTENSION)
            ?: 'bin'
        );

        $stagedPath = $file->storeAs(
            'tmp/media-imports',
            Str::uuid()->toString().'.'.$extension,
            ['disk' => 'local']
        );

        try {
            $model->addMediaFromDisk($stagedPath, 'local')
                ->usingFileName($fileName)
                ->toMediaCollection($collection);
        } finally {
            Storage::disk('local')->delete($stagedPath);
        }
    }
}

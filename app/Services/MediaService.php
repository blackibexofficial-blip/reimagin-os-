<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Media library storage.
 *
 * Files live on the "public" disk with content-hash-ish, collision-free names,
 * and every reference is tracked in media_usages so an asset that is still in
 * use cannot be deleted by accident.
 */
class MediaService
{
    public function upload(UploadedFile $file, ?User $user = null, ?int $folderId = null, ?string $altText = null): Media
    {
        $disk = 'public';
        $directory = 'media/'.now()->format('Y/m');

        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'asset';
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $fileName = Str::limit($name, 60, '').'-'.Str::lower(Str::random(6)).'.'.$extension;

        $path = $file->storeAs($directory, $fileName, $disk);

        [$width, $height] = $this->dimensions($file);

        return DB::transaction(fn () => Media::create([
            'name' => Str::title(str_replace(['-', '_'], ' ', $name)),
            'file_name' => $fileName,
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'alt_text' => $altText,
            'folder_id' => $folderId,
            'uploaded_by' => $user?->id,
        ]));
    }

    public function replace(Media $media, UploadedFile $file): Media
    {
        $oldPath = $media->path;

        $replaces = $this->upload($file, $media->uploader, $media->folder_id, $media->alt_text);

        $media->forceFill([
            'path' => $replaces->path,
            'file_name' => $replaces->file_name,
            'mime_type' => $replaces->mime_type,
            'size' => $replaces->size,
            'width' => $replaces->width,
            'height' => $replaces->height,
        ])->save();

        $replaces->forceFill(['path' => ''])->delete();

        if ($oldPath) {
            Storage::disk($media->disk ?: 'public')->delete($oldPath);
        }

        return $media->refresh();
    }

    /** Crop by rewriting the file with GD; raises when GD is unavailable. */
    public function crop(Media $media, int $x, int $y, int $width, int $height): Media
    {
        if (! function_exists('imagecreatefromstring')) {
            throw new \RuntimeException('The GD extension is not available on this runtime.');
        }

        if (! $media->isImage()) {
            throw new \RuntimeException('Only images can be cropped.');
        }

        $disk = Storage::disk($media->disk ?: 'public');
        $source = $disk->get($media->path);
        $image = @imagecreatefromstring($source);

        if (! $image) {
            throw new \RuntimeException('That image format cannot be cropped.');
        }

        $crop = imagecrop($image, [
            'x' => max(0, $x),
            'y' => max(0, $y),
            'width' => max(1, $width),
            'height' => max(1, $height),
        ]);

        if (! $crop) {
            throw new \RuntimeException('The crop rectangle falls outside the image.');
        }

        ob_start();
        imagepng($crop);
        $encoded = ob_get_clean();

        $disk->put($media->path, $encoded);

        $media->forceFill([
            'width' => imagesx($crop),
            'height' => imagesy($crop),
            'size' => strlen($encoded),
        ])->save();

        return $media->refresh();
    }

    /** @throws \RuntimeException when the asset is still referenced */
    public function delete(Media $media): void
    {
        if ($media->usages()->exists()) {
            throw new \RuntimeException('This asset is still in use.');
        }

        DB::transaction(function () use ($media) {
            Storage::disk($media->disk ?: 'public')->delete($media->path);
            $media->delete();
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function usages(Media $media): array
    {
        return MediaUsage::query()
            ->with('usable')
            ->where('media_id', $media->id)
            ->get()
            ->map(fn ($usage) => [
                'type' => class_basename((string) $usage->usable_type),
                'id' => $usage->usable_id,
                'field' => $usage->field,
            ])
            ->all();
    }

    public function recordUsage(Media|int $media, \Illuminate\Database\Eloquent\Model $owner, string $field = 'image'): void
    {
        MediaUsage::query()->updateOrCreate(
            [
                'media_id' => $media instanceof Media ? $media->id : $media,
                'usable_type' => $owner->getMorphClass(),
                'usable_id' => $owner->getKey(),
                'field' => $field,
            ],
            [],
        );
    }

    public function refreshUsageCounts(): void
    {
        Media::query()->each(function (Media $media) {
            $media->forceFill(['usage_count' => $media->usages()->count()])->save();
        });
    }

    /**
     * Latest assets as plain arrays for the media picker and the builder
     * canvas, which run in the browser and never touch Eloquent models.
     *
     * @return array<int, array{id:int, name:string, url:string, thumb:string, mime:string, width:?int, height:?int}>
     */
    public function recent(int $limit = 40): array
    {
        return Media::query()
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Media $media) => [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->url(),
                'thumb' => $media->url(),
                'mime' => (string) $media->mime_type,
                'width' => $media->width,
                'height' => $media->height,
            ])
            ->all();
    }

    /** @return array{0:int|null,1:int|null} */
    private function dimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getClientMimeType(), 'image/')) {
            return [null, null];
        }

        $size = @getimagesize($file->getRealPath());

        return $size ? [(int) $size[0], (int) $size[1]] : [null, null];
    }
}

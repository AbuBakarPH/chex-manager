<?php

namespace App\Services;

use App\Models\Admin\Media;

/**
 * Class MeidaService
 * @package App\Services
 */
class MeidaService
{
    public function store($img_id, $parent_id, $model)
    {
        if ($img_id) {
            $media = Media::where('id', $img_id)->first();
            if (!$media->mediable_id) {
                // $size = $this->getImageSize($media->path);

                $media->update([
                    'mediable_id' => $parent_id,
                    'mediable_type' => $model,
                    // 'size'  => $size
                ]);
            }
        }
    }

    public function storeMultiple(array $mediaIds, int $parent_id, string $model): void
    {
        if ($mediaIds) {
            $medias = Media::whereIn('id', $mediaIds)->get();
            foreach ($medias as $media) {
                if (!$media->mediable_id) {
            
                    $media->update([
                        'mediable_id' => $parent_id,
                        'mediable_type' => $model,
                    ]);
                }
            }
        }
    }


    public function update($img_id, $parent_id, $model)
    {
        if ($img_id) {
            $media = Media::where('id', '!=', $img_id)->where('mediable_type', $model)->where('mediable_id', $parent_id)->get();
            if (count($media)) {
                $media->each(function ($item) {
                    $item->delete();
                });
            }
            $media = Media::where('id', $img_id)->first();
            if (!$media->mediable_id) {

                $media->update([
                    'mediable_id' => $parent_id,
                    'mediable_type' => $model,
                ]);
            }
        } else if (is_null($img_id)) {
            Media::where('mediable_id', $parent_id)->where('mediable_type', $model)->delete();
        }
    }

    public function updateMultiple(array $mediaIds, int $parent_id, string $model): void
    {
        if ($mediaIds) {
            Media::whereNotIn('id', $mediaIds)
                ->where('mediable_type', $model)
                ->where('mediable_id', $parent_id)
                ->delete();

            Media::whereIn('id', $mediaIds)
                ->whereNull('mediable_id')
                ->update([
                    'mediable_id' => $parent_id,
                    'mediable_type' => $model,
                ]);
        }
    }
}

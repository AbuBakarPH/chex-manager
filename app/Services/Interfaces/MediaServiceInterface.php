<?php

namespace App\Services\Interfaces;

/**
 * Interface MediaServiceInterface
 * @package App\Services\Interfaces
 */
interface MediaServiceInterface
{
    public function store($request);

    public function s3Upload($file, $folder, $mediable);

    public function mulipleImageUploadAndStatusUpdate($images, $model, $config = 'storage/images/media');

    public function upload($file, $request);


}

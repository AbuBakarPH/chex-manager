<?php

namespace App\Services;

use App\Models\Admin\Media;
use App\Services\Interfaces\MediaServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Aws\S3\S3Client;

/**
 * Class MediaService
 * @package App\Services
 */
class MediaService extends BaseService implements MediaServiceInterface
{

    public function __construct(private Media $model)
    {
        parent::__construct($model);
    }

    public function store($request)
    {
        if ($request->hasFile('file')) {
            return $this->upload($request->file, $request);
        }

        if ($request->hasFile('files')) {
            $docs = [];
            foreach ($request->files as $row) {
                foreach ($row as $file) {
                    $docs[] = $this->upload($file, $request, $request->type);
                }
            }
            return $docs;
        }
    }

    public function s3Upload($file, $folder, $mediable)
    {
        $name = time() . '-' . $file->getClientOriginalName();
        $mime_type = $file->getClientMimeType();
        $path = Storage::disk('public')->put($folder, $file);
        $photo = Media::create([
            'name' => $name,
            'path' => $path,
            'user_id' => auth('sanctum')->user()->id,
            'status' => 0,
            'mediable_type' => $mediable,
            'mime_type' => $mime_type,
        ]);
        return $photo;
    }

    public function mulipleImageUploadAndStatusUpdate($images, $model, $config = 'storage/images/media')
    {
        foreach ($images as $file) {
            $image = $this->s3Upload(
                $file,
                $config,
                get_class($model)
            );
            if ($image) {
                $image['status'] = 1;
                $image['user_id'] = auth('sanctum')->user()->id;
                $model->images()->save($image);
            }
        }
    }

    public function upload($file, $request, $type = "photo")
    {

        $user = Auth::user();
        if ($user->company) {
            $path = '/' . $user->company->id . '-' . strtok($user->company->title, " ");
            $folder = 'public/' . $user->company->id . '-' . strtok($user->company->title, " ");
        } else {
            $path = '/images';
            $folder = 'public/images';
        }
        File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
        $uniq = Carbon::now()->format('Y-m-d-H-i-s-u');
        $name = $uniq . '.' . $file->getClientOriginalExtension();
        $name = str_replace(' ', '', $name);
        $mime_type = $file->getClientMimeType();
        $image_name = $file->getClientOriginalName();
        $path = Storage::disk('s3')->put($folder, $file);
        $size = filesize($file) / 1024;

        $photo = Media::create([
            'name' => $name,
            'path' => $path,
            'user_id' => auth()->user()->id,
            'status' => 0,
            'mime_type' => $mime_type,
            'size' => round($size, 2),
            'mediable_id' =>
            $request->mediable_id == 'App\\Models\\User'
                ? auth()->user()->id
                : $request->mediable_id,
            'mediable_type' => $request->mediable_type,
            'type' => $type,
        ]);

        // $this->optimized($photo) ;
        return $photo;
    }

    public function profileUpload($file, $request, $type = "photo")
    {
        $user = Auth::user();
        if ($user->company) {
            $path = '/' . $user->company->id . '-' . strtok($user->company->title, " ");
            $folder = 'public/' . $user->company->id . '-' . strtok($user->company->title, " ");
        } else {
            $path = '/images';
            $folder = 'public/images';
        }

        File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
        $uniq = Carbon::now()->format('Y-m-d-H-i-s-u');
        $name = $uniq . '.' . $file->getClientOriginalExtension();
        $name = str_replace(' ', '', $name);
        $mime_type = $file->getClientMimeType();
        $path = Storage::disk('s3')->put($folder, $file);
        $size = filesize($file) / 1024;

        $photo = Media::create([
            'name' => $name,
            'path' => $path,
            'user_id' => auth()->user()->id,
            'status' => 0,
            'mime_type' => $mime_type,
            'size' => round($size, 2),
            'mediable_id' => auth()->user()->id,
            'mediable_type' => "App\\Models\\User",
            'type' => $type,
        ]);

        return $photo;
    }

    private function optimized($photo)
    {
        $manager = new ImageManager(Driver::class);
        $image = $manager->read($photo->path);
        $image->scale(100, 100);
        $image->save($photo->path);
    }
}

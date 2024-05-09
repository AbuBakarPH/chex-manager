<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\MediaServiceInterface;
use App\Services\MediaService;
use Illuminate\Http\Request;
use App\Http\Requests\MediaRequest;
use App\Models\Admin\Media;
use Carbon\Carbon;

class MediaController extends Controller
{
    public function __construct(private MediaServiceInterface $service)
    {

    }
    public function index(Request $request)
    {
        return $this->response(
            'Media have been retrieved!',
            $this->service->index($request),
        );
    }

    public function store(MediaRequest $request)
    {
        return $this->response(
            'Media have been retrieved!',
            $this->service->store($request),
        );
    }

    public function destroy(\App\Models\Media $media)
    {
        return $this->response('Media deleted!', $media, 200);
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request;

        // Perform the deletion
        Media::whereIn('id', $ids)->delete();
        return $this->response('Images deleted successfully!', 200);
    }

    public function getImage(Request $request)
    {
        try {
            $mediable_id = $request->input('mediable_id');
            $type = $request->input('type');

            $image = Media::where('mediable_id', $mediable_id)
                ->where('type', $type)
                ->first();

            if (!$image) {
                return response()->json(['error' => 'Image not found'], 404);
            }

            return response()->json([
                'message' => "Image fetched successfully",
                'data' => $image,
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}

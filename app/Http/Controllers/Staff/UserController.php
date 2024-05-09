<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Media;
use App\Services\Interfaces\UserInterface;
use App\Services\MediaService;

class UserController extends Controller
{
    public function __construct(private UserInterface $service, private MediaService $mediaService)
    {
    }

    public function postProfilePicture(Request $request)
    {
        if ($request->hasFile('file')) {
            return $this->response('User listing', $this->mediaService->profileUpload($request->file, $request), 200);
        }
        return $this->response('File not found', null, 404);
    }

    public function deleteProfilePicture()
    {
        $userId = auth()->id();

        $media = Media::where('mediable_id', $userId)
            ->where('mediable_type', 'App\Models\User')
            ->delete();

        return response()->noContent();
    }
}

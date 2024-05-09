<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Admin\Notification;
use App\Services\GeneralService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private GeneralService $generalService, private NotificationService $notificaitonService)
    {
    }

    public function index(Request $request)
    {
        return $this->response('Notifications', $this->notificaitonService->notificationList($request), 200);
        $where =  [
            ['key' => 'notifiable_id', 'operator' => '=', 'value' => auth()->user()->id],
        ];

        $data = Notification::where('notifiable_id', auth()->user()->id);
        // $data = $this->generalService->handleWhere($data, $where);
        if ($request['perpage'] == 'all') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
        return $this->response('Notification listing', $data, 200);
    }

    public function update($id)
    {
        Notification::where('id', $id)->update(['read_at' => date('Y-m-d H:i:s')]);
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Notification::where('id', $id)->delete();
        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\ManagerControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Notification;
use App\Services\Interfaces\NotificationInterface;
use Carbon\Carbon;


class NotificationController extends Controller
{
    public function __construct(private NotificationInterface $service)
    {
    }

    public function index(Request $request)
    {
        return $this->response('Notification listing', $this->service->index($request), 200);
    }


    public function notificationList(Request $request)
    {
        return $this->response('Notification listing', $this->service->notificationList($request), 200);
    }

    public function updateNotificationStatus(Request $request)
    {
        Notification::whereIn('id', $request['notification_ids'])->update(['read_at' => date('Y-m-d H:i:s')]);
        return $this->response('Status Updated', null, 201);
    }

    public function readAt($id)
    {
        Notification::where('id', $id)->update(['read_at' => date('Y-m-d H:i:s')]);
        return $this->response('Status Updated', null, 201);
    }
}

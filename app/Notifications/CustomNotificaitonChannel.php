<?php

namespace App\Notifications;


use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CustomNotificaitonChannel
{
    public function send($notifiable, Notification $notification)
    {

        $data = $notification->toDatabase($notifiable);
        if (!class_exists('App\\Models\\Admin\\'.$data['type'], false)) {
            $data['typeable'] = "App\\Models\\".$data['type'];
        }else{
            $data['typeable'] = "App\\Models\\Admin\\".$data['type']; 
        }

        $notification = $notifiable->routeNotificationFor('database')->create([
            'id' => $notification->id,
            'data' => $data['data'],
            'type_id' => $data['type_id'] ,
            'type_action' => $data['type_action'],
            'type' => $data['typeable'],
            'company_id' =>$data['company_id'],
        ]);
        broadcast(new \App\Events\SendNotification($notification));
        return $notification ;
    }
    
}
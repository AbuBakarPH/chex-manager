<?php

namespace App\Notifications;

use BeyondCode\LaravelWebSockets\WebSockets\Channels\Channel;
use Carbon\Traits\Serialization;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class GeneralNotification extends Notification implements ShouldBroadcastNow 
{
    use Queueable, Dispatchable, InteractsWithSockets, Serialization ;

    /**
     * Create a new notification instance.
     */

    protected $model;
    protected $model_name;
    protected $method;
    protected $message;
    protected $subject;
    protected $body;


    public function __construct($model, $model_name, $method, $message, $subject, $body)
    {
        $this->model        = $model;      // Complete Model Obj
        $this->model_name   = $model_name; // User, Team, Checklist or Whatever
        $this->method       = $method;     // Post, Update, Publish or Whatever
        $this->message      = $message;   // Add in the Team, Publish Checklist, or Whatever

        // Mail Content
        $this->subject          = $subject;      // Complete Model Obj
        $this->body             = $body;      // Complete Model body

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [CustomNotificaitonChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->view('emails.generic_email', ['name' => $notifiable->name, 'body' => $this->body, 'logo'   => ($notifiable->company && $notifiable->company->photo != null) ? $notifiable->company->photo->path : 'logo.png'])
            ->action('Login', url('/'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {  
        return [
            'data' => $this->message,
            'type_id' => $this->model->id,
            'type_action' => $this->method,
            'type' => $this->model_name,
            'company_id' => $notifiable->company_id ,
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }
    
    // public function broadcastOn()
    // {
    //     return new Channel("notifications");
    // }
}

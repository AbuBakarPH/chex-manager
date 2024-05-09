<?php

namespace App\Services\Interfaces;

/**
 * Interface FormFieldServiceInterface
 * @package App\Services\Interfaces
 */
interface NotificationInterface
{
    public function index($request);
    public function notificationList($request);
    
}

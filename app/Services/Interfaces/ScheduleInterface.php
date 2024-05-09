<?php

namespace App\Services\Interfaces;

/**
 * Interface FormFieldServiceInterface
 * @package App\Services\Interfaces
 */
interface ScheduleInterface
{
    public function index($request);
    public function approversScheduleList($request);
}

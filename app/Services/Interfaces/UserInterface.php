<?php

namespace App\Services\Interfaces;

/**
 * Interface FormFieldServiceInterface
 * @package App\Services\Interfaces
 */
interface UserInterface
{
    public function index($request);
    public function update($request, $model);
    public function updatePassword($request, $model);
    public function staffList($request);
    
}

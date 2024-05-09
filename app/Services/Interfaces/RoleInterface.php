<?php

namespace App\Services\Interfaces;

/**
 * Interface FormFieldServiceInterface
 * @package App\Services\Interfaces
 */
interface RoleInterface
{
    public function store($request);
    
    public function update($request, $model);
}

<?php

namespace App\Services\Interfaces;

/**
 * Interface FormFieldServiceInterface
 * @package App\Services\Interfaces
 */
interface TeamInterface
{
    public function index($request);
    public function store($request);
    public function update($request, $model);
}

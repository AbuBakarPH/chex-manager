<?php

namespace App\Services\Interfaces;

/**
 * Interface CategoryServiceInterface
 * @package App\Services\Interfaces
 */
interface CompanyServiceInterface
{

    public function index($request);
    public function store($request);

    public function update($request, $model);
}

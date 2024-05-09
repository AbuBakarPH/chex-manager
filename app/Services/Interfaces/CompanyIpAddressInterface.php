<?php

namespace App\Services\Interfaces;

/**
 * Interface CategoryServiceInterface
 * @package App\Services\Interfaces
 */
interface CompanyIpAddressInterface
{

    public function store($request);

    public function update($request, $model);
}

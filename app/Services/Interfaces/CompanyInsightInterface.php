<?php

namespace App\Services\Interfaces;

/**
 * Interface FormFieldServiceInterface
 * @package App\Services\Interfaces
 */
interface CompanyInsightInterface
{
    public function index();
    public function show();
    public function update($request);
    public function store($request);
    public function destroy();
}

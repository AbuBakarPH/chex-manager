<?php

namespace App\Services\Interfaces;

/**
 * Interface FormFieldServiceInterface
 * @package App\Services\Interfaces
 */
interface MythBusterInterface
{
    public function index($request);
    public function store($request);
}

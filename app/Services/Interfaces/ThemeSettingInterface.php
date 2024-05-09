<?php

namespace App\Services\Interfaces;

use App\Models\ThemeSetting;

/**
 * Interface FormFieldServiceInterface
 * @package App\Services\Interfaces
 */
interface ThemeSettingInterface
{
    public function index($request);
    public function store($request);
}

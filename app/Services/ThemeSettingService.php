<?php

namespace App\Services;

use App\Models\ThemeSetting;
use App\Services\Interfaces\ThemeSettingInterface;

/**
 * Class ThemeSettingService
 * @package App\Services
 */
class ThemeSettingService implements ThemeSettingInterface
{

    public function __construct(private ThemeSetting $model, private GeneralService $generalService, private MeidaService $meidaService)
    {
        $this->generalService = $generalService;
    }

    public function index($request)
    {
        $data = ThemeSetting::with('photo')->whereCompanyId(auth()->user()->company->id)->first();
        return $data;
    }

    public function store($validated)
    {
        $data = ThemeSetting::updateOrCreate([
            'company_id'   => auth()->user()->company_id,
        ], $validated);
        $validated['logo_id'] = (array_key_exists('logo_id', $validated)) ? $validated['logo_id'] : null ; 
        // if(isset($validated['logo_id'])){
            $this->meidaService->update($validated['logo_id'], $data->id, "App\\Models\\ThemeSetting");
        // }

        return $data->load('photo');
    }
}

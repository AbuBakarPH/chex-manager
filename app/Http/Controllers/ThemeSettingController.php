<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\ThemeSettingInterface;
use App\Http\Requests\StoreThemeSettingRequest;
use Illuminate\Http\Request;

class ThemeSettingController extends Controller
{
    public function __construct(private ThemeSettingInterface $service)
    {   
    }
    
    public function index(Request $request)
    {
        return $this->response('theme setting', $this->service->index($request, 'checkListSectionQuestions'), 200);
    }
    
    public function store(StoreThemeSettingRequest $request)
    {
        $validated = $request->validated();
        return $this->response('theme setting saved', $this->service->store($validated), 200);
    }
}

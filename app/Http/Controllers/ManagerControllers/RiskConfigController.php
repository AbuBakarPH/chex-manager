<?php

namespace App\Http\Controllers\ManagerControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\MyCheckListRequest;
use App\Services\RiskConfigService;
use Illuminate\Http\Request;

class RiskConfigController extends Controller
{
    public function __construct(private RiskConfigService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->response('Risk Configs listing', $this->service->index($request), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MyCheckListRequest $request)
    {
        $data = $request->validated();
        return $this->response(
            'Risk config created successfully',
            $this->service->store($data),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StorePackagePlanRequest;
use App\Models\PackagePlan;
use App\Services\Interfaces\PackagePlanInterface;


class PackagePlanController extends Controller
{   
    
    public function __construct(private PackagePlanInterface $service)
    {
    }
    
    public  function index(Request $request)
    {
        return $this->response('Package plan listing', $this->service->index($request), 200);
    }   
    
    public function store(StorePackagePlanRequest $request)
    {
        $validated = $request->validated();
         return $this->response(
            'Package plan created successfully',
            $this->service->store($validated),
        );
    }
    
    
    public function show($id) {
        $package = PackagePlan::whereId($id)->first();
         return $this->response(
            'Package plan Retrived successfully', $package
        );
        
    }
    
    
    public function update(StorePackagePlanRequest $request, $id)
    {
        $validated = $request->validated();
        $package = PackagePlan::findOrfail($id);
        return $this->response(
            'Package plan updated successfully',
            $this->service->update($validated, $package),
        );
    }

}

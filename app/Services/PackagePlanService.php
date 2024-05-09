<?php

namespace App\Services;
use App\Services\Interfaces\PackagePlanInterface;
use App\Models\PackagePlan;


/**
 * Class PackagePlanService
 * @package App\Services
 */
class PackagePlanService implements PackagePlanInterface
{
    public function __construct(private PackagePlan $model, private GeneralService $generalService)
    {
        // parent::__construct($model);
    } 
    
    public function index($request)
    {
        $data = PackagePlan::with('company_subscribe_plan');
        
        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }
    public function store($request)
    {
       $model =  PackagePlan::create($request);
        return $model  ;
    }
    
    
    public function update($request, $model)
    {
        $model->update($request);  
        return $model  ;
        
    }
}

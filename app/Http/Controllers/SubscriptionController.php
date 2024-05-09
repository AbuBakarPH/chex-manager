<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Models\PackagePlan;
use Illuminate\Http\Request;
use App\Services\GeneralService;
use App\Models\CompanyPackagePlan;
use Illuminate\Support\Facades\Auth;
use App\Services\SubscriptionService;
use App\Http\Requests\StoreCompanyPlanRequest;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $service, private GeneralService $generalService)
    {
        //
    }

    public function getSubscribedPlanDetail()
    {
       $data = $this->service->getSubscribedPlanDetail();
        return  $data;
    }

    public function store(StoreCompanyPlanRequest $request)
    {
        $validated = $request->validated();
        return $this->response('Subsription created successfully', $this->service->store($validated), 200);
    }
    
    public function subscribedPlan(Request $request ,$id)
    {
        return $this->response('Category listing', $this->service->subscribedPlanCompanies($request, $id) , 200);
    }

}

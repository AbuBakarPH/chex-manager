<?php

namespace App\Http\Controllers\AdminControllers;

use Illuminate\Http\Request;
use App\Services\GeneralService;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\CompanyIpAddress;
use App\Http\Requests\StoreCompanyIpRequest;
use App\Http\Requests\UpdateCompanyIpRequest;
use App\Services\Interfaces\CompanyIpAddressInterface;
use App\Http\Controllers\AdminControllers\Auth\BaseController as BaseController;


class CompanyIpController extends BaseController
{   
    public function __construct(private CompanyIpAddressInterface $service,  private GeneralService $generalService)
    {
    }

    public function index(Request $request)
    {
        // if (Auth::user()->roles->pluck('name')[0] == 'Super Admin') {
        //     $where = $request['company_id'] ;
        // } else if(Auth::user()->roles->pluck('name')[0] == 'Manager') {
        //     $where = Auth::user()->company_id ;
        // }
        return $this->response('Company Ip listing', $this->service->index($request), 200);
    }
    
    public function store(StoreCompanyIpRequest $request)
    {
        // if($this->generalService->hasLimit('ip_address')) {
            return $this->response(
                'Company Ip created successfully',
                $this->service->store($request),
            );
        // }else
        // {
        //     return $this->response('Your Limit Exceeded To Create Ip Address', NULL, 403);
        // }
    }
    
    public function show($id)
    {
        $companyIp = CompanyIpAddress::findOrfail($id);  
        return $this->response('Company ip detail', $companyIp);
        
    }
    
    public function update(UpdateCompanyIpRequest $request, $id)
    {
        
        $company = CompanyIpAddress::findOrfail($id);  
        return $this->response(
            'Company Ip updated successfully',
            $this->service->update($request, $company)
        );  
    }
    
    public function destroy($id)
    {
        $companyIp = CompanyIpAddress::findOrfail($id);  
        return $this->response(
            'Company Ip deleted successfully',
            $this->service->delete($companyIp)
        );  
    }
    
    
    
    
    
}

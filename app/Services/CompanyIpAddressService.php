<?php

namespace App\Services;

use App\Services\Interfaces\CompanyIpAddressInterface;
use App\Models\Admin\CompanyIpAddress;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

/**
 * Class CompanyIpAddressService
 * @package App\Services
 */
class CompanyIpAddressService implements CompanyIpAddressInterface  
{
    public function __construct(private CompanyIpAddress $model, private GeneralService $generalService)
    {
        // parent::__construct($model);
        $this->generalService = $generalService  ;
    } 
    
    public function index($request)
    {
        if (Auth::user()->roles->pluck('name')[0] == 'Super Admin') {
            $where = $request['company_id'] ;
        } else if(Auth::user()->roles->pluck('name')[0] == 'Manager') {
            $where = Auth::user()->company_id ;
        }
        
        $where = [
            [
                'key' => 'company_id' , 'operator' => '=' , 'value' => $where
            ]
            ];

        $data = CompanyIpAddress::query();
        $data = $this->generalService->handleWhere($data, $where);
        $data = $this->generalService->handleSearch($request['searchText'], $data, ['ip_address']);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }
 
    public function store($request)
    {
        $validated = $request->validated();
        $company = $this->model->create($validated);
        $company->refresh();
        // Notification::send($company->company->manager, new GeneralNotification($company, 'CompanyIp', 'POST', "Company Ip Successfully Created",'Company IP Notification','Company Ip Updated'));
        return $company ;
    }
    
    public function update($request, $model)
    {
        $validated = $request->validated();
        $model->update($validated);
        // Notification::send($model->company->manager, new GeneralNotification($model, 'CompanyIp', 'Update', "Company Ip Successfully Updated",'Company IP Notification','Company Ip Updated'));
        return $model->refresh() ;
    }
    
}
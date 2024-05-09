<?php

namespace App\Http\Controllers\AdminControllers;

use App\Services\Interfaces\CompanyServiceInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminControllers\Auth\BaseController as BaseController;
use App\Models\Admin\Company;
use App\Models\Admin\CompanyIpAddress;
use App\Models\User;
use App\Http\Requests\CompanyRequest;
class CompanyController extends BaseController
{
    public function __construct(private CompanyServiceInterface $service)
    {
        //
    }
    public function index(Request $request){
        return $this->response(
            'Company listing',
            $this->service->index($request),
            200
        );
    }
    public function store(CompanyRequest  $request){
        return $this->response(
            'Company created successfully',
            $this->service->store($request),
        );
    }
    public function show($id){

        $company = Company::with(['users'=> function($q){
            $q->with('photo');
        },'photo','ip_address','staff','manager.photo'])->where('id',$id)->first();
        return $this->response('Company detail', $company);
    }
    public function update(CompanyRequest $request, $id){
        $company = Company::find($id);
        if (!is_null($company)) {
            return $this->response(
                'Company updated successfully',
                $this->service->update($request, $company)
            );
        } else {
            return $this->error("company not found");
        }
    }


    public function destroy($id){

        $company = Company::find($id);
        $company->delete();

        $companyUser = User::where('company_id',$id)->first();
        $companyUser->delete();
        return response()->noContent();
    }
}

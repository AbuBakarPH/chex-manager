<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Models\CompanyPlanable;
use App\Models\CompanyPackagePlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PlanableObserver
{
    public function created(Model $model)
    {
        if (Auth::user()->roles->pluck('name')[0] == 'Manager') {
            $className = get_class($model);
            $currentCompanyPackagePlan = CompanyPackagePlan::where('company_id', auth()->user()->company_id)
                ->whereIsActive(true)->where('expire_date', '>=', Carbon::today())->latest()->first();
            if ($currentCompanyPackagePlan) {
                $data['company_package_plan_id'] = $currentCompanyPackagePlan->id;
                $data['planable_id'] = $model->id;
                $data['planable_type'] = $className;
                CompanyPlanable::create($data);
            }
        }
    }

    public function updated(Model $model)
    {
    }
}

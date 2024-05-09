<?php

namespace App\Models;

use App\Models\Admin\Company;
use App\Models\CompanyPlanable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyPackagePlan extends Model
{
    use HasFactory;
    
    protected $table = 'company_package_plans';    
    
    protected $fillable = ['package_plan_id', 'company_id','is_active', 'subscribe_date', 'expire_date'];


    public function companyPlanables()
    {
        return $this->morphMany(CompanyPlanable::class);
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}

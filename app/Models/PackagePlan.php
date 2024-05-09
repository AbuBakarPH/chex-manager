<?php

namespace App\Models;

use App\Models\Admin\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagePlan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'allow_checklist',
        'allow_checklist_config',
        'allow_documents',
        'allow_risk',
        'allow_risk_config',
        'allow_active_ip',
        'allow_users',
        'allow_teams',
        'price',
        'plan_type',
        'status'
    ];
    
    
    public function company_subscribe_plan()
    {
        return $this->belongsToMany(Company::class, 'company_package_plans');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPlanable extends Model
{
    use HasFactory;
    protected $fillable = ['company_package_plan_id', 'planable_id','planable_type'];
}

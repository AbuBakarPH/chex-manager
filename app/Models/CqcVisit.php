<?php

namespace App\Models;

use App\Models\User;
use App\Models\Admin\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\VisitType;


class CqcVisit extends Model
{
    use HasFactory;
    
    protected $fillable = ['title', 'description', 'visit_date', 'company_id', 'created_by','type'];
        
    // protected $casts = [
    //     'type' => VisitType::class,
    // ];
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    

}

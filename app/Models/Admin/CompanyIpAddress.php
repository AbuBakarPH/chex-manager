<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyIpAddress extends Model
{
    use HasFactory;
    
    protected $fillable = ['company_id','ip_address','is_active'];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

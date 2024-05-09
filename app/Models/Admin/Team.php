<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Team extends Model
{
    use HasFactory;
    
    protected $fillable = ['title','is_active','company_id','users_id','start_time','end_time'];
    
    public function users()
    {
      return $this->belongsToMany(User::class, 'team_users');
    }
    
    public function company()
    {
      return $this->belongsTo(Company::class,'company_id');
    }
    
}

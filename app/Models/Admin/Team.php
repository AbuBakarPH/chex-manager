<?php

namespace App\Models\Admin;

use App\Models\OrganizationalRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Team extends Model
{
  use HasFactory;

  protected $fillable = ['title', 'is_active', 'company_id', 'users_id', 'start_time', 'end_time', 'org_role_id', 'category_id'];

  public function users()
  {
    return $this->belongsToMany(User::class, 'team_users');
  }

  public function company()
  {
    return $this->belongsTo(Company::class, 'company_id');
  }

  public function role()
  {
    return $this->belongsTo(OrganizationalRole::class, 'org_role_id');
  }

  public function category()
  {
    return $this->belongsTo(Category::class, 'category_id');
  }
}

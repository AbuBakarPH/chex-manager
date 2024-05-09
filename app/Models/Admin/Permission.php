<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    
    
    protected $guard_name = 'web';

    protected $fillable = ['name', 'guard_name'];
}

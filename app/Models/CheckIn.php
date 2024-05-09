<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'company_id', 'type', 'hr_time', 'description','created_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function scopeToday(Builder $query)
    {
        return $query->whereDate('created_at', today());
    }
}

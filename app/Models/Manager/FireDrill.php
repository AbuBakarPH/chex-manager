<?php

namespace App\Models\Manager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FireDrill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['note', 'user_id'];
}

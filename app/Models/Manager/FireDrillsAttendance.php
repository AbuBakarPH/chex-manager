<?php

namespace App\Models\Manager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FireDrillsAttendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['attended_by', 'attendee_id', 'attendee_type', 'fire_drill_id'];

    public function setAttendeeTypeAttribute($value)
    {
        // Convert type value to appropriate model namespace
        $modelNamespace = ($value === 'user') ? 'App\\Models\\User' : 'App\\Models\\Manager\\Employee';

        $this->attributes['attendee_type'] = $modelNamespace;
    }
}

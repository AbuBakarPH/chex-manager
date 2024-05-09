<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class TaskSection extends Model
{
    use HasFactory;

    protected $table    = 'task_sections';

    protected $fillable = ['task_id', 'title', 'slug', 'status', 'description', 'notes'];

    protected $dates    = ['deleted_at'];

    public function questions()
    {
        $role = auth()->user()->getRoleNames()[0];
        if ($role == 'Staff') {
            return $this->hasMany(SectionQuestion::class, 'section_id')->where('status', 'active')->whereNull('parent_id');
        }

        return $this->hasMany(SectionQuestion::class, 'section_id')->whereNull('parent_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}

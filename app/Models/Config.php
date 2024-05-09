<?php

namespace App\Models;

use App\Models\Schedule;
use App\Models\Admin\Company;
use App\Models\ConfigAssignee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Config extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'company_id',
        'task_id',
        'repeat',
        'repeat_count',
        'repeat_start_dd',
        'repeat_due_count',
        'user_id',
        'team_id',
        'is_active',
        'exceptional_days',
        'exceptional_dates',
    ];

    public function setExceptionalDatesAttribute($value)
    {
        $this->attributes['exceptional_dates'] = $value ? json_encode($value) : NULL;
    }

    public function getExceptionalDatesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setExceptionalDaysAttribute($value)
    {
        $this->attributes['exceptional_days'] = $value ? json_encode($value) : NULL;
    }

    public function getExceptionalDaysAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function checklist_assignee()
    {
        // return $this->hasMany(CheckListAssignee::class, 'checklist_config_id', 'id');
    }

    public function staff()
    {
        return $this->belongsToMany(User::class, 'config_assignees', 'config_id', 'user_id')
            ->wherePivot('type', 'staff')
            ->withPivot('id', 'type');
    }

    // For saving and updating staff
    public function staffPivot()
    {
        return $this->hasMany(ConfigAssignee::class)->where('type', 'staff');
    }

    public function approvers()
    {
        return $this->belongsToMany(User::class, 'config_assignees', 'config_id', 'user_id')
            ->wherePivot('type', 'approver')
            ->withPivot('id', 'type');
    }

    // For saving and updating approvers
    public function approversPivot()
    {
        return $this->hasMany(ConfigAssignee::class)->where('type', 'approver');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'config_assignees', 'config_id', 'user_id')
            ->withPivot('id', 'type');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function previous_checklist_daily()
    {
        return $this->hasMany(Schedule::class, 'config_id')->where('created_at', '<=', date('Y-m-d') . ' 23:59:00');
    }

    public function daily_checklists()
    {
        // return $this->hasMany(DailyChecklist::class, 'checklist_config_id', 'id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Admin\Team::class);
    }

    public function configs_assignee()
    {
        return $this->belongsToMany(Config::class, 'config_assignees', 'config_id')
        ->withPivot('id', 'type');
    }
}

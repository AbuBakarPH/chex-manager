<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Config;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['config_id', 'task_id', 'company_id', 'status', 'approved_by'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function configuration()
    {
        return $this->hasOne(Config::class, 'id', 'config_id');
    }

    public function checklist_config()
    {
        return $this->belongsTo(Config::class, 'config_id');
    }

    public function answers()
    {
        return $this->hasMany(ScheduleAnswer::class);
    }
}

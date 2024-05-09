<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigAssignee extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'config_id', 'type'];

    public function config()
    {
        return $this->belongsTo(Config::class);
    }

    public function daily_checklists()
    {
        // return $this->hasMany(DailyChecklist::class,'checklist_config_id','checklist_config_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'name', 'email');
    }
}

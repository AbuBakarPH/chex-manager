<?php

namespace App\Models;

use App\Models\Admin\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleAnswer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "user_id",
        "question_id",
        "pivot_field_id",
        "schedule_id",
        "answer",
        "reason",
    ];

    public function photo()
    {
        return $this->hasOne(Media::class, 'mediable_id')->where('mediable_type', 'App\\Models\\ScheduleAnswer');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'name', 'email');
    }

    public function question()
    {
        return $this->belongsTo(SectionQuestion::class, 'question_id', 'id')->select('id', 'name', 'email');
    }

    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}

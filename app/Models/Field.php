<?php

namespace App\Models;

use App\Models\ScheduleAnswer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Field extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'fields';
    protected $fillable = ['name', 'label', 'value', 'placeholder', 'file_type', 'type', 'status', 'field_values'];
    protected $dates = ['deleted_at'];

    public function setFieldValuesAttribute($value)
    {
        $this->attributes['field_values'] = $value ? json_encode($value) : [];
    }

    public function getFieldValuesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }


    public function answer()
    {
        return $this->hasOne(ScheduleAnswer::class, 'id', 'question_id')->withDefault(['answer' => '']);
    }
    
    public function sectionQuestion()
    {
        return $this->belongsToMany(SectionQuestion::class, 'field_section_questions', 'section_question_id', 'field_id')
        ->wherePivot('status', 'active')
        ->withPivot('id', 'section_question_id', 'field_id', 'required', 'status', 'sort_no');
    }
}

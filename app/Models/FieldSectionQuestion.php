<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldSectionQuestion extends Model
{
    use HasFactory, SoftDeletes;
    protected $table    = 'field_section_questions';
    protected $fillable = ['field_id', 'section_question_id', 'required', 'sort_no', 'status'];
    protected $dates    = ['deleted_at'];

    // protected $casts = [
    //     'required' => 'integer',
    // ];

    public function checkListSection()
    {
        // return $this->belongsTo(CheckListSection::class, 'check_list_section_question_id');
    }

    public function answer()
    {
        // return $this->hasOne(DailyChecklistAnswer::class, 'question_form_field_id', 'id')->withDefault(['answer' => '']);
    }

    // This is for Sub Question's relation with Checkbox Formfield
    // public function fields()
    // {
    //     return $this->belongsTo(Field::class, 'field_id');
    // }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SectionQuestion extends Model
{
    use HasFactory, SoftDeletes;
    protected $table     = 'section_questions';
    protected $fillable = ['section_id', 'parent_id', 'title', 'status', 'sort_no', 'guidance', 'pinned'];
    protected $dates    = ['deleted_at'];

    public function fields()
    {
        return $this->belongsToMany(Field::class, 'field_section_questions', 'section_question_id', 'field_id')
            ->wherePivot('status', 'active')
            ->withPivot('id', 'section_question_id', 'field_id', 'required', 'status', 'sort_no');
    }

    public function sub_questions()
    {
        return $this->hasMany(SectionQuestion::class, 'parent_id', 'id')->where('status', 'active');
    }

    public function answer()
    {
        return $this->hasOne(ScheduleAnswer::class, 'id', 'question_id')->withDefault(['answer' => '']);
    }

    public function risk()
    {
        return $this->hasOne(QuestionRisk::class)->where('company_id', Auth::user()->company_id)->whereIn('status', ['draft', 'in_progress', 'completed']);
    }

    public function completed_risks()
    {
        return $this->hasMany(QuestionRisk::class)->where('company_id', Auth::user()->company_id)->where('status', 'completed');
    }

    public function risks()
    {
        return $this->hasMany(QuestionRisk::class)->where('company_id', Auth::user()->company_id);
    }

    public function questionFormFields()
    {
        // return $this->hasMany(QuestionFormField::class, 'check_list_section_question_id');
    }

    public function question_form_fields()
    {
        // return $this->belongsToMany(
        //     Formfield::class,
        //     QuestionFormField::class,
        //     'check_list_section_question_id',
        //     'form_field_id',
        //     'id'
        // );
    }

    public function ques_form_fields()
    {
        // return $this->hasManyThrough(QuestionFormField::class, DailyChecklistAnswer::class, 'check_list_section_question_id', 'question_form_field_id', 'form_field_id', 'id');
    }

    public function question_answers()
    {
        // return $this->hasMany(
        //     DailyChecklistAnswer::class,
        //     'question_id'
        // );
    }

    public function section()
    {
        return $this->belongsTo(TaskSection::class, 'section_id');
    }
}

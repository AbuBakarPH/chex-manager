<?php

namespace App\Models;

use App\Http\Controllers\ManagerControllers\RiskController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionRisk extends Model
{
    use HasFactory, SoftDeletes;
    protected $table    = 'question_risks';
    protected $fillable = ['section_question_id', 'company_id', 'status', 'priority', 'due_date'];
    protected $dates    = ['deleted_at'];

    public function question()
    {
        return $this->belongsTo(SectionQuestion::class, 'section_question_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'risk_assignees')->withPivot('id', 'question_risk_id', 'user_id');
    }

    // For saving and updating assignees
    public function assigneesPivot()
    {
        return $this->hasMany(RiskAssignee::class);
    }

    public function conversations()
    {
        return $this->hasMany(RiskConversation::class);
    }

    public function reason()
    {
        return $this->hasOne(RiskConversation::class)->where('reason', 1)->orderBy('created_at', 'desc');
    }
}

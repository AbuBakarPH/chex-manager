<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAssignee extends Model
{
    use HasFactory;
    protected $table    = 'risk_assignees';
    protected $fillable = ['question_risk_id', 'user_id'];
    protected $dates    = ['deleted_at'];

    public function risk()
    {
        return $this->belongsTo(QuestionRisk::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

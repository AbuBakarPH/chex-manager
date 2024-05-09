<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin\Media;

class RiskConversation extends Model
{
    use HasFactory, SoftDeletes;
    protected $table    = 'risk_conversations';
    protected $fillable = ['question_risk_id', 'user_id', 'description', 'reason'];
    protected $dates    = ['deleted_at'];

    public function risk()
    {
        return $this->belongsTo(QuestionRisk::class)->whereIn('status', ['draft', 'in_progress']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photo()
    {
        return $this->morphOne(Media::class, 'mediable')->latestOfMany();
    }
}

<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['type_id', 'type_action', 'read_at','company_id'];

    protected $casts = [
        'id' => 'string'
    ];


    public function typeable()
    {
        return $this->morphTo('typeable', 'type', 'type_id');
    }

    public function notifiable()
    {
        return $this->morphTo('notifiable', 'notifiable_type', 'notifiable_id');
    }

    public function scopeUnreadNotification($query)
    {
        return $query->where('company_id', auth()->user()->company_id)->whereNull('read_at');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', date('Y-m-d'));
    }

    public function scopeYesterday($query)
    {
        return $query->whereDate('created_at', Carbon::yesterday());
    }

    public function scopeWeek($query)
    {
        return $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    }

    public function scopeMonth($query)
    {
        return $query->whereMonth('created_at', date('m'));
    }
}

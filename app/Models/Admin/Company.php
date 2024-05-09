<?php

namespace App\Models\Admin;

use App\Models\User;
use App\Models\MythBuster;
use App\Models\PackagePlan;
use App\Models\Admin\CompanyIpAddress;
use App\Models\ThemeSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PhpParser\Node\Expr\Cast;

class Company extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['title', 'shifts', 'address', 'email', 'phone', 'package_plan_id', 'allow_notification', 'allow_email',
        'start_time',
        'end_time'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'allow_notification' => 'integer',
        'allow_email' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function photo()
    {
        return $this->morphOne(Media::class, 'mediable')->latestOfMany();
    }

    public function ip_address()
    {
        return $this->hasMany(CompanyIpAddress::class);
    }

    public function staff()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function manager()
    {
        return $this->hasOne(User::class)->role('Manager');
    }

    public function managers()
    {
        return $this->hasMany(User::class)->role('Manager');
    }

    public function packagePlan()
    {
        return $this->belongsTo(PackagePlan::class);
    }

    public function subscribe_plans()
    {
        return $this->belongsToMany(PackagePlan::class, 'company_package_plans')
            ->where('company_id', auth()->user()->company_id)
            ->withPivot('id', 'is_active');
    }

    /**
     * Get all of the mythBusters for the task.
     */
    public function mythBusters()
    {
        return $this->morphToMany(MythBuster::class, 'mythbusterable');
    }

    public function theme_setting()
    {
        return $this->hasOne(ThemeSetting::class);
    }
}

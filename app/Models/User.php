<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\CheckIn;
use App\Models\Admin\Media;
use App\Models\Admin\Company;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Admin\LoginHistory;
use App\Models\Admin\Team;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'cnic',
        'phone',
        'address',
        'company_id',
        'fake_name',
        'otp',
        'otp_expiry',
        'category_id',          // For Admin this Column is nullable
        'sub_category_id',      // For Admin this Column is nullable
        'device_token',
        'org_role',
        'allow_notification',
        'status',
        'avatar_id',
        'otp_count',
    ];
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'sub_category_id',
        'category_id',
        "deleted_at",
        "updated_at",
        "email_verified_at"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'category_id' => 'integer',
        'sub_category_id' => 'integer',
        'status' => 'integer',
    ];

    // Protected variable to specify the guarded attributes
    protected $guarded = ['password', 'email'];

    public function photo()
    {
        return $this->morphOne(Media::class, 'mediable')->latestOfMany();
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_users');
    }

    public function login_history()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function checkins()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopeSetStatus($query, $status)
    {
        $query->where('status', $status);
    }
    
    public function time_logs()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function today_time_logs()
    {
        return $this->hasMany(CheckIn::class)->whereDate('created_at', date('Y-m-d'));
    }

    protected function firstName(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucfirst($value),
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucfirst($value),
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucwords($value),
        );
    }

    
}

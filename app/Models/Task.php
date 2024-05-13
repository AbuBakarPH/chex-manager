<?php

namespace App\Models;

use App\Models\MythBuster;
use App\Models\Admin\Company;
use App\Models\Admin\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Casts\Attribute;


class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 
        'sub_category_id',
        'ref_id',
        'name',
        'priority',
        'status',
        'description',
        'type',
        'company_id',
        'created_by',
        'admin_status',
        'org_role_id',
        'frequency',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'sub_category_id' => 'integer',
    ];

    protected $dates    = ['deleted_at'];

    protected $hidden = [
        "deleted_at",
        "updated_at",
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucfirst($value),
        );
    }
    
    public function org_role()
    {
        return $this->belongsTo(OrganizationalRole::class, 'org_role_id');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sub_category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sections()
    {
        $role = auth()->user()->getRoleNames()[0];
        if ($role == 'Staff') {
            return $this->hasMany(TaskSection::class)->where('status', 'active');
        }

        return $this->hasMany(TaskSection::class);
    }

    public function dailyChecklists()
    {
        // return $this->hasMany(DailyChecklist::class, 'checklist_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function config()
    {
        return $this->hasMany(Config::class, 'task_id');
    }

    public function configs()
    {
        return $this->hasMany(Config::class);
    }

    /**
     * Get all of the mythBusters for the task.
     */
    public function mythBusters()
    {
        return $this->morphToMany(MythBuster::class, 'mythbusterable');
    }
}

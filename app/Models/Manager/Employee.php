<?php

namespace App\Models\Manager;

use App\Models\Admin\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'address',
    ];

    protected static function boot()
    {
        parent::boot();

        // Use the saving event to update the 'name' field
        static::saving(function ($employee) {
            $employee->updateNameField();
        });
    }

    public function updateNameField()
    {
        // Update the 'name' field based on 'first_name' and 'last_name'
        $this->name = $this->first_name . ' ' . $this->last_name;
    }

    public function photo()
    {
        return $this->morphOne(Media::class, 'mediable')->latestOfMany();
    }
}

<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Media extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'path', 'mime_type', 'mediable_type', 'mediable_id', 'status', 'admin_id', 'type', 'size'];
    protected $dates    = ['deleted_at'];

    public function getPathAttribute($value)
    {
        return config('app.aws_url').''.$value;
    }

    // protected function path(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn (string $value) => config('app.aws_url').''.$value,
    //     );
    // }
}

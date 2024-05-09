<?php

namespace App\Models\Admin;

use App\Models\Admin\Media;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'icon', 'parent_id'];
    protected array $dates = ['deleted_at'];

    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
    ];

    public function sub_categories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function photo()
    {
        return $this->morphOne(Media::class, 'mediable');
    }

    public function documents()
    {
        return $this->morphMany(Media::class, 'mediable')->whereType('document');
    }

    public function checklists()
    {
        return $this->hasMany(CheckList::class, 'sub_category_id')
            ->where('admin_status', 'approved')
            ->where('company_id', auth()->user()->company_id)
            ->orWhere('type', 'admin_template');
    }
}

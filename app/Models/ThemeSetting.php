<?php

namespace App\Models;

use App\Models\Admin\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    use HasFactory;
    
    protected $fillable = ['company_id', 'app_name', 'theme_color', 'color_code'];

    public function photo()
    {
        return $this->morphOne(Media::class, 'mediable')->latestOfMany();
    }
}

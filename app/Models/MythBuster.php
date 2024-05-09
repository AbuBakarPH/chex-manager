<?php

namespace App\Models;

use App\Models\Task;
use App\Models\Admin\Media;
use App\Models\Admin\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MythBuster extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description'];

    public function document()
    {
        return $this->morphOne(Media::class, 'mediable');
    }
    
    /**
     * Get all of the tasks that are assigned this MythBuster.
     */
    public function tasks()
    {
        return $this->morphedByMany(Task::class, 'mythbusterable');
    }
 
    /**
     * Get all of the videos that are assigned this MythBuster.
     */
    public function companies()
    {
        return $this->morphedByMany(Company::class, 'mythbusterable');
    }

}

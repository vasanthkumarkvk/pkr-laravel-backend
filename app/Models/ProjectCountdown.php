<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCountdown extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'completion_date', 'is_active'];

    // Project kooda relation
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAmenity extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'section_subtitle', 'section_title', 'section_description', 'amenities_list'];
    
    protected $casts = ['amenities_list' => 'array'];

    // Ithu slider details kooda connect aagum
    public function details()
    {
        return $this->hasMany(AmenityDetail::class);
    }
    
}
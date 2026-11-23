<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmenityDetail extends Model
{
    use HasFactory;

    protected $fillable = ['project_amenity_id', 'tag', 'title', 'description', 'tags', 'images'];
    
    protected $casts = [
        'tags' => 'array',
        'images' => 'array'
    ];

    // In ProjectAmenity.php
public function details() {
    return $this->hasMany(AmenityDetail::class, 'project_amenity_id');
}
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectConnectivity extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'title', 'description', 'button_text', 'map_link', 'locations'];
    
    protected $casts = ['locations' => 'array'];
}
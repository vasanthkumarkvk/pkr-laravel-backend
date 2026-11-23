<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreeSixtyDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'eyebrow', 'title', 'description', 'cover_image', 'panorama_image', 'button_text'
    ];
}
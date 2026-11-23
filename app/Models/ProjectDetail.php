<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'hero_subtitle', 'hero_title', 'hero_description', 'hero_image',
        'call_button_text', 'call_button_number',
        'prop_type', 'prop_size', 'prop_units', 'prop_price', 'prop_floors',
        'rera_number', 'rera_pdf'
    ];
}
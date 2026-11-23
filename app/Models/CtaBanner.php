<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CtaBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'title', 'description', 'button_text', 'whatsapp_number', 'background_image'
    ];
}
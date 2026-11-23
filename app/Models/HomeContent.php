<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeContent extends Model
{
protected $fillable = [
    'popup_image',
    'banners', // Old one
    'desktop_banners',
    'mobile_banners',
    'legacy_years',
    'legacy_years_text',
    'legacy_space',
    'legacy_space_text',
    'legacy_landmark',
    'legacy_landmark_text',
];

protected $casts = [
    'banners' => 'array',
    'desktop_banners' => 'array',
    'mobile_banners' => 'array',
];
}

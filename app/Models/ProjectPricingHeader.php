<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProjectPricingHeader extends Model
{
    protected $fillable = [
        'project_id', 'eyebrow', 'heading', 'description', 
        'icon_1_image', 'icon_1_title', 'icon_1_desc',
        'icon_2_image', 'icon_2_title', 'icon_2_desc',
        'icon_3_image', 'icon_3_title', 'icon_3_desc',
        'cta_title', 'cta_description', 'cta_image', 'cta_button_text',
        'validity_message', 'filter_tabs',        'offer_price_status' // Ithu puthu

    ];
    protected $casts = ['filter_tabs' => 'array'];
}
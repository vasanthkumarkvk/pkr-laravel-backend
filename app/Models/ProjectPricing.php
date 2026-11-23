<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProjectPricing extends Model
{
    protected $fillable = [
        'project_id', 'flat_no', 'floor', 'type', 'facing', 'area', 
        'original_price', 'offer_price', 'is_pre_launch'
    ];
}
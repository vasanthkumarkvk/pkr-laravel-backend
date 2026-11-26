<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'nav_name', 
        'status', 
        'thumbnail_image', 
        'desktop_banner', 
        'mobile_banner',
        'gallery_title',
        'broucher'

    ];

    // Default value set panna
    protected $attributes = [
        'status' => 'inactive',
        'name' => 'New Project',
    ];

        // ProjectDetail oda relation
    public function details()
    {
        return $this->hasOne(ProjectDetail::class);
    }

        // Project Amenities Relation
    public function amenities()
    {
        return $this->hasOne(ProjectAmenity::class);
    }


    // Project Gallery relation
    public function galleries()
    {
        return $this->hasMany(ProjectGallery::class);
    }


    public function layoutHeader()
{
    return $this->hasOne(ProjectLayoutHeader::class);
}

public function layouts()
{
    return $this->hasMany(ProjectLayout::class);
}


public function pricingHeader()
{
    return $this->hasOne(ProjectPricingHeader::class);
}

public function pricings()
{
    return $this->hasMany(ProjectPricing::class);
}


    public function threeSixtyDetail()
    {
        return $this->hasOne(ThreeSixtyDetail::class);
    }
    public function connectivity()
    {
        return $this->hasOne(ProjectConnectivity::class);
    }

        public function ctaBanner()
    {
        return $this->hasOne(CtaBanner::class);
    }


     // Define all relationships
    public function detail() {
        return $this->hasOne(ProjectDetail::class, 'project_id');
    }

    public function amenity() {
        return $this->hasOne(ProjectAmenity::class, 'project_id');
    }


    public function threeSixty() {
        return $this->hasOne(ThreeSixtyDetail::class, 'project_id'); // Assuming model name
    }

    public function countdown()
    {
        return $this->hasOne(ProjectCountdown::class, 'project_id');
    }



}
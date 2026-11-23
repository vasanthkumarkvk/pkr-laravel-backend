<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectAmenity;
use App\Models\ProjectGallery;
use App\Models\ProjectLayoutHeader;
use App\Models\ProjectLayout;
use App\Models\ProjectPricingHeader;
use App\Models\ProjectPricing;
use App\Models\ProjectConnectivity;
use App\Models\ThreeSixtyDetail;

class ProjectController extends Controller
{
    /**
     * 1. List Page (Get All)
     * Fetches projects along with the details needed for the UI cards
     */
      public function index()
    {   
        // Eager Loading ALL relationships
        $projects = Project::with([
            'detail',
            'amenity.details', 
            'galleries',
            'layoutHeader',
            'layouts',
            'pricingHeader',
            'pricings', // Warning: Idhu 186 rows vara mudiyum
            'threeSixty',
            'connectivity'
        ])
        ->latest()
        ->get();

        return response()->json([
            'status' => true, 
            'message' => 'Projects with all details fetched successfully',
            'data' => $projects
        ], 200);
    }
    // 9. Navbar Projects API (Just ID and Nav Name)
    public function getNavbarProjects()
    {
        // Only get ID and nav_name, fallback to name if nav_name is null
        $projects = Project::select('id', 'name', 'nav_name')
            ->latest()
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'display_name' => $project->nav_name ?? $project->name // Fallback to original name if nav_name is empty
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $projects
        ], 200);
    }
 
}
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    // 1. CREATE PROJECT (Empty ah create aagum, images illa)
    public function createProject()
    {
        $project = Project::create(); // Default ah 'New Project' and 'inactive' status la create aagum

        return response()->json([
            'status' => 'success',
            'message' => 'Project created successfully',
            'data' => $project
        ]);
    }

    // 2. ADD PROJECT BANNERS (Separate ah images add panna)
    public function addProjectBanners(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'desktop_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'mobile_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
        ]);

        // Pazhaya image irundha delete pandrom
        $images = ['thumbnail_image', 'desktop_banner', 'mobile_banner'];
        foreach ($images as $imgField) {
            if ($request->hasFile($imgField)) {
                if ($project->$imgField && Storage::disk('public')->exists($project->$imgField)) {
                    Storage::disk('public')->delete($project->$imgField);
                }
                
                // Puthu image ah save pandrom
                $path = $request->file($imgField)->store('projects/banners', 'public');
                $project->$imgField = $path;
            }
        }

        $project->save();

        // Response la full URL anuppa
        $responseData = $project->toArray();
        foreach ($images as $imgField) {
            if ($project->$imgField) {
                $responseData[$imgField] = asset('storage/' . $project->$imgField);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Project banners added/updated successfully',
            'data' => $responseData
        ]);
    }

    // 3. UPDATE PROJECT DETAILS (Name, Status mattum)
  public function updateProject(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }

        // 1. Validate the request (Make nav_name optional)
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'nav_name' => 'nullable|string|max:255', // 👈 Ithu puthu column
            'status' => 'sometimes|required|string|in:active,inactive',
            'gallery_title' => 'nullable|string'
        ]);

        // 2. Update the fields
        // If the request has the field, update it. 
        // Using $request->all() might overwrite images if not careful, so we specify.
        $project->name = $request->has('name') ? $request->name : $project->name;
        $project->nav_name = $request->has('nav_name') ? $request->nav_name : $project->nav_name;
        $project->status = $request->has('status') ? $request->status : $project->status;
        $project->gallery_title = $request->has('gallery_title') ? $request->gallery_title : $project->gallery_title;

        // 3. Save to database
        $project->save();

        return response()->json([
            'status' => true,
            'message' => 'Project updated successfully',
            'data' => $project
        ], 200);
    }

    // 4. GET ALL PROJECTS (Admin panel list ku)
    public function getAllProjects()
    {
        $projects = Project::orderBy('id', 'desc')->get();
        
        // Ellathukum images ku full URL set pandrom
        $projects->map(function($project) {
            $project->thumbnail_image = $project->thumbnail_image ? asset('storage/' . $project->thumbnail_image) : null;
            $project->desktop_banner = $project->desktop_banner ? asset('storage/' . $project->desktop_banner) : null;
            $project->mobile_banner = $project->mobile_banner ? asset('storage/' . $project->mobile_banner) : null;
            return $project;
        });

        return response()->json(['status' => 'success', 'data' => $projects]);
    }

        // 5. EDIT PROJECT BANNER (Specific Image ah replace panna)
    public function updateProjectBanner(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'type' => 'required|in:thumbnail,desktop,mobile', // Enna image ah edit pandrom
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // User anuppura type ah vechu column name ah decide pandrom
        $columnName = $request->type . '_image';
        if ($request->type == 'desktop') $columnName = 'desktop_banner';
        if ($request->type == 'mobile') $columnName = 'mobile_banner';

        // Pazhaya image irundha delete pandrom
        if ($project->$columnName && Storage::disk('public')->exists($project->$columnName)) {
            Storage::disk('public')->delete($project->$columnName);
        }

        // Puthu image ah upload pandrom
        $path = $request->file('image')->store('projects/banners', 'public');
        $project->$columnName = $path;
        $project->save();

        return response()->json([
            'status' => 'success',
            'message' => ucfirst($request->type) . ' banner updated successfully',
            'url' => asset('storage/' . $path)
        ]);
    }

        // 6. DELETE PROJECT BANNER
    public function deleteProjectBanner(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Query param la irundhu type ah edukkirom (URL la ?type=desktop nu varum)
        $type = $request->query('type'); 

        if (!in_array($type, ['thumbnail', 'desktop', 'mobile'])) {
            return response()->json(['message' => 'Invalid or missing type parameter'], 422);
        }

        // Type ah vechu column name ah decide pandrom
        $columnName = $type . '_image';
        if ($type == 'desktop') $columnName = 'desktop_banner';
        if ($type == 'mobile') $columnName = 'mobile_banner';

        // Database la image irukka nu check pandrom
        if (!$project->$columnName) {
            return response()->json(['message' => 'No image found to delete'], 404);
        }

        // Storage la irundhu image ah delete pandrom
        if (Storage::disk('public')->exists($project->$columnName)) {
            Storage::disk('public')->delete($project->$columnName);
        }

        // Database la column ah null ah aakkiduvom
        $project->$columnName = null;
        $project->save();

        return response()->json([
            'status' => 'success',
            'message' => ucfirst($type) . ' banner deleted successfully'
        ]);
    }


        // 4. GET SINGLE PROJECT FULL DETAILS (Frontend page ku)
    public function getProjectDetails($id)
    {
        // Project data with details ah load pandrom
        $project = Project::with('details')->findOrFail($id);

        // Banners ku Full URL set pandrom
        $projectData = $project->toArray();
        $projectData['thumbnail_image_url'] = $project->thumbnail_image ? asset('storage/' . $project->thumbnail_image) : null;
        $projectData['desktop_banner_url'] = $project->desktop_banner ? asset('storage/' . $project->desktop_banner) : null;
        $projectData['mobile_banner_url'] = $project->mobile_banner ? asset('storage/' . $project->mobile_banner) : null;

        // Hero section details ku Full URL set pandrom
        if ($project->details) {
            $projectData['details']['hero_image_url'] = $project->details->hero_image ? asset('storage/' . $project->details->hero_image) : null;
            $projectData['details']['rera_pdf_url'] = $project->details->rera_pdf ? asset('storage/' . $project->details->rera_pdf) : null;
        }

        return response()->json([
            'status' => 'success',
            'data' => $projectData
        ]);
    }


     /**
     * Upload or Update Broucher
     */
    public function uploadBroucher(Request $request, $id)
    {
        // 1. Validation (PDF, JPG, PNG file mattum allow panrom, max 10MB)
        $validator = Validator::make($request->all(), [
            'broucher' => 'required|file|mimes:pdf,jpg,jpeg,png', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($request->hasFile('broucher')) {
            
            // 2. Pazhaya file iruntha delete pannu
            if ($project->broucher && Storage::disk('public')->exists($project->broucher)) {
                Storage::disk('public')->delete($project->broucher);
            }

            // 3. Puthu file ah store pannu (storage/app/public/projects/brouchers/ la save aagum)
            $filePath = $request->file('broucher')->store('projects/brouchers', 'public');

            // 4. DB la update pannu
            $project->broucher = $filePath;
            $project->save();

            return response()->json([
                'message' => 'Broucher uploaded successfully',
                'broucher_url' => asset('storage/' . $filePath), // Full URL return panrom
                'data' => $project
            ], 200);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    /**
     * Delete Broucher
     */
    public function deleteBroucher($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        // 1. File ah storage la irunthu delete pannu
        if ($project->broucher && Storage::disk('public')->exists($project->broucher)) {
            Storage::disk('public')->delete($project->broucher);
        }

        // 2. DB la NULL ah set pannu
        $project->broucher = null;
        $project->save();

        return response()->json([
            'message' => 'Broucher deleted successfully',
            'data' => $project
        ], 200);
    }
    
}
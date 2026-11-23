<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectGalleryController extends Controller
{
    // 1. UPDATE SECTION TITLE
    public function updateTitle(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $request->validate([
            'gallery_title' => 'nullable|string',
        ]);

        if ($request->has('gallery_title')) {
            $project->gallery_title = $request->gallery_title;
            $project->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Title updated', 'data' => $project->gallery_title]);
    }

    // 2. ADD GALLERY IMAGE (Single or Multiple)
    public function addImages(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $request->validate([
            'image_titles' => 'nullable|json', // JSON array of titles (e.g., ["Entrance", "Lobby"])
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $titles = json_decode($request->image_titles ?? '[]', true);
        $addedImages = [];

        foreach ($request->file('images') as $index => $image) {
            // Image save pandrom
            $path = $image->store('projects/gallery', 'public');
            
            // Image Title edukkirom (irundha)
            $imageTitle = isset($titles[$index]) ? $titles[$index] : null;

            $gallery = $project->galleries()->create([
                'image_path' => $path,
                'image_title' => $imageTitle
            ]);
            
            $addedImages[] = $gallery;
        }

        return response()->json(['status' => 'success', 'message' => 'Images added successfully', 'data' => $addedImages]);
    }

    // 3. EDIT SINGLE IMAGE (Title / Image)
    public function updateImage(Request $request, $imageId)
    {
        $gallery = ProjectGallery::findOrFail($imageId);

        $request->validate([
            'image_title' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Title update
        if ($request->has('image_title')) {
            $gallery->image_title = $request->image_title;
        }

        // Image update (Pazhaya image delete aagum)
        if ($request->hasFile('image')) {
            if ($gallery->image_path && Storage::disk('public')->exists($gallery->image_path)) {
                Storage::disk('public')->delete($gallery->image_path);
            }
            $path = $request->file('image')->store('projects/gallery', 'public');
            $gallery->image_path = $path;
        }

        $gallery->save();

        return response()->json(['status' => 'success', 'message' => 'Image updated', 'data' => $gallery]);
    }

    // 4. DELETE GALLERY IMAGE
    public function deleteImage($imageId)
    {
        $gallery = ProjectGallery::findOrFail($imageId);

        // Storage la irundhu delete pandrom
        if ($gallery->image_path && Storage::disk('public')->exists($gallery->image_path)) {
            Storage::disk('public')->delete($gallery->image_path);
        }

        $gallery->delete();

        return response()->json(['status' => 'success', 'message' => 'Image deleted successfully']);
    }

    // 5. GET GALLERY
    public function getGallery($projectId)
    {
        $project = Project::with('galleries')->findOrFail($projectId);

        $data = [
            'gallery_title' => $project->gallery_title,
            'images' => $project->galleries->map(function($gal) {
                return [
                    'id' => $gal->id,
                    'image_title' => $gal->image_title,
                    'image_url' => asset('storage/' . $gal->image_path)
                ];
            })
        ];

        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
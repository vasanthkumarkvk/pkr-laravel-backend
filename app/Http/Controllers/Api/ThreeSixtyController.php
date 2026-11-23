<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ThreeSixtyController extends Controller
{
    // 1. UPDATE 360 DETAILS & IMAGES
    public function updateDetails(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $details = $project->threeSixtyDetail()->firstOrCreate([]);

        $request->validate([
            'eyebrow' => 'nullable|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'panorama_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Text update
        $details->fill($request->only(['eyebrow', 'title', 'description', 'button_text']));

        // Cover Image Upload
        if ($request->hasFile('cover_image')) {
            if ($details->cover_image && Storage::disk('public')->exists($details->cover_image)) {
                Storage::disk('public')->delete($details->cover_image);
            }
            $details->cover_image = $request->file('cover_image')->store('projects/360', 'public');
        }

        // Panorama Image Upload
        if ($request->hasFile('panorama_image')) {
            if ($details->panorama_image && Storage::disk('public')->exists($details->panorama_image)) {
                Storage::disk('public')->delete($details->panorama_image);
            }
            $details->panorama_image = $request->file('panorama_image')->store('projects/360/panorama', 'public');
        }

        $details->save();

        // Response la Full URL anuppa
        $responseData = $details->toArray();
        if ($details->cover_image) $responseData['cover_image_url'] = asset('storage/' . $details->cover_image);
        if ($details->panorama_image) $responseData['panorama_image_url'] = asset('storage/' . $details->panorama_image);

        return response()->json([
            'status' => 'success', 
            'message' => '360 details updated successfully', 
            'data' => $responseData
        ]);
    }

    // 2. GET 360 DETAILS
    public function getDetails($projectId)
    {
        $project = Project::with('threeSixtyDetail')->findOrFail($projectId);
        $details = $project->threeSixtyDetail;

        if (!$details) {
            return response()->json(['status' => 'error', 'message' => '360 details not found'], 404);
        }

        $data = $details->toArray();
        if ($details->cover_image) $data['cover_image_url'] = asset('storage/' . $details->cover_image);
        if ($details->panorama_image) $data['panorama_image_url'] = asset('storage/' . $details->panorama_image);

        return response()->json(['status' => 'success', 'data' => $data]);
    }


        // 3. EDIT SPECIFIC IMAGE (Cover or Panorama)
    public function updateImage(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $details = $project->threeSixtyDetail()->firstOrCreate([]);

        $request->validate([
            'type' => 'required|in:cover,panorama', // Enna image ah edit pandrom
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $columnName = $request->type == 'cover' ? 'cover_image' : 'panorama_image';
        $folder = $request->type == 'cover' ? 'projects/360' : 'projects/360/panorama';

        // Pazhaya image ah delete pandrom
        if ($details->$columnName && Storage::disk('public')->exists($details->$columnName)) {
            Storage::disk('public')->delete($details->$columnName);
        }

        // Puthu image ah upload pandrom
        $details->$columnName = $request->file('image')->store($folder, 'public');
        $details->save();

        return response()->json([
            'status' => 'success',
            'message' => ucfirst($request->type) . ' image updated successfully',
            'url' => asset('storage/' . $details->$columnName)
        ]);
    }

    // 4. DELETE SPECIFIC IMAGE
    public function deleteImage(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $details = $project->threeSixtyDetail;

        if (!$details) {
            return response()->json(['status' => 'error', 'message' => 'Details not found'], 404);
        }

        $type = $request->query('type'); // Query param la irundhu edukkirom (e.g., ?type=cover)

        if (!in_array($type, ['cover', 'panorama'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid type. Use cover or panorama'], 422);
        }

        $columnName = $type == 'cover' ? 'cover_image' : 'panorama_image';

        if ($details->$columnName) {
            // Storage la irundhu delete pandrom
            if (Storage::disk('public')->exists($details->$columnName)) {
                Storage::disk('public')->delete($details->$columnName);
            }
            // Database la null ah aakkiduvom
            $details->$columnName = null;
            $details->save();

            return response()->json(['status' => 'success', 'message' => ucfirst($type) . ' image deleted successfully']);
        }

        return response()->json(['status' => 'error', 'message' => 'Image not found to delete'], 404);
    }
}
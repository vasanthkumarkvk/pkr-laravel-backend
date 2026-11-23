<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CtaBannerController extends Controller
{
    // 1. UPDATE DETAILS & IMAGE
    public function updateDetails(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $cta = $project->ctaBanner()->firstOrCreate([]);

        $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string',
            'whatsapp_number' => 'nullable|string',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Text details update
        $cta->fill($request->only(['title', 'description', 'button_text', 'whatsapp_number']));

        // Image upload
        if ($request->hasFile('background_image')) {
            if ($cta->background_image && Storage::disk('public')->exists($cta->background_image)) {
                Storage::disk('public')->delete($cta->background_image);
            }
            $cta->background_image = $request->file('background_image')->store('projects/cta', 'public');
        }

        $cta->save();

        // Full URL anuppa
        $responseData = $cta->toArray();
        if ($cta->background_image) {
            $responseData['background_image_url'] = asset('storage/' . $cta->background_image);
        }

        return response()->json(['status' => 'success', 'data' => $responseData]);
    }

    // 2. GET DETAILS
    public function getDetails($projectId)
    {
        $project = Project::with('ctaBanner')->findOrFail($projectId);
        $cta = $project->ctaBanner;

        if (!$cta) {
            return response()->json(['status' => 'error', 'message' => 'CTA details not found'], 404);
        }

        $data = $cta->toArray();
        if ($cta->background_image) {
            $data['background_image_url'] = asset('storage/' . $cta->background_image);
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    // 3. EDIT ONLY IMAGE
    public function updateImage(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $cta = $project->ctaBanner()->firstOrCreate([]);

        $request->validate([
            'background_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($cta->background_image && Storage::disk('public')->exists($cta->background_image)) {
            Storage::disk('public')->delete($cta->background_image);
        }

        $cta->background_image = $request->file('background_image')->store('projects/cta', 'public');
        $cta->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Background image updated',
            'url' => asset('storage/' . $cta->background_image)
        ]);
    }

    // 4. DELETE IMAGE
    public function deleteImage($projectId)
    {
        $project = Project::findOrFail($projectId);
        $cta = $project->ctaBanner;

        if (!$cta || !$cta->background_image) {
            return response()->json(['status' => 'error', 'message' => 'Image not found'], 404);
        }

        if (Storage::disk('public')->exists($cta->background_image)) {
            Storage::disk('public')->delete($cta->background_image);
        }

        $cta->background_image = null;
        $cta->save();

        return response()->json(['status' => 'success', 'message' => 'Image deleted successfully']);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectDetailController extends Controller
{
    // 1. UPDATE PROJECT DETAILS (Hero section, Prop details, RERA)
    public function updateDetails(Request $request, $projectId)
    {
        // Project irukka nu check pandrom
        $project = Project::findOrFail($projectId);
        
        // Details table la irukka row ah edukkirom, illa na default values oda create pandrom
        $details = $project->details()->firstOrCreate([], [
            'hero_subtitle' => 'MORE THAN JUST A HOME',
            'hero_title' => 'Designed for a Better Way of Life',
            'hero_description' => 'Privana brings together contemporary architecture, premium finishes and a thoughtfully curated set of amenities in one refined residential address.',
            'call_button_text' => 'Call Us',
            'call_button_number' => '+919876543210',
            'prop_type' => 'Studio, 1, 2 & 3 BHK',
            'prop_size' => '1.41 Acres',
            'prop_units' => '186',
            'prop_price' => '₹ 5,100 / Sq.Ft',
            'prop_floors' => 'Stilt + 5',
            'rera_number' => 'TNRERA/35/BLG/0317/2026',
        ]);

        $request->validate([
            'hero_subtitle' => 'nullable|string',
            'hero_title' => 'nullable|string',
            'hero_description' => 'nullable|string',
            'call_button_text' => 'nullable|string',
            'call_button_number' => 'nullable|string',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'prop_type' => 'nullable|string',
            'prop_size' => 'nullable|string',
            'prop_units' => 'nullable|string',
            'prop_price' => 'nullable|string',
            'prop_floors' => 'nullable|string',
            'rera_number' => 'nullable|string',
            'rera_pdf' => 'nullable|mimes:pdf|max:5120', 
        ]);

        // Text details update pandrom
        $details->fill($request->only([
            'hero_subtitle', 'hero_title', 'hero_description',
            'call_button_text', 'call_button_number',
            'prop_type', 'prop_size', 'prop_units', 'prop_price', 'prop_floors', 'rera_number'
        ]));

        // Hero Image Upload
        if ($request->hasFile('hero_image')) {
            if ($details->hero_image && Storage::disk('public')->exists($details->hero_image)) {
                Storage::disk('public')->delete($details->hero_image);
            }
            $details->hero_image = $request->file('hero_image')->store('projects/hero', 'public');
        }

        // RERA PDF Upload
        if ($request->hasFile('rera_pdf')) {
            if ($details->rera_pdf && Storage::disk('public')->exists($details->rera_pdf)) {
                Storage::disk('public')->delete($details->rera_pdf);
            }
            $details->rera_pdf = $request->file('rera_pdf')->store('projects/pdfs', 'public');
        }

        $details->save();

        // Response la Full URL anuppa
        $responseData = $details->toArray();
        if ($details->hero_image) $responseData['hero_image_url'] = asset('storage/' . $details->hero_image);
        if ($details->rera_pdf) $responseData['rera_pdf_url'] = asset('storage/' . $details->rera_pdf);

        return response()->json([
            'status' => 'success', 
            'message' => 'Project details updated successfully', 
            'data' => $responseData
        ]);
    }

    // 2. GET PROJECT DETAILS (Specific project ku)
    public function getDetails($projectId)
    {
        $project = Project::with('details')->findOrFail($projectId);
        
        $details = $project->details;
        
        if (!$details) {
            return response()->json(['status' => 'error', 'message' => 'Details not found for this project'], 404);
        }

        // Full URL ah sethu anuppa
        $detailsArray = $details->toArray();
        if ($details->hero_image) $detailsArray['hero_image_url'] = asset('storage/' . $details->hero_image);
        if ($details->rera_pdf) $detailsArray['rera_pdf_url'] = asset('storage/' . $details->rera_pdf);

        return response()->json([
            'status' => 'success', 
            'data' => $detailsArray
        ]);
    }
}
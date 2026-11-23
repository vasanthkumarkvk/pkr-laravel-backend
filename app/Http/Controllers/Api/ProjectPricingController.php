<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PricingsImport;
use Illuminate\Support\Facades\Validator;




class ProjectPricingController extends Controller
{
    // 1. UPDATE HEADER, ICONS & CTA
    public function updateHeader(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $header = $project->pricingHeader()->firstOrCreate([]);

        $request->validate([
            'eyebrow' => 'nullable|string',
            'heading' => 'nullable|string',
            'description' => 'nullable|string',
            'icon_1_image' => 'nullable|image|max:2048',
            'icon_1_title' => 'nullable|string',
            'icon_1_desc' => 'nullable|string',
            'icon_2_image' => 'nullable|image|max:2048',
            'icon_2_title' => 'nullable|string',
            'icon_2_desc' => 'nullable|string',
            'icon_3_image' => 'nullable|image|max:2048',
            'icon_3_title' => 'nullable|string',
            'icon_3_desc' => 'nullable|string',
            'cta_title' => 'nullable|string',
            'cta_description' => 'nullable|string',
            'cta_image' => 'nullable|image|max:5120',
            'cta_button_text' => 'nullable|string',
            'validity_message' => 'nullable|string',
            'filter_tabs' => 'nullable|json',
        ]);

        $header->fill($request->only([
            'eyebrow', 'heading', 'description',
            'icon_1_title', 'icon_1_desc', 'icon_2_title', 'icon_2_desc', 'icon_3_title', 'icon_3_desc',
            'cta_title', 'cta_description', 'cta_button_text', 'validity_message'
        ]));

        if ($request->has('filter_tabs')) $header->filter_tabs = json_decode($request->filter_tabs, true);

        // File Uploads
        $files = ['icon_1_image', 'icon_2_image', 'icon_3_image', 'cta_image'];
        foreach ($files as $fileField) {
            if ($request->hasFile($fileField)) {
                if ($header->$fileField && Storage::disk('public')->exists($header->$fileField)) {
                    Storage::disk('public')->delete($header->$fileField);
                }
                $header->$fileField = $request->file($fileField)->store('projects/pricing', 'public');
            }
        }

        $header->save();
        return response()->json(['status' => 'success', 'data' => $header]);
    }



    public function bulkUpload(Request $request, $projectId)
    {
        // Project irukka nu check pandrom
        $project = Project::findOrFail($projectId);

        // File validate pandrom
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ]);

        try {
            // Excel file ah import pandrom
            Excel::import(new PricingsImport($projectId), $request->file('file'));

            return response()->json([
                'status' => 'success',
                'message' => 'Pricing data uploaded successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload file. Please check the format.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // 2. ADD PRICING UNIT (Flat)
    public function addPricing(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $request->validate([
            'flat_no' => 'required|string',
            'floor' => 'required|string',
            'type' => 'required|string',
            'facing' => 'required|string',
            'area' => 'required|string',
            'original_price' => 'nullable|string',
            'offer_price' => 'required|string',
            'is_pre_launch' => 'nullable|boolean',
        ]);

        $pricing = $project->pricings()->create([
            'flat_no' => $request->flat_no,
            'floor' => $request->floor,
            'type' => $request->type,
            'facing' => $request->facing,
            'area' => $request->area,
            'original_price' => $request->original_price,
            'offer_price' => $request->offer_price,
            'is_pre_launch' => $request->is_pre_launch ?? true,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Pricing unit added', 'data' => $pricing]);
    }

    // 3. EDIT PRICING UNIT
    public function updatePricing(Request $request, $pricingId)
    {
        $pricing = ProjectPricing::findOrFail($pricingId);

        $request->validate([
            'flat_no' => 'nullable|string',
            'floor' => 'nullable|string',
            'type' => 'nullable|string',
            'facing' => 'nullable|string',
            'area' => 'nullable|string',
            'original_price' => 'nullable|string',
            'offer_price' => 'nullable|string',
            'is_pre_launch' => 'nullable|boolean',
        ]);

        $pricing->fill($request->all());
        $pricing->save();

        return response()->json(['status' => 'success', 'message' => 'Pricing unit updated', 'data' => $pricing]);
    }

    // 4. DELETE PRICING UNIT
    public function deletePricing($pricingId)
    {
        $pricing = ProjectPricing::findOrFail($pricingId);
        $pricing->delete();

        return response()->json(['status' => 'success', 'message' => 'Pricing unit deleted']);
    }

    // 5. GET FULL PRICING DATA
    public function getPricingData($projectId)
    {
        $project = Project::with(['pricingHeader', 'pricings'])->findOrFail($projectId);
        
        $headerData = $project->pricingHeader ? $project->pricingHeader->toArray() : null;
        
        if ($headerData) {
            // Full image URLs
            foreach (['icon_1_image', 'icon_2_image', 'icon_3_image', 'cta_image'] as $img) {
                if ($headerData[$img]) $headerData[$img . '_url'] = asset('storage/' . $headerData[$img]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'header' => $headerData,
                'pricings' => $project->pricings
            ]
        ]);
    }




        // 7. UPDATE OFFER PRICE STATUS (YES / NO)
    public function updateOfferStatus(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        
        // Header record ah edukkirom (illa na create pandrom)
        $header = $project->pricingHeader()->firstOrCreate([]);

        $request->validate([
            'offer_price_status' => 'required|in:yes,no',
        ]);

        $header->offer_price_status = $request->offer_price_status;
        $header->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Offer price status updated to ' . $request->offer_price_status,
            'current_status' => $header->offer_price_status
        ]);
    }

    
}
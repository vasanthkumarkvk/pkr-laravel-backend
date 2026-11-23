<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    // 1. GET HOME DETAILS
    public function getHomeDetails()
    {
        // Ithu first row ah eduthukkum. Illa na puthu row create pannum.
        $content = HomeContent::firstOrCreate([]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'popup_image' => $content->popup_image ? asset('storage/' . $content->popup_image) : null,
                'desktop_banners' => collect($content->desktop_banners ?? [])->map(function($img) {
                    return asset('storage/' . $img); 
                }),
                'mobile_banners' => collect($content->mobile_banners ?? [])->map(function($img) {
                    return asset('storage/' . $img); 
                }),
                'legacy' => [
                    'years' => $content->legacy_years,
                    'years_text' => $content->legacy_years_text,
                    'space' => $content->legacy_space,
                    'space_text' => $content->legacy_space_text,
                    'landmark' => $content->legacy_landmark,
                    'landmark_text' => $content->legacy_landmark_text,
                ]
            ]
        ]);
    }

    // 2. UPDATE POPUP & STATS
     public function updateHomeDetails(Request $request)
    {
        $request->validate([
            'popup_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_popup' => 'nullable|boolean', // Puthu condition
            'legacy_years' => 'nullable|string',
            'legacy_space' => 'nullable|string',
            'legacy_landmark' => 'nullable|string',
        ]);

        $content = HomeContent::firstOrCreate([]);

        // --- DELETE POPUP IMAGE ---
        if ($request->remove_popup == true && $content->popup_image) {
            Storage::disk('public')->delete($content->popup_image);
            $content->popup_image = null; // DB la null ah aakum
        }
        // --- UPLOAD (Ithu ungaluku thevai illa, aana irundha thappilla) ---
        elseif ($request->hasFile('popup_image')) {
            if ($content->popup_image) {
                Storage::disk('public')->delete($content->popup_image);
            }
            $path = $request->file('popup_image')->store('home', 'public');
            $content->popup_image = $path;
        }

        if ($request->has('legacy_years')) $content->legacy_years = $request->legacy_years;
        if ($request->has('legacy_space')) $content->legacy_space = $request->legacy_space;
        if ($request->has('legacy_landmark')) $content->legacy_landmark = $request->legacy_landmark;

        $content->save();

        return response()->json(['status' => 'success', 'message' => 'Updated successfully', 'data' => $content]);
    }

    // 3. ADD NEW BANNERS
    public function addBanners(Request $request)
    {
        $request->validate([
            'type' => 'required|in:desktop,mobile', 
            'banners' => 'required|array',
            'banners.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        // First row ah eduthukkum (DB la irukka single row)
        $content = HomeContent::firstOrCreate([]);
        
        $columnName = $request->type . '_banners'; 
        $currentBanners = $content->$columnName ?? []; 

        foreach ($request->file('banners') as $banner) {
            $path = $banner->store("home/{$request->type}", 'public');
            $currentBanners[] = $path; 
        }

        $content->$columnName = $currentBanners;
        $content->save();

        return response()->json([
            'status' => 'success', 
            'message' => ucfirst($request->type) . ' banners added successfully', 
            'banners' => $content->$columnName
        ]);
    }

    // 4. REORDER BANNERS
    public function reorderBanners(Request $request)
    {
        $request->validate([
            'type' => 'required|in:desktop,mobile',
            'order' => 'required|array',
            'order.*' => 'integer' 
        ]);

        $content = HomeContent::firstOrCreate([]);
        $columnName = $request->type . '_banners';
        $currentBanners = $content->$columnName ?? [];

        if (empty($currentBanners)) {
            return response()->json(['message' => 'No banners found to reorder'], 404);
        }

        $newOrderedBanners = [];
        foreach ($request->order as $index) {
            if (isset($currentBanners[$index])) {
                $newOrderedBanners[] = $currentBanners[$index];
            }
        }

        foreach ($currentBanners as $index => $banner) {
            if (!in_array($index, $request->order)) {
                $newOrderedBanners[] = $banner;
            }
        }

        $content->$columnName = $newOrderedBanners;
        $content->save();

        return response()->json(['status' => 'success', 'banners' => $content->$columnName]);
    }

    // 5. UPDATE (REPLACE) A SPECIFIC BANNER
    public function updateBanner(Request $request, $index)
    {
        $request->validate([
            'type' => 'required|in:desktop,mobile',
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $content = HomeContent::firstOrCreate([]);
        $columnName = $request->type . '_banners';
        $banners = $content->$columnName ?? [];

        if (!isset($banners[$index])) {
            return response()->json(['message' => 'Banner not found at this index'], 404);
        }

        if (Storage::disk('public')->exists($banners[$index])) {
            Storage::disk('public')->delete($banners[$index]);
        }

        $path = $request->file('banner')->store("home/{$request->type}", 'public');
        $banners[$index] = $path;
        $content->$columnName = $banners;
        $content->save();

        return response()->json(['status' => 'success', 'banners' => $content->$columnName]);
    }

    // 6. DELETE A SPECIFIC BANNER
    public function deleteBanner(Request $request, $index)
    {
        $type = $request->query('type'); 

        if (!in_array($type, ['desktop', 'mobile'])) {
            return response()->json(['message' => 'Invalid or missing type parameter'], 422);
        }

        $content = HomeContent::firstOrCreate([]);
        $columnName = $type . '_banners';
        $banners = $content->$columnName ?? [];
        
        if (!isset($banners[$index])) {
            return response()->json(['message' => 'Banner not found at this index'], 404);
        }

        if (Storage::disk('public')->exists($banners[$index])) {
            Storage::disk('public')->delete($banners[$index]);
        }

        unset($banners[$index]);
        $content->$columnName = array_values($banners); 
        $content->save();

        return response()->json(['status' => 'success', 'banners' => $content->$columnName]);
    }
}
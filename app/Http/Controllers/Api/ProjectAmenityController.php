<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\AmenityDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectAmenityController extends Controller
{
    // 1. UPDATE MAIN HEADINGS & GRID ICONS
    public function updateMainDetails(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        
        // Database la default value illa, so inga set pandrom
        $amenity = $project->amenities()->firstOrCreate([], [
            'section_subtitle' => 'LIFE AT PRIVANA',
            'section_title' => 'Everyday Comforts, Exceptional Living',
            'section_description' => 'At Privana, every amenity is designed to make daily life easier and more enjoyable.',
        ]);

        $request->validate([
            'section_subtitle' => 'nullable|string',
            'section_title' => 'nullable|string',
            'section_description' => 'nullable|string',
            'amenities_list' => 'nullable|json',
            'amenities_icons.*' => 'nullable|image|max:2048',
        ]);

        $amenity->fill($request->only(['section_subtitle', 'section_title', 'section_description']));

        if ($request->has('amenities_list')) {
            $list = json_decode($request->amenities_list, true);
            
            if ($request->hasFile('amenities_icons')) {
                $uploadedIcons = [];
                foreach ($request->file('amenities_icons') as $icon) {
                    $uploadedIcons[] = $icon->store('projects/amenities/icons', 'public');
                }
                foreach ($list as $index => &$item) {
                    if (isset($uploadedIcons[$index])) $item['icon'] = $uploadedIcons[$index];
                }
            }
            $amenity->amenities_list = $list;
        }

        $amenity->save();
        return response()->json(['status' => 'success', 'data' => $amenity]);
    }

    // 2. ADD NEW SLIDER DETAIL (Club House)
    public function addSliderDetail(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $amenity = $project->amenities()->firstOrCreate([]);

        $request->validate([
            'tag' => 'nullable|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|json',
            'images' => 'required|array',
            'images.*' => 'image|max:2048',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $imagePaths[] = $img->store('projects/amenities/sliders', 'public');
            }
        }

        $detail = $amenity->details()->create([
            'tag' => $request->tag,
            'title' => $request->title,
            'description' => $request->description,
            'tags' => json_decode($request->tags ?? '[]', true),
            'images' => $imagePaths
        ]);

        return response()->json(['status' => 'success', 'message' => 'Slider added', 'data' => $detail]);
    }

    // 3. EDIT SLIDER DETAIL
    public function updateSliderDetail(Request $request, $detailId)
    {
        $detail = AmenityDetail::findOrFail($detailId);

        $request->validate([
            'tag' => 'nullable|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|json',
            'images' => 'nullable|array',
            'images.*' => 'image',
        ]);

        $detail->fill($request->only(['tag', 'title', 'description']));
        if ($request->has('tags')) $detail->tags = json_decode($request->tags, true);

        if ($request->hasFile('images')) {
            if ($detail->images) {
                foreach ($detail->images as $oldImg) {
                    Storage::disk('public')->delete($oldImg);
                }
            }
            $imagePaths = [];
            foreach ($request->file('images') as $img) {
                $imagePaths[] = $img->store('projects/amenities/sliders', 'public');
            }
            $detail->images = $imagePaths;
        }

        $detail->save();
        return response()->json(['status' => 'success', 'message' => 'Slider updated', 'data' => $detail]);
    }

    // 4. DELETE SLIDER DETAIL
    public function deleteSliderDetail($detailId)
    {
        $detail = AmenityDetail::findOrFail($detailId);

        if ($detail->images) {
            foreach ($detail->images as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        $detail->delete();
        return response()->json(['status' => 'success', 'message' => 'Slider deleted']);
    }

    // 5. GET ALL AMENITIES
    public function getAmenities($projectId)
    {
        $project = Project::with('amenities.details')->findOrFail($projectId);
        
        if (!$project->amenities) {
            return response()->json(['status' => 'error', 'message' => 'Amenities not found'], 404);
        }

        $data = $project->amenities->toArray();

        if (isset($data['amenities_list'])) {
            foreach ($data['amenities_list'] as &$item) {
                if (isset($item['icon'])) $item['icon_url'] = asset('storage/' . $item['icon']);
            }
        }

        if (isset($data['details'])) {
            foreach ($data['details'] as &$slider) {
                $fullImages = [];
                if ($slider['images']) {
                    foreach ($slider['images'] as $imgPath) {
                        $fullImages[] = asset('storage/' . $imgPath);
                    }
                }
                $slider['images_url'] = $fullImages;
            }
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
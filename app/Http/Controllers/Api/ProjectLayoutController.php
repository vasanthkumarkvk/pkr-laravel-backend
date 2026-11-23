<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectLayoutController extends Controller
{
    // 1. UPDATE HEADER & FILTERS
    public function updateHeader(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $header = $project->layoutHeader()->firstOrCreate([]);

        $request->validate([
            'eyebrow' => 'nullable|string',
            'heading' => 'nullable|string',
            'description' => 'nullable|string',
            'unit_type_groups' => 'nullable|json',
            'blocks' => 'nullable|json',
        ]);

        $header->fill($request->only(['eyebrow', 'heading', 'description']));
        
        if ($request->has('unit_type_groups')) $header->unit_type_groups = json_decode($request->unit_type_groups, true);
        if ($request->has('blocks')) $header->blocks = json_decode($request->blocks, true);

        $header->save();
        return response()->json(['status' => 'success', 'data' => $header]);
    }

    // 2. ADD LAYOUT
    public function addLayout(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $request->validate([
            'title' => 'required|string',
            'block_name' => 'nullable|string',
            'unit_type' => 'required|string',
            'facing' => 'required|string',
            'area' => 'required|string',
            'description' => 'nullable|string',
            'plan_image' => 'required|image|max:5120',
        ]);

        $path = $request->file('plan_image')->store('projects/layouts', 'public');

        $layout = $project->layouts()->create([
            'title' => $request->title,
            'block_name' => $request->block_name,
            'unit_type' => $request->unit_type,
            'facing' => $request->facing,
            'area' => $request->area,
            'description' => $request->description,
            'plan_image' => $path
        ]);

        return response()->json(['status' => 'success', 'message' => 'Layout added', 'data' => $layout]);
    }

    // 3. EDIT LAYOUT
    public function updateLayout(Request $request, $layoutId)
    {
        $layout = ProjectLayout::findOrFail($layoutId);

        $request->validate([
            'title' => 'nullable|string',
            'block_name' => 'nullable|string',
            'unit_type' => 'nullable|string',
            'facing' => 'nullable|string',
            'area' => 'nullable|string',
            'description' => 'nullable|string',
            'plan_image' => 'nullable|image|max:5120',
        ]);

        $layout->fill($request->only(['title', 'block_name', 'unit_type', 'facing', 'area', 'description']));

        if ($request->hasFile('plan_image')) {
            if ($layout->plan_image && Storage::disk('public')->exists($layout->plan_image)) {
                Storage::disk('public')->delete($layout->plan_image);
            }
            $layout->plan_image = $request->file('plan_image')->store('projects/layouts', 'public');
        }

        $layout->save();
        return response()->json(['status' => 'success', 'message' => 'Layout updated', 'data' => $layout]);
    }

    // 4. DELETE LAYOUT
    public function deleteLayout($layoutId)
    {
        $layout = ProjectLayout::findOrFail($layoutId);

        if ($layout->plan_image && Storage::disk('public')->exists($layout->plan_image)) {
            Storage::disk('public')->delete($layout->plan_image);
        }

        $layout->delete();
        return response()->json(['status' => 'success', 'message' => 'Layout deleted']);
    }

    // 5. GET FULL FLOOR PLANS DATA
    public function getFloorPlans($projectId)
    {
        $project = Project::with(['layoutHeader', 'layouts'])->findOrFail($projectId);
        
        $data = [
            'header' => $project->layoutHeader,
            'layouts' => $project->layouts->map(function($layout) {
                return [
                    'id' => $layout->id,
                    'title' => $layout->title,
                    'block_name' => $layout->block_name,
                    'unit_type' => $layout->unit_type,
                    'facing' => $layout->facing,
                    'area' => $layout->area,
                    'description' => $layout->description,
                    'plan_image_url' => asset('storage/' . $layout->plan_image)
                ];
            })
        ];

        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
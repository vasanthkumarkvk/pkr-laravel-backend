<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectCountdown;
use Illuminate\Http\Request;

class ProjectCountdownController extends Controller
{
    // 1. Create or Update Countdown (Since 1 project has 1 countdown)
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'completion_date' => 'required|date',
            'is_active' => 'nullable|boolean'
        ]);

        // Ithu already irundha update pannum, illa na puthusa create pannum
        $countdown = ProjectCountdown::updateOrCreate(
            ['project_id' => $request->project_id],
            [
                'completion_date' => $request->completion_date,
                'is_active' => $request->is_active ?? true
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Countdown details saved successfully',
            'data' => $countdown
        ], 200);
    }

    // 2. Update Status (Enable/Disable)
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['is_active' => 'required|boolean']);
        
        $countdown = ProjectCountdown::findOrFail($id);
        $countdown->is_active = $request->is_active;
        $countdown->save();

        return response()->json(['status' => true, 'message' => 'Status updated', 'data' => $countdown]);
    }

    // 3. Delete Countdown
    public function destroy($id)
    {
        $countdown = ProjectCountdown::findOrFail($id);
        $countdown->delete();

        return response()->json(['status' => true, 'message' => 'Countdown deleted successfully']);
    }
    
    // 4. Get Single Project Countdown (Admin Edit Form ku)
    public function show($projectId)
    {
        $countdown = ProjectCountdown::where('project_id', $projectId)->first();
        
        if(!$countdown) {
            return response()->json(['status' => false, 'message' => 'No countdown set for this project'], 404);
        }

        return response()->json(['status' => true, 'data' => $countdown]);
    }
}
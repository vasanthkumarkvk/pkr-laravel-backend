<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\ProjectCountdown;

class ProjectCountdownController extends Controller
{
    /**
     * Get Countdown Details for Frontend
     * URL: /api/projects/countdown/{project_id}
     */
    public function show($projectId)
    {
        // Antha project ku countdown set pannirukka nu check pandrom
        $countdown = ProjectCountdown::where('project_id', $projectId)->first();

        if (!$countdown) {
            return response()->json([
                'status' => false, 
                'message' => 'Countdown not set for this project'
            ], 404);
        }

        // Frontend ku date and status anuppurom
        return response()->json([
            'status' => true,
            'message' => 'Countdown fetched successfully',
            'data' => [
                'project_id' => $countdown->project_id,
                'completion_date' => $countdown->completion_date, // e.g., "2026-12-31 23:59:59"
                'is_active' => $countdown->is_active // true or false
            ]
        ], 200);
    }
}
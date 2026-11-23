<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectConnectivityController extends Controller
{
    // 1. UPDATE DETAILS & LOCATIONS LIST
    public function updateDetails(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $connectivity = $project->connectivity()->firstOrCreate([]);

        $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string',
            'map_link' => 'nullable|string',
            'locations' => 'nullable|json', // JSON string of array
        ]);

        $connectivity->fill($request->only(['title', 'description', 'button_text', 'map_link']));

        if ($request->has('locations')) {
            $connectivity->locations = json_decode($request->locations, true);
        }

        $connectivity->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'Connectivity details updated successfully', 
            'data' => $connectivity
        ]);
    }

    // 2. GET CONNECTIVITY DETAILS
    public function getDetails($projectId)
    {
        $project = Project::with('connectivity')->findOrFail($projectId);
        $connectivity = $project->connectivity;

        if (!$connectivity) {
            return response()->json(['status' => 'error', 'message' => 'Connectivity details not found'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $connectivity]);
    }



        // 3. EDIT A SPECIFIC LOCATION (Based on Index)
    public function updateLocation(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $connectivity = $project->connectivity()->firstOrCreate([]);

        $request->validate([
            'index' => 'required|integer', // JSON array la enna position (0, 1, 2)
            'location' => 'required|string',
            'time' => 'required|string',
        ]);

        $locations = $connectivity->locations ?? [];

        // Andha index la data irukka nu check pandrom
        if (!isset($locations[$request->index])) {
            return response()->json(['status' => 'error', 'message' => 'Location index not found'], 404);
        }

        // Specific index data ah update pandrom
        $locations[$request->index] = [
            'location' => $request->location,
            'time' => $request->time
        ];

        $connectivity->locations = $locations;
        $connectivity->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'Location updated successfully', 
            'data' => $connectivity->locations
        ]);
    }

    // 4. DELETE A SPECIFIC LOCATION
    public function deleteLocation(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $connectivity = $project->connectivity;

        if (!$connectivity) {
            return response()->json(['status' => 'error', 'message' => 'Connectivity data not found'], 404);
        }

        $index = $request->query('index'); // URL la ?index=0 nu varum

        if ($index === null || !is_numeric($index)) {
            return response()->json(['status' => 'error', 'message' => 'Valid index parameter is required'], 422);
        }

        $locations = $connectivity->locations ?? [];

        if (!isset($locations[$index])) {
            return response()->json(['status' => 'error', 'message' => 'Location not found at this index'], 404);
        }

        // Array la irundhu remove pandrom
        unset($locations[$index]);
        
        // Array ah re-index pandrom (0, 1, 2 nu correct ah varra maadhiri)
        $connectivity->locations = array_values($locations); 
        $connectivity->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'Location deleted successfully', 
            'data' => $connectivity->locations
        ]);
    }




        // 5. ADD NEW LOCATION (Ithu Frontend "Add Location" button click pannumbothu call aagum)
    public function addLocation(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $connectivity = $project->connectivity()->firstOrCreate([]);

        $request->validate([
            'location' => 'required|string',
            'time' => 'required|string',
        ]);

        // Pazhaya locations ah eduthukkirom (illa na empty array)
        $locations = $connectivity->locations ?? [];

        // Puthu location ah array oda last la add pandrom
        $locations[] = [
            'location' => $request->location,
            'time' => $request->time
        ];

        $connectivity->locations = $locations;
        $connectivity->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'Location added successfully', 
            'data' => $connectivity->locations
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\HomeContent;
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    /**
     * Get Home Details for Frontend (Public API)
     */
    public function index()
    {
        try {
            // Since it's a single homepage, we fetch the first record
            $homeDetails = HomeContent::first();

            // If no record exists, create a default empty one or return a 404
            if (!$homeDetails) {
                return response()->json([
                    'status' => false,
                    'message' => 'Home details not found. Please add data from admin.',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Home details fetched successfully',
                'data' => $homeDetails
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
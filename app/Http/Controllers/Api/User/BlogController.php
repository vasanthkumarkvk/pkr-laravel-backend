<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Blog;

class BlogController extends Controller
{
    // 1. Get All Blogs (For the grid you showed)
    public function index()
    {
        // Latest blogs first. Use paginate so page fast ah load aagum
        $blogs = Blog::select('id', 'title', 'category', 'short_description', 'image', 'read_time', 'created_at')
            ->latest()
            ->paginate(10); 

        return response()->json(['status' => true, 'data' => $blogs], 200);
    }

    // 2. Get Single Blog Details (For "Read More" page)
    public function show($id)
    {
        $blog = Blog::find($id);

        if (!$blog) return response()->json(['status' => false, 'message' => 'Blog not found'], 404);

        return response()->json(['status' => true, 'data' => $blog], 200);
    }
}
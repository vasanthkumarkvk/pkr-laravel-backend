<?php

namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{


    // 1. Get All Blogs (Admin Dashboard List)
    public function index()
    {
        // Pagination use panrom so dashboard fast ah load aagum (10 per page)
        $blogs = Blog::latest()->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Blogs fetched successfully',
            'data' => $blogs
        ], 200);
    }

    // 2. Get Single Blog (For Edit Form)
    public function show($id)
    {
        $blog = Blog::find($id);
        
        if (!$blog) {
            return response()->json(['status' => false, 'message' => 'Blog not found'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $blog
        ], 200);
    }
    // 1. Create Blog Post (JSON Format)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'short_description' => 'required|string',
            'full_description' => 'nullable|string', // New
            'content' => 'required|string',
            'image' => 'required|string', // Base64 or path
            'thumbnail_image' => 'nullable|string', // New (Base64 or path)
            'read_time' => 'nullable|string'
        ]);

        $data = $request->all();

        // Handle Main Image
        if ($request->has('image') && preg_match('/^data:image\/(\w+);base64,/', $request->image)) {
            $data['image'] = $this->saveBase64Image($request->image, 'blogs');
        }

        // Handle Thumbnail Image
        if ($request->has('thumbnail_image') && preg_match('/^data:image\/(\w+);base64,/', $request->thumbnail_image)) {
            $data['thumbnail_image'] = $this->saveBase64Image($request->thumbnail_image, 'blogs/thumbnails');
        }

        $blog = Blog::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Blog posted successfully',
            'data' => $blog
        ], 201);
    }

    // 2. Update Blog (JSON Format)
    public function update(Request $request, $id)
    {
        $blog = Blog::find($id);
        if (!$blog) return response()->json(['status' => false, 'message' => 'Blog not found'], 404);

        $data = $request->all();

        // Update Main Image
        if ($request->has('image') && preg_match('/^data:image\/(\w+);base64,/', $request->image)) {
            // Delete old
            if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                Storage::disk('public')->delete($blog->image);
            }
            // Save new
            $data['image'] = $this->saveBase64Image($request->image, 'blogs');
        }

        // Update Thumbnail Image
        if ($request->has('thumbnail_image') && preg_match('/^data:image\/(\w+);base64,/', $request->thumbnail_image)) {
            // Delete old
            if ($blog->thumbnail_image && Storage::disk('public')->exists($blog->thumbnail_image)) {
                Storage::disk('public')->delete($blog->thumbnail_image);
            }
            // Save new
            $data['thumbnail_image'] = $this->saveBase64Image($request->thumbnail_image, 'blogs/thumbnails');
        }

        $blog->update($data);

        return response()->json(['status' => true, 'message' => 'Blog updated', 'data' => $blog], 200);
    }

    // 3. Delete Blog
    public function destroy($id)
    {
        $blog = Blog::find($id);
        if (!$blog) return response()->json(['status' => false, 'message' => 'Blog not found'], 404);

        // Delete Main Image
        if ($blog->image && Storage::disk('public')->exists($blog->image)) {
            Storage::disk('public')->delete($blog->image);
        }

        // Delete Thumbnail Image
        if ($blog->thumbnail_image && Storage::disk('public')->exists($blog->thumbnail_image)) {
            Storage::disk('public')->delete($blog->thumbnail_image);
        }
        
        $blog->delete();
        return response()->json(['status' => true, 'message' => 'Blog deleted successfully'], 200);
    }

    // Helper Function to save Base64 Images
    private function saveBase64Image($base64String, $folder)
    {
        preg_match('/^data:image\/(\w+);base64,/', $base64String, $type);
        $extension = strtolower($type[1]);
        $image_parts = explode(";base64,", $base64String);
        $image_base64 = base64_decode($image_parts[1]);
        
        $fileName = Str::random(40) . '.' . $extension;
        $path = $folder . '/' . $fileName;
        
        Storage::disk('public')->put($path, $image_base64);
        return $path;
    }
}
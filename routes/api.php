<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ChannelPartnerController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectDetailController;
use App\Http\Controllers\Api\ProjectAmenityController;
use App\Http\Controllers\Api\ProjectGalleryController;
use App\Http\Controllers\Api\ProjectLayoutController;
use App\Http\Controllers\Api\ProjectPricingController;
use App\Http\Controllers\Api\ThreeSixtyController;
use App\Http\Controllers\Api\ProjectConnectivityController;
use App\Http\Controllers\Api\CtaBannerController;
use App\Http\Controllers\Api\BlogController as AdminBlogController;
use App\Http\Controllers\Api\ProjectCountdownController;







//user use case
use App\Http\Controllers\Api\User\BlogController as UserBlogController;
use App\Http\Controllers\Api\User\HomepageController;
use App\Http\Controllers\Api\User\ProjectController as UserProjectController;
use App\Http\Controllers\Api\User\ProjectCountdownController as UserProjectCountdownController;



        
        // --- Public Routes (Login thevai illa) ---
        Route::post('/admin/login', [AuthController::class, 'login']);
        // Home Details Frontend ku paakka (Public)
        Route::get('/home/details', [HomeController::class, 'getHomeDetails']);
        Route::post('/submit-enquiry', [FormController::class, 'submitEnquiry']);
        Route::post('/channel-partner/register', [ChannelPartnerController::class, 'register']);

Route::prefix('chatbot')->group(function () {
        Route::get('/init', [ChatbotController::class, 'init']);
        Route::post('/chat', [ChatbotController::class, 'chat']);
        Route::get('/history/{session_id}', [ChatbotController::class, 'history']);
        });

        // --- Protected Routes (Admin Login pannirukanum) ---
Route::middleware('auth:sanctum')->group(function () {

        // Logout
        Route::post('/admin/logout', [AuthController::class, 'logout']);

        // Home Page Admin Updates
Route::prefix('home')->group(function () {
        Route::post('/update-home', [HomeController::class, 'updateHomeDetails']);
        Route::post('/banners/add', [HomeController::class, 'addBanners']);
        Route::post('/banners/reorder', [HomeController::class, 'reorderBanners']);
        Route::delete('/banners/{index}', [HomeController::class, 'deleteBanner']);
        });

        Route::prefix('projects')->group(function () {
        // 1. Create Project (Empty ah)
        Route::post('/create', [ProjectController::class, 'createProject']);
        // 2. Get All Projects
        Route::get('/get-all', [ProjectController::class, 'getAllProjects']);
        // 3. Update Project Details (Name, Status)
        Route::post('/update/{id}', [ProjectController::class, 'updateProject']);
        // 4. Add/Update Project Banners (Separate function)
        Route::post('/add-banners/{id}', [ProjectController::class, 'addProjectBanners']);
        Route::post('/edit-banner/{id}', [ProjectController::class, 'updateProjectBanner']);
        Route::delete('/delete-banner/{id}', [ProjectController::class, 'deleteProjectBanner']);

        // Project Details Routes (Ithu puthu)
        Route::post('/firstdetail/update/{project_id}', [ProjectDetailController::class, 'updateDetails']);
        Route::get('/firstdetail/{project_id}', [ProjectDetailController::class, 'getDetails']);
        Route::get('/details/{id}', [ProjectController::class, 'getProjectDetails']);

        // Amenities Main Details
        Route::post('/amenities/main/{project_id}', [ProjectAmenityController::class, 'updateMainDetails']);
        Route::post('/amenities/slider/add/{project_id}', [ProjectAmenityController::class, 'addSliderDetail']);
        Route::post('/amenities/slider/update/{detail_id}', [ProjectAmenityController::class, 'updateSliderDetail']);
        Route::delete('/amenities/slider/delete/{detail_id}', [ProjectAmenityController::class, 'deleteSliderDetail']);
        Route::get('/amenities/{project_id}', [ProjectAmenityController::class, 'getAmenities']);

        // Gallery Routes
        Route::post('/gallery/title/{project_id}', [ProjectGalleryController::class, 'updateTitle']);
        Route::post('/gallery/add/{project_id}', [ProjectGalleryController::class, 'addImages']);
        Route::post('/gallery/update/{image_id}', [ProjectGalleryController::class, 'updateImage']);
        Route::delete('/gallery/delete/{image_id}', [ProjectGalleryController::class, 'deleteImage']);
        // GET Gallery (Public ah irukalam)
        Route::get('/gallery/{project_id}', [ProjectGalleryController::class, 'getGallery']);

        // Floor Plans Routes
        Route::post('/floor-plans/header/{project_id}', [ProjectLayoutController::class, 'updateHeader']);
        Route::post('/floor-plans/add/{project_id}', [ProjectLayoutController::class, 'addLayout']);
        Route::post('/floor-plans/update/{layout_id}', [ProjectLayoutController::class, 'updateLayout']);
        Route::delete('/floor-plans/delete/{layout_id}', [ProjectLayoutController::class, 'deleteLayout']);
        // GET Floor Plans (Public ah irukalam)
        Route::get('/floor-plans/{project_id}', [ProjectLayoutController::class, 'getFloorPlans']);

        // Pricing Routes
        Route::post('/pricing/header/{project_id}', [ProjectPricingController::class, 'updateHeader']);
        Route::post('/pricing/add/{project_id}', [ProjectPricingController::class, 'addPricing']);
        Route::post('/pricing/update/{pricing_id}', [ProjectPricingController::class, 'updatePricing']);
        Route::delete('/pricing/delete/{pricing_id}', [ProjectPricingController::class, 'deletePricing']);
        Route::post('/pricing/bulk-upload/{project_id}', [ProjectPricingController::class, 'bulkUpload']);
        Route::post('/pricing/offer-status/{project_id}', [ProjectPricingController::class, 'updateOfferStatus']);
        // GET Pricing Data (Public ah irukalam)
        Route::get('/pricing/{project_id}', [ProjectPricingController::class, 'getPricingData']);

        // 360 Degree Routes
        Route::post('/360/update/{project_id}', [ThreeSixtyController::class, 'updateDetails']); // Text + Images (First time)
        Route::get('/360/{project_id}', [ThreeSixtyController::class, 'getDetails']);
        // Ithu puthu routes (Edit & Delete specific images)
        Route::post('/360/edit-image/{project_id}', [ThreeSixtyController::class, 'updateImage']);
        Route::delete('/360/delete-image/{project_id}', [ThreeSixtyController::class, 'deleteImage']);

        // Connectivity Routes
        Route::post('/connectivity/update/{project_id}', [ProjectConnectivityController::class, 'updateDetails']);
        Route::get('/connectivity/{project_id}', [ProjectConnectivityController::class, 'getDetails']);
        Route::post('/connectivity/edit-location/{project_id}', [ProjectConnectivityController::class, 'updateLocation']);
        Route::delete('/connectivity/delete-location/{project_id}', [ProjectConnectivityController::class, 'deleteLocation']);
        // Ithu puthu route (Frontend Add button ku)
        Route::post('/connectivity/add-location/{project_id}', [ProjectConnectivityController::class, 'addLocation']);

        // CTA Banner Routes
        Route::post('/cta/update/{project_id}', [CtaBannerController::class, 'updateDetails']);
        Route::get('/cta/{project_id}', [CtaBannerController::class, 'getDetails']);
        // Image Specific Routes
        Route::post('/cta/edit-image/{project_id}', [CtaBannerController::class, 'updateImage']);
        Route::delete('/cta/delete-image/{project_id}', [CtaBannerController::class, 'deleteImage']);
        });

Route::prefix('blogs')->group(function () {
         // GET APIs for Admin Dashboard
        Route::get('/', [AdminBlogController::class, 'index']);             // Get All
        Route::get('/show/{id}', [AdminBlogController::class, 'show']);     // Get Single
        Route::post('/create', [AdminBlogController::class, 'store']);
        Route::post('/update/{id}', [AdminBlogController::class, 'update']);
        Route::delete('/delete/{id}', [AdminBlogController::class, 'destroy']);
    });


Route::prefix('projects/countdown')->group(function () {
        Route::post('/save', [ProjectCountdownController::class, 'store']);           // Create/Update
        Route::get('/{project_id}', [ProjectCountdownController::class, 'show']);     // Get
        Route::post('/status/{id}', [ProjectCountdownController::class, 'updateStatus']); // Enable/Disable
        Route::delete('/delete/{id}', [ProjectCountdownController::class, 'destroy']);    // Delete
    });


});





         // 👇 USE THE NEW CONTROLLER HERE
        Route::get('/home/details', [HomepageController::class, 'index']); 
         // 👇 Project Showcase Routes
Route::prefix('projects')->group(function () {
        Route::get('/list', [UserProjectController::class, 'index']);
        Route::get('/userside-details/{id}', [UserProjectController::class, 'show']);
        Route::get('/navbar', [UserProjectController::class, 'getNavbarProjects']); // 👈 Navbar API
        Route::get('/countdown/{project_id}', [UserProjectCountdownController::class, 'show']);

});

Route::prefix('blogs')->group(function () {
        Route::get('/list', [UserBlogController::class, 'index']);
        Route::get('/details/{id}', [UserBlogController::class, 'show']);
});

   
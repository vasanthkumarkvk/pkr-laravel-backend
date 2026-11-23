<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChannelPartner;
use App\Mail\ChannelPartnerMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ChannelPartnerController extends Controller
{
    /**
     * Channel Partner Registration.
     */
    public function register(Request $request)
    {
        // 1. Validate
        $validator = Validator::make($request->all(), [
            'full_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'email'      => 'required|email|max:255', 
            'profession' => 'nullable|string|max:100',
            'city'       => 'nullable|string|max:100',
            'about'      => 'nullable|string|max:1000', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // 2. Save to DB
            $partner = ChannelPartner::create([
                'full_name'  => $request->full_name,
                'phone'      => $request->phone,
                'email'      => $request->email,
                'profession' => $request->profession,
                'city'       => $request->city,
                'about'      => $request->about,
            ]);

            // 3. Send SMTP Email to admin
            $adminEmail = env('ADMIN_EMAIL', 'admin@pkrestates.com');
            Mail::to($adminEmail)->send(new ChannelPartnerMail($partner));

            // 4. Success response
            return response()->json([
                'status'  => true,
                'message' => 'Thank you for registering as a channel partner. Our team will contact you soon.',
                'data'    => $partner,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again later.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerDetail;
use App\Mail\CustomerEnquiryMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    public function submitEnquiry(Request $request)
    {
        // 1. Validate all details
        $validator = Validator::make($request->all(), [
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'phone'        => 'required|string|max:20',
            'inquiry_type' => 'required|string',
            'message'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // 2. Save the details to customer_details table
            $customer = CustomerDetail::create([
                'full_name'    => $request->full_name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'inquiry_type' => $request->inquiry_type,
                'message'      => $request->message,
            ]);

            // 3. Send SMTP email
            $adminEmail = env('ADMIN_EMAIL', 'admin@pkrestates.com');
            Mail::to($adminEmail)->send(new CustomerEnquiryMail($customer));

            // 4. Return response
            return response()->json([
                'status'  => true,
                'message' => 'Enquiry submitted successfully. We will get back to you within 24 hours.',
                'data'    => $customer
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again later.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
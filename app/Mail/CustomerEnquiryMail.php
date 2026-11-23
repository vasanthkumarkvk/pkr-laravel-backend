<?php

namespace App\Mail;

use App\Models\CustomerDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerEnquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;

    public function __construct(CustomerDetail $customer)
    {
        $this->customer = $customer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Customer Enquiry: ' . $this->customer->inquiry_type,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer_details',
        );
    }
}
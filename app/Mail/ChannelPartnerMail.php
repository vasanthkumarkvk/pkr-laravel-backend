<?php

namespace App\Mail;

use App\Models\ChannelPartner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChannelPartnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $partner;

    public function __construct(ChannelPartner $partner)
    {
        $this->partner = $partner;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Channel Partner Registration: ' . $this->partner->full_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.channel_partner',
        );
    }
}
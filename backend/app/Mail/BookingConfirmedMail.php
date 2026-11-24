<?php

namespace App\Mail;

use App\Models\Antrean;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Antrean $antrean;

    public function __construct(Antrean $antrean)
    {
        $this->antrean = $antrean;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Konfirmasi E-Tiket Janji Temu KLINIKU — ' . $this->antrean->antrean_nomor,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking_confirmed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

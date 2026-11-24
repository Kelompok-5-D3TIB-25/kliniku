<?php

namespace App\Mail;

use App\Models\Antrean;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QueueCalledMail extends Mailable
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
            subject: 'Panggilan Antrean KLINIKU — ' . $this->antrean->antrean_nomor . ' Dipanggil!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.queue_called',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

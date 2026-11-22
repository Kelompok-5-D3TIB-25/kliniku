<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;
    public string $otpCode;
    public int $expiresInMinutes;

    /**
     * Create a new message instance.
     */
    public function __construct(string $userName, string $otpCode, int $expiresInSeconds = 300)
    {
        $this->userName = $userName;
        $this->otpCode = $otpCode;
        $this->expiresInMinutes = (int) ceil($expiresInSeconds / 60);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[KLINIKU] Kode OTP Verifikasi Masuk: {$this->otpCode}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

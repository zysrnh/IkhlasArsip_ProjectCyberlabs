<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $filePath;
    public string $fileName;
    public string $fileSizeFormatted;
    public string $recipientName;
    public string $notes;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $filePath,
        string $fileName,
        string $fileSizeFormatted,
        string $recipientName = 'Super Admin',
        string $notes = ''
    ) {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->fileSizeFormatted = $fileSizeFormatted;
        $this->recipientName = $recipientName;
        $this->notes = $notes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Berkas Backup Database SQL - ' . $this->fileName . ' - ' . config('app.name', 'Ikhlas Solusi'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.database-backup',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (file_exists($this->filePath)) {
            return [
                Attachment::fromPath($this->filePath)
                    ->as($this->fileName)
                    ->withMime('application/sql'),
            ];
        }

        return [];
    }
}

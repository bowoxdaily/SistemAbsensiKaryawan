<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class BackupDatabaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $backupPath;
    public $backupSize;
    public $backupDate;

    /**
     * Create a new message instance.
     */
    public function __construct($backupPath, $backupSize, $backupDate)
    {
        $this->backupPath = $backupPath;
        $this->backupSize = $backupSize;
        $this->backupDate = $backupDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Backup Database Otomatis - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.backup-database',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Check if backup file exists and is not too large (max 25MB for email)
        if (file_exists($this->backupPath) && filesize($this->backupPath) < 25 * 1024 * 1024) {
            return [
                Attachment::fromPath($this->backupPath)
                    ->as(basename($this->backupPath))
                    ->withMime('application/sql'),
            ];
        }

        return [];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Training;

class TrainingCertificate extends Mailable
{
    use Queueable, SerializesModels;

    public $training;
    public $files; // <--- Changed from single $filePath to array $files

    // Constructor now accepts an array of file data
    public function __construct(Training $training, array $files)
    {
        $this->training = $training;
        $this->files = $files;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Certificate of Compliance - ' . $this->training->company_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.certificate',
        );
    }

    // Attach ALL files
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->files as $file) {
            $attachments[] = Attachment::fromPath($file['path'])
                ->as($file['name'])
                ->withMime($file['mime']);
        }

        return $attachments;
    }
}
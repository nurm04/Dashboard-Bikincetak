<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PenawaranMail extends Mailable
{
    use Queueable, SerializesModels;

    public $penawaran;
    public $pdfContent;

    public function __construct($penawaran, $pdfContent)
    {
        $this->penawaran = $penawaran;
        $this->pdfContent = $pdfContent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Surat Penawaran Bikin Cetak - ' . $this->penawaran->id_penawaran,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.penawaran-dokumen',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'Surat_Penawaran_'.$this->penawaran->id_penawaran.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pesan;
    public $totalTransfer;
    public $kodeUnik;
    public $rekening;

    /**
     * Create a new message instance.
     */
    public function __construct($pesan, $totalTransfer, $kodeUnik, $rekening)
    {
        $this->pesan = $pesan;
        $this->totalTransfer = $totalTransfer;
        $this->kodeUnik = $kodeUnik;
        $this->rekening = $rekening;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesanan Berhasil Dibuat - ' . $this->pesan->id_pesan,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.checkout_success',
        );
    }
}

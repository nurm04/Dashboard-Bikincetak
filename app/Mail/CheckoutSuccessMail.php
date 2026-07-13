<?php

namespace App\Mail;

use App\Models\Pesan;
use Illuminate\Mail\Mailable;

class CheckoutSuccessMail extends Mailable
{
    public $pesan;
    public $totalBayar;
    public $kodeUnik;
    public $rekening;

    public function __construct(
        Pesan $pesan,
        int $totalBayar,
        int $kodeUnik,
        array $rekening
    ) {
        $this->pesan = $pesan;
        $this->totalBayar = $totalBayar;
        $this->kodeUnik = $kodeUnik;
        $this->rekening = $rekening;
    }

    public function build()
    {
        return $this->subject(
            'Instruksi Pembayaran Pesanan ' . $this->pesan->id_pesan
        )->view(
            'emails.checkout-success'
        );
    }
}

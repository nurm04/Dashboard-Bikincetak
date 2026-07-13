<?php

namespace App\Mail;

use App\Models\Pesan;
use Illuminate\Mail\Mailable;

class OrderStatusMail extends Mailable
{
    public $pesan;
    public $status;

    public function __construct(
        Pesan $pesan,
        string $status
    ) {
        $this->pesan = $pesan;
        $this->status = $status;
    }

    public function build()
    {
        return $this->subject(
            'Update Status Pesanan ' .
            $this->pesan->id_pesan
        )->view(
            'emails.order-status'
        );
    }
}

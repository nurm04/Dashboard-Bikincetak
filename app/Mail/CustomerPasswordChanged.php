<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class CustomerPasswordChanged extends Mailable
{
    public $customer;
    public $password;

    public function __construct($customer, $password)
    {
        $this->customer = $customer;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Password Akun Anda Telah Diperbarui')
            ->view('emails.customer-password-changed');
    }
}

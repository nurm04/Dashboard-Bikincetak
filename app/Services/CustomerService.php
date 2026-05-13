<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public static function generateId(): string
    {
        $date = now()->format('Ymd');
        $lastCustomer = Customer::whereDate('created_at', now())->orderBy('id_customer', 'desc')->first();

        if ($lastCustomer) {
            $lastNumber = (int) substr($lastCustomer->id_customer, -3);
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return 'CUST-' . $date . '-' . $nextNumber;
    }
}

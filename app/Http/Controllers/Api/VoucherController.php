<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::where('is_active', true)
            ->where('berlaku_dari', '<=', now())
            ->where('berlaku_sampai', '>=', now())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar voucher aktif',
            'data' => $vouchers
        ]);
    }

    public function cekVoucher($kode)
    {
        $voucher = Voucher::where('kode_voucher', $kode)
            ->where('is_active', true)
            ->where('berlaku_dari', '<=', now())
            ->where('berlaku_sampai', '>=', now())
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher tidak ditemukan, sudah tidak aktif, atau kedaluwarsa.'
            ], 404);
        }

        if ($voucher->kuota_penggunaan !== null && $voucher->kuota_penggunaan <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Kuota penggunaan untuk voucher ini sudah habis.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher valid dan dapat digunakan.',
            'data' => $voucher
        ]);
    }
}

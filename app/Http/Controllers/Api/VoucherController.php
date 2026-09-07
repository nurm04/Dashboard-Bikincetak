<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        // Tangkap Role ID user yang sedang login di frontend Next.js
        $user = $request->user();
        $roleId = $user && $user->customer ? (string) $user->customer->id_role_customer : null;

        $vouchers = Voucher::where('is_active', true)
            ->where('berlaku_dari', '<=', now())
            ->where('berlaku_sampai', '>=', now())
            ->where(function($query) {
                $query->whereNull('kuota_penggunaan')
                      ->orWhere('kuota_penggunaan', '>', 0);
            })
            // 👇 FILTER ROLE: Jangan tampilkan jika role tidak sesuai
            ->where(function($query) use ($roleId) {
                // 1. Tampilkan voucher yang bersifat publik (tanpa batas role)
                $query->whereNull('role_customer_targets');

                // 2. ATAU tampilkan voucher yang di dalam array-nya terdapat role_id user
                if ($roleId) {
                    $query->orWhereJsonContains('role_customer_targets', $roleId);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar voucher aktif',
            'data' => $vouchers
        ]);
    }

    public function cekVoucher(Request $request, $kode) // 👈 Tambahkan Request $request
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

        // 👇 VALIDASI ROLE MUTLAK SAAT CEK KODE MANUAL 👇
        if (!empty($voucher->role_customer_targets)) {
            $user = $request->user();
            $roleId = $user && $user->customer ? (string) $user->customer->id_role_customer : null;

            if (!$roleId || !in_array($roleId, $voucher->role_customer_targets)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon maaf, voucher promo ini eksklusif dan tidak berlaku untuk level akun Anda.'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher valid dan dapat digunakan.',
            'data' => $voucher
        ]);
    }
}

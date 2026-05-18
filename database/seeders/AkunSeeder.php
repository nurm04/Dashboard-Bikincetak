<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akun;
use App\Services\AkunService;

class AkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar COA Standar Percetakan
        $akuns = [
            // ==========================================
            // KELOMPOK HARTA (Aset) - Saldo Normal: Debit
            // ==========================================
            ['nama_akun' => 'Kas Tunai', 'kategori' => 'harta', 'saldo_normal' => 'debit'],
            ['nama_akun' => 'Kas Bank (BCA/Mandiri/dll)', 'kategori' => 'harta', 'saldo_normal' => 'debit'],
            ['nama_akun' => 'Piutang Usaha (Customer)', 'kategori' => 'harta', 'saldo_normal' => 'debit'],
            ['nama_akun' => 'Persediaan Bahan Baku', 'kategori' => 'harta', 'saldo_normal' => 'debit'],
            ['nama_akun' => 'Aset Tetap (Mesin Cetak & Komputer)', 'kategori' => 'harta', 'saldo_normal' => 'debit'],

            // ==========================================
            // KELOMPOK KEWAJIBAN (Liabilitas) - Saldo Normal: Kredit
            // ==========================================
            ['nama_akun' => 'Hutang Usaha (Supplier Bahan)', 'kategori' => 'kewajiban', 'saldo_normal' => 'kredit'],
            ['nama_akun' => 'Hutang Gaji Karyawan', 'kategori' => 'kewajiban', 'saldo_normal' => 'kredit'],

            // ==========================================
            // KELOMPOK MODAL (Ekuitas) - Saldo Normal: Kredit
            // ==========================================
            ['nama_akun' => 'Modal Pemilik', 'kategori' => 'modal', 'saldo_normal' => 'kredit'],
            ['nama_akun' => 'Prive (Penarikan Pribadi)', 'kategori' => 'modal', 'saldo_normal' => 'debit'], // Prive itu pengecualian, dia ngurangin modal jadi debit
            ['nama_akun' => 'Laba Ditahan', 'kategori' => 'modal', 'saldo_normal' => 'kredit'],

            // ==========================================
            // KELOMPOK PENDAPATAN - Saldo Normal: Kredit
            // ==========================================
            ['nama_akun' => 'Pendapatan Jasa Percetakan', 'kategori' => 'pendapatan', 'saldo_normal' => 'kredit'],
            ['nama_akun' => 'Pendapatan Lain-lain', 'kategori' => 'pendapatan', 'saldo_normal' => 'kredit'],

            // ==========================================
            // KELOMPOK BEBAN (Biaya) - Saldo Normal: Debit
            // ==========================================
            ['nama_akun' => 'Harga Pokok Produksi (HPP)', 'kategori' => 'beban', 'saldo_normal' => 'debit'],
            ['nama_akun' => 'Beban Gaji & Upah', 'kategori' => 'beban', 'saldo_normal' => 'debit'],
            ['nama_akun' => 'Beban Listrik, Air & Internet', 'kategori' => 'beban', 'saldo_normal' => 'debit'],
            ['nama_akun' => 'Beban Pemeliharaan Mesin', 'kategori' => 'beban', 'saldo_normal' => 'debit'],
            ['nama_akun' => 'Beban Perlengkapan (Tinta, Lakban, dll)', 'kategori' => 'beban', 'saldo_normal' => 'debit'],
        ];

        foreach ($akuns as $item) {
            // Generate ID otomatis pakai Service yang Lu bikin
            $idAkun = AkunService::generateId($item['kategori']);

            Akun::create([
                'id_akun'      => $idAkun,
                'nama_akun'    => $item['nama_akun'],
                'kategori'     => $item['kategori'],
                'saldo_normal' => $item['saldo_normal'],
            ]);
        }
    }
}

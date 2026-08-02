<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_produk',
        'id_kategori',
        'nama_produk',
        'gambar',
        'is_active',
    ];

    protected $casts = [
        'gambar' => 'array',
    ];

    public function produkSku(): HasMany
    {
        return $this->hasMany(ProdukSku::class, 'id_produk', 'id_produk');
    }
    public function produkVarian(): HasMany
    {
        return $this->hasMany(ProdukVarian::class, 'id_produk', 'id_produk');
    }
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
    public function varians()
    {
        return $this->belongsToMany(
            Varian::class,
            'produk_varian',
            'id_produk',
            'id_varian'
        );
    }
    protected static function booted()
    {
        // Fungsi ini dipanggil setiap kali lu klik "Save/Update" di admin
        static::saved(function ($item) {
            self::clearNextjsCache($item->id_produk); // Sesuaikan 'id_produk' dengan nama kolom ID (primary key) lu
        });

        // Fungsi ini dipanggil setiap kali lu menghapus produk di admin
        static::deleted(function ($item) {
            self::clearNextjsCache($item->id_produk);
        });
    }

    private static function clearNextjsCache($idProduk)
    {
        try {
            // Tembak Next.js
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(5)
                ->post('https://127.0.0.1:3000/api/clear-cache', [
                    'secret' => 'rahasia-bikin-cetak',
                    'id_produk' => $idProduk
                ]);

            // Cek balasannya apa
            if ($response->successful()) {
                \Log::info("✅ SUKSES BERSIHKAN CACHE. Next.js bilang: " . $response->body());
            } else {
                \Log::error("❌ GAGAL BERSIHKAN CACHE. Status: " . $response->status() . " | Pesan: " . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error('⚠️ SERVER NEXT.JS DOWN / TIMEOUT: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\PesananItem;
use App\Models\PesananItemProduksi;
use App\Models\TagihanVendor;
use Carbon\Carbon;

class FileManageController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/FileManage/Index', [
            'desain_pesanan' => $this->getFilesFromStorage(
                'desain_pesanan',
                PesananItem::class,
                'file_desain->nilai',
                fn($item) => $item->id_pesan
            ),

            'nota_vendor' => $this->getFilesFromStorage(
                'nota_vendor',
                PesananItemProduksi::class,
                'file_nota',
                fn($item) => $item->pesananItem->id_pesan
            ),

            'bukti_bayar_vendor' => $this->getFilesFromStorage(
                'bukti_bayar_vendor',
                TagihanVendor::class,
                'bukti_bayar',
                fn($item) => $item->kode_tagihan ?? 'TAG-' . $item->id
            ),
        ]);
    }

    public function hapusMassal(Request $request)
    {
        $request->validate([
            'files_to_delete' => 'required|array',
        ]);

        foreach ($request->files_to_delete as $filePath) {
            // Hapus file fisik
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            // UBAH BAGIAN INI: Null-kan kolom di database jika referensinya masih ada
            if (str_starts_with($filePath, 'desain_pesanan/')) {
                PesananItem::where('file_desain', $filePath)->update(['file_desain' => null]);
            } elseif (str_starts_with($filePath, 'nota_vendor/')) {
                PesananItemProduksi::where('file_nota', $filePath)->update(['file_nota' => null]);
            } elseif (str_starts_with($filePath, 'bukti_bayar_vendor/')) {
                TagihanVendor::where('bukti_bayar', $filePath)->update(['bukti_bayar' => null]);
            }
        }

        return back()->with('success', count($request->files_to_delete) . ' File berhasil dihapus permanen!');
    }

    /**
     * Helper pintar untuk membaca folder fisik dan mencocokkannya ke database
     */
    private function getFilesFromStorage($folder, $modelClass, $dbColumn, $idFormatter)
    {
        // Pastikan foldernya eksis
        if (!Storage::disk('public')->exists($folder)) {
            return [];
        }

        // 1. Baca semua file fisik langsung dari VPS/Storage
        $files = Storage::disk('public')->files($folder);

        return collect($files)->map(function ($path) use ($modelClass, $dbColumn, $idFormatter) {

            // 2. Cari file tersebut di database
            $record = $modelClass::where($dbColumn, $path)->first();

            // 3. Logika penentuan ID Referensi dan Tanggal
            if ($record) {
                $idReferensi = $idFormatter($record);
                $updatedAt = $record->updated_at;
            } else {
                // JIKA FILE FISIK ADA TAPI DATA DB KOSONG/HAPUS
                $idReferensi = 'Data referensi sudah dihapus';
                // Ambil tanggal terakhir file dimodifikasi dari sistem operasi server
                $updatedAt = Carbon::createFromTimestamp(Storage::disk('public')->lastModified($path));
            }

            $sizeInBytes = Storage::disk('public')->size($path);

            return [
                'path' => $path,
                'name' => basename($path),
                'id_referensi' => $idReferensi,
                'updated_at' => $updatedAt,
                'size' => round($sizeInBytes / 1024, 2) . ' KB',
                'url' => Storage::url($path),
            ];
        })->values();
    }
}

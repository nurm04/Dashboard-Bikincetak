<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Staf;
use App\Models\HakAkses;
use Inertia\Inertia;

class CheckAccess
{
    public function handle(Request $request, Closure $next, $slugModul, $aksi = null): Response
    {
        $user = $request->user();

        if (is_null($aksi)) {
            $method = $request->route()->getActionMethod();

            $aksi = match($method) {
                'create', 'store' => 'tambah',
                'edit', 'update'  => 'ubah',
                'destroy'         => 'hapus',
                default           => 'buka',
            };
        }

        if ($user && $user->role === 'vendor' && $slugModul === 'produksi') {
            return $next($request);
        }

        if (!$user || $user->role !== 'staf') {
            return Inertia::render('Error/AccessDenied', ['modul' => $slugModul])->toResponse($request);
        }

        $staf = Staf::where('user_id', $user->id)->first();
        if (!$staf) {
            return Inertia::render('Error/AccessDenied', ['modul' => $slugModul])->toResponse($request);
        }

        $akses = HakAkses::where('id_role_staf', $staf->id_role_staf)
            ->whereHas('modul', function($q) use ($slugModul) {
                $q->where('slug', $slugModul);
            })->first();

        $punyaAkses = match($aksi) {
            'tambah' => (bool) ($akses->bisa_tambah ?? false),
            'ubah'   => (bool) ($akses->bisa_ubah ?? false),
            'hapus'  => (bool) ($akses->bisa_hapus ?? false),
            default  => (bool) ($akses->bisa_buka ?? false),
        };

        if (!$punyaAkses) {
            return Inertia::render('Error/AccessDenied', [
                'modul' => strtoupper(str_replace('-', ' ', $slugModul))
            ])->toResponse($request);
        }

        return $next($request);
    }
}

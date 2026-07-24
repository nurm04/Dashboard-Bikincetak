<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockVendor
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->role === 'vendor') {
            return redirect()->route('produksi.index')->with('error', 'Akses Ditolak! Anda hanya dapat mengakses menu Produksi.');
        }

        return $next($request);
    }
}

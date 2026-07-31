<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfilUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfilController extends Controller
{
    /**
     * Display the user's profil form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profil/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profil information.
     */
    public function update(ProfilUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        // Jika yang update vendor, paksa nama user pakai nama_vendor biar sinkron
        if ($request->user()->role === 'vendor' && $request->filled('nama_vendor')) {
            $request->user()->name = $request->nama_vendor;
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // --- TAMBAHAN KHUSUS VENDOR ---
        if ($request->user()->role === 'vendor' && $request->user()->vendor) {
            $request->user()->vendor->update([
                'nama_vendor' => $request->nama_vendor,
                'nama_pic' => $request->nama_pic,
                'no_hp' => $request->no_hp,
                'alamat_lengkap' => $request->alamat_lengkap,
                'nama_bank' => $request->nama_bank,
                'no_rekening' => $request->no_rekening,
                'atas_nama' => $request->atas_nama,
            ]);
        }
        // -------------------------------

        return Redirect::route('profil.edit')->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

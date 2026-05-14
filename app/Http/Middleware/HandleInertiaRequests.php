<?php

namespace App\Http\Middleware;

use App\Models\HakAkses;
use App\Models\Staf;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $permissions = [];

        if ($user) {
            $staf = Staf::where('user_id', $user->id)->first();

            if ($staf) {
                $permissions = HakAkses::with('modul')
                    ->where('id_role_staf', $staf->id_role_staf)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        if (!$item->modul) return [];
                        return [
                            $item->modul->slug => [
                                'buka'   => (bool)$item->bisa_buka,
                                'tambah' => (bool)$item->bisa_tambah,
                                'ubah'   => (bool)$item->bisa_ubah,
                                'hapus'  => (bool)$item->bisa_hapus,
                            ]
                        ];
                    })->all();
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'can'  => $permissions,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}

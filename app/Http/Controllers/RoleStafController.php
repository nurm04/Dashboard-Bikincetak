<?php

namespace App\Http\Controllers;

use App\Models\RoleStaf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleStafController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required|string|max:255|unique:role_staf,role',
        ]);

        $idRole = 'ROLE-STAF-' . strtoupper(Str::slug($request->role));

        if (RoleStaf::where('id_role_staf', $idRole)->exists()) {
            $idRole .= '-' . rand(100, 999);
        }

        RoleStaf::create([
            'id_role_staf' => $idRole,
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Role Staf baru berhasil ditambahkan!');
    }
}

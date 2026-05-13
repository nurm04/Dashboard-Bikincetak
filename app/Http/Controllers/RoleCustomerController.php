<?php

namespace App\Http\Controllers;

use App\Models\RoleCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleCustomerController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required|string|max:255|unique:role_customer,role',
        ]);

        $idRole = 'ROLE-' . strtoupper(Str::slug($request->role));

        if (RoleCustomer::where('id_role_customer', $idRole)->exists()) {
            $idRole .= '-' . rand(100, 999);
        }

        RoleCustomer::create([
            'id_role_customer' => $idRole,
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Level member baru berhasil ditambahkan!');
    }
}

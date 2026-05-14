<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Staf;
use App\Models\RoleStaf;
use App\Services\StafService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StafController extends Controller
{
    public function index()
    {
        return Inertia::render('Staf/Index', [
            'stafs' => Staf::with(['user', 'roleStaf'])->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Staf/Form', [
            'roles' => RoleStaf::all()
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Staf/Form', [
            'staf' => Staf::with('user')->findOrFail($id),
            'roles' => RoleStaf::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|min:8',
            'no_hp' => 'required|string|max:15',
            'id_role_staf' => 'required|exists:role_staf,id_role_staf',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staf',
        ]);

        Staf::create([
            'id_staf' => StafService::generateId(),
            'user_id' => $user->id,
            'no_hp' => $request->no_hp,
            'id_role_staf' => $request->id_role_staf,
        ]);

        return redirect()->route('staf.index')->with('success', 'Data staf berhasil didaftarkan.');
    }

    public function update(Request $request, $id)
    {
        $staf = Staf::findOrFail($id);
        $user = User::findOrFail($staf->user_id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'no_hp' => 'required|string|max:15',
            'id_role_staf' => 'required|exists:role_staf,id_role_staf',
            'password' => 'nullable|min:8',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $staf->update([
            'no_hp' => $request->no_hp,
            'id_role_staf' => $request->id_role_staf,
        ]);

        return redirect()->route('staf.index')->with('success', 'Data staf berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $staf = Staf::findOrFail($id);
        User::find($staf->user_id)->delete();

        return redirect()->back()->with('success', 'Data staf berhasil dihapus.');
    }
}

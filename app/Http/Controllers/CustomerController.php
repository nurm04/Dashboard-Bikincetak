<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use App\Models\Customer;
use App\Models\RoleCustomer;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        return Inertia::render('Customer/Index', [
            'customers' => Customer::with(['user', 'roleCustomer'])->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Customer/Form', [
            'roles' => RoleCustomer::all(),
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Customer/Form', [
            // Pastikan relasi di model namanya 'alamat' atau 'alamats'
            'customer' => Customer::with(['user', 'alamat'])->findOrFail($id),
            'roles' => RoleCustomer::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|min:8',
            'no_hp' => 'required|string|max:15',
            'id_role_customer' => 'required|exists:role_customer,id_role_customer',
            'alamats' => 'required|array|min:1',
            'alamats.*' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer',
            ]);

            $id_customer = CustomerService::generateId();
            Customer::create([
                'id_customer' => $id_customer,
                'user_id' => $user->id,
                'no_hp' => $request->no_hp,
                'id_role_customer' => $request->id_role_customer,
            ]);

            foreach ($request->alamats as $stringAlamat) {
                if (blank($stringAlamat)) continue;

                Alamat::create([
                    // Membuat kode alamat unik menggunakan tahun 2026 saat ini
                    'id_alamat' => 'ALM-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'id_customer' => $id_customer, // Tetap aman pakai string CUST-XXX
                    'alamat' => $stringAlamat,
                ]);
            }

            DB::commit();
            return redirect()->route('customer.index')->with('success', 'Customer berhasil didaftarkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mendaftarkan customer: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $user = User::findOrFail($customer->user_id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'no_hp' => 'required|string|max:15',
            'id_role_customer' => 'required|exists:role_customer,id_role_customer',
            'password' => 'nullable|min:8',
            'alamats' => 'required|array|min:1',
            'alamats.*' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            $customer->update([
                'no_hp' => $request->no_hp,
                'id_role_customer' => $request->id_role_customer,
            ]);

            // FIX UTAMA: Hapus alamat lama bersandarkan string 'id_customer' milik model Customer, bukan route $id murni
            Alamat::where('id_customer', $customer->id_customer)->delete();

            foreach ($request->alamats as $stringAlamat) {
                if (blank($stringAlamat)) continue;

                Alamat::create([
                    'id_alamat' => 'ALM-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'id_customer' => $customer->id_customer, // FIX: Gunakan kode unik string dari model
                    'alamat' => $stringAlamat,
                ]);
            }

            DB::commit();
            return redirect()->route('customer.index')->with('success', 'Data customer berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        User::find($customer->user_id)->delete();

        return redirect()->back()->with('success', 'Data customer berhasil dihapus.');
    }
}

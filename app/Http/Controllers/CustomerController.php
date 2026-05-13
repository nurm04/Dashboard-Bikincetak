<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\RoleCustomer;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'roles' => RoleCustomer::all()
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Customer/Form', [
            'customer' => Customer::with('user')->findOrFail($id),
            'roles' => RoleCustomer::all()
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
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
        ]);

        Customer::create([
            'id_customer' => CustomerService::generateId(),
            'user_id' => $user->id,
            'no_hp' => $request->no_hp,
            'id_role_customer' => $request->id_role_customer,
        ]);

        return redirect()->route('customer.index')->with('success', 'Customer berhasil didaftarkan.');
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
        ]);

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

        return redirect()->route('customer.index')->with('success', 'Data customer berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        User::find($customer->user_id)->delete();

        return redirect()->back()->with('success', 'Data customer berhasil dihapus.');
    }
}

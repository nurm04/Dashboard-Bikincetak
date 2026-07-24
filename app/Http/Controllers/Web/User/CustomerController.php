<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RoleCustomer;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Mail\CustomerPasswordChanged;
use Illuminate\Support\Facades\Mail;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $filterRole = $request->query('id_role_customer');

        $query = Customer::with(['user', 'roleCustomer']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_customer', 'like', "%{$search}%")
                ->orWhere('no_hp', 'like', "%{$search}%")
                ->orWhereHas('user', function($qUser) use ($search) {
                    $qUser->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        if (!empty($filterRole) && $filterRole !== 'semua') {
            $query->where('id_role_customer', $filterRole);
        }

        $customers = $query->latest()->get();

        $roles = RoleCustomer::all();

        return Inertia::render('Customer/Index', [
            'customers' => $customers,
            'roles' => $roles,
            'filters' => $request->only(['search', 'id_role_customer'])
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

    public function editPassword($id)
    {
        $customer = Customer::with('user')->findOrFail($id);

        return Inertia::render('Customer/Password', [
            'customer' => $customer
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

        try {
            DB::beginTransaction();

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

            DB::commit();

            return redirect()
                ->route('customer.index')
                ->with('success', 'Customer berhasil didaftarkan.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with(
                'error',
                'Gagal mendaftarkan customer: ' . $e->getMessage()
            );
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
        ]);

        try {
            DB::beginTransaction();

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $customer->update([
                'no_hp' => $request->no_hp,
                'id_role_customer' => $request->id_role_customer,
            ]);

            DB::commit();

            return redirect()
                ->route('customer.index')
                ->with('success', 'Data customer berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with(
                'error',
                'Gagal memperbarui data: ' . $e->getMessage()
            );
        }
    }

    public function updatePassword(Request $request, $id)
    {
        $customer = Customer::with('user')->findOrFail($id);

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        try {

            $plainPassword = $request->password;

            $customer->user->update([
                'password' => Hash::make($plainPassword)
            ]);

            Mail::to($customer->user->email)
                ->send(
                    new CustomerPasswordChanged(
                        $customer->user,
                        $plainPassword
                    )
                );

            return redirect()
                ->route('customer.index')
                ->with(
                    'success',
                    'Password berhasil diperbarui dan email telah dikirim.'
                );

        } catch (\Exception $e) {

            return back()->with(
                'error',
                'Gagal mengubah password: ' . $e->getMessage()
            );
        }
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        User::find($customer->user_id)->delete();

        return redirect()->back()->with('success', 'Data customer berhasil dihapus.');
    }
}

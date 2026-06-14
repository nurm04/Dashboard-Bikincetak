<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RoleCustomer;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|min:8',
            'no_hp' => 'required|string|max:15',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer',
            ]);

            $defaultRoleName = 'Reguler';
            $idRole = 'ROLE-CUST-' . strtoupper(Str::slug($defaultRoleName));

            $roleCustomer = RoleCustomer::firstOrCreate(
                ['id_role_customer' => $idRole],
                ['role' => $defaultRoleName]
            );
            $id_customer = CustomerService::generateId();
            Customer::create([
                'id_customer' => $id_customer,
                'user_id' => $user->id,
                'no_hp' => $request->no_hp,
                'id_role_customer' => $roleCustomer->id_role_customer,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user->load('customer')
                ],
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::with('customer')
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial tidak valid'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load('customer');

        return response()->json([
            'success' => true,
            'data' => $user,
            'token' => $token
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'no_hp' => 'required|string|max:15',
        ]);

        try {
            DB::beginTransaction();

            $user->update([
                'name' => $request->name,
            ]);

            $user->customer()->update([
                'no_hp' => $request->no_hp,
            ]);

            DB::commit();

            $user->load('customer');

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check(
            $request->old_password,
            $user->password
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        $user->update([
            'password' => Hash::make(
                $request->password
            )
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string',
            'password' => 'required|string',
        ], [
            'employee_id.required' => 'ID Karyawan wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Try login with employee_id
        $credentials = [
            'employee_id' => $request->employee_id,
            'password' => $request->password,
            'is_active' => true,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update FCM token if provided
            if ($request->filled('fcm_token')) {
                /** @var User $user */
                $user = Auth::user();
                $user->update(['fcm_token' => $request->fcm_token]);
            }

            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'employee_id' => 'ID Karyawan atau password salah.',
        ])->withInput($request->only('employee_id', 'remember'));
    }

    protected function redirectBasedOnRole(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->intended('/admin/dashboard');
        }
        return redirect()->intended('/employee/dashboard');
    }
}

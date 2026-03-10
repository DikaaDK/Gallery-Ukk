<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt([
            'Email' => $request->input('email'),
            'password' => $request->input('password'),
        ])) {
            $request->session()->regenerate();

            $role = strtolower((string) (Auth::user()->role ?? 'user'));
            if ($role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('gallery.preview');
        }

        return back()->withErrors(['email' => 'Email atau password tidak cocok'])->withInput();
    }

    public function register(Request $request){
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,Email',
            'password' => 'required|string|min:8',
            'nama_lengkap' => 'required|string|max:255',
            'alamat' => 'required|string',
        ]);

        $user = User::create([
            'Username' => $request->username,
            'Email' => $request->email,
            'Password' => Hash::make($request->password),
            'role' => 'user',
            'NamaLengkap' => $request->nama_lengkap,
            'Alamat' => $request->alamat,
        ]);


        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

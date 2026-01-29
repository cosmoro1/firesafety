<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show Login Page
    public function showLogin() {
        return view('login');
    }

    // Handle Login Logic
    public function login(Request $request) {
        // 1. Validate Input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Attempt Login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // 3. Redirect based on Role
            $role = Auth::user()->role;

            if ($role === 'admin') {
                return redirect('/dashboard');
            } elseif ($role === 'officer') {
                return redirect('/incidents');
            } elseif ($role === 'clerk') {
                return redirect('/documents');
            } else {
                // Trainees and others go to training
                return redirect('/training');
            }
        }

        // 4. Login Failed
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handle Logout
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
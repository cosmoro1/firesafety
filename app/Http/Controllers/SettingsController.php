<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index', [
            'user' => auth()->user()
        ]);
    }

    // 1. NEW: Update Personal Info (Name)
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            // Email is validated but usually kept read-only to prevent lockout, 
            // or you can add unique validation if you want to allow changes.
        ]);

        $request->user()->update([
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Profile information updated successfully!');
    }

    // 2. EXISTING: Update Password
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
}
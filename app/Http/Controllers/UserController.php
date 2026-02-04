<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    // 1. SHOW ALL USERS
    public function index()
    {
        // Get all users except the currently logged in one (optional)
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    // 2. STORE NEW USER
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Saves 'admin', 'fire_officer', or 'records_clerk'
        ]);

        return redirect()->back()->with('success', 'New ' . $request->role . ' account created successfully!');
    }
    
    // 3. DELETE USER (Optional utility)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'User account deleted.');
    }
}
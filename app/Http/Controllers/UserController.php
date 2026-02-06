<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password; // Required for strong password rules

class UserController extends Controller
{
    // 1. SHOW ALL USERS
    public function index()
    {
        // Fetch users and paginate for better performance
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    // 2. STORE NEW USER (With Strong Password Enforcement)
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role'     => ['required', 'string', 'in:admin,officer,clerk'],
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8)
                    ->mixedCase()  // Must have upper and lower case
                    ->numbers()    // Must have at least one number
                    ->symbols()    // Must have at least one special character (@, #, $, etc.)
            ],
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return redirect()->back()->with('success', 'New ' . ucfirst($request->role) . ' account created successfully!');
    }

    // 3. ADMIN RESET PASSWORD (Updated for the Key Icon action)
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // We return success and the specific user name to the UI
        return redirect()->back()->with('success', "Password for {$user->name} has been reset successfully!");
    }
    
    // 4. DELETE USER
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent Admin from deleting themselves
        if (auth()->id() == $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User account deleted successfully.');
    }
}
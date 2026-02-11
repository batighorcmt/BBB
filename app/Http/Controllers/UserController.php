<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => request()->only(['search']),
        ]);
    }

    /**
     * Update the specified user's status.
     */
    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user->update(['status' => $validated['status']]);

        return back()->with('success', 'User status updated successfully.');
    }

    /**
     * Reset the specified user's password.
     */
    public function resetPassword(Request $request, User $user)
    {
        // Default password or validated input
        $password = '123456'; 

        $user->update([
            'password' => Hash::make($password),
        ]);

        return back()->with('success', 'User password reset to default (123456).');
    }
}

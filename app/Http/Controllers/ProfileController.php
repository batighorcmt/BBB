<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return Inertia::render('ComingSoon', [
            'module' => 'Profile',
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        return redirect()->route('profile.edit')->with('status', 'Profile updated (placeholder)');
    }

    public function destroy(Request $request)
    {
        return redirect()->route('login');
    }
}

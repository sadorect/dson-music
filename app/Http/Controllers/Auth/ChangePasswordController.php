<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    /**
     * Show the forced password change form.
     */
    public function show(Request $request): View
    {
        return view('auth.change-password');
    }

    /**
     * Handle the forced password change.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();

        $user->update([
            'password'              => Hash::make($request->password),
            'password_changed_at'   => now(),
            'must_change_password'  => false,
        ]);

        $request->session()->flash('status', 'password-updated');

        // Redirect to intended destination or dashboard
        return redirect()->intended(route('dashboard'));
    }
}

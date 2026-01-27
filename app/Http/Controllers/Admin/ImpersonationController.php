<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class ImpersonationController extends Controller
{
    public function impersonate(User $user)
    {
        if (session()->has('impersonated_by')) {
            return redirect()->route('home')
                ->with('error', 'Already impersonating a user');
        }

        session()->put('impersonated_by', [
            'id' => auth()->id(),
            'role' => auth()->user()->role,
        ]);

        auth()->login($user);

        return redirect()->route('home')
            ->with('success', 'Now viewing as '.$user->name);
    }

    public function stopImpersonating()
    {
        if (! session()->has('impersonated_by')) {
            return redirect()->route('home');
        }

        $adminData = session()->pull('impersonated_by');
        $admin = User::find($adminData['id']);

        auth()->login($admin);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Returned to admin account');
    }
}

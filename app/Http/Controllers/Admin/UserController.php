<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Check if the admin has permission to manage users
        if (! can_admin('users')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to manage users.');
        }

        if ($request->filled('type')) {
            $query->where('user_type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // For super admins, show all users
        if (auth()->user()->isSuperAdmin()) {
            $users = User::with('artistProfile')
                ->orderBy('name')
                ->paginate(15);
        } else {
            // For delegated admins, hide super admin accounts
            $users = User::with('artistProfile')
                ->where(function ($query) {
                    $query->where('user_type', '!=', 'admin')
                        ->orWhere('is_super_admin', false);
                })
                ->orderBy('name')
                ->paginate(20);
        }
        // $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        // Check if the admin has permission to manage users
        if (! can_admin('users')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to manage users.');
        }

        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        // Check if the admin has permission to manage users
        if (! can_admin('users')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to manage users.');
        }

        // Prevent delegated admins from creating admin users
        if (
            $request->has('user_type') &&
            $request->input('user_type') === 'admin' &&
            ! auth()->user()->isSuperAdmin()
        ) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Only super admins can create admin accounts.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'user_type' => 'required|in:user,artist,admin',
            'status' => 'required|in:active,suspended',
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        // Check if the admin has permission to manage users
        if (! can_admin('users')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to manage users.');
        }

        // Prevent delegated admins from editing super admins
        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to edit super admin accounts.');
        }

        // Continue with the edit logic
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Check if the admin has permission to manage users
        if (! can_admin('users')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to manage users.');
        }

        // Prevent delegated admins from updating super admins
        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to update super admin accounts.');
        }

        // Prevent changing user type to admin if not a super admin
        if (
            $request->has('user_type') &&
            $request->input('user_type') === 'admin' &&
            ! auth()->user()->isSuperAdmin()
        ) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Only super admins can create or modify admin accounts.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'user_type' => 'required|in:user,artist,admin',
            'status' => 'required|in:active,suspended',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        // Check if the admin has permission to manage users
        if (! can_admin('users')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to manage users.');
        }

        // Prevent delegated admins from deleting super admins
        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to delete super admin accounts.');
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }
        // Continue with the delete logic
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }

    public function suspend(User $user)
    {
        // Check if the admin has permission to manage users
        if (! can_admin('users')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to manage users.');
        }

        // Prevent delegated admins from suspending super admins
        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to suspend super admin accounts.');
        }
        // Prevent suspending yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot suspend your own account.');
        }
        // Continue with the suspend logic

        $user->update(['status' => 'suspended']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User suspended successfully');
    }

    public function show(User $user)
    {
        // Eager load relationships to prevent N+1 queries
        $user->load([
            'artistProfile',
            'activities' => fn ($query) => $query->latest()->take(5),
        ]);

        // Get the count of tracks if user has an artist profile
        $tracksCount = $user->artistProfile?->tracks()->count() ?? 0;

        return view('admin.users.show', [
            'user' => $user,
            'tracks_count' => $tracksCount,
        ]);
    }
}

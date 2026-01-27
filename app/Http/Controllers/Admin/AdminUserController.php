<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    private function checkSuperAdmin()
    {
        if (! auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to manage admin users.');
        }

        return null;
    }

    public function index()
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }
        $admins = User::where('user_type', 'admin')
            ->orderBy('name')
            ->get();

        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }

        $permissions = $this->getAvailablePermissions();

        return view('admin.admins.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_super_admin' => 'boolean',
            'permissions' => 'nullable|array',
        ]);

        $user = new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->user_type = 'admin';
        $user->is_super_admin = $request->has('is_super_admin');

        // Only store permissions for delegated admins
        if (! $user->is_super_admin && isset($validated['permissions'])) {
            $user->admin_permissions = $validated['permissions'];
        }

        $user->save();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin user created successfully.');
    }

    public function edit(User $admin)
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }
        // Make sure we're editing an admin
        if ($admin->user_type !== 'admin') {
            return redirect()->route('admin.admins.index')
                ->with('error', 'User is not an admin.');
        }

        $permissions = $this->getAvailablePermissions();

        return view('admin.admins.edit', compact('admin', 'permissions'));
    }

    public function update(Request $request, User $admin)
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($admin->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'is_super_admin' => 'boolean',
            'permissions' => 'nullable|array',
        ]);

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];

        if (! empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->is_super_admin = $request->has('is_super_admin');

        // Update permissions for delegated admins
        if (! $admin->is_super_admin && isset($validated['permissions'])) {
            $admin->admin_permissions = $validated['permissions'];
        } else {
            $admin->admin_permissions = null;
        }

        $admin->save();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin user updated successfully.');
    }

    public function destroy(User $admin)
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }
        // Prevent deleting yourself
        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin user deleted successfully.');
    }

    private function getAvailablePermissions()
    {
        // Define available permissions for delegated admins
        return [
            'users' => 'Manage Users',
            'tracks' => 'Manage Tracks',
            'artists' => 'Manage Artists',
            'playlists' => 'Manage Playlists',
            'comments' => 'Manage Comments',
            'reports' => 'View Reports',
            'settings' => 'Manage Settings',
        ];
    }
}

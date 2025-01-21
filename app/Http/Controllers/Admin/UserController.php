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
      
      if ($request->filled('type')) {
          $query->where('user_type', $request->type);
      }
      
      if ($request->filled('search')) {
          $query->where(function($q) use ($request) {
              $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
          });
      }
      
      $users = $query->latest()->paginate(20);
      
      return view('admin.users.index', compact('users'));
  }
  
  

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'user_type' => 'required|in:user,artist,admin',
            'status' => 'required|in:active,suspended'
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'user_type' => 'required|in:user,artist,admin',
            'status' => 'required|in:active,suspended'
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }

    public function suspend(User $user)
    {
        $user->update(['status' => 'suspended']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User suspended successfully');
    }

    public function show(User $user)
    {
        // Eager load relationships to prevent N+1 queries
        $user->load([
            'artistProfile',
            'activities' => fn($query) => $query->latest()->take(5),
        ]);

        // Get the count of tracks if user has an artist profile
        $tracksCount = $user->artistProfile?->tracks()->count() ?? 0;
        
        return view('admin.users.show', [
            'user' => $user,
            'tracks_count' => $tracksCount
        ]);
    }
    
}

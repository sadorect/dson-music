@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header Section -->
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">User Details</h1>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <!-- User Information -->
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Name</h3>
                            <p class="text-lg text-gray-900">{{ $user->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Email</h3>
                            <p class="text-lg text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Joined Date</h3>
                            <p class="text-lg text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Login</h3>
                            <p class="text-lg text-gray-900">
                                {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Role</h3>
                            <p class="text-lg text-gray-900">{{ ucfirst($user->role) }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Artist Profile</h3>
                            @if($user->artistProfile)
                                <a href="{{ route('admin.artists.show', $user->artistProfile) }}" 
                                   class="text-blue-600 hover:text-blue-800">
                                    View Artist Profile
                                </a>
                            @else
                                <p class="text-gray-500">No artist profile</p>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Tracks</h3>
                            <p class="text-lg text-gray-900">{{ $user->tracks_count ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end space-x-4">
                    <button onclick="toggleUserStatus({{ $user->id }})" 
                            class="px-4 py-2 {{ $user->is_active ? 'bg-red-600' : 'bg-green-600' }} text-white rounded-md hover:opacity-90">
                        {{ $user->is_active ? 'Deactivate User' : 'Activate User' }}
                    </button>
                    
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Edit User
                    </a>

                    @if(auth()->user()->can('impersonate'))
                        <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                                Impersonate User
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Activity Log Section -->
            <div class="border-t">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
                    <div class="space-y-4">
                        @forelse($user->activities()->latest()->take(5)->get() as $activity)
                            <div class="flex items-center justify-between py-2">
                                <span class="text-gray-600">{{ $activity->description }}</span>
                                <span class="text-sm text-gray-500">
                                    {{ $activity->created_at->diffForHumans() }}
                                </span>
                            </div>
                        @empty
                            <p class="text-gray-500">No recent activity</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleUserStatus(userId) {
    // Add your AJAX call here to toggle user status
    if (confirm('Are you sure you want to change this user\'s status?')) {
        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }
}
</script>
@endpush
@endsection

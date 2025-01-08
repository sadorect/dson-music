@extends('layouts.artist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Artist Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Profile Overview -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Profile Overview</h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-gray-600">Artist Name</label>
                        <p class="font-medium">{{ $artist->artist_name }}</p>
                    </div>
                    <div>
                        <label class="text-gray-600">Genre</label>
                        <p class="font-medium">{{ $artist->genre }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Statistics</h2>
                <!-- Add statistics here -->
            </div>

            <!-- Recent Activity -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
                <!-- Add activity feed here -->
            </div>
        </div>
    </div>
</div>
@endsection

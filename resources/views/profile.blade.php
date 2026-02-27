@extends('layouts.glass-app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10 space-y-6">

    <h1 class="text-2xl font-bold text-gray-800">Account Settings</h1>

    {{-- Profile Info --}}
    <div class="glass-card rounded-2xl p-6 sm:p-8">
        <livewire:profile.update-profile-information-form />
    </div>

    {{-- Change Password --}}
    <div class="glass-card rounded-2xl p-6 sm:p-8">
        <livewire:profile.update-password-form />
    </div>

    {{-- Delete Account --}}
    <div class="glass-card rounded-2xl p-6 sm:p-8 border border-red-100">
        <livewire:profile.delete-user-form />
    </div>

</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="">
    <div class="flex items-end gap-4 p-4 bg-black/10">
        <div class="w-48 h-48 rounded-lg overflow-hidden shadow-md mb-2">
            <img src="#"
                alt="#"
                onerror="this.style.display='none';
                    this.parentElement.style.background='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }})';
                    this.parentElement.style.backgroundImage='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }})'">
        </div>
        <div>
            <p class="text-white text-sm ">Single</p>
            <h1 class="text-7xl font-bold mb-4 text-white">Song Title</h1>
            <div class="flex gap-4 items-center">
                <p class="text-white text-sm font-bold">Artist Name</p>
                <p class="text-white text-sm font-bold">Date</p>
                <p class="text-white/30 text-sm">10,000,000 plays</p>
                <p class="text-white/30 text-sm">1:37</p>
            </div>
        </div>
    </div>
    <p class="text-gray-600 mb-4">Artist Name</p>
    <img src="https://via.placeholder.com/600x400" alt="Song Image" class="mb-4">
    <p class="mb-4">Song description goes here...</p>
</div>
@endsection
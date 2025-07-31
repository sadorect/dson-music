@extends('layouts.app')

@section('content')
<div class="">
    <div class="flex items-end gap-4 p-8 bg-black/10 ">
        <div class="w-48 h-48 rounded-lg overflow-hidden shadow-md mb-2">
            <img src="#"
                alt="#"
                onerror="this.style.display='none';
                    this.parentElement.style.background='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }})';
                    this.parentElement.style.backgroundImage='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }})'">
        </div>
        <div>
            <p class="text-white text-sm font-bold">Single</p>
            <h1 class="text-7xl font-bold mb-4 text-white">Song Title</h1>
            <div class="flex gap-4 items-center">
                <p class="text-white text-sm font-bold">Artist Name</p>
                <p class="text-white text-sm font-bold">Date</p>
                <p class="text-white/30 text-sm">10,000,000 plays</p>
                <p class="text-white/30 text-sm">2 Minutes 53 seconds</p>
            </div>
        </div>
    </div>

    <div class="flex gap-4 items-center justify-between p-4">
        <div class="flex gap-4 items-center">
            <button class="bg-primary-color rounded-full p-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="primary-color lucide lucide-circle-play-icon lucide-circle-play">
                    <path d="M9 9.003a1 1 0 0 1 1.517-.859l4.997 2.997a1 1 0 0 1 0 1.718l-4.997 2.997A1 1 0 0 1 9 14.996z" />
                    <circle cx="12" cy="12" r="10" />
                </svg>
            </button>

            <button>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class=" text-white/50 lucide lucide-circle-plus-icon lucide-circle-plus">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M8 12h8" />
                    <path d="M12 8v8" />
                </svg>
            </button>

            <button>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class=" text-white/50 lucide lucide-ellipsis-vertical-icon lucide-ellipsis-vertical">
                    <circle cx="12" cy="12" r="1" />
                    <circle cx="12" cy="5" r="1" />
                    <circle cx="12" cy="19" r="1" />
                </svg>
            </button>
        </div>

        <div>
            <button class="flex items-center gap-2 text-white/50">
                List
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-collapse-icon lucide-list-collapse">
                    <path d="M10 12h11" />
                    <path d="M10 18h11" />
                    <path d="M10 6h11" />
                    <path d="m3 10 3-3-3-3" />
                    <path d="m3 20 3-3-3-3" />
                </svg>

            </button>
        </div>
    </div>

    <div>
        <div class="flex gap-4 items-center p-4 border-b border-white/50">
            <div class="flex items-center justify-center w-1/12 ">
                <h1 class="text-white/50">#</h1>
            </div>
            <div class="w-10/12">
                <h1 class="text-white/50">Title</h1>
            </div>
            <div class="w-1/12 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white/50 lucide lucide-clock3-icon lucide-clock-3">
                    <path d="M12 6v6h4" />
                    <circle cx="12" cy="12" r="10" />
                </svg>
            </div>
        </div>

        <div class="flex gap-4 items-center p-4 hover:bg-white/20 rounded-md">
            <div class="flex items-center justify-center w-1/12 ">
                <h1 class="text-white/50">1</h1>
            </div>
            <div class="w-10/12">
                <h1 class="text-white">Track Title</h1>
                <p class="text-white/50 text-sm">Artist Name</p>
            </div>
            <div class="w-1/12 flex items-center justify-center">
                <p class="text-white/50 text-sm">2:53</p>
            </div>
        </div>


    </div>


    <div class="p-8">
        <p class="text-white/80 text-sm font-medium">August 22, 2022</p>
        <p class="text-white/50 text-sm ">Lorem ipsum dolor sit amet consectetur adipisicing elit. Sed ad repellat eius animi, libero sapiente a necessitatibus sint inventore quis possimus deleniti ratione, numquam non ducimus, quam dicta rem magni.</p>
    </div>


    <div class="px-4 py-8">
       
        <x-home.trending title="More by Artists" />
        
    </div>

</div>
@endsection
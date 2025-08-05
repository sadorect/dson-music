@extends('layouts.app')

@section('content')

<div class="w-full">
    <div class="relative h-[300px] w-full ">
        <img src="https://media.istockphoto.com/id/994280546/photo/passionate-singer-playing-the-guitar-and-recording-song-in-studio.jpg?s=612x612&w=0&k=20&c=MvAY7l1ZVL8RhQNIsoj4BD-GuWqOOjF411eW2LOMmGU=" alt="" class="w-full h-full object-cover">

        <div class="flex flex-col gap-2 absolute p-8 bottom-0 left-0 ">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class=" text-white lucide lucide-badge-check-icon lucide-badge-check">
                    <path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
                <p class="text-white">Verified Arist</p>

            </div>
            <h1 class="text-7xl font-bold text-white">Artists Name</h1>
            <p class="text-white/50">2,434,787,000 monthly listeners</p>
        </div>
    </div>

    <div class="flex gap-4 items-center  p-4">
        <div class="flex gap-4 items-center">
            <button class="bg-primary-color rounded-full p-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="primary-color lucide lucide-circle-play-icon lucide-circle-play">
                    <path d="M9 9.003a1 1 0 0 1 1.517-.859l4.997 2.997a1 1 0 0 1 0 1.718l-4.997 2.997A1 1 0 0 1 9 14.996z" />
                    <circle cx="12" cy="12" r="10" />
                </svg>
            </button>

            <button class="text-white/50 text-sm border border-white/50 rounded-full px-4 py-2">
                Follow
            </button>

            <button>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class=" text-white/50 lucide lucide-ellipsis-vertical-icon lucide-ellipsis-vertical">
                    <circle cx="12" cy="12" r="1" />
                    <circle cx="12" cy="5" r="1" />
                    <circle cx="12" cy="19" r="1" />
                </svg>
            </button>
        </div>


    </div>

    <div class="p-4">
        <h1 class="font-bold text-white text-lg">Popular</h1>


        <div class="flex gap-4 items-center p-2 hover:bg-white/20 rounded-md">
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
        <div class="flex gap-4 items-center p-2 hover:bg-white/20 rounded-md">
            <div class="flex items-center justify-center w-1/12 ">
                <h1 class="text-white/50">2</h1>
            </div>
            <div class="w-10/12">
                <h1 class="text-white">Track Title 2</h1>
                <p class="text-white/50 text-sm">Artist Name</p>
            </div>
            <div class="w-1/12 flex items-center justify-center">
                <p class="text-white/50 text-sm">2:53</p>
            </div>
        </div>
        <div class="flex gap-4 items-center p-2 hover:bg-white/20 rounded-md">
            <div class="flex items-center justify-center w-1/12 ">
                <h1 class="text-white/50">3</h1>
            </div>
            <div class="w-10/12">
                <h1 class="text-white">Track Title 3</h1>
                <p class="text-white/50 text-sm">Artist Name</p>
            </div>
            <div class="w-1/12 flex items-center justify-center">
                <p class="text-white/50 text-sm">2:53</p>
            </div>
        </div>

        <button class="text-white/50 text-sm">see more</button>



    </div>

    <div class="p-4">

        <div class="w-full">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-2xl font-bold text-white">Discography</h2>
                <a href="#" class="text-sm text-gray-400 hover:text-white">Show all</a>
            </div>


            <div>
                <button class="text-black text-sm border border-white/50 rounded-full px-4 py-2 bg-white/50">Popular releases</button>
                <button class="text-white/50 text-sm border border-white/50 rounded-full px-4 py-2">Albums</button>
                <button class="text-white/50 text-sm border border-white/50 rounded-full px-4 py-2">Singles & EPs</button>
                <button class="text-white/50 text-sm border border-white/50 rounded-full px-4 py-2">Mixtapes</button>
            </div>

            <div class="flex gap-2 overflow-x-scroll scroll-smooth w-full [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">
                <x-song-card
                    title="Gold"
                    artist="J Hus, Asake"
                    image="https://link-to-image/gold.jpg" />

                <x-song-card
                    title="Ewo"
                    artist="Famous Pluto, Shallipopi, Zerrydl"
                    image="https://link-to-image/ewo.jpg" />

                <x-song-card
                    title="Don't Let Me Drown"
                    artist="Burna Boy, F1 The Album"
                    image="https://link-to-image/drown.jpg" />

                <x-song-card
                    title="Gang"
                    artist="Ayo Maff, Seyi Vibez"
                    image="https://link-to-image/gang.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />


                <x-song-card
                    title="Gang"
                    artist="Ayo Maff, Seyi Vibez"
                    image="https://link-to-image/gang.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />
            </div>
        </div>

    </div>

    <div class="p-4">

        <div class="w-full">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-2xl font-bold text-white">Featuring the artist</h2>
                <a href="#" class="text-sm text-gray-400 hover:text-white">Show all</a>
            </div>




            <div class="flex gap-2 overflow-x-scroll scroll-smooth w-full [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">
                <x-song-card
                    title="Gold"
                    artist="J Hus, Asake"
                    image="https://link-to-image/gold.jpg" />

                <x-song-card
                    title="Ewo"
                    artist="Famous Pluto, Shallipopi, Zerrydl"
                    image="https://link-to-image/ewo.jpg" />

                <x-song-card
                    title="Don't Let Me Drown"
                    artist="Burna Boy, F1 The Album"
                    image="https://link-to-image/drown.jpg" />

                <x-song-card
                    title="Gang"
                    artist="Ayo Maff, Seyi Vibez"
                    image="https://link-to-image/gang.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />


                <x-song-card
                    title="Gang"
                    artist="Ayo Maff, Seyi Vibez"
                    image="https://link-to-image/gang.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />
            </div>
        </div>

    </div>

    <div class="p-4">
        <h1 class="text-2xl font-bold text-white">About the artist</h1>
        <div class="relative h-[300px] w-full rounded-lg py-4">
            <img src="https://media.istockphoto.com/id/994280546/photo/passionate-singer-playing-the-guitar-and-recording-song-in-studio.jpg?s=612x612&w=0&k=20&c=MvAY7l1ZVL8RhQNIsoj4BD-GuWqOOjF411eW2LOMmGU=" alt="" class="w-full h-full object-cover">

            <div class="flex flex-col gap-2 absolute p-8 bottom-0 left-0 ">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class=" text-white lucide lucide-badge-check-icon lucide-badge-check">
                        <path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                    <p class="text-white">2,434,787,000 monthly listeners</p>

                </div>

                <p class="text-white/50 text-sm">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Incidunt aspernatur, accusamus ea mollitia, ipsa dicta pariatur architecto voluptate labore illo recusandae officiis vel porro, dolorum cum temporibus unde? Totam quos laudantium error. Incidunt molestias eveniet ducimus quaerat laboriosam doloribus iure ea labore dolorem, corrupti quam ratione, dolorum, ullam nesciunt nulla!</p>
            </div>
        </div>
    </div>


    <div class="px-4 py-8">
    <x-home.popular-artists title="Fans also like" />
    </div>


    <div class="px-4 py-8">

        <div class="w-full">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-2xl font-bold text-white">Appears on</h2>
                <a href="#" class="text-sm text-gray-400 hover:text-white">Show all</a>
            </div>




            <div class="flex gap-2 overflow-x-scroll scroll-smooth w-full [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">
                <x-song-card
                    title="Gold"
                    artist="J Hus, Asake"
                    image="https://link-to-image/gold.jpg" />

                <x-song-card
                    title="Ewo"
                    artist="Famous Pluto, Shallipopi, Zerrydl"
                    image="https://link-to-image/ewo.jpg" />

                <x-song-card
                    title="Don't Let Me Drown"
                    artist="Burna Boy, F1 The Album"
                    image="https://link-to-image/drown.jpg" />

                <x-song-card
                    title="Gang"
                    artist="Ayo Maff, Seyi Vibez"
                    image="https://link-to-image/gang.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />


                <x-song-card
                    title="Gang"
                    artist="Ayo Maff, Seyi Vibez"
                    image="https://link-to-image/gang.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />

                <x-song-card
                    title="Lighter"
                    artist="A7S, David Guetta, Wizkid"
                    image="https://link-to-image/lighter.jpg" />
            </div>
        </div>

    </div>
</div>






@endsection
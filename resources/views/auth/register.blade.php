<x-guest-layout>
    <div class="text-center mb-8">
        <!-- HEADING -->
         <h1 class="text-white text-6xl text-center font-extrabold"> Sign Up to start listening  </h1>
        <p class="mt-2 text-white/50">Start your musical journey today</p>
    </div>

    @if (session('error'))
        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
            <p class="font-medium">Oops!</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
            <p class="font-medium">Success!</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-6" id="registerForm">
        @csrf

        <!-- Name -->
        <div class="flex flex-col gap-2">
            <label for="name" class="block font-medium text-sm text-white ">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" 
                   class="border-white/30 focus:border-primary-color focus:ring-primary-color rounded-md shadow-sm bg-transparent text-white @error('name') border-red-500 @enderror"
                   placeholder="Enter your full name">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="flex flex-col gap-2">
            <label for="email" class="block font-medium text-sm text-white ">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="autofill:fill-transparent border-white/30 focus:border-primary-color focus:ring-primary-color rounded-md shadow-sm bg-transparent text-white @error('email') border-red-500 @enderror"
                   placeholder="your.email@example.com">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="flex flex-col gap-2">
            <label for="password" class="block font-medium text-sm text-white ">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="border-white/30 focus:border-primary-color  focus:ring-primary-color rounded-md shadow-sm bg-transparent text-white @error('password') border-red-500 @enderror"
                   placeholder="Create a strong password">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="flex flex-col gap-2">
            <label for="password_confirmation" class="block font-medium text-sm text-white ">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="border-white/30 focus:border-primary-color focus:ring-primary-color rounded-md shadow-sm bg-transparent text-white"
                   placeholder="Confirm your password">
            @error('password_confirmation')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- User Type -->
        <div class="flex flex-col gap-2">
            <label for="user_type" class="block font-medium text-sm text-white ">Join As</label>
            <div class="grid grid-cols-2 gap-4 mt-1">
                <label class="relative flex cursor-pointer rounded-lg  bg-white/5 p-4 shadow-sm ">
                    <input type="radio" name="user_type" value="user" class="sr-only" checked>
                    <span class="flex flex-1">
                        <span class="flex flex-col">
                            <span class="block text-sm font-medium text-white">Music Lover</span>
                            <span class="mt-1 flex items-center text-sm text-white/60">Discover and enjoy music</span>
                        </span>
                    </span>
                    <svg class="h-5 w-5 text-primary-color" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </label>
                <label class="relative flex cursor-pointer rounded-lg  bg-white/5 p-4 shadow-sm ">
                    <input type="radio" name="user_type" value="artist" class="sr-only">
                    <span class="flex flex-1">
                        <span class="flex flex-col">
                            <span class="block text-sm font-medium text-white">Artist</span>
                            <span class="mt-1 flex items-center text-sm text-white/60">Share your music with fans</span>
                        </span>
                    </span>
                    <svg class="h-5 w-5 text-primary-color invisible" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </label>
            </div>
            @error('user_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- reCAPTCHA -->
        <div class="flex justify-center mt-4">
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
            @error('g-recaptcha-response')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-4 mt-8">
            <a href="{{ route('login') }}" class="auth-link text-sm text-white/60">
                Already have an account?
            </a>

             <x-primary-button class="">
                {{ __('Create Account') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 text-center text-sm text-gray-500">
        By signing up, you agree to our 
        <a href="#" class="auth-link">Terms of Service</a> and 
        <a href="#" class="auth-link">Privacy Policy</a>
    </div>

    
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        // Show checkmark on selected user type
        document.addEventListener('DOMContentLoaded', function() {
            const radioButtons = document.querySelectorAll('input[name="user_type"]');
            
            radioButtons.forEach(button => {
                button.addEventListener('change', function() {
                    // Hide all checkmarks
                    document.querySelectorAll('input[name="user_type"] + span + svg').forEach(svg => {
                        svg.classList.add('invisible');
                    });
                    
                    // Show checkmark for selected option
                    if (this.checked) {
                        this.parentNode.querySelector('svg').classList.remove('invisible');
                    }
                });
            });
            
            // Initialize with the default selection
            const checkedButton = document.querySelector('input[name="user_type"]:checked');
            if (checkedButton) {
                checkedButton.parentNode.querySelector('svg').classList.remove('invisible');
            }
        });
    </script>
    
</x-guest-layout>

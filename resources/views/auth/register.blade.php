<x-guest-layout>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <div class="min-h-screen flex items-center justify-center bg-black">
        <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-xl">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold dson-primary">Join DSON Music</h1>
                <p class="text-gray-600 mt-2">Start your musical journey today</p>
            </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-6" id="registerForm" >
                        @csrf

                        @if (session('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Name')" class="text-gray-700" />
                    <x-text-input id="name" class="block mt-1 w-full border-gray-300 rounded-lg focus:border-red-500 focus:ring-red-500" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-gray-700" />
                    <x-text-input id="email" class="block mt-1 w-full border-gray-300 rounded-lg focus:border-red-500 focus:ring-red-500" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" class="text-gray-700" />
                    <x-text-input id="password" class="block mt-1 w-full border-gray-300 rounded-lg focus:border-red-500 focus:ring-red-500" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full border-gray-300 rounded-lg focus:border-red-500 focus:ring-red-500" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- User Type -->
                <div>
                    <x-input-label for="user_type" :value="__('Join As')" class="text-gray-700" />
                    <select id="user_type" name="user_type" class="block mt-1 w-full border-gray-300 rounded-lg focus:border-red-500 focus:ring-red-500">
                        <option value="user">Music Lover</option>
                        <option value="artist">Artist</option>
                    </select>
                    <x-input-error :messages="$errors->get('user_type')" class="mt-2" />
                </div>
                
               
                <div class="flex items-center justify-between mt-6">
                    <a class="text-sm text-gray-600 hover:text-red-500" href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <button type="submit" class="dson-btn">
                        {{ __('Register') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
    <script src="{{ asset('js/recaptcha.js') }}"></script>
    @endpush
    
</x-guest-layout>

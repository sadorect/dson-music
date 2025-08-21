@extends('layouts.app')

@section('title', 'Error Occurred - GRIN Music')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-4xl w-full bg-black/10 rounded-lg shadow-lg overflow-hidden">
        <div class="p-8">
            <div class="flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            
            <h1 class="text-2xl md:text-3xl font-bold text-center mb-4">Something Went Wrong</h1>
            
            <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded mb-6">
                <p class="text-sm md:text-base">{{ $message ?? 'An unexpected error occurred.' }}</p>
            </div>
            
            <div class="space-y-4 text-center">
                <p class="text-gray-600">Our team has been notified of this issue.</p>
                
                <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 justify-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-5 py-2 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Return to Homepage
                    </a>
                    
                    <button onclick="window.history.back()" class="inline-flex items-center justify-center px-5 py-2 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Go Back
                    </button>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
                <p>If this problem persists, please contact support at <a href="mailto:support@grinmuzik.com" class="text-blue-600 hover:underline">support@grinmuzik.com</a></p>
                
                @if(config('app.debug') && isset($message))
                    <div class="mt-4 p-3 bg-gray-100 rounded text-left overflow-auto">
                        <pre class="text-xs">{{ $message }}</pre>
                    </div>
                @endif
                
                <p class="mt-4">Error ID: {{ now()->timestamp }}-{{ rand(1000, 9999) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureArtistProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->isArtist() && !auth()->user()->artistProfile) {
            Log::info('Incomplete artist profile detected', [
                'user_id' => auth()->id(),
                'user_type' => auth()->user()->user_type
            ]);
            
            return redirect()->route('artist.profile.create')
                ->with('message', 'Please complete your artist profile to continue.');
        }

        if (auth()->user()->isArtist() && auth()->user()->artistProfile && !auth()->user()->artistProfile->is_complete) {
            return redirect()->route('artist.profile.edit')
                ->with('warning', 'Your artist profile needs additional information.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureArtistProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        dd(
        auth()->user()->user_type,
        auth()->user()->isArtist(),
        auth()->user()->artistProfile
    );
        if (auth()->user()->isArtist() && !auth()->user()->artistProfile) {
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

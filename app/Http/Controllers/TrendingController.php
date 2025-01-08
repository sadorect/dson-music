<?php

namespace App\Http\Controllers;

use App\Models\Track;

class TrendingController extends Controller
{
    public function index()
    {
        $trendingTracks = Track::trending()->with('artist')->get();
        return view('trending.index', compact('trendingTracks'));
    }
}

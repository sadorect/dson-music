<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        // Later we'll add featured playlists and new releases
        return view('home');
    }
}

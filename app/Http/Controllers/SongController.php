<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SongController extends Controller
{
    public function show()
    {
        return view('songs.show');
    }
}

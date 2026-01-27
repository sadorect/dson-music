<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandlePostSizeErrors
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->server('CONTENT_LENGTH') > $this->getPostMaxSize()) {
            return redirect()->back()->withErrors([
                'track_file' => 'Upload size limit exceeded. Maximum allowed size is '.ini_get('post_max_size'),
            ]);
        }

        return $next($request);
    }

    protected function getPostMaxSize()
    {
        $max_size = ini_get('post_max_size');
        switch (substr($max_size, -1)) {
            case 'G': return substr($max_size, 0, -1) * 1024 * 1024 * 1024;
            case 'M': return substr($max_size, 0, -1) * 1024 * 1024;
            case 'K': return substr($max_size, 0, -1) * 1024;
            default: return $max_size;
        }
    }
}

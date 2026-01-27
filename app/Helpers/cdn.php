<?php

if (! function_exists('cdn_url')) {
    /**
     * Generate a CDN URL for the given path.
     *
     * If CDN_URL is configured, it will be used. Otherwise, falls back to asset().
     *
     * @param  string  $path
     * @return string
     */
    function cdn_url($path)
    {
        $cdnUrl = env('CDN_URL') ?: env('AWS_CLOUDFRONT_URL');

        if (! empty($cdnUrl)) {
            // Ensure no double slashes
            $path = ltrim($path, '/');
            $cdnUrl = rtrim($cdnUrl, '/');

            return $cdnUrl.'/'.$path;
        }

        // Fallback to local asset URL
        return asset($path);
    }
}

if (! function_exists('cdn_asset')) {
    /**
     * Alias for cdn_url for better readability
     *
     * @param  string  $path
     * @return string
     */
    function cdn_asset($path)
    {
        return cdn_url($path);
    }
}

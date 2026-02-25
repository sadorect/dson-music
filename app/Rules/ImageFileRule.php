<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImageFileRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof \Illuminate\Http\UploadedFile) {
            $fail('The :attribute must be a file.');
            return;
        }

        $allowedMimes = [
            'image/jpeg',      // JPEG
            'image/jpg',       // JPG (alternative)
            'image/png',       // PNG
            'image/webp',      // WebP
            'image/gif',       // GIF (for covers, not profile pics)
        ];

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        // Check MIME type
        if (!in_array($value->getMimeType(), $allowedMimes)) {
            $fail('The :attribute must be a valid image file (JPEG, PNG, WebP, or GIF).');
            return;
        }

        // Check file extension
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            $fail('The :attribute must have a valid image file extension (jpg, jpeg, png, webp, or gif).');
            return;
        }

        // Additional security: check if file content matches extension
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $value->getPathname());
        finfo_close($finfo);

        if (!in_array($detectedMime, $allowedMimes)) {
            $fail('The :attribute file content does not match its extension.');
            return;
        }

        // Check minimum file size (1KB to prevent empty files)
        if ($value->getSize() < 1024) {
            $fail('The :attribute must be at least 1KB.');
            return;
        }

        // Check image dimensions to prevent extremely large images
        try {
            $imageInfo = getimagesize($value->getPathname());
            if ($imageInfo === false) {
                $fail('The :attribute is not a valid image file.');
                return;
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Maximum dimensions: 4096x4096
            if ($width > 4096 || $height > 4096) {
                $fail('The :attribute dimensions are too large (maximum 4096x4096 pixels).');
                return;
            }

            // Minimum dimensions: 100x100
            if ($width < 100 || $height < 100) {
                $fail('The :attribute dimensions are too small (minimum 100x100 pixels).');
                return;
            }

        } catch (\Exception $e) {
            $fail('The :attribute could not be processed as a valid image.');
            return;
        }

        // Check if file is actually readable
        if (!$value->isReadable()) {
            $fail('The :attribute could not be read. Please upload a valid file.');
            return;
        }

        // Additional check for GIF files (limit to small sizes)
        if ($extension === 'gif' || $detectedMime === 'image/gif') {
            if ($value->getSize() > 5 * 1024 * 1024) { // 5MB limit for GIFs
                $fail('GIF files cannot exceed 5MB.');
                return;
            }
        }
    }
}
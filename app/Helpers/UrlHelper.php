<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

class UrlHelper
{
    /**
     * Generate a dynamic API URL that works for both web and mobile.
     *
     * For API requests: Uses the request's host (e.g., 192.168.100.99:8000)
     * For web requests: Uses config('app.url') (e.g., http://localhost:8000)
     *
     * @param string|null $storagePath The storage path (e.g., 'courses/video.mp4')
     * @return string|null The full URL or null if path is empty
     */
    public static function storageUrl(?string $storagePath): ?string
    {
        if (empty($storagePath)) {
            return null;
        }

        // Get the storage relative URL (e.g., /storage/courses/video.mp4)
        $storageRelativePath = Storage::url($storagePath);

        // If this is an API request, use the request's base URL dynamically
        if (Request::is('api/*')) {
            $baseUrl = Request::getSchemeAndHttpHost();
            return $baseUrl . $storageRelativePath;
        }

        // For web requests, use the configured APP_URL
        return url($storageRelativePath);
    }

    /**
     * Generate a relative storage URL for the mobile app to handle.
     * Returns: /storage/path/to/file.ext
     *
     * The mobile app can then prefix this with its own baseUrl.
     *
     * @param string|null $storagePath The storage path
     * @return string|null The relative URL or null if path is empty
     */
    public static function relativeStorageUrl(?string $storagePath): ?string
    {
        if (empty($storagePath)) {
            return null;
        }

        return Storage::url($storagePath);
    }

    /**
     * Get the dynamic base URL based on the current request.
     *
     * @return string The base URL (e.g., http://192.168.100.99:8000 or http://localhost:8000)
     */
    public static function getBaseUrl(): string
    {
        return Request::getSchemeAndHttpHost();
    }
}

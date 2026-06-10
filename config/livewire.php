<?php

return [
    'asset_url' => null,
    'app_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Temporary File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Livewire's temporary file upload behavior.
    | This is critical for Filament FileUpload to handle large videos.
    |
    */
    'temporary_file_upload' => [
        'disk' => 'public',
        'rules' => ['required', 'file', 'max:512000'], // 500 MB
        'directory' => 'livewire-tmp',
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webm', 'wma',
            'pdf',
        ],
        'max_upload_time' => 300, // 5 minutes max upload time
        'cleanup' => true,
    ],
];

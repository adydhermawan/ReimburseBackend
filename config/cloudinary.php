<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | An HTTP or HTTPS URL to notify your application (a callback) when the process of uploads,
    | deletes, and any other image manipulations are completed.
    |
    */
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration Codecs
    |--------------------------------------------------------------------------
    |
    | Here you may specify the cloud URL that should be used by the framework.
    |
    */
    'cloud_url' => env('CLOUDINARY_URL'),

    /**
    * Upload Preset Name
    */
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
];

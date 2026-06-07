<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Configura CLOUDINARY_URL en .env con el formato:
    | cloudinary://API_KEY:API_SECRET@CLOUD_NAME
    |
    */

    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    'cloud_url' => env('CLOUDINARY_URL'),

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),

    'folder' => env('CLOUDINARY_FOLDER', 'bida-events'),

];

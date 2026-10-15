<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary (fotos, música y video de las invitaciones)
    |--------------------------------------------------------------------------
    |
    | CLOUDINARY_URL con el formato cloudinary://API_KEY:API_SECRET@CLOUD_NAME. Sin ella, los
    | archivos se guardan en el disco local (storage/app/public). Ver App\Services\MediaUploadService.
    |
    */

    'cloud_url' => env('CLOUDINARY_URL'),

    // Carpeta raíz dentro de la cuenta de Cloudinary
    'folder' => env('CLOUDINARY_FOLDER', 'bida-events'),

];

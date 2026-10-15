<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Servicios externos
    |--------------------------------------------------------------------------
    |
    | Credenciales de servicios de terceros que no tienen archivo propio. Los pagos están en
    | config/payments.php y Cloudinary en config/cloudinary.php.
    |
    */

    // Entrar con Google (Laravel Socialite). Sin client_id el botón no se muestra.
    // En Google Cloud › Credenciales, la URI de redirección autorizada es APP_URL + /auth/google/callback
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ImageKit Configuration
    |--------------------------------------------------------------------------
    | Server-side credentials for ImageKit media uploads.
    | The private key MUST stay on the server only — never in client apps.
    */

    'private_key'   => env('IMAGEKIT_PRIVATE_KEY', ''),
    'public_key'    => env('IMAGEKIT_PUBLIC_KEY', ''),
    'url_endpoint'  => env('IMAGEKIT_URL_ENDPOINT', 'https://ik.imagekit.io/77z5w3wmv'),
    'upload_url'    => 'https://upload.imagekit.io/api/v1/files/upload',
];

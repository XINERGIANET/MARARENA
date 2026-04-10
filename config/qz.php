<?php

return [
    'certificate_path' => env('QZ_CERTIFICATE_PATH', storage_path('app/qz/certificate.pem')),
    'private_key_path' => env('QZ_PRIVATE_KEY_PATH', storage_path('app/qz/private-key.pem')),
    'printer_name' => env('QZ_PRINTER_NAME', 'Ticketera'),
    'signature_algorithm' => env('QZ_SIGNATURE_ALGORITHM', 'SHA1'),
    'print_mode' => env('QZ_PRINT_MODE', 'pixel'),
    'production_lock' => env('QZ_PRODUCTION_LOCK', true),
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('QZ_ALLOWED_ORIGINS', (string) env('APP_URL', '')))))),
];

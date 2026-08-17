<?php

return [

    'paths' => [
        resource_path('views'),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        rtrim((string) ($_ENV['LARAVEL_STORAGE_PATH'] ?? dirname(__DIR__).'/storage'), '/\\').'/framework/views',
    ),

];

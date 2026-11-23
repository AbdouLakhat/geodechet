<?php

return [
    // Ces identifiants proviennent du fichier .env et servent aux seeders de démonstration.
    'administrateur' => [
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
    'chauffeur_password' => env('CHAUFFEUR_PASSWORD'),
];

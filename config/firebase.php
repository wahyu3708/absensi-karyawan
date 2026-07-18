<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Project Configuration
    |--------------------------------------------------------------------------
    */

    'project_id' => env('FIREBASE_PROJECT_ID', ''),
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (FCM)
    |--------------------------------------------------------------------------
    */

    'vapid_key' => env('VITE_FIREBASE_VAPID_KEY', ''),
];

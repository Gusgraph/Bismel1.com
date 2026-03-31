<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: config/firestore.php
// ======================================================

return [
    'enabled' => env('FIRESTORE_ENABLED', false),

    'role' => env('FIRESTORE_ROLE', 'runtime_support'),

    'project_id' => env('FIRESTORE_PROJECT_ID'),

    'database' => env('FIRESTORE_DATABASE', '(default)'),

    'credentials' => env('FIRESTORE_CREDENTIALS', env('GOOGLE_APPLICATION_CREDENTIALS')),

    'user_collection' => env('FIRESTORE_USER_COLLECTION', 'users'),

    'child_collections' => [
        'bots' => env('FIRESTORE_BOTS_COLLECTION', 'bots'),
        'runtimes' => env('FIRESTORE_RUNTIMES_COLLECTION', 'runtimes'),
    ],
];

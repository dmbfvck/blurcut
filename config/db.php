<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => env('DB_AUTH_DSN'),
    'username' => env('DB_AUTH_USER'),
    'password' => env('DB_AUTH_PASS'),
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];

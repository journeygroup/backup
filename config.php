<?php

return [
    // Local database connection details
    'connection' => [
        'host' => "127.0.0.1",
        'username' => "mysql-username",
        'password' => "mysql-password",
    ],

    // String of databases to back up, empty value will backup all databases
    'databases' => [],

    // Location of the storage, can be absolute directory or s3 stream (s3://bucket-name)
    'storage' => 's3://your-s3-bucket',

    // List of AWS Connection credentials
    'aws' => [
        'key' => 'your-aws-key',
        'secret' => 'your-aws-secret'
    ],

    // Temporary location for dump
    'temp' => '/tmp'
];

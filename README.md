# Backup

## Why

Journey/Backup is a lightweight MySQL backup script written in PHP, and ideal for cron jobs. It's task is simple:

- Dump MySQL a user-selected list, or all databases on the local server
- Store those databases in a mounted directory, or on Amazon S3


## Usage

### Installation

The easiest way is to install via composer:

```sh
composer create-project journey/backup your-directory-name
```

### Configuration

To configure, edit the `config.php` file with your own details. Here's is the sample configuration file, with helpful comments:

```php
<?php

return [
    // Local database connection details
    'connection' => [
        'host' => "127.0.0.1",
        'username' => "root",
        'password' => "your-password",
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
```

*Note: The host parameter is used only when databases is set to auto-discover all databases (empty array), otherwise all calls to MySQL are performed via shell operation.*

### Schedule

Scheduling your backups is as simple as setting up a cron job to execute the `boot.php` file.
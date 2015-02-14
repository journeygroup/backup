<?php
include __DIR__ . "/vendor/autoload.php";

$file = (file_exists(__DIR__ . '/local.php')) ? 'local':'config';

$conf = require __DIR__ . '/' . $file . '.php';

new Journey\Backup($conf);

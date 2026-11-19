<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure .env exists
if (!file_exists(__DIR__ . '/../.env') && file_exists(__DIR__ . '/../.env.example')) {
    copy(__DIR__ . '/../.env.example', __DIR__ . '/../.env');
}

// Auto-extract vendor package if missing...
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $zipPath = file_exists(__DIR__ . '/../vendor.zip') 
        ? __DIR__ . '/../vendor.zip' 
        : (file_exists(__DIR__ . '/vendor.zip') ? __DIR__ . '/vendor.zip' : null);

    if ($zipPath && class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo(dirname(__DIR__));
            $zip->close();
        }
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());

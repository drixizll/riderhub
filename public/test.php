<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP is working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

echo "<h2>Checking Laravel files:</h2>";
$base = dirname(__DIR__);

$checks = [
    '.env' => file_exists($base . '/.env'),
    'vendor/autoload.php' => file_exists($base . '/vendor/autoload.php'),
    'bootstrap/app.php' => file_exists($base . '/bootstrap/app.php'),
    'storage writable' => is_writable($base . '/storage'),
    'storage/logs writable' => is_writable($base . '/storage/logs'),
    'storage/framework/views writable' => is_writable($base . '/storage/framework/views'),
    'storage/framework/sessions writable' => is_writable($base . '/storage/framework/sessions'),
    'database/database.sqlite' => file_exists($base . '/database/database.sqlite'),
];

foreach ($checks as $name => $ok) {
    echo "<p>$name: " . ($ok ? "<b style='color:green'>OK</b>" : "<b style='color:red'>FAILED</b>") . "</p>";
}

echo "<h2>Attempting Laravel Bootstrap:</h2>";
try {
    require $base . '/vendor/autoload.php';
    $app = require_once $base . '/bootstrap/app.php';
    echo "<p style='color:green'><b>Laravel Bootstrapped Successfully!</b></p>";
} catch (\Throwable $e) {
    echo "<p style='color:red'><b>Bootstrap Error:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

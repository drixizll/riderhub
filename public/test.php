<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$base = dirname(__DIR__);

echo "<h1>Detailed Laravel Debugger</h1>";

// 1. Copy .env if missing
if (!file_exists($base . '/.env') && file_exists($base . '/.env.example')) {
    $ok = copy($base . '/.env.example', $base . '/.env');
    echo "<p>Copy .env.example -> .env: " . ($ok ? "<b style='color:green'>SUCCESS</b>" : "<b style='color:red'>FAILED (permission?)</b>") . "</p>";
}

echo "<p>.env exists now: " . (file_exists($base . '/.env') ? 'YES' : 'NO') . "</p>";

// 2. Read latest log
$logFile = $base . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "<h2>Last 20 lines of laravel.log:</h2>";
    $lines = file($logFile);
    echo "<pre style='background:#f4f4f4;padding:10px;'>" . htmlspecialchars(implode("", array_slice($lines, -25))) . "</pre>";
} else {
    echo "<p>No log file found at $logFile</p>";
}

// 3. Try full request execution and catch error
echo "<h2>Simulating Full Request Handling:</h2>";
try {
    require_once $base . '/vendor/autoload.php';
    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once $base . '/bootstrap/app.php';
    
    $request = \Illuminate\Http\Request::capture();
    $response = $app->handleRequest($request);
    
    echo "<p style='color:green'><b>Request Handled! Status Code: " . $response->getStatusCode() . "</b></p>";
    if ($response->getStatusCode() >= 400) {
        echo "<pre style='background:#fee;padding:10px;'>" . htmlspecialchars(substr($response->getContent(), 0, 1000)) . "</pre>";
    }
} catch (\Throwable $e) {
    echo "<p style='color:red;font-size:16px;'><b>Caught Exception:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>In file:</b> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<pre style='background:#fee;padding:10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

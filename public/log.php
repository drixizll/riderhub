<?php
$logFile = dirname(__DIR__) . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    echo "<pre>" . htmlspecialchars(implode("", $lastLines)) . "</pre>";
} else {
    echo "No log file found at $logFile";
}

<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

$base = dirname(__DIR__);

// 1. Ensure .env exists
if (!file_exists($base . '/.env')) {
    if (file_exists($base . '/.env.example')) {
        copy($base . '/.env.example', $base . '/.env');
        echo "<p style='color:green'>Created .env from .env.example</p>";
    }
}

// 2. Find and extract vendor.zip
$zipPaths = [
    $base . '/vendor.zip',
    __DIR__ . '/vendor.zip'
];

$foundZip = null;
foreach ($zipPaths as $p) {
    if (file_exists($p)) {
        $foundZip = $p;
        break;
    }
}

if ($foundZip) {
    $zip = new ZipArchive;
    if ($zip->open($foundZip) === TRUE) {
        $zip->extractTo($base);
        $zip->close();
        echo "<h1 style='color:green'>Success! vendor.zip extracted.</h1>";
        if (file_exists($base . '/vendor/autoload.php')) {
            echo "<p style='color:green'><b>vendor/autoload.php verified!</b></p>";
            echo "<p><a href='/'>Go to Homepage</a></p>";
        } else {
            echo "<p style='color:orange'>Warning: vendor/autoload.php not found after extract.</p>";
        }
    } else {
        echo "<h1 style='color:red'>Error opening zip archive.</h1>";
    }
} else {
    echo "<h1>Waiting for vendor.zip</h1>";
}

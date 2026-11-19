<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

$base = dirname(__DIR__);
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
        echo "<h1 style='color:green'>Success! vendor.zip has been extracted.</h1>";
        echo "<p>Laravel dependencies are now installed.</p>";
        echo "<p><a href='/'>Go to Homepage</a></p>";
    } else {
        echo "<h1 style='color:red'>Error opening zip archive.</h1>";
    }
} else {
    echo "<h1>Waiting for vendor.zip</h1>";
    echo "<p>Please upload <code>vendor.zip</code> to <code>public_html/</code> via FTP.</p>";
}

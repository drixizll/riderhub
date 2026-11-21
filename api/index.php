<?php
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';
if ($path === '/' || $path === '/index.php') {
    $_SERVER['REQUEST_URI'] = '/api/v1/health';
} elseif (! str_starts_with($path, '/api/')) {
    $_SERVER['REQUEST_URI'] = '/api/v1/'.ltrim($uri, '/');
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__).'/public/index.php';
require dirname(__DIR__).'/public/index.php';

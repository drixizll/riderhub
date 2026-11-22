<?php
$publicDir = realpath(dirname(__DIR__).'/public');
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($publicDir !== false && $uriPath !== '/' && $uriPath !== '/index.php') {
    $candidate = realpath($publicDir.DIRECTORY_SEPARATOR.ltrim($uriPath, '/'));
    $ext = strtolower(pathinfo($candidate ?: '', PATHINFO_EXTENSION));
    $mime = ['css'=>'text/css; charset=utf-8','js'=>'application/javascript; charset=utf-8','mjs'=>'application/javascript; charset=utf-8','svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','webp'=>'image/webp','ico'=>'image/x-icon','woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf','webmanifest'=>'application/manifest+json','json'=>'application/json','txt'=>'text/plain; charset=utf-8','html'=>'text/html; charset=utf-8'];
    if ($candidate !== false && is_file($candidate) && str_starts_with($candidate, $publicDir.DIRECTORY_SEPARATOR) && isset($mime[$ext])) {
        header('Content-Type: '.$mime[$ext]);
        header('Cache-Control: public, max-age=86400');
        readfile($candidate);
        exit;
    }
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__).'/public/index.php';
require dirname(__DIR__).'/public/index.php';

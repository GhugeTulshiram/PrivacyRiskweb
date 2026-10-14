<?php

// Change current working directory to project root so relative includes work
chdir(__DIR__ . '/..');

// Set writable directory for session storage in serverless environments
if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.save_path', '/tmp');
}

$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim(urldecode($request_uri), '/');

// Default entry point
if ($path === '' || $path === 'index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

$file = __DIR__ . '/../' . $path;

// If direct PHP file exists in root or subdirectories
if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
    require $file;
    exit;
}

// Fallback to index.php
if (is_file(__DIR__ . '/../index.php')) {
    require __DIR__ . '/../index.php';
    exit;
}

http_response_code(404);
echo "404 Not Found";

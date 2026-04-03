<?php

// Strip leading/trailing slashes, default to 'index'
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if ($path === '') $path = 'index';

// Route map: URL path => PHP file
$routes = [
    'index'       => 'index.php',
    'why'         => 'why.php',
    'about'       => 'about.php',
    'impressum'   => 'impressum.php',
    'datenschutz' => 'datenschutz.php',
    'agb'         => 'agb.php',
];

if (isset($routes[$path])) {
    $file = __DIR__ . '/' . $routes[$path];
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// 404 fallback
http_response_code(404);
$notFound = __DIR__ . '/404.php';
if (file_exists($notFound)) {
    require $notFound;
} else {
    echo '<!DOCTYPE html><html><body><h1>404 Not Found</h1></body></html>';
}

<?php

// Serve static files directly (images, fonts, etc.)
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$staticFile = __DIR__ . $requestPath;
if ($requestPath !== '/' && file_exists($staticFile) && is_file($staticFile)) {
    return false;
}

// Strip leading/trailing slashes, default to 'index'
$path = trim($requestPath, '/');
if ($path === '') $path = 'index';

// Route map: URL path => PHP file
$routes = [
    'index'       => 'index.php',
    'why'         => 'why.php',
    'about'       => 'about.php',
    'impressum'   => 'impressum.php',
    'datenschutz' => 'datenschutz.php',
    'agb'         => 'agb.php',
    'admin'       => 'admin.php',
    'feedback'    => 'feedback.php',
    'contact'     => 'contact.php',
    'signup'      => 'signup.php',
    'notify'      => 'notify.php',
    'download'    => 'download.php',
    'changelog'   => 'changelog.php',
    'roadmap'     => 'roadmap.php',
    'update'      => 'update.php',
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

<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Router;
use App\Controllers\PhotoController;
use App\Controllers\HealthController;

// Initialize router
$router = new Router();

// Define routes
$router->get('/health', HealthController::class, 'index');

$router->get('/photos', PhotoController::class, 'index');
$router->get('/photos/{id}', PhotoController::class, 'show');
$router->post('/photos/upload', PhotoController::class, 'upload');
// $router->delete('/photos/{id}', PhotoController::class, 'delete');
$router->patch('/photos/{id}/reassign', PhotoController::class, 'reassign');

// Serve uploaded files and thumbnails via backend
$router->get('/uploads/{year}/{filename}', PhotoController::class, 'serveUpload');
$router->get('/uploads/{year}/thumbnails/{filename}', PhotoController::class, 'serveThumbnail');

// Dispatch the request
$router->dispatch();

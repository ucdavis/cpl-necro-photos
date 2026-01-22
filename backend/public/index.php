<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Router;
use App\Controllers\PhotoController;

// Initialize router
$router = new Router();

// Define routes
$router->get('/photos', PhotoController::class, 'index');
$router->get('/photos/{id}', PhotoController::class, 'show');
$router->post('/photos/upload', PhotoController::class, 'upload');
$router->delete('/photos/{id}', PhotoController::class, 'delete');

// Dispatch the request
$router->dispatch();

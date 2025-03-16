<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use App\Actions\MonitorAction;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$env = Dotenv::createImmutable(base_path());
$env->safeLoad();

// Instantiate the app
$app = AppFactory::create();

// Register routes
$app->get('/', [MonitorAction::class, 'view']);
$app->get('/weather', [MonitorAction::class, 'weather']);
$app->get('/transport', [MonitorAction::class, 'transport']);

// Handle errors
$app->addErrorMiddleware(false, false, false);

$app->run();

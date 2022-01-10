<?php

use App\Adapters\ContainerAdapter;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

session_start();

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} else {
    throw new Exception('Env file should be created.');
}

error_reporting(E_ALL);
ini_set('display_errors', getenv('PHP_INI_SET_DISPLAY_ERRORS'));
date_default_timezone_set(getenv('PHP_DATE_DEFAULT_DEFAULT_TIMEZONE_SET'));

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');

AppFactory::setContainer(new ContainerAdapter($containerBuilder->build()));
$app = AppFactory::create();

(require __DIR__ . '/../config/middleware.php')($app);
(require __DIR__ . '/../config/routes.php')($app);

// Run app
$app->run();
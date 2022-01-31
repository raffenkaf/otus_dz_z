#!/usr/bin/env php
<?php

use App\Adapters\ContainerAdapter;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(getenv('PHP_ERROR_REPORTING_LEVEL'));
ini_set('display_errors', getenv('PHP_INI_SET_DISPLAY_ERRORS'));
date_default_timezone_set(getenv('PHP_DATE_DEFAULT_DEFAULT_TIMEZONE_SET'));

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');

/** @var ContainerInterface $container */
$container = new ContainerAdapter($containerBuilder->build());

$application = $container->get(Application::class);
$application->run();
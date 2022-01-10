<?php

use App\Middlewares\AuthMiddleware;
use App\Middlewares\GeneralViewDataMiddleware;
use Slim\App;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    $settings = $app->getContainer()->get('settings')['error'];

    $app->addErrorMiddleware(
        (bool)$settings['display_error_details'],
        (bool)$settings['log_errors'],
        (bool)$settings['log_error_details']
    );

    $app->add(TwigMiddleware::createFromContainer($app));
    $app->add(new GeneralViewDataMiddleware($app->getContainer()->get('view')));
    $app->add(new AuthMiddleware($app->getContainer()->get('pdo')));

    $app->addRoutingMiddleware();
};
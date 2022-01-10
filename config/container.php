<?php

use DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Views\Twig;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },

    'validator' => function () {
        return new Awurth\SlimValidation\Validator();
    },

    'view' => function (Container $container) {
        $pathToViews = __DIR__ . '/../views';
        $view = Twig::create($pathToViews, ['cache' => false]);
        $view->addExtension(
            new Awurth\SlimValidation\ValidatorExtension($container->get('validator'))
        );
        return $view;
    },

    'pdo' => function (Container $container) {
        $settings = $container->get('settings')['database'];

        $host = $settings['host'];
        $db   = $settings['database'];
        $user = $settings['username'];
        $pass = $settings['password'];
        $charset = $settings['charset'];
        $port = $settings['port'];

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, $user, $pass, $opt);
    }
];

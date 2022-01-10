<?php

namespace App\Controllers;

use Awurth\SlimValidation\Validator;
use DI\Container;
use Slim\Views\Twig;

class BaseController
{
    protected Twig $view;
    protected Container $container;
    protected Validator $validator;

    public function __construct(Container $container)
    {
        $this->view = $container->get('view');
        $this->container = $container;
        $this->validator = $container->get('validator');
    }
}

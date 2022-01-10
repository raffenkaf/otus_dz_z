<?php

namespace App\Middlewares;

use App\Auth\AuthManager;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Request;
use Slim\Views\Twig;

class GeneralViewDataMiddleware
{
    private Twig $view;

    public function __construct(Twig $twig)
    {
        $this->view = $twig;
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler)
    {
        /** @var AuthManager $authManager */
        $authManager = $request->getAttribute('auth_manager');

        $this->view->getEnvironment()->addGlobal('user', $authManager->getUser());

        return $handler->handle($request);
    }
}
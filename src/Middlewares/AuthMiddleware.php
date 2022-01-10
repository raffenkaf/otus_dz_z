<?php

namespace App\Middlewares;

use App\Auth\AuthManager;
use App\Models\User;
use PDO;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Request;

class AuthMiddleware
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler)
    {
        $authManager = new AuthManager();

        if (isset($_SESSION['auth_user_id'])) {
            $user = new User($this->pdo);
            $user = $user->findById($_SESSION['auth_user_id']);
            $authManager->login($user);
        }

        $request = $request->withAttribute('auth_manager', $authManager);
        return $handler->handle($request);
    }
}

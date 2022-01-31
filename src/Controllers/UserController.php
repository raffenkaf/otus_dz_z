<?php

namespace App\Controllers;

use App\Models\City;
use App\Models\User;
use App\Models\UserSearchDTO;
use Respect\Validation\Validator;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UserController extends BaseController
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function register(Request $request, Response $response): Response
    {
        if (!$request->getAttribute('auth_manager')->isGuest()) {
            return $response->withStatus(302)->withHeader('Location', '/');
        }

        if ($request->getMethod() == 'POST') {
            $user = new User($this->container->get('pdo'));
            $validationRules = $user->getValidationRules();

            $validationRules['confirm_password'] = [
                'rules' => Validator::equals($request->getParsedBody()['password']),
                'messages' => [
                    'equals' => 'The password confirmation must be equal to the password'
                ]
            ];

            $this->validator->validate($request, $validationRules);

            if ($this->validator->isValid()) {
                $user->saveNew($request->getParsedBody());
                return $response->withStatus(302)->withHeader('Location', '/user/login');
            }
        }

        /** @var City $city */
        $city = new City($this->container->get('pdo'));

        return $this->view->render($response, 'register.twig', [
            'seo' => ['title' => "Регистрация"],
            'cities' => $city->all()
        ]);
    }

    public function login(Request $request, Response $response): Response
    {
        if (!$request->getAttribute('auth_manager')->isGuest()) {
            $response = $response->withStatus(302)->withHeader('Location', '/');
            return $response;
        }

        if ($request->getMethod() == 'POST') {
            $user = new User($this->container->get('pdo'));
            $user = $user->findByLoginAndPass($request->getParsedBody());

            if ($user) {
                $request->getAttribute('auth_manager')->login($user);
                return $response->withStatus(302)->withHeader('Location', '/user/personal-page');
            }
        }

        return $this->view->render($response, 'login.twig', [
            'seo' => ['title' => "Логин"],
        ]);
    }

    public function logout(Request $request, Response $response): Response
    {
        if ($request->getAttribute('auth_manager')->isGuest()) {
            return $response->withStatus(302)->withHeader('Location', '/');
        }

        $request->getAttribute('auth_manager')->logout();

        return $response->withStatus(302)->withHeader('Location', '/');
    }

    public function page(Request $request, Response $response, array $args): Response
    {
        /** @var User $authUser */
        $authUser = $request->getAttribute('auth_manager')->getUser();

        $user = (new User($this->container->get('pdo')))->findById($args['id']);

        $city = (new City($this->container->get('pdo')))->findById($user->city_id);

        return $this->view->render($response, 'user_page.twig', [
            'seo' => ['title' => "Страница пользователя"],
            'user' => $user,
            'sex' => $user->getSexAsString(),
            'city' => $city,
            'canAddFriend' => $user->canAddFriend($authUser),
            'canRemoveFriend' => $user->canRemoveFriend($authUser),
        ]);
    }

    public function personalPage(Request $request, Response $response): Response
    {
        if ($request->getAttribute('auth_manager')->isGuest()) {
            return $response->withStatus(302)->withHeader('Location', '/');
        }

        /** @var User $user */
        $user = $request->getAttribute('auth_manager')->getUser();

        $city = (new City($this->container->get('pdo')))->findById($user->city_id);

        return $this->view->render($response, 'user_personal_page.twig', [
            'seo' => ['title' => "Персональная страница пользователя"],
            'user' => $user,
            'sex' => $user->getSexAsString(),
            'city' => $city,
            'friends' => $user->getFriends()
        ]);
    }

    public function addFriend(Request $request, Response $response, array $args): Response
    {
        if ($request->getAttribute('auth_manager')->isGuest()) {
            return $response->withStatus(302)->withHeader('Location', '/');
        }

        /** @var User $user */
        /** @var User $authUser */
        $authUser = $request->getAttribute('auth_manager')->getUser();
        $user = (new User($this->container->get('pdo')))->findById($args['id']);

        if ($user->isFriend($authUser)) {
            return $response->withStatus(302)->withHeader('Location', '/user/page/' . $user->id);
        }

        $authUser->addFriend($user);

        return $response->withStatus(302)->withHeader('Location', '/user/page/' . $user->id);
    }

    public function removeFriend(Request $request, Response $response, array $args): Response
    {
        if ($request->getAttribute('auth_manager')->isGuest()) {
            return $response->withStatus(302)->withHeader('Location', '/');
        }

        /** @var User $user */
        /** @var User $authUser */
        $authUser = $request->getAttribute('auth_manager')->getUser();
        $user = (new User($this->container->get('pdo')))->findById($args['id']);

        $authUser->removeFriend($user);

        return $response->withStatus(302)->withHeader('Location', '/user/page/' . $user->id);
    }

    public function search(Request $request, Response $response, array $args): Response
    {
        $usersOnPage = 20;
        $queryParams = $request->getQueryParams();

        $maxId = $queryParams['max_id'] ?? null;
        $minId = $queryParams['min_id'] ?? null;

        $firstName = $queryParams['first_name'] ?? null;
        $lastName = $queryParams['last_name'] ?? null;

        $searchDTO = new UserSearchDTO($minId, $maxId, $firstName, $lastName, $usersOnPage);

        $users = (new User($this->container->get('pdo')))->search($searchDTO);

        return $this->view->render($response, 'user_search.twig', [
            'users' => $users,
            'last_user_id' => end($users)->id,
            'first_name' => $firstName,
            'last_name' => $lastName
        ]);
    }
}
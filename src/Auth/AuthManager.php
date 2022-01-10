<?php

namespace App\Auth;

use App\Models\User;

class AuthManager
{
    public const SESSION_USER_ID = 'auth_user_id';

    private ?User $user = null;

    public function login(User $user): void
    {
        if (empty($_SESSION[self::SESSION_USER_ID])) {
            $_SESSION[self::SESSION_USER_ID] = $user->getId();
        }

        $this->user = $user;
    }

    public function logout(): void
    {
        if (empty($_SESSION[self::SESSION_USER_ID])) {
            $this->user = null;
            return;
        }

        unset($_SESSION[self::SESSION_USER_ID]);

        $this->user = null;
    }

    public function isGuest(): bool
    {
        return is_null($this->user);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}

<?php

namespace App\Models;

class UserSearchDTO
{
    private ?int $minId;
    private ?int $maxId;
    private ?string $firstName;
    private ?string $lastName;
    private int $userOnPage;

    public function __construct(?int $minId, ?int $maxId, ?string $firstName, ?string $secondName, int $userOnPage)
    {
        $this->minId = $minId;
        $this->maxId = $maxId;
        $this->firstName = $firstName;
        $this->lastName = $secondName;
        $this->userOnPage = $userOnPage;
    }

    /**
     * @return int|null
     */
    public function getMinId(): ?int
    {
        return $this->minId;
    }

    /**
     * @return int|null
     */
    public function getMaxId(): ?int
    {
        return $this->maxId;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return int
     */
    public function getUserOnPage(): int
    {
        return $this->userOnPage;
    }

    public function hasAnyValue(): bool
    {
        return !is_null($this->firstName)
            || !is_null($this->lastName)
            || empty($this->maxId)
            || empty($this->minId);
    }
}
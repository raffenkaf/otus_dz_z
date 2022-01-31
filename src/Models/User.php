<?php

namespace App\Models;

use InvalidArgumentException;
use PDO;
use Respect\Validation\Validator;

class User extends BaseModel
{
    public const SEX_MALE = 1;
    public const SEX_FEMALE = 2;

    public ?int $id;
    public ?string $first_name;
    public ?string $last_name;
    public ?int $age;
    public ?int $sex;
    public ?string $interests;
    public ?int $city_id;
    public ?string $login;
    public ?string $password;

    public function getValidationRules(): array
    {
        return [
            'login' => Validator::length(2, 50)->addRule(Validator::notBlank()),
            'password' => [
                'rules' => Validator::length(5, 25),
                'messages' => [
                    'length' => 'This field must have a length between {{minValue}} and {{maxValue}} characters'
                ]
            ],
            'first_name' => Validator::notBlank(),
            'last_name' => Validator::notBlank(),
            'age' => Validator::notBlank()->addRule(Validator::numeric()),
            'sex' => Validator::notBlank()
                ->addRule(Validator::numeric())
                ->addRule(Validator::max(2))
                ->addRule(Validator::min(1)),
            'city' => Validator::numeric()
        ];
    }

    public function findById(int $id): ?self
    {
        $statement = $this->pdo->prepare("SELECT * FROM user WHERE id = :id");
        $statement->execute(['id' => $id]);

        $userRow = $statement->fetch();

        return $this->createUserByArray($userRow);
    }

    /**
     * @param array $getParsedBody
     * @return void
     */
    public function saveNew(array $getParsedBody)
    {
        $getParsedBody['password'] = password_hash($getParsedBody['password'], PASSWORD_DEFAULT);
        unset($getParsedBody['confirm_password']);

        $sql = "
        INSERT INTO user 
        SET first_name=:first_name,
            last_name=:last_name,
            age=:age,
            sex=:sex,
            interests=:interests,
            city_id=:city,
            login=:login,
            password=:password;";

        $this->pdo->prepare($sql)->execute($getParsedBody);
    }

    public function findByLoginAndPass(array $getParsedBody): ?User
    {
        $statement = $this->pdo->prepare("SELECT * FROM user WHERE login = :login");
        $statement->execute(['login' => $getParsedBody['login']]);

        $userRow = $statement->fetch();

        if (!password_verify($getParsedBody['password'], $userRow['password'])) {
            return null;
        }

        return $this->createUserByArray($userRow);
    }

    private function createUserByArray(mixed $userRow): ?self
    {
        if (empty($userRow['id'])) {
            return null;
        }

        $user = new self($this->pdo);
        $user->id = $userRow['id'];
        $user->first_name = $userRow['first_name'];
        $user->last_name = $userRow['last_name'];
        $user->age = $userRow['age'];
        $user->sex = $userRow['sex'];
        $user->interests = $userRow['interests'];
        $user->city_id = $userRow['city_id'];
        $user->login = $userRow['login'];
        $user->password = $userRow['password'];

        return $user;
    }

    public function getSexAsString()
    {
        return $this->sex == self::SEX_MALE ? 'мужской' : "женский";
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function canAddFriend(?User $user): bool
    {
        if (is_null($user)) {
            return false;
        }

        if ($this->id == $user->id) {
            return false;
        }

        if ($this->isFriend($user)) {
            return false;
        }

        return true;
    }

    public function canRemoveFriend(?User $user): bool
    {
        if (is_null($user) || is_null($user->id)) {
            return false;
        }

        if ($this->id == $user->id) {
            return false;
        }

        if (!$this->isFriend($user)) {
            return false;
        }

        return true;
    }

    public function isFriend(?User $user): bool
    {
        if (is_null($user) || is_null($user->id)) {
            return false;
        }

        $statement = $this->pdo->prepare("
        SELECT * 
        FROM users_friends
        WHERE (user_one_id = :id_one AND user_two_id = :id_two) OR (user_two_id = :id_three AND user_one_id = :id_four)");

        $statement->execute([
            'id_one' => $user->id,
            'id_two' => $this->id,
            'id_three' => $user->id,
            'id_four' => $this->id,
        ]);

        return !empty($statement->fetch());
    }

    public function addFriend(?User $user)
    {
        if (is_null($user->id) || is_null($this->id)) {
            throw new InvalidArgumentException('Can not save friends');
        }

        $sql = "
        INSERT INTO users_friends 
        SET user_one_id = :one_id,
            user_two_id = :two_id;";

        $this->pdo->prepare($sql)->execute(['one_id' => $user->id, 'two_id' => $this->id]);
    }

    public function removeFriend(?User $user)
    {
        if (is_null($user->id) || is_null($this->id)) {
            throw new InvalidArgumentException('Can not save friends');
        }

        $sql = "
        DELETE 
        FROM users_friends
        WHERE (user_one_id = :id_one AND user_two_id = :id_two) OR (user_two_id = :id_three AND user_one_id = :id_four)";

        $this->pdo->prepare($sql)->execute([
            'id_one' => $user->id,
            'id_two' => $this->id,
            'id_three' => $user->id,
            'id_four' => $this->id,
        ]);
    }

    public function getFriends(): array
    {
        if (is_null($this->id)) {
            return [];
        }

        $sql = "
        SELECT user.*
        FROM user
        inner join users_friends as uf1 on uf1.user_one_id = user.id and uf1.user_two_id = :id_one
        union
        SELECT user.*
        FROM user
        inner join users_friends as uf2 on uf2.user_two_id = user.id and uf2.user_one_id = :id_two;
        ";

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id_one' => $this->id, 'id_two' => $this->id]);

        $resultArray = [];
        while ($row = $statement->fetch(PDO::FETCH_LAZY))
        {
            $resultArray[] = $this->createUserByArray($row);
        }

        return $resultArray;
    }

    public function search(UserSearchDTO $searchDTO): array
    {
        $sql = "
        SELECT *
        FROM user
        ";

        if ($searchDTO->hasAnyValue()) {
            $sql .= ' WHERE ';
        }

        $substituteValues = [];

        if (!is_null($searchDTO->getMinId())) {
            $sql .= ' id > :min_id AND ';
            $substituteValues[':min_id'] = $searchDTO->getMinId();
        }

        if (!is_null($searchDTO->getMaxId())) {
            $sql .= ' id < :max_id AND ';
            $substituteValues[':max_id'] = $searchDTO->getMaxId();
        }

        if (!empty($searchDTO->getFirstName())) {
            $sql .= " first_name LIKE :first_name_prefix AND ";
            $substituteValues[':first_name_prefix'] = $searchDTO->getFirstName() . '%';
        }

        if (!empty($searchDTO->getLastName())) {
            $sql .= " last_name LIKE :last_name_prefix AND ";
            $substituteValues[':last_name_prefix'] = $searchDTO->getLastName() . '%';
        }

        if ($searchDTO->hasAnyValue()) {
            $sql = substr($sql, 0, -4);
        }

        $sql .= " ORDER BY id DESC LIMIT :user_on_page ";
        $substituteValues['user_on_page'] = $searchDTO->getUserOnPage();

        $statement = $this->pdo->prepare($sql);
        $statement->execute($substituteValues);

        $resultArray = [];
        while ($row = $statement->fetch(PDO::FETCH_LAZY))
        {
            $resultArray[] = $this->createUserByArray($row);
        }

        return $resultArray;
    }
}
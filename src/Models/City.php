<?php

namespace App\Models;

use PDO;

class City extends BaseModel
{
    public int $id;
    public string $name;

    public function all(): array
    {
        $resultArray = [];

        $stmt = $this->pdo->prepare("SELECT id, name FROM city");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_LAZY))
        {
            $resultArray[] = $this->createCityByArray($row);
        }

        return $resultArray;
    }

    public function findById(int $id): ?self
    {
        $statement = $this->pdo->prepare("SELECT * FROM city WHERE id = :id");
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        return $this->createCityByArray($row);
    }

    private function createCityByArray(mixed $row): ?self
    {
        if (empty($row['id'])) {
            return null;
        }

        $city = new self($this->pdo);
        $city->id = $row['id'];
        $city->name = $row['name'];
        return $city;
    }
}
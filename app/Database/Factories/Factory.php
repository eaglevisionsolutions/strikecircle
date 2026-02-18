<?php
// app/Database/Factories/Factory.php

declare(strict_types=1);

namespace App\Database\Factories;

use App\Database\Faker\SeededRandom;
use PDO;
use DateTimeImmutable;

abstract class Factory
{
    protected PDO $db;
    protected SeededRandom $rand;
    protected array $usedEmails = [];
    protected array $usedUsernames = [];

    public function __construct(PDO $db, SeededRandom $rand)
    {
        $this->db = $db;
        $this->rand = $rand;
    }

    abstract public function make(array $overrides = []): array;
    abstract public function create(array $overrides = []): array;

    protected function now(): string
    {
        return (new DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    protected function slugify(string $str): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $str));
    }

    protected function uniqueEmail(string $prefix = 'user'): string
    {
        do {
            $email = $prefix . $this->rand->int(1000, 9999) . '@strikecircle.local';
        } while (in_array($email, $this->usedEmails, true));
        $this->usedEmails[] = $email;
        return $email;
    }

    protected function uniqueUsername(string $prefix = 'user'): string
    {
        do {
            $username = $prefix . $this->rand->int(1000, 9999);
        } while (in_array($username, $this->usedUsernames, true));
        $this->usedUsernames[] = $username;
        return $username;
    }

    protected function insertAndReturnId(string $table, array $data): int
    {
        $cols = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO `$table` (" . implode(',', $cols) . ") VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }
}

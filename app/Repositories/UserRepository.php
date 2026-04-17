<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository extends BaseRepository
{
    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT * FROM users WHERE LOWER(email) = LOWER(:email) AND deleted_at IS NULL LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function updateLastLogin(int $userId, ?\DateTimeInterface $at = null): void
    {
        $at ??= new \DateTimeImmutable('now');
        $sql = 'UPDATE users SET last_login_at = :ts, updated_at = NOW() WHERE id = :id';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'ts' => $at->format('Y-m-d H:i:s'),
            'id' => $userId,
        ]);
    }

    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        $sql = 'UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'password' => $passwordHash,
            'id' => $userId,
        ]);
    }
}

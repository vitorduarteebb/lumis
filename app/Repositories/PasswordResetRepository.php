<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PasswordResetRepository extends BaseRepository
{
    public function deleteByEmail(string $email): void
    {
        $sql = 'DELETE FROM password_resets WHERE LOWER(email) = LOWER(:email)';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['email' => $email]);
    }

    public function insert(string $email, string $tokenHash, \DateTimeInterface $expiresAt): void
    {
        $sql = 'INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'token' => $tokenHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findValidByEmailAndTokenHash(string $email, string $tokenHash): ?array
    {
        $sql = <<<SQL
SELECT * FROM password_resets
WHERE LOWER(email) = LOWER(:email)
  AND token = :token
  AND expires_at > NOW()
ORDER BY id DESC
LIMIT 1
SQL;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'token' => $tokenHash,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function deleteExpired(): int
    {
        return $this->pdo()->exec('DELETE FROM password_resets WHERE expires_at < NOW()');
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CompanyRepository extends BaseRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function allActive(): array
    {
        $stmt = $this->pdo()->query('SELECT id, name, slug FROM companies WHERE status = 1 ORDER BY name ASC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM companies WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    public function updateBasics(int $id, string $name, string $slug, int $status): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE companies SET name = :name, slug = :slug, status = :st, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['name' => $name, 'slug' => $slug, 'st' => $status, 'id' => $id]);
    }
}

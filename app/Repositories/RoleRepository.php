<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class RoleRepository extends BaseRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function allOrdered(): array
    {
        $stmt = $this->pdo()->query('SELECT id, name, slug FROM roles ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

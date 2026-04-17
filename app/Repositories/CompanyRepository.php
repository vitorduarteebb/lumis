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
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class StoreRepository extends BaseRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function byCompanyId(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name, slug FROM stores WHERE company_id = :cid AND status = 1 ORDER BY name ASC'
        );
        $stmt->execute(['cid' => $companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

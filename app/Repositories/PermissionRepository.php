<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PermissionRepository extends BaseRepository
{
    /**
     * @return list<string>
     */
    public function getSlugsByUserId(int $userId): array
    {
        $sql = <<<SQL
SELECT DISTINCT p.slug
FROM permissions p
INNER JOIN role_permissions rp ON rp.permission_id = p.id
INNER JOIN user_roles ur ON ur.role_id = rp.role_id
WHERE ur.user_id = :uid
ORDER BY p.slug ASC
SQL;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_values(array_map(static fn ($s) => (string) $s, $rows));
    }

    /**
     * @return list<string>
     */
    public function getRoleSlugsByUserId(int $userId): array
    {
        $sql = <<<SQL
SELECT DISTINCT r.slug
FROM roles r
INNER JOIN user_roles ur ON ur.role_id = r.id
WHERE ur.user_id = :uid
ORDER BY r.slug ASC
SQL;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_values(array_map(static fn ($s) => (string) $s, $rows));
    }
}

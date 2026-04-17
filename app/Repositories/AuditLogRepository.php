<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AuditLogRepository extends BaseRepository
{
    public function log(
        ?int $userId,
        string $action,
        string $module,
        ?string $description,
        ?string $ipAddress,
        ?string $userAgent
    ): void {
        $sql = <<<SQL
INSERT INTO audit_logs (user_id, action, module, description, ip_address, user_agent, created_at)
VALUES (:user_id, :action, :module, :description, :ip_address, :user_agent, NOW())
SQL;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}

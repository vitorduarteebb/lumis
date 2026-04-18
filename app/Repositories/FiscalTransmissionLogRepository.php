<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class FiscalTransmissionLogRepository extends BaseRepository
{
    /**
     * @param array<string, mixed> $row
     */
    public function insert(int $companyId, ?int $fiscalDocumentId, string $phase, array $row): int
    {
        if (!$this->tableExists('fiscal_transmission_logs')) {
            return 0;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO fiscal_transmission_logs (
                company_id, fiscal_document_id, phase, endpoint, http_status,
                request_payload, response_payload, error_message, created_at
            ) VALUES (
                :cid, :fdid, :ph, :ep, :http,
                :req, :res, :err, NOW()
            )'
        );
        $stmt->execute([
            'cid' => $companyId,
            'fdid' => $fiscalDocumentId,
            'ph' => $phase,
            'ep' => $row['endpoint'] ?? null,
            'http' => $row['http_status'] ?? null,
            'req' => $row['request_payload'] ?? null,
            'res' => $row['response_payload'] ?? null,
            'err' => $row['error_message'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listByDocument(int $companyId, int $fiscalDocumentId, int $limit = 100): array
    {
        if (!$this->tableExists('fiscal_transmission_logs')) {
            return [];
        }
        $lim = max(1, min(500, $limit));
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM fiscal_transmission_logs
             WHERE company_id = :cid AND fiscal_document_id = :fid
             ORDER BY id DESC
             LIMIT ' . (int) $lim
        );
        $stmt->execute(['cid' => $companyId, 'fid' => $fiscalDocumentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

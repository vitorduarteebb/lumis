<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class FiscalDocumentEventRepository extends BaseRepository
{
    /**
     * @param array<string, mixed> $extra
     */
    public function insert(
        int $companyId,
        int $fiscalDocumentId,
        string $eventType,
        ?string $protocol,
        string $status,
        ?string $payloadPath,
        ?string $responsePath,
        ?array $metadataJson = null
    ): int {
        if (!$this->tableExists('fiscal_document_events')) {
            return 0;
        }
        $meta = $metadataJson !== null ? json_encode($metadataJson, JSON_UNESCAPED_UNICODE) : null;
        $stmt = $this->pdo()->prepare(
            'INSERT INTO fiscal_document_events (
                company_id, fiscal_document_id, event_type, protocol, status,
                payload_path, response_path, metadata_json, created_at
            ) VALUES (
                :cid, :fid, :et, :prot, :st, :pp, :rp, :meta, NOW()
            )'
        );
        $stmt->execute([
            'cid' => $companyId,
            'fid' => $fiscalDocumentId,
            'et' => $eventType,
            'prot' => $protocol,
            'st' => $status,
            'pp' => $payloadPath,
            'rp' => $responsePath,
            'meta' => $meta,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listByDocument(int $companyId, int $fiscalDocumentId): array
    {
        if (!$this->tableExists('fiscal_document_events')) {
            return [];
        }
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM fiscal_document_events
             WHERE company_id = :cid AND fiscal_document_id = :fid
             ORDER BY id ASC'
        );
        $stmt->execute(['cid' => $companyId, 'fid' => $fiscalDocumentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

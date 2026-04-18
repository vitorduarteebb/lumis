<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class DigitalCertificateRepository extends BaseRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listByCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM digital_certificates WHERE company_id = :cid ORDER BY created_at DESC'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(
        int $companyId,
        string $label,
        ?string $expiresAt,
        ?string $filePath,
        string $status,
        ?string $certType = null,
        ?string $notes = null,
        ?string $passwordEncrypted = null
    ): int {
        if (!$this->columnExists('digital_certificates', 'cert_type')) {
            $stmt = $this->pdo()->prepare(
                'INSERT INTO digital_certificates (company_id, label, expires_at, file_path, status, created_at, updated_at)
                 VALUES (:cid, :label, :exp, :fp, :st, NOW(), NOW())'
            );
            $stmt->execute([
                'cid' => $companyId,
                'label' => $label,
                'exp' => $expiresAt,
                'fp' => $filePath,
                'st' => $status,
            ]);

            return (int) $this->pdo()->lastInsertId();
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO digital_certificates (
                company_id, label, cert_type, expires_at, file_path, password_encrypted, notes, status, created_at, updated_at
             ) VALUES (
                :cid, :label, :ctype, :exp, :fp, :pw, :notes, :st, NOW(), NOW()
             )'
        );
        $stmt->execute([
            'cid' => $companyId,
            'label' => $label,
            'ctype' => $certType ?? 'A1',
            'exp' => $expiresAt,
            'fp' => $filePath,
            'pw' => $passwordEncrypted,
            'notes' => $notes,
            'st' => $status,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateStatus(int $id, int $companyId, string $status): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE digital_certificates SET status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['st' => $status, 'id' => $id, 'cid' => $companyId]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM digital_certificates WHERE id = :id AND company_id = :cid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    public function delete(int $id, int $companyId): void
    {
        $stmt = $this->pdo()->prepare(
            'DELETE FROM digital_certificates WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
    }
}

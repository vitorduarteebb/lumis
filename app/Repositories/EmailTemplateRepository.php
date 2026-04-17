<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class EmailTemplateRepository extends BaseRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listByCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM email_templates WHERE company_id = :cid ORDER BY name ASC'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM email_templates WHERE id = :id AND company_id = :cid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    public function slugExists(int $companyId, string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM email_templates WHERE company_id = :cid AND slug = :slug';
        $params = ['cid' => $companyId, 'slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id != :eid';
            $params['eid'] = $exceptId;
        }
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO email_templates (company_id, slug, name, subject, body_html, status, created_at, updated_at)
             VALUES (:cid, :slug, :name, :subject, :body, :st, NOW(), NOW())'
        );
        $stmt->execute([
            'cid' => $companyId,
            'slug' => $data['slug'],
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => $data['body_html'],
            'st' => (int) ($data['status'] ?? 1),
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $companyId, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE email_templates SET slug = :slug, name = :name, subject = :subject, body_html = :body, status = :st, updated_at = NOW()
             WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => $data['body_html'],
            'st' => (int) ($data['status'] ?? 1),
            'id' => $id,
            'cid' => $companyId,
        ]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE email_templates SET status = 0, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
    }
}

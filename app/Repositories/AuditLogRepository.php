<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Logs de auditoria visíveis no escopo da empresa (usuários vinculados a ela).
 */
final class AuditLogRepository extends BaseRepository
{
    /**
     * @param array{q?: string, action?: string, module?: string, user_id?: int, date_from?: string|null, date_to?: string|null} $filters
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginateForCompany(int $companyId, array $filters, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['uc.company_id = :cid'];
        $params = ['cid' => $companyId];
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(al.description LIKE :q OR al.action LIKE :q2 OR al.module LIKE :q3 OR u.name LIKE :q4 OR u.email LIKE :q5)';
            $w = '%' . $q . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
            $params['q4'] = $w;
            $params['q5'] = $w;
        }
        $action = trim((string) ($filters['action'] ?? ''));
        if ($action !== '') {
            $where[] = 'al.action LIKE :ac';
            $params['ac'] = '%' . $action . '%';
        }
        $module = trim((string) ($filters['module'] ?? ''));
        if ($module !== '') {
            $where[] = 'al.module LIKE :mo';
            $params['mo'] = '%' . $module . '%';
        }
        $uid = (int) ($filters['user_id'] ?? 0);
        if ($uid > 0) {
            $where[] = 'al.user_id = :uid';
            $params['uid'] = $uid;
        }
        $df = trim((string) ($filters['date_from'] ?? ''));
        if ($df !== '') {
            $where[] = 'DATE(al.created_at) >= :df';
            $params['df'] = $df;
        }
        $dt = trim((string) ($filters['date_to'] ?? ''));
        if ($dt !== '') {
            $where[] = 'DATE(al.created_at) <= :dt';
            $params['dt'] = $dt;
        }
        $whereSql = implode(' AND ', $where);
        $baseFrom = "audit_logs al
             INNER JOIN user_companies uc ON uc.user_id = al.user_id
             LEFT JOIN users u ON u.id = al.user_id";
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM {$baseFrom} WHERE {$whereSql}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT al.*, u.name AS user_name, u.email AS user_email
                FROM {$baseFrom}
                WHERE {$whereSql}
                ORDER BY al.id DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * Usuários da empresa (para filtro).
     *
     * @return list<array<string, mixed>>
     */
    public function usersForFilter(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT DISTINCT u.id, u.name, u.email
             FROM users u
             INNER JOIN user_companies uc ON uc.user_id = u.id AND uc.company_id = :cid
             ORDER BY u.name ASC'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

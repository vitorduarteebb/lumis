<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ServiceOrderRepository extends BaseRepository
{
    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(
        int $companyId,
        string $search,
        string $status,
        ?string $dateFrom,
        ?string $dateTo,
        int $page,
        int $perPage,
        ?int $assignedUserId = null,
        ?int $clientId = null
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['so.company_id = :cid', 'so.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($status !== '' && $status !== 'all') {
            $where[] = 'so.status = :st';
            $params['st'] = $status;
        }
        if ($assignedUserId !== null && $assignedUserId > 0) {
            $where[] = 'so.assigned_user_id = :au';
            $params['au'] = $assignedUserId;
        }
        if ($clientId !== null && $clientId > 0) {
            $where[] = 'so.client_id = :cl';
            $params['cl'] = $clientId;
        }
        if ($search !== '') {
            $where[] = '(so.code LIKE :q OR c.name LIKE :q2 OR so.description LIKE :q3)';
            $params['q'] = '%' . $search . '%';
            $params['q2'] = '%' . $search . '%';
            $params['q3'] = '%' . $search . '%';
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = 'DATE(so.opened_at) >= :df';
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $where[] = 'DATE(so.opened_at) <= :dt';
            $params['dt'] = $dateTo;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM service_orders so
             LEFT JOIN clients c ON c.id = so.client_id
             WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT so.*, c.name AS client_name, u.name AS technician_name
                FROM service_orders so
                LEFT JOIN clients c ON c.id = so.client_id
                LEFT JOIN users u ON u.id = so.assigned_user_id
                WHERE {$whereSql}
                ORDER BY so.opened_at DESC, so.id DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT so.*, c.name AS client_name, c.email AS client_email, u.name AS technician_name
             FROM service_orders so
             LEFT JOIN clients c ON c.id = so.client_id
             LEFT JOIN users u ON u.id = so.assigned_user_id
             WHERE so.id = :id AND so.company_id = :cid AND so.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getItems(int $serviceOrderId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT oi.*, p.name AS product_name, s.name AS service_name
             FROM service_order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             LEFT JOIN services s ON s.id = oi.service_id
             WHERE oi.service_order_id = :id ORDER BY oi.id ASC'
        );
        $stmt->execute(['id' => $serviceOrderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param list<array{product_id?: int|null, service_id?: int|null, description?: string, qty: float, unit_price: float, line_discount: float}> $lines
     */
    public function replaceItems(int $serviceOrderId, array $lines): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM service_order_items WHERE service_order_id = :id')->execute(['id' => $serviceOrderId]);
        $ins = $pdo->prepare(
            'INSERT INTO service_order_items (service_order_id, product_id, service_id, description, qty, unit_price, line_discount, line_total)
             VALUES (:sid, :pid, :svid, :desc, :qty, :up, :ld, :lt)'
        );
        foreach ($lines as $ln) {
            $qty = (float) ($ln['qty'] ?? 1);
            $up = (float) ($ln['unit_price'] ?? 0);
            $ld = (float) ($ln['line_discount'] ?? 0);
            $lt = max(0, $qty * $up - $ld);
            $ins->execute([
                'sid' => $serviceOrderId,
                'pid' => isset($ln['product_id']) && (int) $ln['product_id'] > 0 ? (int) $ln['product_id'] : null,
                'svid' => isset($ln['service_id']) && (int) $ln['service_id'] > 0 ? (int) $ln['service_id'] : null,
                'desc' => $ln['description'] ?? null,
                'qty' => $qty,
                'up' => $up,
                'ld' => $ld,
                'lt' => $lt,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO service_orders (company_id, client_id, code, status, priority, description, internal_notes, customer_notes,
             assigned_user_id, quote_id, os_type, opened_at, expected_at, completed_at, created_at, updated_at)
             VALUES (:cid, :clid, :code, :st, :pri, :desc, :in, :cn, :aid, :qid, :otyp, :op, :ex, :comp, NOW(), NOW())'
        );
        $stmt->execute([
            'cid' => $companyId,
            'clid' => $data['client_id'] ?? null,
            'code' => $data['code'] ?? 'TMP',
            'st' => $data['status'] ?? 'open',
            'pri' => $data['priority'] ?? 'normal',
            'desc' => $data['description'] ?? null,
            'in' => $data['internal_notes'] ?? null,
            'cn' => $data['customer_notes'] ?? null,
            'aid' => $data['assigned_user_id'] ?? null,
            'qid' => $data['quote_id'] ?? null,
            'otyp' => $data['os_type'] ?? null,
            'op' => $data['opened_at'] ?? date('Y-m-d H:i:s'),
            'ex' => $data['expected_at'] ?? null,
            'comp' => $data['completed_at'] ?? null,
        ]);
        $id = (int) $this->pdo()->lastInsertId();
        if (($data['code'] ?? 'TMP') === 'TMP' || ($data['code'] ?? '') === '') {
            $code = sprintf('OS-%d', $id);
            $this->pdo()->prepare('UPDATE service_orders SET code = :c WHERE id = :id')->execute(['c' => $code, 'id' => $id]);
        }

        return $id;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $companyId, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE service_orders SET client_id = :clid, status = :st, priority = :pri, description = :desc,
             internal_notes = :in, customer_notes = :cn, assigned_user_id = :aid, os_type = :otyp,
             expected_at = :ex, completed_at = :comp, updated_at = NOW()
             WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute([
            'clid' => $data['client_id'] ?? null,
            'st' => $data['status'],
            'pri' => $data['priority'] ?? 'normal',
            'desc' => $data['description'] ?? null,
            'in' => $data['internal_notes'] ?? null,
            'cn' => $data['customer_notes'] ?? null,
            'aid' => $data['assigned_user_id'] ?? null,
            'otyp' => $data['os_type'] ?? null,
            'ex' => $data['expected_at'] ?? null,
            'comp' => $data['completed_at'] ?? null,
            'id' => $id,
            'cid' => $companyId,
        ]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $this->pdo()->prepare(
            'UPDATE service_orders SET deleted_at = NOW(), status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        )->execute(['st' => 'cancelled', 'id' => $id, 'cid' => $companyId]);
    }

    /**
     * @return array<string, int>
     */
    public function countByStatus(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT status, COUNT(*) AS cnt FROM service_orders WHERE company_id = :cid AND deleted_at IS NULL GROUP BY status'
        );
        $stmt->execute(['cid' => $companyId]);
        $out = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[(string) $r['status']] = (int) $r['cnt'];
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recent(int $companyId, int $limit): array
    {
        $limit = max(1, min(50, $limit));
        $stmt = $this->pdo()->prepare(
            "SELECT so.*, c.name AS client_name
             FROM service_orders so
             LEFT JOIN clients c ON c.id = so.client_id
             WHERE so.company_id = :cid AND so.deleted_at IS NULL
             ORDER BY so.opened_at DESC LIMIT {$limit}"
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * O.S. abertas com previsão vencida.
     *
     * @return list<array<string, mixed>>
     */
    public function overdue(int $companyId, int $limit): array
    {
        $limit = max(1, min(30, $limit));
        $stmt = $this->pdo()->prepare(
            "SELECT so.*, c.name AS client_name
             FROM service_orders so
             LEFT JOIN clients c ON c.id = so.client_id
             WHERE so.company_id = :cid AND so.deleted_at IS NULL
             AND so.status NOT IN ('done','delivered','cancelled')
             AND so.expected_at IS NOT NULL AND so.expected_at < NOW()
             ORDER BY so.expected_at ASC LIMIT {$limit}"
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

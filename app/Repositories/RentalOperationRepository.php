<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class RentalOperationRepository extends BaseRepository
{
    /** @var list<string> */
    public const STATUSES = [
        'pending', 'in_route', 'delivered', 'delivered_issues', 'pickup_pending', 'collected', 'cancelled',
    ];

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        'pending' => 'Pendente',
        'in_route' => 'Em rota',
        'delivered' => 'Entregue',
        'delivered_issues' => 'Entregue com ressalvas',
        'pickup_pending' => 'Coleta pendente',
        'collected' => 'Coletado',
        'cancelled' => 'Cancelado',
    ];

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        'delivery' => 'Entrega',
        'pickup' => 'Coleta',
        'both' => 'Entrega e coleta',
    ];

    public function nextDocumentNumber(int $companyId): string
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COUNT(*) FROM rental_operations WHERE company_id = :cid'
        );
        $stmt->execute(['cid' => $companyId]);
        $n = (int) $stmt->fetchColumn() + 1;

        return 'LOC-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO rental_operations (
                company_id, store_id, document_number, client_id, rental_date,
                expected_delivery_date, expected_pickup_date, cep, street, address_number, complement,
                district, city, state, reference, latitude, longitude,
                contact_name, phone_primary, phone_secondary, notes_internal, notes_driver,
                operation_type, status, delivery_user_id, created_by, created_at, updated_at
            ) VALUES (
                :cid, :sid, :doc, :clid, :rd,
                :edd, :epd, :cep, :st, :num, :comp,
                :dist, :city, :uf, :ref, :lat, :lng,
                :cname, :ph1, :ph2, :ni, :nd,
                :otype, :stt, :duid, :cb, NOW(), NOW()
            )'
        );
        $stmt->execute([
            'cid' => $companyId,
            'sid' => $data['store_id'] ?? null,
            'doc' => $data['document_number'],
            'clid' => $data['client_id'],
            'rd' => $data['rental_date'],
            'edd' => $data['expected_delivery_date'] ?? null,
            'epd' => $data['expected_pickup_date'] ?? null,
            'cep' => $data['cep'] ?? null,
            'st' => $data['street'] ?? null,
            'num' => $data['address_number'] ?? null,
            'comp' => $data['complement'] ?? null,
            'dist' => $data['district'] ?? null,
            'city' => $data['city'] ?? null,
            'uf' => $data['state'] ?? null,
            'ref' => $data['reference'] ?? null,
            'lat' => $data['latitude'] ?? null,
            'lng' => $data['longitude'] ?? null,
            'cname' => $data['contact_name'] ?? null,
            'ph1' => $data['phone_primary'] ?? null,
            'ph2' => $data['phone_secondary'] ?? null,
            'ni' => $data['notes_internal'] ?? null,
            'nd' => $data['notes_driver'] ?? null,
            'otype' => $data['operation_type'] ?? 'both',
            'stt' => $data['status'] ?? 'pending',
            'duid' => $data['delivery_user_id'] ?? null,
            'cb' => $data['created_by'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $companyId, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE rental_operations SET
                store_id = :sid, client_id = :clid, rental_date = :rd,
                expected_delivery_date = :edd, expected_pickup_date = :epd,
                cep = :cep, street = :st, address_number = :num, complement = :comp,
                district = :dist, city = :city, state = :uf, reference = :ref,
                latitude = :lat, longitude = :lng,
                contact_name = :cname, phone_primary = :ph1, phone_secondary = :ph2,
                notes_internal = :ni, notes_driver = :nd, operation_type = :otype,
                status = :stt, delivery_user_id = :duid, updated_by = :ub, updated_at = NOW()
             WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute([
            'sid' => $data['store_id'] ?? null,
            'clid' => $data['client_id'],
            'rd' => $data['rental_date'],
            'edd' => $data['expected_delivery_date'] ?? null,
            'epd' => $data['expected_pickup_date'] ?? null,
            'cep' => $data['cep'] ?? null,
            'st' => $data['street'] ?? null,
            'num' => $data['address_number'] ?? null,
            'comp' => $data['complement'] ?? null,
            'dist' => $data['district'] ?? null,
            'city' => $data['city'] ?? null,
            'uf' => $data['state'] ?? null,
            'ref' => $data['reference'] ?? null,
            'lat' => $data['latitude'] ?? null,
            'lng' => $data['longitude'] ?? null,
            'cname' => $data['contact_name'] ?? null,
            'ph1' => $data['phone_primary'] ?? null,
            'ph2' => $data['phone_secondary'] ?? null,
            'ni' => $data['notes_internal'] ?? null,
            'nd' => $data['notes_driver'] ?? null,
            'otype' => $data['operation_type'] ?? 'both',
            'stt' => $data['status'] ?? 'pending',
            'duid' => $data['delivery_user_id'] ?? null,
            'ub' => $data['updated_by'] ?? null,
            'id' => $id,
            'cid' => $companyId,
        ]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE rental_operations SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
    }

    /**
     * Locação atribuída ao entregador (painel).
     *
     * @return array<string, mixed>|null
     */
    public function findForDeliveryUser(int $id, int $companyId, int $deliveryUserId): ?array
    {
        $row = $this->findById($id, $companyId);
        if ($row === null) {
            return null;
        }
        if ((int) ($row['delivery_user_id'] ?? 0) !== $deliveryUserId) {
            return null;
        }

        return $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT ro.*, c.name AS client_name, c.phone AS client_phone, c.mobile AS client_mobile,
                    u.name AS delivery_name
             FROM rental_operations ro
             LEFT JOIN clients c ON c.id = ro.client_id
             LEFT JOIN users u ON u.id = ro.delivery_user_id
             WHERE ro.id = :id AND ro.company_id = :cid AND ro.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(
        int $companyId,
        string $search,
        string $status,
        int $deliveryUserId,
        int $clientId,
        ?string $dateFrom,
        ?string $dateTo,
        string $district,
        int $page,
        int $perPage
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['ro.company_id = :cid', 'ro.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($status !== '' && $status !== 'all') {
            $where[] = 'ro.status = :st';
            $params['st'] = $status;
        }
        if ($deliveryUserId > 0) {
            $where[] = 'ro.delivery_user_id = :du';
            $params['du'] = $deliveryUserId;
        }
        if ($clientId > 0) {
            $where[] = 'ro.client_id = :cl';
            $params['cl'] = $clientId;
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = 'ro.rental_date >= :df';
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $where[] = 'ro.rental_date <= :dt';
            $params['dt'] = $dateTo;
        }
        if ($search !== '') {
            $where[] = '(ro.document_number LIKE :q OR c.name LIKE :q2 OR ro.street LIKE :q3 OR ro.city LIKE :q4 OR ro.reference LIKE :q5)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
            $params['q4'] = $w;
            $params['q5'] = $w;
        }
        if ($district !== '') {
            $where[] = 'ro.district LIKE :dist';
            $params['dist'] = '%' . $district . '%';
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM rental_operations ro LEFT JOIN clients c ON c.id = ro.client_id WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT ro.*, c.name AS client_name, u.name AS delivery_name
                FROM rental_operations ro
                LEFT JOIN clients c ON c.id = ro.client_id
                LEFT JOIN users u ON u.id = ro.delivery_user_id
                WHERE {$whereSql}
                ORDER BY ro.id DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * Locações pendentes de atribuição ou lista para distribuição.
     *
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginateForDistribution(
        int $companyId,
        string $search,
        string $status,
        int $deliveryUserId,
        int $clientId,
        ?string $dateFrom,
        ?string $dateTo,
        string $district,
        bool $onlyUnassigned,
        int $page,
        int $perPage
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['ro.company_id = :cid', 'ro.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($onlyUnassigned) {
            $where[] = 'ro.delivery_user_id IS NULL';
        }
        if ($status !== '' && $status !== 'all') {
            $where[] = 'ro.status = :st';
            $params['st'] = $status;
        }
        if ($deliveryUserId > 0) {
            $where[] = 'ro.delivery_user_id = :du';
            $params['du'] = $deliveryUserId;
        }
        if ($clientId > 0) {
            $where[] = 'ro.client_id = :cl';
            $params['cl'] = $clientId;
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = 'ro.rental_date >= :df';
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $where[] = 'ro.rental_date <= :dt';
            $params['dt'] = $dateTo;
        }
        if ($district !== '') {
            $where[] = 'ro.district LIKE :dist';
            $params['dist'] = '%' . $district . '%';
        }
        if ($search !== '') {
            $where[] = '(ro.document_number LIKE :q OR c.name LIKE :q2 OR ro.street LIKE :q3 OR ro.city LIKE :q4 OR ro.reference LIKE :q5)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
            $params['q4'] = $w;
            $params['q5'] = $w;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM rental_operations ro LEFT JOIN clients c ON c.id = ro.client_id WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT ro.*, c.name AS client_name, u.name AS delivery_name
                FROM rental_operations ro
                LEFT JOIN clients c ON c.id = ro.client_id
                LEFT JOIN users u ON u.id = ro.delivery_user_id
                WHERE {$whereSql}
                ORDER BY ro.expected_delivery_date IS NULL, ro.expected_delivery_date ASC, ro.id DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * Atualização pelo entregador: status, observação no histórico e texto em notes_driver.
     */
    public function appendDriverNotesAndUpdateStatus(
        int $id,
        int $companyId,
        int $deliveryUserId,
        string $newStatus,
        ?string $historyNote,
        ?string $additionalDriverNote,
        ?int $userId
    ): bool {
        $row = $this->findForDeliveryUser($id, $companyId, $deliveryUserId);
        if ($row === null) {
            return false;
        }
        $old = (string) ($row['status'] ?? '');
        $notesDriver = (string) ($row['notes_driver'] ?? '');
        if ($additionalDriverNote !== null && trim($additionalDriverNote) !== '') {
            $line = '[' . date('d/m/Y H:i') . '] ' . trim($additionalDriverNote);
            $notesDriver = trim($notesDriver . "\n" . $line);
        }
        $stmt = $this->pdo()->prepare(
            'UPDATE rental_operations SET status = :st, notes_driver = :nd, updated_by = :ub, updated_at = NOW()
             WHERE id = :id AND company_id = :cid AND delivery_user_id = :du AND deleted_at IS NULL'
        );
        $stmt->execute([
            'st' => $newStatus,
            'nd' => $notesDriver !== '' ? $notesDriver : null,
            'ub' => $userId,
            'id' => $id,
            'cid' => $companyId,
            'du' => $deliveryUserId,
        ]);
        $this->addHistory($id, $old !== '' ? $old : null, $newStatus, $historyNote, $userId);

        return true;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listItems(int $rentalOperationId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM rental_operation_items WHERE rental_operation_id = :rid ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['rid' => $rentalOperationId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function replaceItems(int $rentalOperationId, array $items): void
    {
        $this->pdo()->prepare('DELETE FROM rental_operation_items WHERE rental_operation_id = :rid')
            ->execute(['rid' => $rentalOperationId]);
        $sort = 0;
        foreach ($items as $it) {
            $name = trim((string) ($it['product_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $qty = (float) ($it['qty'] ?? 1);
            if ($qty <= 0) {
                $qty = 1;
            }
            $this->pdo()->prepare(
                'INSERT INTO rental_operation_items (rental_operation_id, product_id, product_name, qty, notes, sort_order, created_at, updated_at)
                 VALUES (:rid, :pid, :pn, :qty, :notes, :so, NOW(), NOW())'
            )->execute([
                'rid' => $rentalOperationId,
                'pid' => !empty($it['product_id']) ? (int) $it['product_id'] : null,
                'pn' => $name,
                'qty' => $qty,
                'notes' => trim((string) ($it['notes'] ?? '')) === '' ? null : trim((string) $it['notes']),
                'so' => $sort++,
            ]);
        }
    }

    public function addHistory(int $rentalOperationId, ?string $from, string $to, ?string $note, ?int $userId): void
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO rental_operation_status_history (rental_operation_id, from_status, to_status, note, user_id, created_at)
             VALUES (:rid, :fs, :ts, :n, :uid, NOW())'
        );
        $stmt->execute([
            'rid' => $rentalOperationId,
            'fs' => $from,
            'ts' => $to,
            'n' => $note,
            'uid' => $userId,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listHistory(int $rentalOperationId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT h.*, u.name AS user_name
             FROM rental_operation_status_history h
             LEFT JOIN users u ON u.id = h.user_id
             WHERE h.rental_operation_id = :rid
             ORDER BY h.id DESC'
        );
        $stmt->execute(['rid' => $rentalOperationId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id, int $companyId, string $newStatus, ?string $note, ?int $userId): void
    {
        $row = $this->findById($id, $companyId);
        if ($row === null) {
            return;
        }
        $old = (string) ($row['status'] ?? '');
        $stmt = $this->pdo()->prepare(
            'UPDATE rental_operations SET status = :st, updated_by = :ub, updated_at = NOW() WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute(['st' => $newStatus, 'ub' => $userId, 'id' => $id, 'cid' => $companyId]);
        $this->addHistory($id, $old !== '' ? $old : null, $newStatus, $note, $userId);
    }

    /**
     * @param list<int> $ids
     */
    public function assignBatch(int $companyId, array $ids, ?int $deliveryUserId, ?int $updatedBy): int
    {
        if ($ids === []) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$deliveryUserId, $updatedBy, $companyId], $ids);
        $stmt = $this->pdo()->prepare(
            "UPDATE rental_operations SET delivery_user_id = ?, updated_by = ?, updated_at = NOW()
             WHERE company_id = ? AND deleted_at IS NULL AND id IN ({$placeholders})"
        );
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginateForDeliveryUser(
        int $companyId,
        int $userId,
        string $status,
        int $page,
        int $perPage
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['ro.company_id = :cid', 'ro.deleted_at IS NULL', 'ro.delivery_user_id = :uid'];
        $params = ['cid' => $companyId, 'uid' => $userId];
        if ($status !== '' && $status !== 'all') {
            $where[] = 'ro.status = :st';
            $params['st'] = $status;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM rental_operations ro WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT ro.*, c.name AS client_name
                FROM rental_operations ro
                LEFT JOIN clients c ON c.id = ro.client_id
                WHERE {$whereSql}
                ORDER BY ro.expected_delivery_date IS NULL, ro.expected_delivery_date ASC, ro.id DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * Contagem por entregador (status não cancelado).
     *
     * @return list<array{delivery_user_id: int|null, cnt: int, name: string|null}>
     */
    public function countsByDeliveryUser(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            "SELECT ro.delivery_user_id, COUNT(*) AS cnt, u.name
             FROM rental_operations ro
             LEFT JOIN users u ON u.id = ro.delivery_user_id
             WHERE ro.company_id = :cid AND ro.deleted_at IS NULL AND ro.status NOT IN ('cancelled','collected','delivered','delivered_issues')
             GROUP BY ro.delivery_user_id, u.name
             ORDER BY cnt DESC"
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Max updated_at para polling (painel entregador).
     */
    public function maxUpdatedAtForDeliveryUser(int $companyId, int $userId): ?string
    {
        $stmt = $this->pdo()->prepare(
            'SELECT MAX(updated_at) FROM rental_operations WHERE company_id = :cid AND delivery_user_id = :uid AND deleted_at IS NULL'
        );
        $stmt->execute(['cid' => $companyId, 'uid' => $userId]);
        $v = $stmt->fetchColumn();

        return $v !== false && $v !== null ? (string) $v : null;
    }
}

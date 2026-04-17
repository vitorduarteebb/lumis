<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CarrierRepository extends BaseRepository
{
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM carriers WHERE id = :id AND company_id = :cid AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(int $companyId, string $search, ?string $statusFilter, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['company_id = :cid', 'deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($search !== '') {
            $where[] = '(legal_name LIKE :q OR trade_name LIKE :q2 OR document LIKE :q3 OR email LIKE :q4)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
            $params['q4'] = $w;
        }
        if ($statusFilter === '1' || $statusFilter === '0') {
            $where[] = 'status = :st';
            $params['st'] = (int) $statusFilter;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM carriers WHERE {$whereSql}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $sql = "SELECT * FROM carriers WHERE {$whereSql} ORDER BY legal_name ASC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data, ?int $createdBy): int
    {
        $sql = 'INSERT INTO carriers (
            company_id, legal_name, trade_name, document, state_registration, email, phone, mobile,
            cep, street, address_number, complement, district, city, state, notes, status,
            created_by, created_at, updated_at
        ) VALUES (
            :company_id, :legal_name, :trade_name, :document, :state_registration, :email, :phone, :mobile,
            :cep, :street, :address_number, :complement, :district, :city, :state, :notes, :status,
            :created_by, NOW(), NOW()
        )';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
            'legal_name' => $data['legal_name'],
            'trade_name' => $data['trade_name'],
            'document' => $data['document'],
            'state_registration' => $data['state_registration'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'mobile' => $data['mobile'],
            'cep' => $data['cep'],
            'street' => $data['street'],
            'address_number' => $data['address_number'],
            'complement' => $data['complement'],
            'district' => $data['district'],
            'city' => $data['city'],
            'state' => $data['state'],
            'notes' => $data['notes'],
            'status' => $data['status'],
            'created_by' => $createdBy,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $companyId, array $data, ?int $updatedBy): void
    {
        $sql = 'UPDATE carriers SET
            legal_name = :legal_name, trade_name = :trade_name, document = :document, state_registration = :state_registration,
            email = :email, phone = :phone, mobile = :mobile, cep = :cep, street = :street, address_number = :address_number,
            complement = :complement, district = :district, city = :city, state = :state, notes = :notes, status = :status,
            updated_by = :updated_by, updated_at = NOW()
            WHERE id = :id AND company_id = :cid AND deleted_at IS NULL';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'cid' => $companyId,
            'legal_name' => $data['legal_name'],
            'trade_name' => $data['trade_name'],
            'document' => $data['document'],
            'state_registration' => $data['state_registration'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'mobile' => $data['mobile'],
            'cep' => $data['cep'],
            'street' => $data['street'],
            'address_number' => $data['address_number'],
            'complement' => $data['complement'],
            'district' => $data['district'],
            'city' => $data['city'],
            'state' => $data['state'],
            'notes' => $data['notes'],
            'status' => $data['status'],
            'updated_by' => $updatedBy,
        ]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE carriers SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
    }
}

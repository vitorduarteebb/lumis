<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ClientRepository extends BaseRepository
{
    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $sql = 'SELECT * FROM clients WHERE id = :id AND company_id = :cid AND deleted_at IS NULL LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
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
            $where[] = '(name LIKE :q OR trade_name LIKE :q2 OR document LIKE :q3 OR email LIKE :q4 OR phone LIKE :q5 OR mobile LIKE :q6)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
            $params['q4'] = $w;
            $params['q5'] = $w;
            $params['q6'] = $w;
        }
        if ($statusFilter === '1' || $statusFilter === '0') {
            $where[] = 'status = :st';
            $params['st'] = (int) $statusFilter;
        }
        $whereSql = implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) FROM clients WHERE {$whereSql}";
        $stmt = $this->pdo()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT * FROM clients WHERE {$whereSql} ORDER BY name ASC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allForCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name, trade_name FROM clients WHERE company_id = :cid AND deleted_at IS NULL ORDER BY name ASC'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data, ?int $createdBy): int
    {
        $sql = 'INSERT INTO clients (
            company_id, person_type, name, trade_name, document, state_registration,
            email, phone, mobile, contact_name, cep, street, address_number, complement,
            district, city, state, notes, status, created_by, created_at, updated_at
        ) VALUES (
            :company_id, :person_type, :name, :trade_name, :document, :state_registration,
            :email, :phone, :mobile, :contact_name, :cep, :street, :address_number, :complement,
            :district, :city, :state, :notes, :status, :created_by, NOW(), NOW()
        )';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
            'person_type' => $data['person_type'],
            'name' => $data['name'],
            'trade_name' => $data['trade_name'],
            'document' => $data['document'],
            'state_registration' => $data['state_registration'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'mobile' => $data['mobile'],
            'contact_name' => $data['contact_name'],
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
        $sql = 'UPDATE clients SET
            person_type = :person_type, name = :name, trade_name = :trade_name, document = :document,
            state_registration = :state_registration, email = :email, phone = :phone, mobile = :mobile,
            contact_name = :contact_name, cep = :cep, street = :street, address_number = :address_number,
            complement = :complement, district = :district, city = :city, state = :state, notes = :notes,
            status = :status, updated_by = :updated_by, updated_at = NOW()
            WHERE id = :id AND company_id = :cid AND deleted_at IS NULL';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'cid' => $companyId,
            'person_type' => $data['person_type'],
            'name' => $data['name'],
            'trade_name' => $data['trade_name'],
            'document' => $data['document'],
            'state_registration' => $data['state_registration'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'mobile' => $data['mobile'],
            'contact_name' => $data['contact_name'],
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
            'UPDATE clients SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForSelect(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name FROM clients WHERE company_id = :cid AND deleted_at IS NULL ORDER BY name ASC'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CompanyProfileRepository extends BaseRepository
{
    public function ensureRow(int $companyId): void
    {
        $stmt = $this->pdo()->prepare('SELECT 1 FROM company_profiles WHERE company_id = :cid LIMIT 1');
        $stmt->execute(['cid' => $companyId]);
        if ($stmt->fetchColumn()) {
            return;
        }
        $this->pdo()->prepare(
            'INSERT INTO company_profiles (company_id, created_at, updated_at) VALUES (:cid, NOW(), NOW())'
        )->execute(['cid' => $companyId]);
    }

    /**
     * @return array<string, mixed>
     */
    public function findByCompanyId(int $companyId): array
    {
        $this->ensureRow($companyId);
        $stmt = $this->pdo()->prepare('SELECT * FROM company_profiles WHERE company_id = :cid LIMIT 1');
        $stmt->execute(['cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateProfile(int $companyId, array $data): void
    {
        $this->ensureRow($companyId);
        $fields = [
            'display_name', 'app_title', 'legal_name', 'trade_name', 'document_cnpj', 'state_registration', 'municipal_registration',
            'email', 'phone', 'mobile', 'website', 'cep', 'street', 'address_number', 'complement', 'district',
            'city', 'state', 'timezone', 'locale', 'default_currency', 'default_page_size',
            'logo_path', 'favicon_path', 'primary_color', 'accent_color', 'notes',
        ];
        $set = [];
        $params = ['cid' => $companyId];
        foreach ($fields as $f) {
            if (!array_key_exists($f, $data)) {
                continue;
            }
            $set[] = "{$f} = :{$f}";
            $params[$f] = $data[$f];
        }
        if ($set === []) {
            return;
        }
        $sql = 'UPDATE company_profiles SET ' . implode(', ', $set) . ', updated_at = NOW() WHERE company_id = :cid';
        $this->pdo()->prepare($sql)->execute($params);
    }
}

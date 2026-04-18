<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Configuração fiscal por empresa / loja (store_id 0 = padrão empresa).
 */
final class CompanyFiscalSettingsRepository extends BaseRepository
{
    /**
     * @return array<string, mixed>|null
     */
    public function find(int $companyId, int $storeId = 0): ?array
    {
        if (!$this->tableExists('company_fiscal_settings')) {
            return null;
        }
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM company_fiscal_settings WHERE company_id = :cid AND store_id = :sid LIMIT 1'
        );
        $stmt->execute(['cid' => $companyId, 'sid' => $storeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * Resolve config: tenta loja específica, depois store_id 0.
     *
     * @return array<string, mixed>|null
     */
    public function resolveForCompanyStore(int $companyId, ?int $storeId): ?array
    {
        if ($storeId !== null && $storeId > 0) {
            $row = $this->find($companyId, $storeId);
            if ($row !== null) {
                return $row;
            }
        }

        return $this->find($companyId, 0);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function upsert(int $companyId, int $storeId, array $data): void
    {
        if (!$this->tableExists('company_fiscal_settings')) {
            return;
        }
        $fields = [
            'tp_amb', 'crt', 'issuer_ibge_city_code', 'default_series_nfe', 'default_series_nfce',
            'nfce_csc_id', 'nfce_csc_encrypted', 'active_digital_certificate_id',
            'nfse_integration_mode', 'nfse_endpoint', 'nfse_city_ibge', 'nfse_extra_json', 'reform_ready',
        ];
        $existing = $this->find($companyId, $storeId);
        if ($existing === null) {
            $cols = ['company_id', 'store_id', 'created_at', 'updated_at'];
            $vals = [':cid', ':sid', 'NOW()', 'NOW()'];
            $params = ['cid' => $companyId, 'sid' => $storeId];
            foreach ($fields as $f) {
                if (!array_key_exists($f, $data)) {
                    continue;
                }
                $cols[] = $f;
                $vals[] = ':' . $f;
                $params[$f] = $data[$f];
            }
            $sql = 'INSERT INTO company_fiscal_settings (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ')';
            $this->pdo()->prepare($sql)->execute($params);

            return;
        }
        $sets = [];
        $params = ['cid' => $companyId, 'sid' => $storeId];
        foreach ($fields as $f) {
            if (!array_key_exists($f, $data)) {
                continue;
            }
            $sets[] = $f . ' = :' . $f;
            $params[$f] = $data[$f];
        }
        if ($sets === []) {
            return;
        }
        $sets[] = 'updated_at = NOW()';
        $sql = 'UPDATE company_fiscal_settings SET ' . implode(', ', $sets) . ' WHERE company_id = :cid AND store_id = :sid';
        $this->pdo()->prepare($sql)->execute($params);
    }
}

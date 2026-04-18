<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Repositories\CompanyFiscalSettingsRepository;

/**
 * Defaults de configuração fiscal mesclados com company_fiscal_settings.
 */
final class FiscalConfigService
{
    public function __construct(
        private readonly CompanyFiscalSettingsRepository $settingsRepo = new CompanyFiscalSettingsRepository()
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveFor(int $companyId, ?int $storeId): array
    {
        $defaults = [
            'tp_amb' => 2,
            'crt' => null,
            'issuer_ibge_city_code' => null,
            'default_series_nfe' => '1',
            'default_series_nfce' => '1',
            'nfce_csc_id' => null,
            'nfce_csc_encrypted' => null,
            'active_digital_certificate_id' => null,
            'nfse_integration_mode' => 'driver',
            'nfse_endpoint' => null,
            'nfse_city_ibge' => null,
            'nfse_extra_json' => null,
            'reform_ready' => 1,
        ];
        $row = $this->settingsRepo->resolveForCompanyStore($companyId, $storeId);
        if ($row === null) {
            return $defaults;
        }

        return array_merge($defaults, array_intersect_key($row, $defaults));
    }

    public function ambientProduction(int $companyId, ?int $storeId): bool
    {
        $c = $this->resolveFor($companyId, $storeId);

        return (int) ($c['tp_amb'] ?? 2) === 1;
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Repositories\FiscalTransmissionLogRepository;

final class FiscalTransmissionService
{
    public function __construct(
        private readonly FiscalTransmissionLogRepository $logs = new FiscalTransmissionLogRepository()
    ) {
    }

    /**
     * @param array<string, mixed> $row endpoint?, http_status?, request_payload?, response_payload?, error_message?
     */
    public function log(int $companyId, ?int $fiscalDocumentId, string $phase, array $row): int
    {
        return $this->logs->insert($companyId, $fiscalDocumentId, $phase, $row);
    }
}

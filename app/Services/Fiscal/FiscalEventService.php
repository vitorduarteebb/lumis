<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Repositories\FiscalDocumentEventRepository;

final class FiscalEventService
{
    public function __construct(
        private readonly FiscalDocumentEventRepository $events = new FiscalDocumentEventRepository()
    ) {
    }

    /**
     * @param array<string, mixed>|null $metadataJson
     */
    public function register(
        int $companyId,
        int $fiscalDocumentId,
        string $eventType,
        ?string $protocol,
        string $status,
        ?string $payloadPath = null,
        ?string $responsePath = null,
        ?array $metadataJson = null
    ): int {
        return $this->events->insert($companyId, $fiscalDocumentId, $eventType, $protocol, $status, $payloadPath, $responsePath, $metadataJson);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

/**
 * Orquestra NF-e (mod. 55) — XML, SOAP e DANFE na ETAPA 3.
 */
final class NfeEmissionService
{
    public function __construct(
        private readonly FiscalTransmissionService $transmission = new FiscalTransmissionService(),
        private readonly FiscalEventService $events = new FiscalEventService()
    ) {
    }

    /**
     * @throws \RuntimeException implementação SEFAZ pendente (ETAPA 3)
     */
    public function emitProductInvoice(int $fiscalDocumentId, int $companyId): void
    {
        $this->transmission->log($companyId, $fiscalDocumentId, 'transmit', [
            'error_message' => 'Pipeline NF-e ainda não ligado ao sped-nfe/SEFAZ (ETAPA 3).',
        ]);
        $this->events->register($companyId, $fiscalDocumentId, 'pending', null, 'skipped', null, null, [
            'reason' => 'emit_not_implemented',
        ]);
        throw new \RuntimeException('Emissão NF-e real: concluir ETAPA 3 (sped-nfe, SEFAZ, DANFE).');
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

/**
 * NFC-e (mod. 65) — CSC + QRCode; transmissão como NF-e com modelo 65.
 */
final class NfceEmissionService
{
    public function __construct(
        private readonly FiscalTransmissionService $transmission = new FiscalTransmissionService(),
        private readonly FiscalEventService $events = new FiscalEventService()
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function emit(int $fiscalDocumentId, int $companyId): void
    {
        $this->transmission->log($companyId, $fiscalDocumentId, 'transmit', [
            'error_message' => 'NFC-e (mod. 65): CSC/QR e SEFAZ na ETAPA 3.',
        ]);
        $this->events->register($companyId, $fiscalDocumentId, 'pending', null, 'skipped', null, null, [
            'reason' => 'nfce_emit_not_implemented',
        ]);
        throw new \RuntimeException('Emissão NFC-e real: concluir ETAPA 3 (CSC, modelo 65, sped-nfe).');
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Repositories\FiscalDocumentRepository;
use App\Services\Fiscal\Contracts\NfseDriverInterface;

/**
 * NFS-e via driver injetável (municipal / nacional / HTTP).
 */
final class NfseEmissionService
{
    public function __construct(
        private readonly FiscalConfigService $config = new FiscalConfigService(),
        private readonly FiscalDocumentRepository $documents = new FiscalDocumentRepository(),
        private readonly FiscalTransmissionService $transmission = new FiscalTransmissionService(),
        private readonly ?NfseDriverInterface $driver = null
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function emit(int $fiscalDocumentId, int $companyId): void
    {
        $this->config->resolveFor($companyId, null);
        $this->documents->findWithLines($fiscalDocumentId, $companyId);
        if ($this->driver === null) {
            $this->transmission->log($companyId, $fiscalDocumentId, 'transmit', [
                'error_message' => 'Driver NFS-e não configurado (modo nfse_integration_mode / bean).',
            ]);
            throw new \RuntimeException('NFS-e: definir driver concreto conforme município (ETAPA 4).');
        }
        $this->driver->emit([]);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Fiscal\Contracts;

/**
 * Contrato para drivers NFS-e (municipal / nacional / HTTP genérico).
 */
interface NfseDriverInterface
{
    /**
     * @param array<string, mixed> $context Empresa, certificado, DPS/serviço já validados
     * @return array<string, mixed> número, protocolo, caminhos, status bruto
     */
    public function emit(array $context): array;

    /**
     * @param array<string, mixed> $context
     */
    public function cancel(array $context): array;

    /**
     * @param array<string, mixed> $context
     */
    public function queryStatus(array $context): array;
}

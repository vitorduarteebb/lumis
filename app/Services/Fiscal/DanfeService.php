<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

/**
 * Geração DANFE/PDF — dompdf ou Danfe do pacote complementar na ETAPA 3/5.
 */
final class DanfeService
{
    /**
     * @throws \RuntimeException
     */
    public function renderPdfFromAuthorizedXml(string $xmlAuthorizedPath): string
    {
        throw new \RuntimeException('DanfeService: ligar geração de DANFE após autorização (ETAPA 3).');
    }
}

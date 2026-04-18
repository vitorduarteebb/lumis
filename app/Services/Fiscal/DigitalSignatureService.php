<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

/**
 * Assinatura XML NFe/NFCe — implementação concreta usa NFePHP na ETAPA 3.
 */
final class DigitalSignatureService
{
    /**
     * @throws \RuntimeException até integração sped-nfe (ETAPA 3)
     */
    public function signNfeXmlString(string $xml, string $pfxPath, string $pfxPassword): string
    {
        throw new \RuntimeException('Assinatura NF-e: integrar NFePHP (Tools::signNFe) na ETAPA 3.');
    }
}

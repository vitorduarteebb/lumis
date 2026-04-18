<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

/**
 * Montagem de XML oficial — delegar a Make do sped-nfe na ETAPA 3.
 */
final class XmlBuilderService
{
    /**
     * @param array<string, mixed> $payload Estrutura alinhada ao Make NFePHP
     *
     * @throws \RuntimeException
     */
    public function buildNfeXml(array $payload): string
    {
        throw new \RuntimeException('XmlBuilderService: usar NFePHP\\Make na ETAPA 3.');
    }
}

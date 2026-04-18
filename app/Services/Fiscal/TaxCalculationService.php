<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

/**
 * Motor de cálculo tributário e base CBS/IBS (reforma) — expandir com normas vigentes.
 */
final class TaxCalculationService
{
    /**
     * Gera estrutura JSON auxiliar para reform_tax_json / tax_payload_json (campos reservados evolução 2026).
     *
     * @param list<array<string, mixed>> $lines
     * @return array{cbs: float, ibs: float, regime_flag: string, lines: list<array<string, mixed>>}
     */
    public function buildReformPlaceholders(array $lines): array
    {
        $outLines = [];
        foreach ($lines as $i => $ln) {
            $outLines[] = [
                'index' => $i,
                'cbs_base' => 0.0,
                'cbs_amount' => 0.0,
                'ibs_base' => 0.0,
                'ibs_amount' => 0.0,
                'notes' => 'placeholder até leiaute oficial CBS/IBS no documento',
            ];
        }

        return [
            'cbs' => 0.0,
            'ibs' => 0.0,
            'regime_flag' => 'unset',
            'lines' => $outLines,
        ];
    }
}

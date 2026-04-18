<?php

declare(strict_types=1);

/** @var int $meses */
/** @var list<array{ym: string, entradas: float, saidas: float}> $series */

$meses = (int) ($meses ?? 12);
$series = is_array($series ?? null) ? $series : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Financeiro</div>
        <h2 class="h4 mb-1 text-white">Fluxo de caixa</h2>
        <div class="text-secondary small">Entradas e saídas com base em recebimentos e pagamentos registrados nos títulos (status recebido/pago).</div>
    </div>
</div>

<form method="get" action="/financeiro/fluxo-caixa" class="lumis-toolbar mb-4 d-flex flex-wrap gap-2 align-items-end">
    <div>
        <label class="form-label lumis-label small mb-1" for="meses">Meses</label>
        <select class="form-select app-input" id="meses" name="meses" style="max-width: 140px;">
            <?php foreach ([6, 12, 18, 24] as $m): ?>
                <option value="<?= $m ?>" <?= $meses === $m ? 'selected' : '' ?>><?= $m ?> meses</option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-sm btn-primary rounded-3">Atualizar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Mês</th>
                <th class="text-end">Entradas (recebimentos)</th>
                <th class="text-end">Saídas (pagamentos)</th>
                <th class="text-end">Saldo do mês</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($series === []): ?>
                <tr><td colspan="4" class="text-secondary small py-4">Sem movimentação registrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($series as $row): ?>
                <?php
                $ent = (float) ($row['entradas'] ?? 0);
                $sai = (float) ($row['saidas'] ?? 0);
                $saldo = $ent - $sai;
                ?>
                <tr>
                    <td class="text-white"><?= htmlspecialchars((string) ($row['ym'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end text-success small"><?= htmlspecialchars(lumis_money_br($ent), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end text-danger small"><?= htmlspecialchars(lumis_money_br($sai), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end text-white small"><?= htmlspecialchars(lumis_money_br($saldo), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

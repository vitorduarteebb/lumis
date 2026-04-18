<?php

declare(strict_types=1);

$nfSum = is_array($nfSum ?? null) ? $nfSum : [];
$rows = is_array($rows ?? null) ? $rows : [];
$byStatus = is_array($nfSum['by_status'] ?? null) ? $nfSum['by_status'] : [];
$kind = (string) ($kind ?? 'all');
$kindL = [
    'all' => 'Todos',
    'product_out' => 'Saída produto',
    'service' => 'Serviço',
    'consumer' => 'Consumidor',
    'purchase_in' => 'Entrada (compra)',
];
$stL = ['draft' => 'Rascunho', 'issued' => 'Emitida', 'cancelled' => 'Cancelada', 'voided' => 'Anulada', 'error' => 'Erro'];

/**
 * @param array<string, mixed> $row
 */
$nfUrl = static function (array $row): string {
    $id = (int) ($row['id'] ?? 0);
    $k = (string) ($row['document_kind'] ?? '');

    return match ($k) {
        'service' => '/notas-fiscais/servicos/' . $id,
        'consumer' => '/notas-fiscais/consumidor/' . $id,
        'purchase_in' => '/notas-fiscais/compras/' . $id,
        default => '/notas-fiscais/produtos/' . $id,
    };
};
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Relatórios</div>
        <h2 class="h4 mb-1 text-white">Notas fiscais</h2>
        <div class="text-secondary small">Totais por status e listagem administrativa.</div>
    </div>
</div>

<div class="row g-2 mb-3" data-report-scope="notas_fiscais">
    <div class="col-md-4">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Valor total (período / filtro)</div>
            <div class="h5 text-white mb-0"><?= htmlspecialchars(lumis_money_br((float) ($nfSum['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="rounded-3 border border-secondary-subtle p-3 bg-dark bg-opacity-25">
            <div class="text-secondary small mb-1">Por status</div>
            <div class="d-flex flex-wrap gap-3">
                <?php if ($byStatus === []): ?>
                    <span class="text-secondary small">Sem registros.</span>
                <?php endif; ?>
                <?php foreach ($byStatus as $stk => $cnt): ?>
                    <span class="small"><span class="text-white"><?= (int) $cnt ?></span> <span class="text-secondary"><?= htmlspecialchars($stL[(string) $stk] ?? (string) $stk, ENT_QUOTES, 'UTF-8') ?></span></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<form method="get" action="<?= htmlspecialchars((string) ($basePath ?? '/relatorios/notas-fiscais'), ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end" data-export-ready="1">
    <select name="kind" class="form-select app-input" style="max-width: 220px;">
        <?php foreach ($kindL as $k => $lab): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $kind === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" class="form-select app-input" style="max-width: 160px;">
        <option value="all">Status (todos)</option>
        <?php foreach (['draft', 'issued', 'cancelled', 'voided', 'error'] as $s): ?>
            <option value="<?= $s ?>" <?= ($statusFilter ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($stL[$s] ?? $s, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateFrom ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateTo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 280px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Número, chave, cliente…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2" data-table="notas_fiscais">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Número</th>
                <th>Tipo</th>
                <th>Cliente / fornecedor</th>
                <th>Status</th>
                <th class="text-end">Total</th>
                <th>Emissão</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="7" class="text-secondary small py-4">Nenhuma nota encontrada. Verifique se a tabela fiscal está migrada e possui dados.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $st = (string) ($row['status'] ?? '');
                $badge = $st === 'issued' ? 'badge-lumis-success' : ($st === 'draft' ? 'badge-lumis-warning' : 'text-bg-secondary');
                $dk = (string) ($row['document_kind'] ?? '');
                ?>
                <tr>
                    <td class="text-white font-monospace small"><?= htmlspecialchars((string) ($row['document_number'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($kindL[$dk] ?? $dk, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['client_name'] ?? $row['supplier_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($stL[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-end text-white small"><?= htmlspecialchars(lumis_money_br((float) ($row['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['issued_at'] ?? $row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($nfUrl($row), ENT_QUOTES, 'UTF-8') ?>">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

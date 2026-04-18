<?php

declare(strict_types=1);

$tot = is_array($tot ?? null) ? $tot : [];
$venc = is_array($venc ?? null) ? $venc : [];
$fluxo = is_array($fluxo ?? null) ? $fluxo : [];
$rows = is_array($rows ?? null) ? $rows : [];
$tab = (string) ($tab ?? 'pagar');
$stL = ['open' => 'Em aberto', 'paid' => ($tab === 'pagar' ? 'Pago' : 'Recebido'), 'cancelled' => 'Cancelado'];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Relatórios</div>
        <h2 class="h4 mb-1 text-white">Financeiro</h2>
        <div class="text-secondary small">Posição, vencidos e fluxo no período (recebimentos/pagamentos efetivados).</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-sm <?= $tab === 'pagar' ? 'btn-primary' : 'btn-lumis-secondary' ?>" href="/relatorios/financeiro?<?= http_build_query(array_merge(array_filter([
            'date_from' => ($dateFrom ?? '') !== '' ? ($dateFrom ?? '') : null,
            'date_to' => ($dateTo ?? '') !== '' ? ($dateTo ?? '') : null,
            'status' => ($statusFilter ?? 'all') !== 'all' ? ($statusFilter ?? '') : null,
            'supplier_id' => (int) ($supplierId ?? 0) > 0 ? (int) $supplierId : null,
            'client_id' => (int) ($clientId ?? 0) > 0 ? (int) $clientId : null,
        ]), ['tab' => 'pagar'])) ?>">Contas a pagar</a>
        <a class="btn btn-sm <?= $tab === 'receber' ? 'btn-primary' : 'btn-lumis-secondary' ?>" href="/relatorios/financeiro?<?= http_build_query(array_merge(array_filter([
            'date_from' => ($dateFrom ?? '') !== '' ? ($dateFrom ?? '') : null,
            'date_to' => ($dateTo ?? '') !== '' ? ($dateTo ?? '') : null,
            'status' => ($statusFilter ?? 'all') !== 'all' ? ($statusFilter ?? '') : null,
            'supplier_id' => (int) ($supplierId ?? 0) > 0 ? (int) $supplierId : null,
            'client_id' => (int) ($clientId ?? 0) > 0 ? (int) $clientId : null,
        ]), ['tab' => 'receber'])) ?>">Contas a receber</a>
    </div>
</div>

<div class="row g-2 mb-3" data-report-scope="financeiro">
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">A pagar em aberto</div>
            <div class="h5 text-warning mb-0"><?= htmlspecialchars(lumis_money_br((float) ($tot['ap_open'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">A receber em aberto</div>
            <div class="h5 text-white mb-0"><?= htmlspecialchars(lumis_money_br((float) ($tot['ar_open'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Vencido (a pagar)</div>
            <div class="small text-white"><?= htmlspecialchars(lumis_money_br((float) ($venc['ap_overdue'] ?? 0)), ENT_QUOTES, 'UTF-8') ?> <span class="text-secondary">(<?= (int) ($venc['ap_overdue_count'] ?? 0) ?> tít.)</span></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Vencido (a receber)</div>
            <div class="small text-white"><?= htmlspecialchars(lumis_money_br((float) ($venc['ar_overdue'] ?? 0)), ENT_QUOTES, 'UTF-8') ?> <span class="text-secondary">(<?= (int) ($venc['ar_overdue_count'] ?? 0) ?> tít.)</span></div>
        </div>
    </div>
</div>

<?php if (($dateFrom ?? '') !== '' && ($dateTo ?? '') !== ''): ?>
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <div class="rounded-3 border border-secondary-subtle p-3 bg-dark bg-opacity-25">
                <div class="text-secondary small">Fluxo no período — recebimentos</div>
                <div class="text-success"><?= htmlspecialchars(lumis_money_br((float) ($fluxo['entradas'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rounded-3 border border-secondary-subtle p-3 bg-dark bg-opacity-25">
                <div class="text-secondary small">Fluxo no período — pagamentos</div>
                <div class="text-danger"><?= htmlspecialchars(lumis_money_br((float) ($fluxo['saidas'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rounded-3 border border-secondary-subtle p-3 bg-dark bg-opacity-25">
                <div class="text-secondary small">Saldo do período</div>
                <div class="text-white"><?= htmlspecialchars(lumis_money_br((float) ($fluxo['saldo'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<form method="get" action="<?= htmlspecialchars((string) ($basePath ?? '/relatorios/financeiro'), ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end" data-export-ready="1">
    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateFrom ?? ''), ENT_QUOTES, 'UTF-8') ?>" title="Filtra vencimento dos títulos e alimenta o fluxo quando ambas as datas estiverem preenchidas">
    <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateTo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <select name="status" class="form-select app-input" style="max-width: 160px;">
        <option value="all">Status (todos)</option>
        <?php foreach (['open', 'paid', 'cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= ($statusFilter ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($stL[$s] ?? $s, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <?php if ($tab === 'pagar'): ?>
        <select name="supplier_id" class="form-select app-input" style="max-width: 220px;">
            <option value="0">Fornecedor (todos)</option>
            <?php foreach (is_array($suppliers ?? null) ? $suppliers : [] as $s): ?>
                <option value="<?= (int) ($s['id'] ?? 0) ?>" <?= (int) ($supplierId ?? 0) === (int) ($s['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    <?php else: ?>
        <select name="client_id" class="form-select app-input" style="max-width: 220px;">
            <option value="0">Cliente (todos)</option>
            <?php foreach (is_array($clients ?? null) ? $clients : [] as $c): ?>
                <option value="<?= (int) ($c['id'] ?? 0) ?>" <?= (int) ($clientId ?? 0) === (int) ($c['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2" data-table="financeiro_<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8') ?>">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Parte</th>
                <th class="text-end">Valor</th>
                <th class="text-end">Pago/recebido</th>
                <th>Vencimento</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="text-secondary small py-4">Nenhum título para os filtros.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $st = (string) ($row['status'] ?? '');
                $badge = $st === 'open' ? 'badge-lumis-warning' : ($st === 'paid' ? 'badge-lumis-success' : 'text-bg-secondary');
                ?>
                <tr>
                    <td class="text-white small"><?= htmlspecialchars((string) ($row['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['party_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end text-white small"><?= htmlspecialchars(lumis_money_br((float) ($row['amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end text-secondary small"><?= htmlspecialchars(lumis_money_br((float) ($row['paid_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($stL[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

<?php

declare(strict_types=1);

$rows = is_array($rows ?? null) ? $rows : [];
$products = is_array($products ?? null) ? $products : [];
$stores = is_array($stores ?? null) ? $stores : [];
$search = (string) ($search ?? '');
$typeFilter = (string) ($typeFilter ?? 'all');
$filterProductId = (int) ($filterProductId ?? 0);
$filterStoreId = (int) ($filterStoreId ?? 0);
$dateFrom = (string) ($dateFrom ?? '');
$dateTo = (string) ($dateTo ?? '');
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$total = (int) ($total ?? 0);
$basePath = (string) ($basePath ?? '/estoque/movimentacoes');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];

$mtLabels = [
    'sale' => 'Venda',
    'purchase' => 'Compra',
    'adjust' => 'Ajuste',
    'transfer_in' => 'Transf. destino',
    'transfer_out' => 'Transf. origem',
    'return_sale' => 'Devol. venda',
    'return_purchase' => 'Devol. compra',
];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Movimentações</h2>
        <div class="text-secondary small">Histórico com saldos e referências.</div>
    </div>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 280px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Ref., observações, SKU…" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="type" class="form-select app-input" style="max-width: 180px;">
        <option value="all">Tipo (todos)</option>
        <?php foreach ($mtLabels as $k => $lab): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $typeFilter === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="product_id" class="form-select app-input" style="max-width: 200px;">
        <option value="0">Produto (todos)</option>
        <?php foreach ($products as $p): ?>
            <?php $pid = (int) ($p['id'] ?? 0); ?>
            <option value="<?= $pid ?>" <?= $filterProductId === $pid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="store_id" class="form-select app-input" style="max-width: 180px;">
        <option value="0">Loja (todas)</option>
        <?php foreach ($stores as $s): ?>
            <?php $sid = (int) ($s['id'] ?? 0); ?>
            <option value="<?= $sid ?>" <?= $filterStoreId === $sid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Data</th>
                <th>Tipo</th>
                <th>Produto</th>
                <th>Loja</th>
                <th class="text-end">Qtd</th>
                <th class="text-end">Antes</th>
                <th class="text-end">Depois</th>
                <th>Ref.</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="10" class="text-secondary small py-4">Nenhum registro.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $mt = (string) ($row['movement_type'] ?? '');
                $qty = (float) ($row['qty'] ?? 0);
                ?>
                <tr>
                    <td class="text-secondary small font-monospace"><?= $rid ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge text-bg-dark border border-secondary-subtle"><?= htmlspecialchars($mtLabels[$mt] ?? $mt, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-white small"><?= htmlspecialchars((string) ($row['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end font-monospace small <?= $qty < 0 ? 'text-warning' : 'text-success' ?>"><?= htmlspecialchars(number_format($qty, 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end text-secondary small"><?= htmlspecialchars(number_format((float) ($row['balance_before'] ?? 0), 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end text-white small"><?= htmlspecialchars(number_format((float) ($row['balance_after'] ?? 0), 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small text-truncate" style="max-width:140px;"><?= htmlspecialchars((string) ($row['reference'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                        <?php if (can('estoque.movimentacoes.view')): ?>
                            <a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($basePath . '/' . $rid, ENT_QUOTES, 'UTF-8') ?>">Detalhe</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

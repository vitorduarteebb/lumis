<?php

declare(strict_types=1);

$rows = is_array($rows ?? null) ? $rows : [];
$products = is_array($products ?? null) ? $products : [];
$filterProductId = (int) ($filterProductId ?? 0);
$dateFrom = (string) ($dateFrom ?? '');
$dateTo = (string) ($dateTo ?? '');
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$total = (int) ($total ?? 0);
$basePath = (string) ($basePath ?? '/estoque/ajustes');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Ajustes</h2>
        <div class="text-secondary small">Entradas e saídas manuais com motivo.</div>
    </div>
    <?php if (can('estoque.ajustes.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/novo">Novo ajuste</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <select name="product_id" class="form-select app-input" style="max-width: 260px;">
        <option value="0">Produto (todos)</option>
        <?php foreach ($products as $p): ?>
            <?php $pid = (int) ($p['id'] ?? 0); ?>
            <option value="<?= $pid ?>" <?= $filterProductId === $pid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
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
                <th>Produto</th>
                <th>Loja</th>
                <th>Tipo</th>
                <th class="text-end">Qtd</th>
                <th>Motivo</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="8" class="text-secondary small py-4">Nenhum ajuste.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $dir = (string) ($row['direction'] ?? '');
                ?>
                <tr>
                    <td class="text-secondary small font-monospace"><?= $rid ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-white small"><?= htmlspecialchars((string) ($row['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $dir === 'out' ? '<span class="badge text-bg-warning">Saída</span>' : '<span class="badge badge-lumis-success">Entrada</span>' ?></td>
                    <td class="text-end font-monospace small"><?= htmlspecialchars(number_format((float) ($row['qty'] ?? 0), 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small text-truncate" style="max-width:180px;"><?= htmlspecialchars((string) ($row['reason_text'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($basePath . '/' . $rid, ENT_QUOTES, 'UTF-8') ?>">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

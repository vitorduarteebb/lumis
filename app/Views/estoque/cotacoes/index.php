<?php

declare(strict_types=1);

$rows = is_array($rows ?? null) ? $rows : [];
$search = (string) ($search ?? '');
$statusFilter = (string) ($statusFilter ?? 'all');
$dateFrom = (string) ($dateFrom ?? '');
$dateTo = (string) ($dateTo ?? '');
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$total = (int) ($total ?? 0);
$basePath = (string) ($basePath ?? '/estoque/cotacoes');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];

$stLab = ['open' => 'Aberta', 'approved' => 'Aprovada', 'cancelled' => 'Cancelada'];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Cotações</h2>
        <div class="text-secondary small">Cotações de compra com fornecedores.</div>
    </div>
    <?php if (can('estoque.cotacoes.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/novo">Nova cotação</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 300px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Número, fornecedor…" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="status" class="form-select app-input" style="max-width: 170px;">
        <option value="all">Status (todos)</option>
        <?php foreach (['open', 'approved', 'cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= htmlspecialchars($stLab[$s] ?? $s, ENT_QUOTES, 'UTF-8') ?></option>
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
                <th>Número</th>
                <th>Fornecedor</th>
                <th>Status</th>
                <th class="text-end">Total</th>
                <th>Data</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="text-secondary small py-4">Nenhuma cotação.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $st = (string) ($row['status'] ?? '');
                $dt = (string) ($row['quoted_at'] ?? $row['created_at'] ?? '');
                ?>
                <tr>
                    <td class="text-white font-monospace small"><?= htmlspecialchars((string) ($row['quote_number'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['supplier_name'] ?? $row['supplier_legal'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if ($st === 'approved'): ?>
                            <span class="badge badge-lumis-success"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php elseif ($st === 'cancelled'): ?>
                            <span class="badge text-bg-secondary"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php else: ?>
                            <span class="badge badge-lumis-warning"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-white small"><?= htmlspecialchars(lumis_money_br((float) ($row['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($dt, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($basePath . '/' . $rid, ENT_QUOTES, 'UTF-8') ?>">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

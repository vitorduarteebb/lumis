<?php

declare(strict_types=1);

$kind = (string) ($kind ?? 'product');
$basePath = (string) ($basePath ?? '/vendas/produtos');
$label = $kind === 'service' ? 'serviços' : 'produtos';
$rows = is_array($rows ?? null) ? $rows : [];
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$total = (int) ($total ?? 0);
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];
$stL = ['open' => 'Aberta', 'finalized' => 'Finalizada', 'cancelled' => 'Cancelada'];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Vendas</div>
        <h2 class="h4 mb-1 text-white">Vendas · <?= $kind === 'service' ? 'Serviços' : 'Produtos' ?></h2>
        <div class="text-secondary small">Pedidos com totais, filtros e ações.</div>
    </div>
    <?php
    $canCreate = $kind === 'service' ? can('vendas.servicos.create') : can('vendas.produtos.create');
    ?>
    <?php if ($canCreate): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath . '/novo', ENT_QUOTES, 'UTF-8') ?>">Nova venda</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 320px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Número, cliente…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="status" class="form-select app-input" style="max-width: 160px;">
        <option value="all">Status (todos)</option>
        <?php foreach (['open', 'finalized', 'cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($stL[$s] ?? $s, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateFrom ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateTo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Número</th>
                <th>Cliente</th>
                <th>Status</th>
                <th class="text-end">Total</th>
                <th>Data</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="text-secondary small py-4">Nenhuma venda encontrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $num = (string) ($row['document_number'] ?? '—');
                $cn = (string) ($row['client_name'] ?? '—');
                $st = (string) ($row['status'] ?? '');
                $tot = lumis_money_br((float) ($row['total_amount'] ?? 0));
                $dt = (string) ($row['issued_at'] ?? $row['created_at'] ?? '');
                ?>
                <tr>
                    <td class="text-white font-monospace"><?= htmlspecialchars($num, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($cn, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= $st === 'finalized' ? 'badge-lumis-success' : ($st === 'cancelled' ? 'text-bg-secondary' : 'badge-lumis-warning') ?>"><?= htmlspecialchars($stL[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-end text-white small"><?= htmlspecialchars($tot, ENT_QUOTES, 'UTF-8') ?></td>
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

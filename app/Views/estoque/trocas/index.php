<?php

declare(strict_types=1);

$rows = is_array($rows ?? null) ? $rows : [];
$search = (string) ($search ?? '');
$kindFilter = (string) ($kindFilter ?? 'all');
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$total = (int) ($total ?? 0);
$basePath = (string) ($basePath ?? '/estoque/trocas-devolucoes');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];

$kindLab = [
    'sale_return' => 'Devolução de venda',
    'purchase_return' => 'Devolução de compra',
    'exchange' => 'Troca',
];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Trocas e devoluções</h2>
        <div class="text-secondary small">Registros com impacto em estoque e histórico.</div>
    </div>
    <?php if (can('estoque.trocas_devolucoes.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/novo">Novo registro</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 300px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Motivo, produto…" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="kind" class="form-select app-input" style="max-width: 220px;">
        <option value="all">Tipo (todos)</option>
        <?php foreach ($kindLab as $k => $lab): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $kindFilter === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
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
                <th>Qtd</th>
                <th>Cliente / Fornecedor</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="7" class="text-secondary small py-4">Nenhum registro.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $rk = (string) ($row['return_kind'] ?? '');
                $cf = trim((string) ($row['client_name'] ?? '')) !== '' ? (string) $row['client_name'] : trim((string) ($row['supplier_name'] ?? ''));
                ?>
                <tr>
                    <td class="font-monospace small"><?= $rid ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small"><?= htmlspecialchars($kindLab[$rk] ?? $rk, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-white small"><?= htmlspecialchars((string) ($row['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="font-monospace small"><?= htmlspecialchars(number_format((float) ($row['qty'] ?? 0), 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($cf !== '' ? $cf : '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($basePath . '/' . $rid, ENT_QUOTES, 'UTF-8') ?>">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

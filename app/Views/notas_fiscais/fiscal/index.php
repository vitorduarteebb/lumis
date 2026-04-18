<?php

declare(strict_types=1);

$cfg = is_array($cfg ?? null) ? $cfg : [];
$perm = (string) ($cfg['perm'] ?? 'notas_fiscais.produtos');
$rows = is_array($rows ?? null) ? $rows : [];
$stores = is_array($stores ?? null) ? $stores : [];
$basePath = (string) ($basePath ?? '/notas-fiscais/produtos');
$search = (string) ($search ?? '');
$statusFilter = (string) ($statusFilter ?? 'all');
$filterStoreId = (int) ($filterStoreId ?? 0);
$dateFrom = (string) ($dateFrom ?? '');
$dateTo = (string) ($dateTo ?? '');
$page = (int) ($page ?? 1);
$total = (int) ($total ?? 0);
$totalPages = (int) ($totalPages ?? 1);
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];

$stLab = [
    'draft' => 'Digitada',
    'issued' => 'Emitida',
    'cancelled' => 'Cancelada',
    'voided' => 'Inutilizada',
    'error' => 'Erro',
];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Notas fiscais</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($pageTitle ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">Controle administrativo; anexe XML/PDF na edição.</div>
    </div>
    <?php if (can($perm . '.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/novo">Nova nota</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 280px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Número, chave, cliente…" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="status" class="form-select app-input" style="max-width: 160px;">
        <option value="all">Status</option>
        <?php foreach (['draft', 'issued', 'cancelled', 'voided', 'error'] as $s): ?>
            <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= htmlspecialchars($stLab[$s] ?? $s, ENT_QUOTES, 'UTF-8') ?></option>
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
                <th>Número</th>
                <th>Série</th>
                <th>Parte</th>
                <th>Status</th>
                <th class="text-end">Total</th>
                <th>Emissão</th>
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
                $st = (string) ($row['status'] ?? '');
                $party = (string) ($row['client_name'] ?? $row['supplier_name'] ?? '—');
                ?>
                <tr>
                    <td class="text-white font-monospace small"><?= htmlspecialchars((string) ($row['document_number'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['series'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($party, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= $st === 'issued' ? 'badge-lumis-success' : ($st === 'cancelled' || $st === 'voided' ? 'text-bg-secondary' : 'badge-lumis-warning') ?>"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-end text-white small"><?= htmlspecialchars(lumis_money_br((float) ($row['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars(substr((string) ($row['issued_at'] ?? $row['created_at'] ?? ''), 0, 16), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($basePath . '/' . $rid, ENT_QUOTES, 'UTF-8') ?>">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

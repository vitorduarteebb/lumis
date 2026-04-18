<?php

declare(strict_types=1);

$rows = is_array($rows ?? null) ? $rows : [];
$summary = is_array($summary ?? null) ? $summary : [];
$sumProd = is_array($sumProd ?? null) ? $sumProd : [];
$sumBal = is_array($sumBal ?? null) ? $sumBal : [];
$sumSvc = is_array($sumSvc ?? null) ? $sumSvc : [];
$kind = (string) ($kind ?? 'product');
$stL = ['open' => 'Aberta', 'finalized' => 'Finalizada', 'cancelled' => 'Cancelada'];
$kindL = ['all' => 'Todas', 'product' => 'Produtos (+ balcão)', 'balcao' => 'Balcão', 'service' => 'Serviços'];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Relatórios</div>
        <h2 class="h4 mb-1 text-white">Vendas</h2>
        <div class="text-secondary small">Resumo, ticket médio e detalhamento com filtros.</div>
    </div>
</div>

<div class="row g-2 mb-2" data-report-scope="vendas">
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Escopo da lista</div>
            <div class="text-white small"><?= htmlspecialchars($kindL[$kind] ?? $kind, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="text-secondary tiny mt-1">Qtd. vendas: <span class="text-white"><?= (int) ($summary['count'] ?? 0) ?></span></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Total vendido</div>
            <div class="h5 text-white mb-0"><?= htmlspecialchars(lumis_money_br((float) ($summary['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Ticket médio</div>
            <div class="h5 text-white mb-0"><?= htmlspecialchars(lumis_money_br((float) ($summary['avg_ticket'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Detalhe por tipo (mesmos filtros)</div>
            <div class="small text-secondary">Produtos: <span class="text-white"><?= htmlspecialchars(lumis_money_br((float) ($sumProd['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></span></div>
            <div class="small text-secondary">Balcão: <span class="text-white"><?= htmlspecialchars(lumis_money_br((float) ($sumBal['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></span></div>
            <div class="small text-secondary">Serviços: <span class="text-white"><?= htmlspecialchars(lumis_money_br((float) ($sumSvc['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></span></div>
        </div>
    </div>
</div>

<form method="get" action="<?= htmlspecialchars((string) ($basePath ?? '/relatorios/vendas'), ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end" data-export-ready="1">
    <select name="kind" class="form-select app-input" style="max-width: 220px;">
        <?php foreach ($kindL as $k => $lab): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $kind === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" class="form-select app-input" style="max-width: 160px;">
        <option value="all">Status (todos)</option>
        <?php foreach (['open', 'finalized', 'cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= ($statusFilter ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($stL[$s] ?? $s, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="client_id" class="form-select app-input" style="max-width: 200px;">
        <option value="0">Cliente (todos)</option>
        <?php foreach (is_array($clients ?? null) ? $clients : [] as $c): ?>
            <option value="<?= (int) ($c['id'] ?? 0) ?>" <?= (int) ($clientId ?? 0) === (int) ($c['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="store_id" class="form-select app-input" style="max-width: 180px;">
        <option value="0">Loja (todas)</option>
        <?php foreach (is_array($stores ?? null) ? $stores : [] as $st): ?>
            <option value="<?= (int) ($st['id'] ?? 0) ?>" <?= (int) ($storeId ?? 0) === (int) ($st['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($st['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="seller_user_id" class="form-select app-input" style="max-width: 200px;">
        <option value="0">Vendedor (todos)</option>
        <?php foreach (is_array($sellers ?? null) ? $sellers : [] as $u): ?>
            <option value="<?= (int) ($u['id'] ?? 0) ?>" <?= (int) ($sellerUserId ?? 0) === (int) ($u['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($u['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateFrom ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateTo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 260px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Número, cliente…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2" data-table="vendas">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Número</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Loja</th>
                <th>Vendedor</th>
                <th>Status</th>
                <th class="text-end">Total</th>
                <th>Data</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="9" class="text-secondary small py-4">Nenhuma venda encontrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $dk = (string) ($row['document_kind'] ?? '');
                $base = $dk === 'service' ? '/vendas/servicos' : '/vendas/produtos';
                ?>
                <tr>
                    <td class="text-white font-monospace small"><?= htmlspecialchars((string) ($row['document_number'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($dk, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['client_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['seller_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php $st = (string) ($row['status'] ?? ''); ?>
                    <td><span class="badge <?= $st === 'finalized' ? 'badge-lumis-success' : ($st === 'cancelled' ? 'text-bg-secondary' : 'badge-lumis-warning') ?>"><?= htmlspecialchars($stL[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-end text-white small"><?= htmlspecialchars(lumis_money_br((float) ($row['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['issued_at'] ?? $row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($base . '/' . $rid, ENT_QUOTES, 'UTF-8') ?>">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

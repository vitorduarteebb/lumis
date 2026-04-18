<?php

declare(strict_types=1);

$osSummary = is_array($osSummary ?? null) ? $osSummary : [];
$rows = is_array($rows ?? null) ? $rows : [];
$stL = [
    'open' => 'Aberta',
    'in_progress' => 'Em andamento',
    'done' => 'Concluída',
    'cancelled' => 'Cancelada',
];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Relatórios</div>
        <h2 class="h4 mb-1 text-white">Ordens de serviço</h2>
        <div class="text-secondary small">Indicadores e lista com filtros por período, status, técnico e cliente.</div>
    </div>
</div>

<div class="row g-2 mb-3" data-report-scope="ordens_servico">
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Total</div>
            <div class="h5 text-white mb-0"><?= (int) ($osSummary['total'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Abertas</div>
            <div class="h5 text-white mb-0"><?= (int) ($osSummary['open'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Em andamento</div>
            <div class="h5 text-white mb-0"><?= (int) ($osSummary['progress'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Concluídas</div>
            <div class="h5 text-white mb-0"><?= (int) ($osSummary['done'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Canceladas</div>
            <div class="h5 text-white mb-0"><?= (int) ($osSummary['cancelled'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Tempo médio (h)</div>
            <div class="h5 text-white mb-0"><?php
                $avg = $osSummary['avg_hours'] ?? null;
            echo $avg !== null ? htmlspecialchars(number_format((float) $avg, 1, ',', '.'), ENT_QUOTES, 'UTF-8') : '—';
            ?></div>
        </div>
    </div>
</div>

<form method="get" action="<?= htmlspecialchars((string) ($basePath ?? '/relatorios/ordens-servico'), ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end" data-export-ready="1">
    <select name="status" class="form-select app-input" style="max-width: 180px;">
        <option value="all">Status (todos)</option>
        <?php foreach ($stL as $k => $lab): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= ($statusFilter ?? '') === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="assigned_user_id" class="form-select app-input" style="max-width: 220px;">
        <option value="0">Técnico (todos)</option>
        <?php foreach (is_array($techs ?? null) ? $techs : [] as $u): ?>
            <option value="<?= (int) ($u['id'] ?? 0) ?>" <?= (int) ($assignedUserId ?? 0) === (int) ($u['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($u['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="client_id" class="form-select app-input" style="max-width: 220px;">
        <option value="0">Cliente (todos)</option>
        <?php foreach (is_array($clients ?? null) ? $clients : [] as $c): ?>
            <option value="<?= (int) ($c['id'] ?? 0) ?>" <?= (int) ($clientId ?? 0) === (int) ($c['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateFrom ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateTo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 280px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Código, descrição, cliente…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2" data-table="ordens_servico">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Responsável</th>
                <th>Status</th>
                <th>Abertura</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="text-secondary small py-4">Nenhuma O.S. encontrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $st = (string) ($row['status'] ?? '');
                ?>
                <tr>
                    <td class="text-white font-monospace small"><?= htmlspecialchars((string) ($row['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['client_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['assigned_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= $st === 'done' ? 'badge-lumis-success' : ($st === 'cancelled' ? 'text-bg-secondary' : 'badge-lumis-warning') ?>"><?= htmlspecialchars($stL[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['opened_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-lumis-secondary" href="/ordens-servico/<?= $rid ?>">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

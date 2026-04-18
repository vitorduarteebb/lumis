<?php

declare(strict_types=1);

$rows = $rows ?? [];
$basePath = (string) ($basePath ?? '/ordens-servico');
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$total = (int) ($total ?? 0);
$search = (string) ($search ?? '');
$statusFilter = (string) ($statusFilter ?? '');
$dateFrom = (string) ($dateFrom ?? '');
$dateTo = (string) ($dateTo ?? '');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];

$stLabels = [
    'open' => 'Aberta', 'in_analysis' => 'Em análise', 'in_progress' => 'Em andamento', 'waiting_part' => 'Aguardando peça',
    'done' => 'Concluída', 'delivered' => 'Entregue', 'cancelled' => 'Cancelada',
];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Ordens de serviço</div>
        <h2 class="h4 mb-1 text-white">Gerenciar O.S.</h2>
        <div class="text-secondary small">Listagem operacional com busca e filtros.</div>
    </div>
    <?php if (can('ordens_servico.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="/ordens-servico/novo">Nova O.S.</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 320px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Código, cliente, descrição…" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="status" class="form-select app-input" style="max-width: 200px;">
        <option value="">Status (todos)</option>
        <?php foreach ($statuses as $st): ?>
            <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= htmlspecialchars($stLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="from" class="form-control app-input" style="max-width: 155px;" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="to" class="form-control app-input" style="max-width: 155px;" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Status</th>
                <th>Prioridade</th>
                <th>Abertura</th>
                <th>Técnico</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="7" class="text-secondary small py-4">Nenhuma O.S. encontrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $code = (string) ($row['code'] ?? '—');
                $cname = (string) ($row['client_name'] ?? '—');
                $st = (string) ($row['status'] ?? '');
                $pr = (string) ($row['priority'] ?? '');
                $op = (string) ($row['opened_at'] ?? '');
                $tech = (string) ($row['technician_name'] ?? '—');
                $prLab = ['low' => 'Baixa', 'normal' => 'Média', 'high' => 'Alta', 'urgent' => 'Urgente'][$pr] ?? $pr;
                ?>
                <tr>
                    <td class="text-white font-monospace"><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge badge-lumis badge-lumis-secondary"><?= htmlspecialchars($stLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-secondary small"><?= htmlspecialchars($prLab, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= $op !== '' ? htmlspecialchars($op, ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($tech, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                        <div class="dropdown d-inline">
                            <button class="btn btn-sm btn-lumis-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Ações</button>
                            <ul class="dropdown-menu dropdown-menu-end app-dropdown border-0">
                                <li><a class="dropdown-item" href="/ordens-servico/<?= $rid ?>">Ver</a></li>
                                <li><a class="dropdown-item" href="/ordens-servico/<?= $rid ?>/pdf" target="_blank">PDF</a></li>
                                <?php if (can('ordens_servico.edit') && !in_array($st, ['delivered', 'cancelled'], true)): ?>
                                    <li><a class="dropdown-item" href="/ordens-servico/<?= $rid ?>/editar">Editar</a></li>
                                <?php endif; ?>
                                <?php if (can('ordens_servico.delete') && !in_array($st, ['cancelled'], true)): ?>
                                    <li>
                                        <form method="post" action="/ordens-servico/<?= $rid ?>/excluir" class="m-0" onsubmit="return confirm('Cancelar esta O.S.?');">
                                            <?= \App\Helpers\Csrf::field() ?>
                                            <button type="submit" class="dropdown-item text-danger">Cancelar O.S.</button>
                                        </form>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

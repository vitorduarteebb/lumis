<?php
declare(strict_types=1);
$rows = is_array($rows ?? null) ? $rows : [];
$clients = is_array($clients ?? null) ? $clients : [];
$search = (string) ($search ?? '');
$statusFilter = (string) ($statusFilter ?? 'all');
$filterClientId = (int) ($filterClientId ?? 0);
$dateFrom = (string) ($dateFrom ?? '');
$dateTo = (string) ($dateTo ?? '');
$page = (int) ($page ?? 1);
$total = (int) ($total ?? 0);
$totalPages = (int) ($totalPages ?? 1);
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];
$basePath = (string) ($basePath ?? '/contratos/servicos');
$stLab = ['active' => 'Ativo', 'suspended' => 'Suspenso', 'closed' => 'Encerrado', 'cancelled' => 'Cancelado'];
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Contratos</div>
        <h2 class="h4 mb-1 text-white">Contratos de serviços</h2>
        <div class="text-secondary small">Prestação de serviços, periodicidade e valores.</div>
    </div>
    <?php if (can('contratos.servicos.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="/contratos/servicos/novo">Novo contrato</a>
    <?php endif; ?>
</div>
<form method="get" action="/contratos/servicos" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 280px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Número, cliente, descrição…" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="status" class="form-select app-input" style="max-width: 160px;">
        <option value="all">Status</option>
        <?php foreach ($stLab as $k => $lab): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $statusFilter === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="client_id" class="form-select app-input" style="max-width: 200px;">
        <option value="0">Cliente (todos)</option>
        <?php foreach ($clients as $c): ?>
            <?php $cid = (int) ($c['id'] ?? 0); ?>
            <option value="<?= $cid ?>" <?= $filterClientId === $cid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
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
                <th>Cliente</th>
                <th>Serviço</th>
                <th>Status</th>
                <th class="text-end">Valor</th>
                <th>Período</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="7" class="text-secondary small py-4">Nenhum contrato.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $st = (string) ($row['status'] ?? '');
                $sd = (string) ($row['start_date'] ?? '');
                $ed = (string) ($row['end_date'] ?? '');
                $period = trim($sd . ($ed !== '' ? ' — ' . $ed : ''));
                ?>
                <tr>
                    <td class="text-white font-monospace small"><?= htmlspecialchars((string) ($row['contract_number'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['client_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['service_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= $st === 'active' ? 'badge-lumis-success' : ($st === 'cancelled' ? 'text-bg-secondary' : 'badge-lumis-warning') ?>"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-end text-white small"><?= htmlspecialchars(lumis_money_br((float) ($row['amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($period !== '' ? $period : '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-lumis-secondary" href="/contratos/servicos/<?= $rid ?>">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

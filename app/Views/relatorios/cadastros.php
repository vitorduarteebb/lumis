<?php

declare(strict_types=1);

$totals = is_array($totals ?? null) ? $totals : [];
$rows = is_array($rows ?? null) ? $rows : [];
$entity = (string) ($entity ?? 'clients');
$el = [
    'clients' => 'Clientes',
    'suppliers' => 'Fornecedores',
    'employees' => 'Funcionários',
    'carriers' => 'Transportadoras',
];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Relatórios</div>
        <h2 class="h4 mb-1 text-white">Cadastros</h2>
        <div class="text-secondary small">Consolidado por tipo com totais e listagem filtrável.</div>
    </div>
</div>

<div class="row g-2 mb-3" data-report-scope="cadastros">
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Clientes</div>
            <div class="h5 text-white mb-0"><?= (int) ($totals['clients'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Fornecedores</div>
            <div class="h5 text-white mb-0"><?= (int) ($totals['suppliers'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Funcionários</div>
            <div class="h5 text-white mb-0"><?= (int) ($totals['employees'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Transportadoras</div>
            <div class="h5 text-white mb-0"><?= (int) ($totals['carriers'] ?? 0) ?></div>
        </div>
    </div>
</div>
<?php if (((string) ($status ?? 'all')) === 'all' && ($dateFrom ?? '') === '' && ($dateTo ?? '') === ''): ?>
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <div class="rounded-3 border border-secondary-subtle p-3 small text-secondary">
                Clientes ativos: <span class="text-white"><?= (int) ($totals['clients_active'] ?? 0) ?></span>
                · Inativos: <span class="text-white"><?= (int) ($totals['clients_inactive'] ?? 0) ?></span>
            </div>
        </div>
    </div>
<?php endif; ?>

<form method="get" action="<?= htmlspecialchars((string) ($basePath ?? '/relatorios/cadastros'), ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end" data-export-ready="1">
    <select name="entity" class="form-select app-input" style="max-width: 200px;" title="Tipo de cadastro">
        <?php foreach ($el as $k => $lab): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $entity === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" class="form-select app-input" style="max-width: 160px;">
        <option value="all">Status (todos)</option>
        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Ativos</option>
        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inativos</option>
    </select>
    <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateFrom ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateTo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 320px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Nome, e-mail, documento…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2" data-table="cadastros">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome / razão</th>
                <th>Documento</th>
                <th>E-mail</th>
                <th>Status</th>
                <th>Cadastro</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="text-secondary small py-4">Nenhum registro para os filtros.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $st = (int) ($row['status'] ?? 0);
                ?>
                <tr>
                    <td class="text-secondary font-monospace small"><?= (int) ($row['id'] ?? 0) ?></td>
                    <td class="text-white small"><?= htmlspecialchars((string) ($row['display_name'] ?? $row['name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['document'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= $st === 1 ? 'badge-lumis-success' : 'text-bg-secondary' ?>"><?= $st === 1 ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

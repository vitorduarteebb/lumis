<?php

declare(strict_types=1);

$quoteKind = (string) ($quoteKind ?? 'product');
$rows = $rows ?? [];
$basePath = (string) ($basePath ?? '/orcamentos/produtos');
$statuses = is_array($statuses ?? null) ? $statuses : [];
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$total = (int) ($total ?? 0);
$search = (string) ($search ?? '');
$statusFilter = (string) ($statusFilter ?? '');
$dateFrom = (string) ($dateFrom ?? '');
$dateTo = (string) ($dateTo ?? '');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];

$stLabels = [
    'open' => 'Aberto',
    'approved' => 'Aprovado',
    'rejected' => 'Recusado',
    'cancelled' => 'Cancelado',
    'converted' => 'Convertido',
];

$permView = $quoteKind === 'product' ? 'orcamentos.produtos.view' : 'orcamentos.servicos.view';
$permCreate = $quoteKind === 'product' ? 'orcamentos.produtos.create' : 'orcamentos.servicos.create';
$permEdit = $quoteKind === 'product' ? 'orcamentos.produtos.edit' : 'orcamentos.servicos.edit';
$permDelete = $quoteKind === 'product' ? 'orcamentos.produtos.delete' : 'orcamentos.servicos.delete';

$section = $quoteKind === 'product' ? 'Produtos' : 'Serviços';
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Orçamentos</div>
        <h2 class="h4 mb-1 text-white">Orçamentos · <?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">Listagem, filtros e ações sobre orçamentos reais.</div>
    </div>
    <?php if (can($permCreate)): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath . '/novo', ENT_QUOTES, 'UTF-8') ?>">Novo orçamento</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 320px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Número ou cliente…" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="status" class="form-select app-input" style="max-width: 180px;">
        <option value="">Status (todos)</option>
        <?php foreach ($statuses as $st): ?>
            <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= htmlspecialchars($stLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="from" class="form-control app-input" style="max-width: 155px;" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>" title="De">
    <input type="date" name="to" class="form-control app-input" style="max-width: 155px;" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>" title="Até">
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Número</th>
                <th>Cliente</th>
                <th>Emissão</th>
                <th>Validade</th>
                <th>Total</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="7" class="text-secondary small py-4">Nenhum orçamento encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $qnum = (string) ($row['quote_number'] ?? '—');
                $cname = (string) ($row['client_name'] ?? '—');
                $iss = (string) ($row['issued_at'] ?? '');
                $vu = (string) ($row['valid_until'] ?? '');
                $tot = (float) ($row['total_amount'] ?? 0);
                $st = (string) ($row['status'] ?? '');
                ?>
                <tr>
                    <td class="text-white font-monospace"><?= htmlspecialchars($qnum, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= $iss !== '' ? htmlspecialchars($iss, ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td class="text-secondary small"><?= $vu !== '' ? htmlspecialchars($vu, ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td class="text-white"><?= htmlspecialchars(lumis_money_br($tot), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge badge-lumis badge-lumis-secondary"><?= htmlspecialchars($stLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-end">
                        <?php if (can($permView)): ?>
                            <div class="dropdown d-inline">
                                <button class="btn btn-sm btn-lumis-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Ações</button>
                                <ul class="dropdown-menu dropdown-menu-end app-dropdown border-0">
                                    <li><a class="dropdown-item" href="<?= htmlspecialchars($basePath . '/' . $rid, ENT_QUOTES, 'UTF-8') ?>">Ver</a></li>
                                    <li><a class="dropdown-item" href="<?= htmlspecialchars($basePath . '/' . $rid . '/pdf', ENT_QUOTES, 'UTF-8') ?>" target="_blank">PDF</a></li>
                                    <?php if (can($permEdit) && !in_array($st, ['converted', 'cancelled'], true)): ?>
                                        <li><a class="dropdown-item" href="<?= htmlspecialchars($basePath . '/' . $rid . '/editar', ENT_QUOTES, 'UTF-8') ?>">Editar</a></li>
                                    <?php endif; ?>
                                    <?php if ($quoteKind === 'product' && can($permEdit) && !in_array($st, ['converted', 'cancelled'], true)): ?>
                                        <li>
                                            <form method="post" action="<?= htmlspecialchars($basePath . '/' . $rid . '/converter-venda', ENT_QUOTES, 'UTF-8') ?>" class="m-0" onsubmit="return confirm('Converter em venda e baixar estoque?');">
                                                <?= \App\Helpers\Csrf::field() ?>
                                                <button type="submit" class="dropdown-item">Converter em venda</button>
                                            </form>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($quoteKind === 'service' && can($permEdit) && !in_array($st, ['converted', 'cancelled'], true)): ?>
                                        <li>
                                            <form method="post" action="<?= htmlspecialchars($basePath . '/' . $rid . '/converter-os', ENT_QUOTES, 'UTF-8') ?>" class="m-0" onsubmit="return confirm('Criar ordem de serviço a partir deste orçamento?');">
                                                <?= \App\Helpers\Csrf::field() ?>
                                                <button type="submit" class="dropdown-item">Converter em O.S.</button>
                                            </form>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (can($permCreate) && !in_array($st, ['cancelled'], true)): ?>
                                        <li>
                                            <form method="post" action="<?= htmlspecialchars($basePath . '/' . $rid . '/duplicar', ENT_QUOTES, 'UTF-8') ?>" class="m-0">
                                                <?= \App\Helpers\Csrf::field() ?>
                                                <button type="submit" class="dropdown-item">Duplicar</button>
                                            </form>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (can($permDelete) && !in_array($st, ['cancelled'], true)): ?>
                                        <li>
                                            <form method="post" action="<?= htmlspecialchars($basePath . '/' . $rid . '/excluir', ENT_QUOTES, 'UTF-8') ?>" class="m-0" onsubmit="return confirm('Cancelar este orçamento?');">
                                                <?= \App\Helpers\Csrf::field() ?>
                                                <button type="submit" class="dropdown-item text-danger">Cancelar</button>
                                            </form>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

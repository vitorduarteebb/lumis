<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var list<array<string, mixed>> $suppliers */
/** @var array<string, mixed>|null $editRow */
/** @var int $total */
/** @var int $page */
/** @var int $totalPages */
/** @var string $statusFilter */
/** @var string $basePath */
/** @var array<string, string|int|float|bool|null> $queryParams */

$rows = $rows ?? [];
$suppliers = $suppliers ?? [];
$editRow = isset($editRow) && is_array($editRow) ? $editRow : null;
$total = (int) ($total ?? 0);
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$statusFilter = (string) ($statusFilter ?? '');
$basePath = (string) ($basePath ?? '/financeiro/contas-pagar');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];

$stLabel = static function (string $s): string {
    return match ($s) {
        'open' => 'Em aberto',
        'paid' => 'Pago',
        'cancelled' => 'Cancelado',
        default => $s,
    };
};
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Financeiro</div>
        <h2 class="h4 mb-1 text-white">Contas a pagar</h2>
        <div class="text-secondary small">Títulos a fornecedores e despesas com baixa parcial.</div>
    </div>
</div>

<?php if ($editRow !== null): ?>
    <?php
    $eid = (int) ($editRow['id'] ?? 0);
    $esid = $editRow['supplier_id'] !== null ? (int) $editRow['supplier_id'] : 0;
    ?>
    <div class="card app-card border-secondary-subtle mb-4">
        <div class="card-body">
            <h3 class="h6 text-white mb-3">Editar título #<?= $eid ?></h3>
            <form method="post" action="/financeiro/contas-pagar" class="row g-3 align-items-end">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="_action" value="update">
                <input type="hidden" name="id" value="<?= $eid ?>">
                <div class="col-md-4">
                    <label class="form-label lumis-label" for="ed_desc">Descrição</label>
                    <input type="text" class="form-control app-input" id="ed_desc" name="description" required
                           value="<?= htmlspecialchars((string) ($editRow['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label lumis-label" for="ed_sup">Fornecedor</label>
                    <select class="form-select app-input" id="ed_sup" name="supplier_id">
                        <option value="">—</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= (int) ($s['id'] ?? 0) ?>" <?= $esid === (int) ($s['id'] ?? 0) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label lumis-label" for="ed_amt">Valor (R$)</label>
                    <input type="text" class="form-control app-input" id="ed_amt" name="amount" required
                           value="<?= htmlspecialchars(number_format((float) ($editRow['amount'] ?? 0), 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label lumis-label" for="ed_due">Vencimento</label>
                    <input type="date" class="form-control app-input" id="ed_due" name="due_date" required
                           value="<?= htmlspecialchars(substr((string) ($editRow['due_date'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label lumis-label" for="ed_st">Status</label>
                    <select class="form-select app-input" id="ed_st" name="status">
                        <?php foreach (['open', 'paid', 'cancelled'] as $sv): ?>
                            <option value="<?= $sv ?>" <?= ((string) ($editRow['status'] ?? '') === $sv) ? 'selected' : '' ?>><?= htmlspecialchars($stLabel($sv), ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-3">Salvar</button>
                    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card app-card border-secondary-subtle mb-4">
        <div class="card-body">
            <h3 class="h6 text-white mb-3">Novo título</h3>
            <form method="post" action="/financeiro/contas-pagar" class="row g-3 align-items-end">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="_action" value="store">
                <div class="col-md-4">
                    <label class="form-label lumis-label" for="nw_desc">Descrição</label>
                    <input type="text" class="form-control app-input" id="nw_desc" name="description" required placeholder="Ex.: NF 1020 — fornecedor X">
                </div>
                <div class="col-md-3">
                    <label class="form-label lumis-label" for="nw_sup">Fornecedor</label>
                    <select class="form-select app-input" id="nw_sup" name="supplier_id">
                        <option value="">—</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= (int) ($s['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label lumis-label" for="nw_amt">Valor (R$)</label>
                    <input type="text" class="form-control app-input" id="nw_amt" name="amount" required placeholder="0,00">
                </div>
                <div class="col-md-2">
                    <label class="form-label lumis-label" for="nw_due">Vencimento</label>
                    <input type="date" class="form-control app-input" id="nw_due" name="due_date" required>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm rounded-3 w-100">Incluir</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <select name="status" class="form-select app-input" style="max-width: 200px;">
        <option value="">Status (todos)</option>
        <option value="open" <?= $statusFilter === 'open' ? 'selected' : '' ?>>Em aberto</option>
        <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : '' ?>>Pago</option>
        <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
    </select>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Fornecedor</th>
                <th class="text-end">Valor</th>
                <th>Vencimento</th>
                <th class="text-end">Pago</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="7" class="text-secondary small py-4">Nenhum título encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $st = (string) ($row['status'] ?? '');
                $sup = (string) ($row['supplier_name'] ?? '');
                ?>
                <tr>
                    <td class="text-white small"><?= htmlspecialchars((string) ($row['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= $sup !== '' ? htmlspecialchars($sup, ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td class="text-end text-white small"><?= htmlspecialchars(lumis_money_br((float) ($row['amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars(substr((string) ($row['due_date'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end text-secondary small"><?= htmlspecialchars(lumis_money_br((float) ($row['paid_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge badge-lumis <?= $st === 'paid' ? 'badge-lumis-success' : ($st === 'cancelled' ? 'text-bg-secondary' : 'badge-lumis-warning') ?>"><?= htmlspecialchars($stLabel($st), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-end">
                        <?php if (can('financeiro.contas_pagar.view')): ?>
                            <a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($basePath . '?edit=' . $rid, ENT_QUOTES, 'UTF-8') ?>">Editar</a>
                            <?php if ($st === 'open'): ?>
                                <form method="post" action="/financeiro/contas-pagar" class="d-inline-flex gap-1 align-items-center">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <input type="hidden" name="_action" value="pay">
                                    <input type="hidden" name="id" value="<?= $rid ?>">
                                    <input type="text" name="payment_amount" class="form-control form-control-sm app-input" style="width: 88px;" placeholder="Valor" title="Valor a pagar">
                                    <button type="submit" class="btn btn-sm btn-primary">Pagar</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="/financeiro/contas-pagar" class="d-inline" onsubmit="return confirm('Excluir este título?');">
                                <?= \App\Helpers\Csrf::field() ?>
                                <input type="hidden" name="_action" value="delete">
                                <input type="hidden" name="id" value="<?= $rid ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

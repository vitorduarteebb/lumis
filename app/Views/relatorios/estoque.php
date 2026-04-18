<?php

declare(strict_types=1);

$view = (string) ($view ?? 'saldos');
$rows = is_array($rows ?? null) ? $rows : [];
$mtLabels = [
    'in' => 'Entrada',
    'out' => 'Saída',
    'sale' => 'Venda',
    'purchase' => 'Compra',
    'adjust' => 'Ajuste',
    'adjustment' => 'Ajuste',
    'transfer_in' => 'Transf. destino',
    'transfer_out' => 'Transf. origem',
    'transfer' => 'Transferência',
    'return_sale' => 'Devol. venda',
    'return_purchase' => 'Devol. compra',
];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Relatórios</div>
        <h2 class="h4 mb-1 text-white">Estoque</h2>
        <div class="text-secondary small">Saldos por loja, movimentações e posição abaixo do mínimo.</div>
    </div>
</div>

<?php if ($view === 'saldos'): ?>
    <div class="row g-2 mb-3" data-report-scope="estoque">
        <div class="col-md-4">
            <div class="rounded-3 border border-secondary-subtle p-3 bg-dark bg-opacity-25">
                <div class="text-secondary small">Produtos abaixo do mínimo (alerta)</div>
                <div class="h5 text-warning mb-0"><?= (int) ($belowCount ?? 0) ?></div>
                <a class="small text-decoration-none" href="<?= htmlspecialchars((
                    '/relatorios/estoque?' . http_build_query(array_filter([
                        'view' => 'abaixo',
                        'store_id' => (int) ($storeId ?? 0) > 0 ? (int) $storeId : null,
                    ]))
                ), ENT_QUOTES, 'UTF-8') ?>">Ver relatório</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<form method="get" action="<?= htmlspecialchars((string) ($basePath ?? '/relatorios/estoque'), ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end" data-export-ready="1">
    <select name="view" class="form-select app-input" style="max-width: 220px;" onchange="this.form.submit()">
        <option value="saldos" <?= $view === 'saldos' ? 'selected' : '' ?>>Saldo por produto/loja</option>
        <option value="movimentos" <?= $view === 'movimentos' ? 'selected' : '' ?>>Movimentações</option>
        <option value="abaixo" <?= $view === 'abaixo' ? 'selected' : '' ?>>Abaixo do mínimo</option>
    </select>
    <select name="store_id" class="form-select app-input" style="max-width: 180px;">
        <option value="0">Loja (todas)</option>
        <?php foreach (is_array($stores ?? null) ? $stores : [] as $st): ?>
            <option value="<?= (int) ($st['id'] ?? 0) ?>" <?= (int) ($storeId ?? 0) === (int) ($st['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($st['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <?php if ($view === 'saldos'): ?>
        <select name="category_id" class="form-select app-input" style="max-width: 200px;">
            <option value="0">Categoria (todas)</option>
            <?php foreach (is_array($categories ?? null) ? $categories : [] as $cat): ?>
                <option value="<?= (int) ($cat['id'] ?? 0) ?>" <?= (int) ($categoryId ?? 0) === (int) ($cat['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($cat['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <div class="input-group lumis-search flex-grow-1" style="max-width: 280px;">
            <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
            <input type="search" name="q" class="form-control app-input" placeholder="Produto, SKU…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
    <?php elseif ($view === 'movimentos'): ?>
        <select name="product_id" class="form-select app-input" style="max-width: 220px;">
            <option value="0">Produto (todos)</option>
            <?php foreach (is_array($products ?? null) ? $products : [] as $p): ?>
                <option value="<?= (int) ($p['id'] ?? 0) ?>" <?= (int) ($productId ?? 0) === (int) ($p['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <select name="movement_type" class="form-select app-input" style="max-width: 200px;">
            <option value="all">Tipo (todos)</option>
            <?php foreach (['sale', 'purchase', 'adjust', 'transfer_in', 'transfer_out', 'return_sale', 'return_purchase', 'in', 'out'] as $mt): ?>
                <option value="<?= htmlspecialchars($mt, ENT_QUOTES, 'UTF-8') ?>" <?= ($movementType ?? '') === $mt ? 'selected' : '' ?>><?= htmlspecialchars($mtLabels[$mt] ?? $mt, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateFrom ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateTo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <div class="input-group lumis-search flex-grow-1" style="max-width: 220px;">
            <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
            <input type="search" name="q" class="form-control app-input" placeholder="Ref., observações…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
    <?php endif; ?>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2" data-table="estoque_<?= htmlspecialchars($view, ENT_QUOTES, 'UTF-8') ?>">
    <table class="table lumis-table mb-0">
        <thead>
            <?php if ($view === 'saldos'): ?>
                <tr>
                    <th>Produto</th>
                    <th>SKU</th>
                    <th>Categoria</th>
                    <th>Loja</th>
                    <th class="text-end">Quantidade</th>
                    <th class="text-end">Mínimo</th>
                </tr>
            <?php elseif ($view === 'abaixo'): ?>
                <tr>
                    <th>Produto</th>
                    <th>SKU</th>
                    <th>Loja</th>
                    <th class="text-end">Qtd</th>
                    <th class="text-end">Mínimo</th>
                </tr>
            <?php else: ?>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Produto</th>
                    <th>Loja</th>
                    <th class="text-end">Qtd</th>
                    <th>Ref.</th>
                    <th class="text-end">Ações</th>
                </tr>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="<?= $view === 'saldos' ? '6' : ($view === 'abaixo' ? '5' : '7') ?>" class="text-secondary small py-4">Nenhum registro.</td></tr>
            <?php endif; ?>
            <?php if ($view === 'saldos'): ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="text-white small"><?= htmlspecialchars((string) ($row['product_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary font-monospace small"><?= htmlspecialchars((string) ($row['sku'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars((string) ($row['category_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars((string) ($row['store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end text-white small"><?= htmlspecialchars((string) ($row['qty'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end text-secondary small"><?= htmlspecialchars((string) ($row['stock_min'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php elseif ($view === 'abaixo'): ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="text-white small"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary font-monospace small"><?= htmlspecialchars((string) ($row['sku'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars((string) ($row['store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end text-warning small"><?= htmlspecialchars((string) ($row['qty_store'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end text-secondary small"><?= htmlspecialchars((string) ($row['stock_min'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $mid = (int) ($row['id'] ?? 0);
                $mt = (string) ($row['movement_type'] ?? '');
                $qty = (float) ($row['qty'] ?? 0);
                    ?>
                    <tr>
                        <td class="text-secondary small"><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge text-bg-dark border border-secondary-subtle"><?= htmlspecialchars($mtLabels[$mt] ?? $mt, ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td class="text-white small"><?= htmlspecialchars((string) ($row['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars((string) ($row['store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end font-monospace small <?= $qty < 0 ? 'text-warning' : 'text-success' ?>"><?= htmlspecialchars(number_format($qty, 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary font-monospace small text-truncate" style="max-width:140px;"><?= htmlspecialchars((string) ($row['reference'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end"><a class="btn btn-sm btn-lumis-secondary" href="/estoque/movimentacoes/<?= $mid ?>">Ver</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>

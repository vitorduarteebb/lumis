<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var int $total */
/** @var int $page */
/** @var int $totalPages */
/** @var string $search */
/** @var string|null $statusFilter */
/** @var int|null $categoryFilter */
/** @var list<array<string, mixed>> $categories */
/** @var string $basePath */
/** @var array<string, string|int|float|bool|null> $queryParams */

$rows = $rows ?? [];
$categories = is_array($categories ?? null) ? $categories : [];
$categoryFilter = isset($categoryFilter) ? (int) $categoryFilter : null;
if ($categoryFilter !== null && $categoryFilter < 1) {
    $categoryFilter = null;
}
$total = (int) ($total ?? 0);
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$search = (string) ($search ?? '');
$statusFilter = $statusFilter ?? null;
$basePath = (string) ($basePath ?? '/produtos');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];

$fmt = static fn ($v): string => number_format((float) $v, 2, ',', '.');
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Produtos</div>
        <h2 class="h4 mb-1 text-white">Gerenciar produtos</h2>
        <div class="text-secondary small">Catálogo com SKU, preços e estoque.</div>
    </div>
    <?php if (can('produtos.gerenciar.create') || can('products.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="/produtos/novo">Novo produto</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 360px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Nome, SKU, código, código de barras…" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="category_id" class="form-select app-input" style="max-width: 200px;">
        <option value="">Categoria (todas)</option>
        <?php foreach ($categories as $c): ?>
            <?php $cid = (int) ($c['id'] ?? 0); ?>
            <option value="<?= $cid ?>" <?= $categoryFilter === $cid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" class="form-select app-input" style="max-width: 160px;">
        <option value="">Status (todos)</option>
        <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Ativo</option>
        <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Inativo</option>
    </select>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>SKU</th>
                <th>Categoria</th>
                <th class="text-end">Venda</th>
                <th class="text-end">Estoque</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="7" class="text-secondary small py-4">Nenhum produto encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $st = (int) ($row['status'] ?? 0);
                ?>
                <tr>
                    <td class="text-white"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['category_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small text-end">R$ <?= htmlspecialchars($fmt($row['sale_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small text-end"><?= htmlspecialchars($fmt($row['stock_qty'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $st === 1 ? '<span class="badge badge-lumis badge-lumis-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-lumis-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Ações</button>
                            <ul class="dropdown-menu dropdown-menu-end app-dropdown border-0">
                                <li><a class="dropdown-item" href="/produtos/<?= $rid ?>">Ver</a></li>
                                <?php if (can('produtos.gerenciar.edit') || can('products.edit')): ?>
                                    <li><a class="dropdown-item" href="/produtos/<?= $rid ?>/editar">Editar</a></li>
                                <?php endif; ?>
                                <?php if (can('produtos.gerenciar.delete') || can('products.delete')): ?>
                                    <li>
                                        <form method="post" action="/produtos/<?= $rid ?>/excluir" class="m-0" onsubmit="return confirm('Excluir este produto?');">
                                            <?= \App\Helpers\Csrf::field() ?>
                                            <button type="submit" class="dropdown-item text-danger">Excluir</button>
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

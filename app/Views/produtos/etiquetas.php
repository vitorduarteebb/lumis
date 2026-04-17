<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
$rows = is_array($rows ?? null) ? $rows : [];
$basePath = (string) ($basePath ?? '/produtos/etiquetas');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Produtos</div>
        <h2 class="h4 mb-1 text-white">Etiquetas</h2>
        <div class="text-secondary small">Informe a quantidade de etiquetas por produto e gere uma página para impressão.</div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/produtos">Produtos</a>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 360px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Nome, SKU, código…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<form method="post" action="/produtos/etiquetas/imprimir" target="_blank">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="text-secondary small">Somente produtos ativos. Quantidade 0 ignora o item.</div>
        <?php if (can('produtos.etiquetas.print')): ?>
            <button type="submit" class="btn btn-primary btn-sm rounded-3"><i class="bi bi-printer me-1" aria-hidden="true"></i> Gerar página de etiquetas</button>
        <?php endif; ?>
    </div>

    <div class="lumis-table-wrap mb-2">
        <table class="table lumis-table mb-0">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>SKU</th>
                    <th>Preço</th>
                    <th style="width: 140px;">Qtd. etiquetas</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="4" class="text-secondary small py-4">Nenhum produto encontrado.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <?php $rid = (int) ($row['id'] ?? 0); ?>
                    <tr>
                        <td class="text-white"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars((string) ($row['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars(lumis_money_br((float) ($row['sale_price'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <input type="number" min="0" max="999" class="form-control form-control-sm app-input" name="qty[<?= $rid ?>]" value="0">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include base_path('app/Views/partials/pagination.php'); ?>
</form>

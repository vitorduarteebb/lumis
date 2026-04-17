<?php

declare(strict_types=1);

/** @var array<string, mixed> $product */

$p = is_array($product ?? null) ? $product : [];
$id = (int) ($p['id'] ?? 0);
$fmt = static fn ($v): string => number_format((float) $v, 2, ',', '.');
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Produtos</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">SKU: <?= htmlspecialchars((string) ($p['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/produtos">Voltar</a>
        <?php if (can('produtos.gerenciar.edit') || can('products.edit')): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="/produtos/<?= $id ?>/editar">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm module-shell__panel">
    <div class="card-body p-4">
        <div class="row g-3 small">
            <div class="col-md-4">
                <div class="text-secondary">Categoria</div>
                <div class="text-white"><?= htmlspecialchars((string) ($p['category_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-secondary">Marca</div>
                <div class="text-white"><?= htmlspecialchars((string) ($p['brand_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-secondary">Unidade</div>
                <div class="text-white">
                    <?php
                    $un = (string) ($p['unit_name'] ?? '');
                    $ua = (string) ($p['unit_abbr'] ?? '');
                    $uLabel = $un !== '' ? $un . ($ua !== '' ? ' (' . $ua . ')' : '') : '—';
                    echo htmlspecialchars($uLabel, ENT_QUOTES, 'UTF-8');
                    ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary">Custo</div>
                <div class="text-white">R$ <?= htmlspecialchars($fmt($p['cost'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary">Preço de venda</div>
                <div class="text-white">R$ <?= htmlspecialchars($fmt($p['sale_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary">Estoque</div>
                <div class="text-white"><?= htmlspecialchars($fmt($p['stock_qty'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary">Estoque mínimo</div>
                <div class="text-white"><?= htmlspecialchars($fmt($p['stock_min'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-secondary">Código interno</div>
                <div class="text-white"><?= htmlspecialchars((string) ($p['internal_code'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-secondary">Código de barras</div>
                <div class="text-white"><?= htmlspecialchars((string) ($p['barcode'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary">Status</div>
                <div class="text-white"><?= (int) ($p['status'] ?? 0) === 1 ? 'Ativo' : 'Inativo' ?></div>
            </div>
            <?php if (!empty($p['description'])): ?>
                <div class="col-12">
                    <div class="text-secondary">Descrição</div>
                    <div class="text-white"><?= nl2br(htmlspecialchars((string) $p['description'], ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

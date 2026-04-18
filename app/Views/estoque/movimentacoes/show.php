<?php

declare(strict_types=1);

$row = is_array($row ?? null) ? $row : [];
$id = (int) ($row['id'] ?? 0);
$mtLabels = [
    'sale' => 'Venda',
    'purchase' => 'Compra',
    'adjust' => 'Ajuste',
    'transfer_in' => 'Transf. destino',
    'transfer_out' => 'Transf. origem',
    'return_sale' => 'Devol. venda',
    'return_purchase' => 'Devol. compra',
];
$mt = (string) ($row['movement_type'] ?? '');
$qty = (float) ($row['qty'] ?? 0);
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Movimentação #<?= $id ?></h2>
        <div class="text-secondary small">
            <span class="badge text-bg-dark border border-secondary-subtle"><?= htmlspecialchars($mtLabels[$mt] ?? $mt, ENT_QUOTES, 'UTF-8') ?></span>
            · <?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/estoque/movimentacoes">Voltar</a>
</div>

<div class="lumis-form-section mb-3">
    <div class="row g-3 text-secondary small">
        <div class="col-md-6">
            <div><span class="text-white">Produto:</span> <?= htmlspecialchars((string) ($row['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($row['sku'])): ?> <span class="font-monospace">(<?= htmlspecialchars((string) $row['sku'], ENT_QUOTES, 'UTF-8') ?>)</span><?php endif; ?>
            </div>
            <div><span class="text-white">Loja:</span> <?= htmlspecialchars((string) ($row['store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            <div><span class="text-white">Usuário:</span> <?= htmlspecialchars((string) ($row['user_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="col-md-6">
            <div><span class="text-white">Quantidade:</span> <span class="<?= $qty < 0 ? 'text-warning' : 'text-success' ?>"><?= htmlspecialchars(number_format($qty, 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></span></div>
            <div><span class="text-white">Saldo anterior:</span> <?= htmlspecialchars(number_format((float) ($row['balance_before'] ?? 0), 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
            <div><span class="text-white">Saldo posterior:</span> <?= htmlspecialchars(number_format((float) ($row['balance_after'] ?? 0), 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="col-12">
            <div><span class="text-white">Referência:</span> <?= htmlspecialchars((string) ($row['reference'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($row['ref_table'])): ?>
                <div><span class="text-white">Origem:</span> <?= htmlspecialchars((string) $row['ref_table'], ENT_QUOTES, 'UTF-8') ?> #<?= htmlspecialchars((string) ($row['ref_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if (!empty($row['notes'])): ?>
                <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars((string) $row['notes'], ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

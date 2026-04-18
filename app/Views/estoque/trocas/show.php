<?php

declare(strict_types=1);

$row = is_array($row ?? null) ? $row : [];
$id = (int) ($row['id'] ?? 0);
$rk = (string) ($row['return_kind'] ?? '');
$kindLab = [
    'sale_return' => 'Devolução de venda',
    'purchase_return' => 'Devolução de compra',
    'exchange' => 'Troca',
];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Registro #<?= $id ?></h2>
        <div class="text-secondary small"><?= htmlspecialchars($kindLab[$rk] ?? $rk, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/estoque/trocas-devolucoes">Voltar</a>
</div>

<div class="lumis-form-section mb-3">
    <div class="row g-3 text-secondary small">
        <div class="col-md-6">
            <div><span class="text-white">Produto:</span> <?= htmlspecialchars((string) ($row['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            <div><span class="text-white">Quantidade:</span> <?= htmlspecialchars(number_format((float) ($row['qty'] ?? 0), 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
            <div><span class="text-white">Data:</span> <?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="col-md-6">
            <?php if (!empty($row['sales_document_id'])): ?>
                <div><span class="text-white">Venda:</span> #<?= (int) $row['sales_document_id'] ?></div>
            <?php endif; ?>
            <?php if (!empty($row['purchase_order_id'])): ?>
                <div><span class="text-white">Compra:</span> #<?= (int) $row['purchase_order_id'] ?></div>
            <?php endif; ?>
            <?php if (!empty($row['client_id'])): ?>
                <div><span class="text-white">Cliente ID:</span> <?= (int) $row['client_id'] ?></div>
            <?php endif; ?>
            <?php if (!empty($row['supplier_id'])): ?>
                <div><span class="text-white">Fornecedor ID:</span> <?= (int) $row['supplier_id'] ?></div>
            <?php endif; ?>
        </div>
        <div class="col-12">
            <div><span class="text-white">Motivo:</span> <?= htmlspecialchars((string) ($row['reason'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($row['notes'])): ?>
                <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars((string) $row['notes'], ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

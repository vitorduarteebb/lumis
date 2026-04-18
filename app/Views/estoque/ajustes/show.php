<?php

declare(strict_types=1);

$row = is_array($row ?? null) ? $row : [];
$id = (int) ($row['id'] ?? 0);
$dir = (string) ($row['direction'] ?? '');
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Ajuste #<?= $id ?></h2>
        <div class="text-secondary small">
            <?= $dir === 'out' ? '<span class="badge text-bg-warning">Saída</span>' : '<span class="badge badge-lumis-success">Entrada</span>' ?>
            · <?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/estoque/ajustes">Voltar</a>
</div>

<div class="lumis-form-section mb-3">
    <div class="row g-3 text-secondary small">
        <div class="col-md-6">
            <div><span class="text-white">Produto:</span> <?= htmlspecialchars((string) ($row['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            <div><span class="text-white">Loja:</span> <?= htmlspecialchars((string) ($row['store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            <div><span class="text-white">Responsável:</span> <?= htmlspecialchars((string) ($row['user_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="col-md-6">
            <div><span class="text-white">Quantidade:</span> <?= htmlspecialchars(number_format((float) ($row['qty'] ?? 0), 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
            <div><span class="text-white">Motivo:</span> <?= htmlspecialchars((string) ($row['reason_text'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php if (!empty($row['notes'])): ?>
            <div class="col-12"><span class="text-white">Observações:</span><br><?= nl2br(htmlspecialchars((string) $row['notes'], ENT_QUOTES, 'UTF-8')) ?></div>
        <?php endif; ?>
    </div>
</div>

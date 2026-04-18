<?php

declare(strict_types=1);

$bundle = isset($bundle) && is_array($bundle) ? $bundle : ['doc' => [], 'lines' => []];
$doc = $bundle['doc'];
$lines = $bundle['lines'];
$basePath = (string) ($basePath ?? '/vendas/produtos');
$kind = (string) ($kind ?? 'product');
$statusLabels = is_array($statusLabels ?? null) ? $statusLabels : [];
$id = (int) ($doc['id'] ?? 0);
$st = (string) ($doc['status'] ?? '');
$perm = $kind === 'service' ? 'vendas.servicos' : 'vendas.produtos';
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Vendas</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($doc['document_number'] ?? 'Venda'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">
            <?php if (($doc['document_kind'] ?? '') === 'balcao'): ?>
                <span class="badge text-bg-info me-1">Balcão</span>
            <?php endif; ?>
            <?= htmlspecialchars($statusLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?>
            <?php if (!empty($doc['client_name'])): ?> · <?= htmlspecialchars((string) $doc['client_name'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Voltar</a>
        <?php if (can($perm . '.view')): ?>
            <a class="btn btn-outline-light btn-sm rounded-3" href="<?= htmlspecialchars($basePath . '/' . $id . '/pdf', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">PDF</a>
        <?php endif; ?>
        <?php if ($st === 'open' && can($perm . '.edit')): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath . '/' . $id . '/editar', ENT_QUOTES, 'UTF-8') ?>">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="lumis-form-section mb-3">
    <div class="row g-2 text-secondary small">
        <div class="col-md-4"><span class="text-white">Subtotal:</span> <?= htmlspecialchars(lumis_money_br((float) ($doc['subtotal_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="col-md-4"><span class="text-white">Desconto:</span> <?= htmlspecialchars(lumis_money_br((float) ($doc['discount_total'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="col-md-4"><span class="text-white">Total:</span> <?= htmlspecialchars(lumis_money_br((float) ($doc['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php if (!empty($doc['notes'])): ?>
        <p class="mt-2 mb-0 small text-secondary"><?= nl2br(htmlspecialchars((string) $doc['notes'], ENT_QUOTES, 'UTF-8')) ?></p>
    <?php endif; ?>
</div>

<div class="lumis-table-wrap mb-3">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-end">Qtd</th>
                <th class="text-end">V. unit.</th>
                <th class="text-end">Desc.</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lines as $ln): ?>
                <?php
                $nm = $kind === 'service'
                    ? ($ln['service_name'] ?? '')
                    : ($ln['product_name'] ?? '');
                ?>
                <tr>
                    <td><?= htmlspecialchars((string) $nm, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars((string) ($ln['qty'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['unit_price'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['line_discount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['line_total'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($st === 'open' && can($perm . '.edit')): ?>
    <div class="card border-secondary-subtle bg-transparent rounded-3 p-3 mb-3">
        <h3 class="h6 text-white">Finalizar venda</h3>
        <form method="post" action="<?= htmlspecialchars($basePath . '/' . $id . '/finalizar', ENT_QUOTES, 'UTF-8') ?>" class="row g-2 align-items-end">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="create_receivable" value="1" id="cr">
                    <label class="form-check-label text-secondary small" for="cr">Gerar conta a receber (com cliente)</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label small">Vencimento CR</label>
                <input type="date" name="receivable_due_date" class="form-control form-control-sm app-input">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-success btn-sm">Finalizar</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php if (($st === 'open' || $st === 'finalized') && can($perm . '.edit')): ?>
    <form method="post" action="<?= htmlspecialchars($basePath . '/' . $id . '/cancelar', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Cancelar esta venda?');" class="d-inline">
        <?= \App\Helpers\Csrf::field() ?>
        <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar venda</button>
    </form>
<?php endif; ?>

<?php

declare(strict_types=1);

$bundle = is_array($bundle ?? null) ? $bundle : ['order' => [], 'lines' => []];
$o = $bundle['order'];
$lines = is_array($bundle['lines'] ?? null) ? $bundle['lines'] : [];
$id = (int) ($o['id'] ?? 0);
$st = (string) ($o['status'] ?? '');
$stLab = ['open' => 'Aberta', 'finalized' => 'Finalizada', 'cancelled' => 'Cancelada'];
$supplierQuote = is_array($supplierQuote ?? null) ? $supplierQuote : null;
$sq = $supplierQuote['quote'] ?? null;
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($o['document_number'] ?? 'Compra'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">
            <?= htmlspecialchars((string) ($o['supplier_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            · <?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/estoque/compras">Voltar</a>
        <?php if ($st === 'open' && can('estoque.compras.edit')): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="/estoque/compras/<?= $id ?>/editar">Editar</a>
        <?php endif; ?>
    </div>
</div>

<?php if (is_array($sq)): ?>
    <div class="alert alert-secondary border-secondary-subtle small mb-3">
        Origem cotação: <strong><?= htmlspecialchars((string) ($sq['quote_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
<?php endif; ?>

<div class="lumis-form-section mb-3">
    <div class="row g-2 text-secondary small">
        <div class="col-md-4"><span class="text-white">Total:</span> <?= htmlspecialchars(lumis_money_br((float) ($o['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="col-md-4"><span class="text-white">Emissão:</span> <?= htmlspecialchars(substr((string) ($o['issued_at'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="col-md-4"><span class="text-white">Previsto:</span> <?= htmlspecialchars(substr((string) ($o['expected_at'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php if (!empty($o['notes'])): ?>
        <p class="mt-2 mb-0 small"><?= nl2br(htmlspecialchars((string) $o['notes'], ENT_QUOTES, 'UTF-8')) ?></p>
    <?php endif; ?>
</div>

<div class="lumis-table-wrap mb-3">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Produto</th>
                <th class="text-end">Qtd</th>
                <th class="text-end">Custo unit.</th>
                <th class="text-end">Desc.</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lines as $ln): ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($ln['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars((string) ($ln['qty'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['unit_price'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['line_discount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['line_total'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($st === 'open' && can('estoque.compras.edit')): ?>
    <div class="card border-secondary-subtle bg-transparent rounded-3 p-3 mb-3">
        <h3 class="h6 text-white">Finalizar compra</h3>
        <form method="post" action="/estoque/compras/<?= $id ?>/finalizar" class="row g-2 align-items-end">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="create_payable" value="1" id="cp">
                    <label class="form-check-label text-secondary small" for="cp">Gerar conta a pagar</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label small">Vencimento CP</label>
                <input type="date" name="payable_due_date" class="form-control form-control-sm app-input">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-success btn-sm">Finalizar</button>
            </div>
        </form>
    </div>
<?php endif; ?>

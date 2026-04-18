<?php

declare(strict_types=1);

$mode = (string) ($mode ?? 'create');
$suppliers = is_array($suppliers ?? null) ? $suppliers : [];
$products = is_array($products ?? null) ? $products : [];
$stores = is_array($stores ?? null) ? $stores : [];
$quotes = is_array($quotes ?? null) ? $quotes : [];
$bundle = is_array($bundle ?? null) ? $bundle : null;
$o = isset($bundle['order']) && is_array($bundle['order']) ? $bundle['order'] : null;
$lines = is_array($bundle['lines'] ?? null) ? $bundle['lines'] : [];
$isEdit = $mode === 'edit' && $o !== null;
$id = $isEdit ? (int) ($o['id'] ?? 0) : 0;
$od = is_array($o) ? $o : [];

if ($lines === [] && !$isEdit) {
    $lines = [['product_id' => '', 'qty' => 1, 'unit_price' => 0, 'line_discount' => 0]];
}
$action = $isEdit ? '/estoque/compras/' . $id : '/estoque/compras';
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar compra' : 'Nova compra' ?></h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/estoque/compras">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label lumis-label">Fornecedor</label>
                <select name="supplier_id" class="form-select app-input" required>
                    <option value="">—</option>
                    <?php foreach ($suppliers as $s): ?>
                        <?php $sid = (int) ($s['id'] ?? 0); ?>
                        <option value="<?= $sid ?>" <?= (string) ($od['supplier_id'] ?? '') === (string) $sid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['trade_name'] ?? $s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Número</label>
                <input type="text" name="document_number" class="form-control app-input" value="<?= htmlspecialchars((string) ($od['document_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Loja destino</label>
                <select name="store_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($stores as $s): ?>
                        <?php $sid = (int) ($s['id'] ?? 0); ?>
                        <option value="<?= $sid ?>" <?= (string) ($od['store_id'] ?? '') === (string) $sid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label">Cotação aprovada (opcional)</label>
                <select name="supplier_quote_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($quotes as $qrow): ?>
                        <?php $qid = (int) ($qrow['id'] ?? 0); ?>
                        <option value="<?= $qid ?>" <?= (string) ($od['supplier_quote_id'] ?? '') === (string) $qid ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($qrow['quote_number'] ?? '#' . $qid), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Emissão</label>
                <input type="date" name="issued_at" class="form-control app-input" value="<?= htmlspecialchars(substr((string) ($od['issued_at'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Previsto</label>
                <input type="date" name="expected_at" class="form-control app-input" value="<?= htmlspecialchars(substr((string) ($od['expected_at'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label">Observações</label>
                <textarea name="notes" class="form-control app-input" rows="2"><?= htmlspecialchars((string) ($od['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </div>
    <div class="lumis-form-section mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 text-white mb-0">Itens</h3>
            <button type="button" class="btn btn-sm btn-outline-light" id="po-add">+ Linha</button>
        </div>
        <div class="table-responsive">
            <table class="table lumis-table mb-0" id="po-lines">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th style="width:90px;">Qtd</th>
                        <th style="width:120px;">Custo unit.</th>
                        <th style="width:100px;">Desc.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $idx => $ln): ?>
                        <tr>
                            <td>
                                <select name="items[<?= $idx ?>][product_id]" class="form-select form-select-sm app-input" required>
                                    <option value="">—</option>
                                    <?php foreach ($products as $p): ?>
                                        <?php $pid = (int) ($p['id'] ?? 0); ?>
                                        <option value="<?= $pid ?>" <?= (int) ($ln['product_id'] ?? 0) === $pid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="items[<?= $idx ?>][qty]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['qty'] ?? 1), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="text" name="items[<?= $idx ?>][unit_price]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['unit_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="text" name="items[<?= $idx ?>][line_discount]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['line_discount'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3 d-flex gap-2 justify-content-end">
        <a class="btn btn-lumis-secondary" href="/estoque/compras">Cancelar</a>
        <?php if ($isEdit ? can('estoque.compras.edit') : can('estoque.compras.create')): ?>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar' : 'Registrar compra' ?></button>
        <?php endif; ?>
    </div>
</form>

<template id="tpl-po-line">
    <tr>
        <td>
            <select name="items[__I__][product_id]" class="form-select form-select-sm app-input" required>
                <option value="">—</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int) ($p['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="text" name="items[__I__][qty]" class="form-control form-control-sm app-input" value="1"></td>
        <td><input type="text" name="items[__I__][unit_price]" class="form-control form-control-sm app-input" value="0"></td>
        <td><input type="text" name="items[__I__][line_discount]" class="form-control form-control-sm app-input" value="0"></td>
    </tr>
</template>
<script>
(function(){
  const b=document.getElementById('po-add'),tb=document.querySelector('#po-lines tbody');
  if(!b||!tb)return;let i=tb.querySelectorAll('tr').length;
  b.addEventListener('click',function(){const t=document.getElementById('tpl-po-line');if(!t)return;tb.insertAdjacentHTML('beforeend',t.innerHTML.replace(/__I__/g,String(i++)));});
})();
</script>

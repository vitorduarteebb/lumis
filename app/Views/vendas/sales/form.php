<?php

declare(strict_types=1);

$kind = (string) ($kind ?? 'product');
$basePath = (string) ($basePath ?? '/vendas/produtos');
$mode = (string) ($mode ?? 'create');
$bundle = isset($bundle) && is_array($bundle) ? $bundle : null;
$doc = $bundle['doc'] ?? null;
$lineRows = $bundle['lines'] ?? [];
$clients = is_array($clients ?? null) ? $clients : [];
$stores = is_array($stores ?? null) ? $stores : [];
$users = is_array($users ?? null) ? $users : [];
$products = is_array($products ?? null) ? $products : [];
$services = is_array($services ?? null) ? $services : [];
$isEdit = $mode === 'edit' && is_array($doc);
$id = $isEdit ? (int) ($doc['id'] ?? 0) : 0;

if ($lineRows === [] && !$isEdit) {
    $lineRows = [['qty' => 1, 'unit_price' => 0, 'line_discount' => 0, 'product_id' => '', 'service_id' => '', 'description' => '']];
}
$action = $isEdit ? $basePath . '/' . $id : $basePath;
$perm = $kind === 'service' ? 'vendas.servicos' : 'vendas.produtos';
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Vendas</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar venda' : 'Nova venda' ?></h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label lumis-label">Cliente</label>
                <select name="client_id" class="form-select app-input">
                    <option value="">— Opcional —</option>
                    <?php foreach ($clients as $c): ?>
                        <?php $cid = (int) ($c['id'] ?? 0); ?>
                        <option value="<?= $cid ?>" <?= (string) ($doc['client_id'] ?? '') === (string) $cid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Loja / depósito</label>
                <select name="store_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($stores as $s): ?>
                        <?php $sid = (int) ($s['id'] ?? 0); ?>
                        <option value="<?= $sid ?>" <?= (string) ($doc['store_id'] ?? '') === (string) $sid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Vendedor</label>
                <select name="seller_user_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($users as $u): ?>
                        <?php $uid = (int) ($u['id'] ?? 0); ?>
                        <option value="<?= $uid ?>" <?= (string) ($doc['seller_user_id'] ?? '') === (string) $uid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($u['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Desconto global (R$)</label>
                <input type="text" name="discount_total" class="form-control app-input" value="<?= htmlspecialchars((string) ($doc['discount_total'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label">Observações</label>
                <textarea name="notes" class="form-control app-input" rows="2"><?= htmlspecialchars((string) ($doc['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </div>
    <div class="lumis-form-section mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 text-white mb-0">Itens</h3>
            <button type="button" class="btn btn-sm btn-outline-light" id="lumis-add-sale-line">+ Linha</button>
        </div>
        <div class="table-responsive">
            <table class="table lumis-table mb-0" id="sale-lines">
                <thead>
                    <tr>
                        <?php if ($kind === 'product'): ?><th>Produto</th><?php else: ?><th>Serviço</th><?php endif; ?>
                        <th style="width:90px;">Qtd</th>
                        <th style="width:120px;">V. unit.</th>
                        <th style="width:120px;">Desc. linha</th>
                        <th>Obs.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineRows as $idx => $ln): ?>
                        <tr class="lumis-sale-line">
                            <?php if ($kind === 'product'): ?>
                                <td>
                                    <select name="items[<?= $idx ?>][product_id]" class="form-select form-select-sm app-input" required>
                                        <option value="">—</option>
                                        <?php foreach ($products as $p): ?>
                                            <?php $pid = (int) ($p['id'] ?? 0); ?>
                                            <option value="<?= $pid ?>" <?= (int) ($ln['product_id'] ?? 0) === $pid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            <?php else: ?>
                                <td>
                                    <select name="items[<?= $idx ?>][service_id]" class="form-select form-select-sm app-input" required>
                                        <option value="">—</option>
                                        <?php foreach ($services as $s): ?>
                                            <?php $sid = (int) ($s['id'] ?? 0); ?>
                                            <option value="<?= $sid ?>" <?= (int) ($ln['service_id'] ?? 0) === $sid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            <?php endif; ?>
                            <td><input type="text" name="items[<?= $idx ?>][qty]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['qty'] ?? 1), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="text" name="items[<?= $idx ?>][unit_price]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['unit_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="text" name="items[<?= $idx ?>][line_discount]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['line_discount'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="text" name="items[<?= $idx ?>][description]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3 d-flex gap-2 justify-content-end">
        <a class="btn btn-lumis-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
        <?php if ($isEdit ? can($perm . '.edit') : can($perm . '.create')): ?>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar' : 'Criar venda' ?></button>
        <?php endif; ?>
    </div>
</form>

<template id="tpl-sale-line-product">
    <tr class="lumis-sale-line">
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
        <td><input type="text" name="items[__I__][description]" class="form-control form-control-sm app-input" value=""></td>
    </tr>
</template>
<template id="tpl-sale-line-service">
    <tr class="lumis-sale-line">
        <td>
            <select name="items[__I__][service_id]" class="form-select form-select-sm app-input" required>
                <option value="">—</option>
                <?php foreach ($services as $s): ?>
                    <option value="<?= (int) ($s['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="text" name="items[__I__][qty]" class="form-control form-control-sm app-input" value="1"></td>
        <td><input type="text" name="items[__I__][unit_price]" class="form-control form-control-sm app-input" value="0"></td>
        <td><input type="text" name="items[__I__][line_discount]" class="form-control form-control-sm app-input" value="0"></td>
        <td><input type="text" name="items[__I__][description]" class="form-control form-control-sm app-input" value=""></td>
    </tr>
</template>
<script>
(function () {
  const btn = document.getElementById('lumis-add-sale-line');
  const tb = document.querySelector('#sale-lines tbody');
  const kind = <?= json_encode($kind, JSON_THROW_ON_ERROR) ?>;
  const tplId = kind === 'product' ? 'tpl-sale-line-product' : 'tpl-sale-line-service';
  if (!btn || !tb) return;
  let idx = tb.querySelectorAll('tr').length;
  btn.addEventListener('click', function () {
    const tpl = document.getElementById(tplId);
    if (!tpl) return;
    const html = tpl.innerHTML.replace(/__I__/g, String(idx++));
    tb.insertAdjacentHTML('beforeend', html);
  });
})();
</script>

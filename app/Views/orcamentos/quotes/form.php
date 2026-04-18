<?php

declare(strict_types=1);

$quoteKind = (string) ($quoteKind ?? 'product');
$mode = (string) ($mode ?? 'create');
$quote = is_array($quote ?? null) ? $quote : null;
$items = is_array($items ?? null) ? $items : [];
$clients = is_array($clients ?? null) ? $clients : [];
$products = is_array($products ?? null) ? $products : [];
$services = is_array($services ?? null) ? $services : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$isEdit = $mode === 'edit';
$basePath = $quoteKind === 'product' ? '/orcamentos/produtos' : '/orcamentos/servicos';

$stLabels = [
    'open' => 'Aberto', 'approved' => 'Aprovado', 'rejected' => 'Recusado', 'cancelled' => 'Cancelado', 'converted' => 'Convertido',
];

$cidVal = $old['client_id'] ?? ($quote['client_id'] ?? '');
$stVal = $old['status'] ?? ($quote['status'] ?? 'open');
$discVal = $old['discount_total'] ?? ($quote['discount_total'] ?? '0');
$vuVal = $old['valid_until'] ?? ($quote['valid_until'] ?? '');
$issVal = $old['issued_at'] ?? ($quote['issued_at'] ?? '');
$notesVal = $old['notes'] ?? ($quote['notes'] ?? '');

$rows = $items;
if ($rows === [] && $mode === 'create') {
    $rows = [['qty' => 1, 'unit_price' => 0, 'line_discount' => 0, 'product_id' => '', 'service_id' => '', 'description' => '']];
}

$action = $isEdit ? $basePath . '/' . (int) ($quote['id'] ?? 0) : $basePath;
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Orçamentos</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar orçamento' : 'Novo orçamento' ?></h2>
        <div class="text-secondary small"><?= $quoteKind === 'product' ? 'Itens de produto' : 'Itens de serviço' ?></div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label lumis-label">Cliente <span class="text-danger">*</span></label>
                <select name="client_id" class="form-select app-input <?= isset($errors['client_id']) ? 'is-invalid' : '' ?>" required>
                    <option value="">Selecione…</option>
                    <?php foreach ($clients as $c): ?>
                        <?php $coid = (int) ($c['id'] ?? 0); ?>
                        <option value="<?= $coid ?>" <?= (string) $cidVal === (string) $coid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['client_id'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['client_id'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Status</label>
                <select name="status" class="form-select app-input">
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $stVal === $st ? 'selected' : '' ?>><?= htmlspecialchars($stLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Desconto global (R$)</label>
                <input type="text" name="discount_total" class="form-control app-input" value="<?= htmlspecialchars((string) $discVal, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Emissão</label>
                <input type="date" name="issued_at" class="form-control app-input" value="<?= htmlspecialchars((string) $issVal, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Validade</label>
                <input type="date" name="valid_until" class="form-control app-input" value="<?= htmlspecialchars((string) $vuVal, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label">Observações</label>
                <textarea name="notes" class="form-control app-input" rows="2"><?= htmlspecialchars($notesVal, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </div>

    <div class="lumis-form-section mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 text-white mb-0">Itens</h3>
            <button type="button" class="btn btn-sm btn-outline-light" id="lumis-add-quote-line">+ Linha</button>
        </div>
        <div class="table-responsive">
            <table class="table lumis-table mb-0" id="quote-lines">
                <thead>
                    <tr>
                        <?php if ($quoteKind === 'product'): ?>
                            <th>Produto</th>
                        <?php else: ?>
                            <th>Serviço</th>
                        <?php endif; ?>
                        <th style="width: 90px;">Qtd</th>
                        <th style="width: 120px;">V. unit.</th>
                        <th style="width: 120px;">Desc.</th>
                        <th>Obs. linha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $idx => $ln): ?>
                        <tr class="lumis-quote-line">
                            <?php if ($quoteKind === 'product'): ?>
                                <td>
                                    <select name="items[<?= $idx ?>][product_id]" class="form-select form-select-sm app-input" required>
                                        <option value="">—</option>
                                        <?php foreach ($products as $p): ?>
                                            <?php $pid = (int) ($p['id'] ?? 0); ?>
                                            <option value="<?= $pid ?>" <?= (int) ($ln['product_id'] ?? 0) === $pid ? 'selected' : '' ?>>
                                                <?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            <?php else: ?>
                                <td>
                                    <select name="items[<?= $idx ?>][service_id]" class="form-select form-select-sm app-input" required>
                                        <option value="">—</option>
                                        <?php foreach ($services as $s): ?>
                                            <?php $sid = (int) ($s['id'] ?? 0); ?>
                                            <option value="<?= $sid ?>" <?= (int) ($ln['service_id'] ?? 0) === $sid ? 'selected' : '' ?>>
                                                <?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            <?php endif; ?>
                            <td><input type="text" name="items[<?= $idx ?>][qty]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['qty'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="text" name="items[<?= $idx ?>][unit_price]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['unit_price'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="text" name="items[<?= $idx ?>][line_discount]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['line_discount'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="text" name="items[<?= $idx ?>][description]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($ln['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-end pt-3">
        <a class="btn btn-lumis-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>

<template id="tpl-quote-line-product">
    <tr class="lumis-quote-line">
        <td>
            <select name="items[__I__][product_id]" class="form-select form-select-sm app-input" required>
                <option value="">—</option>
                <?php foreach ($products as $p): ?>
                    <?php $pid = (int) ($p['id'] ?? 0); ?>
                    <option value="<?= $pid ?>"><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="text" name="items[__I__][qty]" class="form-control form-control-sm app-input" value="1"></td>
        <td><input type="text" name="items[__I__][unit_price]" class="form-control form-control-sm app-input" value="0"></td>
        <td><input type="text" name="items[__I__][line_discount]" class="form-control form-control-sm app-input" value="0"></td>
        <td><input type="text" name="items[__I__][description]" class="form-control form-control-sm app-input" value=""></td>
    </tr>
</template>
<template id="tpl-quote-line-service">
    <tr class="lumis-quote-line">
        <td>
            <select name="items[__I__][service_id]" class="form-select form-select-sm app-input" required>
                <option value="">—</option>
                <?php foreach ($services as $s): ?>
                    <?php $sid = (int) ($s['id'] ?? 0); ?>
                    <option value="<?= $sid ?>"><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
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
  const btn = document.getElementById('lumis-add-quote-line');
  const tb = document.querySelector('#quote-lines tbody');
  const kind = <?= json_encode($quoteKind, JSON_THROW_ON_ERROR) ?>;
  const tplId = kind === 'product' ? 'tpl-quote-line-product' : 'tpl-quote-line-service';
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

<?php

declare(strict_types=1);

$stores = is_array($stores ?? null) ? $stores : [];
$products = is_array($products ?? null) ? $products : [];
$clients = is_array($clients ?? null) ? $clients : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Vendas</div>
        <h2 class="h4 mb-1 text-white">Balcão — venda rápida</h2>
        <div class="text-secondary small">Lançamento direto e finalização com baixa de estoque.</div>
    </div>
</div>

<form class="lumis-form" method="post" action="/vendas/balcao">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label lumis-label">Cliente (opcional)</label>
                <select name="client_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int) ($c['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Loja</label>
                <select name="store_id" class="form-select app-input" required>
                    <?php foreach ($stores as $s): ?>
                        <option value="<?= (int) ($s['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Desconto global (R$)</label>
                <input type="text" name="discount_total" class="form-control app-input" value="0">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label">Observações</label>
                <textarea name="notes" class="form-control app-input" rows="2"></textarea>
            </div>
        </div>
    </div>
    <div class="lumis-form-section mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 text-white mb-0">Itens</h3>
            <button type="button" class="btn btn-sm btn-outline-light" id="balcao-add">+ Linha</button>
        </div>
        <div class="table-responsive">
            <table class="table lumis-table mb-0" id="balcao-lines">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th style="width:90px;">Qtd</th>
                        <th style="width:120px;">V. unit.</th>
                        <th style="width:120px;">Desc.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="items[0][product_id]" class="form-select form-select-sm app-input" required>
                                <option value="">—</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= (int) ($p['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="items[0][qty]" class="form-control form-control-sm app-input" value="1"></td>
                        <td><input type="text" name="items[0][unit_price]" class="form-control form-control-sm app-input" value="0"></td>
                        <td><input type="text" name="items[0][line_discount]" class="form-control form-control-sm app-input" value="0"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="lumis-form-section mt-3">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="create_receivable" value="1" id="bcr">
            <label class="form-check-label text-secondary small" for="bcr">Gerar conta a receber</label>
        </div>
        <input type="date" name="receivable_due_date" class="form-control app-input mb-3" style="max-width:200px;">
        <?php if (can('vendas.balcao.create')): ?>
            <button type="submit" class="btn btn-success">Finalizar venda</button>
        <?php endif; ?>
    </div>
</form>

<template id="tpl-balcao-line">
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
  const b=document.getElementById('balcao-add'),tb=document.querySelector('#balcao-lines tbody');
  if(!b||!tb)return;let i=1;
  b.addEventListener('click',function(){const t=document.getElementById('tpl-balcao-line');if(!t)return;tb.insertAdjacentHTML('beforeend',t.innerHTML.replace(/__I__/g,String(i++)));});
})();
</script>

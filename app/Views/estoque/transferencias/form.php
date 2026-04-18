<?php

declare(strict_types=1);

$stores = is_array($stores ?? null) ? $stores : [];
$products = is_array($products ?? null) ? $products : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Nova transferência</h2>
        <div class="text-secondary small">Itens saem da origem ao concluir (estoque validado).</div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/estoque/transferencias">Voltar</a>
</div>

<form class="lumis-form" method="post" action="/estoque/transferencias">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label lumis-label">Loja origem</label>
                <select name="from_store_id" class="form-select app-input" required>
                    <?php foreach ($stores as $s): ?>
                        <option value="<?= (int) ($s['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label">Loja destino</label>
                <select name="to_store_id" class="form-select app-input" required>
                    <?php foreach ($stores as $s): ?>
                        <option value="<?= (int) ($s['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
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
            <button type="button" class="btn btn-sm btn-outline-light" id="tr-add">+ Linha</button>
        </div>
        <div class="table-responsive">
            <table class="table lumis-table mb-0" id="tr-lines">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th style="width:120px;">Qtd</th>
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
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3 d-flex gap-2 justify-content-end">
        <a class="btn btn-lumis-secondary" href="/estoque/transferencias">Cancelar</a>
        <?php if (can('estoque.transferencias.create')): ?>
            <button type="submit" class="btn btn-primary">Criar transferência</button>
        <?php endif; ?>
    </div>
</form>

<template id="tpl-tr-line">
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
    </tr>
</template>
<script>
(function(){
  const b=document.getElementById('tr-add'),tb=document.querySelector('#tr-lines tbody');
  if(!b||!tb)return;let i=1;
  b.addEventListener('click',function(){const t=document.getElementById('tpl-tr-line');if(!t)return;tb.insertAdjacentHTML('beforeend',t.innerHTML.replace(/__I__/g,String(i++)));});
})();
</script>

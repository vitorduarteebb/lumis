<?php

declare(strict_types=1);

$cfg = is_array($cfg ?? null) ? $cfg : [];
$perm = (string) ($cfg['perm'] ?? 'notas_fiscais.produtos');
$mode = (string) ($mode ?? 'create');
$bundle = is_array($bundle ?? null) ? $bundle : null;
$doc = is_array($bundle['doc'] ?? null) ? $bundle['doc'] : [];
$lines = is_array($bundle['lines'] ?? null) ? $bundle['lines'] : [];
$isEdit = $mode === 'edit' && $bundle !== null;
$id = $isEdit ? (int) ($doc['id'] ?? 0) : 0;
$lineMode = (string) ($cfg['lineMode'] ?? 'product');
$basePath = (string) ($cfg['base'] ?? '/notas-fiscais/produtos');
$clients = is_array($clients ?? null) ? $clients : [];
$suppliers = is_array($suppliers ?? null) ? $suppliers : [];
$stores = is_array($stores ?? null) ? $stores : [];
$products = is_array($products ?? null) ? $products : [];
$services = is_array($services ?? null) ? $services : [];
$purchaseOrders = is_array($purchaseOrders ?? null) ? $purchaseOrders : [];
$kind = (string) ($cfg['kind'] ?? '');

if ($lines === [] && !$isEdit) {
    $lines = [['qty' => 1, 'unit_price' => 0, 'line_discount' => 0, 'product_id' => '', 'service_id' => '']];
}
$action = $isEdit ? $basePath . '/' . $id : $basePath;
$stOpts = ['draft' => 'Digitada', 'issued' => 'Emitida', 'cancelled' => 'Cancelada', 'voided' => 'Inutilizada', 'error' => 'Erro'];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Notas fiscais</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar nota' : 'Nova nota' ?></h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <?php if ($kind !== 'purchase_in'): ?>
            <div class="col-md-6">
                <label class="form-label lumis-label">Cliente / destinatário</label>
                <select name="client_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($clients as $c): ?>
                        <?php $cid = (int) ($c['id'] ?? 0); ?>
                        <option value="<?= $cid ?>" <?= (string) ($doc['client_id'] ?? '') === (string) $cid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <?php if ($kind === 'purchase_in'): ?>
            <div class="col-md-6">
                <label class="form-label lumis-label">Fornecedor</label>
                <select name="supplier_id" class="form-select app-input" required>
                    <option value="">—</option>
                    <?php foreach ($suppliers as $s): ?>
                        <?php $sid = (int) ($s['id'] ?? 0); ?>
                        <option value="<?= $sid ?>" <?= (string) ($doc['supplier_id'] ?? '') === (string) $sid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['trade_name'] ?? $s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <label class="form-label lumis-label">Loja</label>
                <select name="store_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($stores as $s): ?>
                        <?php $sid = (int) ($s['id'] ?? 0); ?>
                        <option value="<?= $sid ?>" <?= (string) ($doc['store_id'] ?? '') === (string) $sid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Número</label>
                <input type="text" name="document_number" class="form-control app-input" value="<?= htmlspecialchars((string) ($doc['document_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label">Série</label>
                <input type="text" name="series" class="form-control app-input" value="<?= htmlspecialchars((string) ($doc['series'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Chave de acesso</label>
                <input type="text" name="access_key" maxlength="44" class="form-control app-input font-monospace small" value="<?= htmlspecialchars((string) ($doc['access_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Emissão</label>
                <input type="datetime-local" name="issued_at" class="form-control app-input" value="<?= htmlspecialchars(str_replace(' ', 'T', substr((string) ($doc['issued_at'] ?? ''), 0, 16)), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Status</label>
                <select name="status" class="form-select app-input">
                    <?php foreach ($stOpts as $k => $lab): ?>
                        <option value="<?= $k ?>" <?= (string) ($doc['status'] ?? 'draft') === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Desconto global (R$)</label>
                <input type="text" name="discount_total" class="form-control app-input" value="<?= htmlspecialchars((string) ($doc['discount_total'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <?php if ($kind === 'purchase_in' && $purchaseOrders !== []): ?>
            <div class="col-md-6">
                <label class="form-label lumis-label">Vincular compra (opcional)</label>
                <select name="purchase_order_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($purchaseOrders as $po): ?>
                        <?php $poid = (int) ($po['id'] ?? 0); ?>
                        <option value="<?= $poid ?>" <?= (string) ($doc['purchase_order_id'] ?? '') === (string) $poid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($po['document_number'] ?? '#' . $poid), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <label class="form-label lumis-label">Natureza (ID lookup)</label>
                <input type="number" name="nature_entry_id" class="form-control app-input" value="<?= (int) ($doc['nature_entry_id'] ?? 0) > 0 ? (int) $doc['nature_entry_id'] : '' ?>" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">CFOP (ID lookup)</label>
                <input type="number" name="cfop_entry_id" class="form-control app-input" value="<?= (int) ($doc['cfop_entry_id'] ?? 0) > 0 ? (int) $doc['cfop_entry_id'] : '' ?>" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Modelo (ID lookup)</label>
                <input type="number" name="model_entry_id" class="form-control app-input" value="<?= (int) ($doc['model_entry_id'] ?? 0) > 0 ? (int) $doc['model_entry_id'] : '' ?>" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label">Série cad. (ID lookup)</label>
                <input type="number" name="series_entry_id" class="form-control app-input" value="<?= (int) ($doc['series_entry_id'] ?? 0) > 0 ? (int) $doc['series_entry_id'] : '' ?>" min="0">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label">Observações</label>
                <textarea name="notes" class="form-control app-input" rows="2"><?= htmlspecialchars((string) ($doc['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label">Anexar XML (opcional)</label>
                <input type="file" name="file_xml" class="form-control app-input" accept=".xml,application/xml">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label">Anexar PDF (opcional)</label>
                <input type="file" name="file_pdf" class="form-control app-input" accept=".pdf,application/pdf">
            </div>
        </div>
    </div>
    <div class="lumis-form-section mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 text-white mb-0">Itens</h3>
            <button type="button" class="btn btn-sm btn-outline-light" id="nf-add-line">+ Linha</button>
        </div>
        <div class="table-responsive">
            <table class="table lumis-table mb-0" id="nf-lines">
                <thead>
                    <tr>
                        <?php if ($lineMode === 'service'): ?>
                            <th>Serviço</th>
                        <?php else: ?>
                            <th>Produto</th>
                        <?php endif; ?>
                        <th style="width:90px;">Qtd</th>
                        <th style="width:120px;">V. unit.</th>
                        <th style="width:110px;">Desc.</th>
                        <th>Obs. linha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $idx => $ln): ?>
                        <tr class="nf-line">
                            <?php if ($lineMode === 'service'): ?>
                                <td>
                                    <select name="items[<?= $idx ?>][service_id]" class="form-select form-select-sm app-input" required>
                                        <option value="">—</option>
                                        <?php foreach ($services as $s): ?>
                                            <?php $svid = (int) ($s['id'] ?? 0); ?>
                                            <option value="<?= $svid ?>" <?= (int) ($ln['service_id'] ?? 0) === $svid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            <?php else: ?>
                                <td>
                                    <select name="items[<?= $idx ?>][product_id]" class="form-select form-select-sm app-input" required>
                                        <option value="">—</option>
                                        <?php foreach ($products as $p): ?>
                                            <?php $pid = (int) ($p['id'] ?? 0); ?>
                                            <option value="<?= $pid ?>" <?= (int) ($ln['product_id'] ?? 0) === $pid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
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
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar' : 'Registrar' ?></button>
        <?php endif; ?>
    </div>
</form>

<?php if ($lineMode === 'service'): ?>
<template id="tpl-nf-svc">
    <tr class="nf-line">
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
<?php else: ?>
<template id="tpl-nf-prd">
    <tr class="nf-line">
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
<?php endif; ?>
<script>
(function(){
  const b=document.getElementById('nf-add-line'),tb=document.querySelector('#nf-lines tbody');
  if(!b||!tb)return;
  const tpl=document.getElementById(<?= json_encode($lineMode === 'service' ? 'tpl-nf-svc' : 'tpl-nf-prd', JSON_THROW_ON_ERROR) ?>);
  let i=tb.querySelectorAll('tr').length;
  b.addEventListener('click',function(){if(!tpl)return;tb.insertAdjacentHTML('beforeend',tpl.innerHTML.replace(/__I__/g,String(i++)));});
})();
</script>

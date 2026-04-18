<?php

declare(strict_types=1);

$mode = (string) ($mode ?? 'create');
$order = is_array($order ?? null) ? $order : null;
$items = is_array($items ?? null) ? $items : [];
$clients = is_array($clients ?? null) ? $clients : [];
$users = is_array($users ?? null) ? $users : [];
$products = is_array($products ?? null) ? $products : [];
$services = is_array($services ?? null) ? $services : [];
$quotes = is_array($quotes ?? null) ? $quotes : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];
$priorities = is_array($priorities ?? null) ? $priorities : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$isEdit = $mode === 'edit';

$stLabels = [
    'open' => 'Aberta', 'in_analysis' => 'Em análise', 'in_progress' => 'Em andamento', 'waiting_part' => 'Aguardando peça',
    'done' => 'Concluída', 'delivered' => 'Entregue', 'cancelled' => 'Cancelada',
];
$prLabels = ['low' => 'Baixa', 'normal' => 'Média', 'high' => 'Alta', 'urgent' => 'Urgente'];

$cidVal = $old['client_id'] ?? ($order['client_id'] ?? '');
$qidVal = $old['quote_id'] ?? ($order['quote_id'] ?? '');
$stVal = $old['status'] ?? ($order['status'] ?? 'open');
$prVal = $old['priority'] ?? ($order['priority'] ?? 'normal');
$aidVal = $old['assigned_user_id'] ?? ($order['assigned_user_id'] ?? '');
$osTypeVal = $old['os_type'] ?? ($order['os_type'] ?? '');
$descVal = $old['description'] ?? ($order['description'] ?? '');
$intVal = $old['internal_notes'] ?? ($order['internal_notes'] ?? '');
$custVal = $old['customer_notes'] ?? ($order['customer_notes'] ?? '');
$opVal = $old['opened_at'] ?? ($order['opened_at'] ?? '');
$exVal = $old['expected_at'] ?? ($order['expected_at'] ?? '');
$cmpVal = $old['completed_at'] ?? ($order['completed_at'] ?? '');

$action = $isEdit ? '/ordens-servico/' . (int) ($order['id'] ?? 0) : '/ordens-servico';

$rows = $items;
if ($rows === [] && $mode === 'create') {
    $rows = [['line_type' => 'service', 'qty' => 1, 'unit_price' => 0, 'line_discount' => 0, 'product_id' => '', 'service_id' => '', 'description' => '']];
}
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Ordens de serviço</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar O.S.' : 'Nova O.S.' ?></h2>
        <div class="text-secondary small">Serviços e produtos vinculados à ordem.</div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/ordens-servico">Voltar</a>
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
            <div class="col-md-6">
                <label class="form-label lumis-label">Orçamento de serviço (opcional)</label>
                <select name="quote_id" class="form-select app-input">
                    <option value="">— Origem manual —</option>
                    <?php foreach ($quotes as $q): ?>
                        <?php $qid = (int) ($q['id'] ?? 0); ?>
                        <option value="<?= $qid ?>" <?= (string) $qidVal === (string) $qid ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($q['quote_number'] ?? '#' . $qid), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                <label class="form-label lumis-label">Prioridade</label>
                <select name="priority" class="form-select app-input">
                    <?php foreach ($priorities as $pr): ?>
                        <option value="<?= htmlspecialchars($pr, ENT_QUOTES, 'UTF-8') ?>" <?= $prVal === $pr ? 'selected' : '' ?>><?= htmlspecialchars($prLabels[$pr] ?? $pr, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label">Técnico / responsável</label>
                <select name="assigned_user_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($users as $u): ?>
                        <?php $uid = (int) ($u['id'] ?? 0); ?>
                        <option value="<?= $uid ?>" <?= (string) $aidVal === (string) $uid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($u['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Tipo (catálogo)</label>
                <input type="text" name="os_type" class="form-control app-input" value="<?= htmlspecialchars((string) $osTypeVal, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex.: garantia, visita">
            </div>
            <?php if (!$isEdit): ?>
                <div class="col-md-4">
                    <label class="form-label lumis-label">Abertura</label>
                    <input type="datetime-local" name="opened_at" class="form-control app-input" value="<?= htmlspecialchars(str_replace(' ', 'T', substr((string) $opVal, 0, 16)), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            <?php endif; ?>
            <div class="col-md-4">
                <label class="form-label lumis-label">Previsão</label>
                <input type="datetime-local" name="expected_at" class="form-control app-input" value="<?= htmlspecialchars($exVal !== '' ? str_replace(' ', 'T', substr($exVal, 0, 16)) : '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Conclusão</label>
                <input type="datetime-local" name="completed_at" class="form-control app-input" value="<?= htmlspecialchars($cmpVal !== '' ? str_replace(' ', 'T', substr($cmpVal, 0, 16)) : '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label">Descrição do problema / serviço</label>
                <textarea name="description" class="form-control app-input" rows="2"><?= htmlspecialchars((string) $descVal, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label">Observações internas</label>
                <textarea name="internal_notes" class="form-control app-input" rows="2"><?= htmlspecialchars((string) $intVal, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label">Observações ao cliente</label>
                <textarea name="customer_notes" class="form-control app-input" rows="2"><?= htmlspecialchars((string) $custVal, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </div>

    <div class="lumis-form-section mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 text-white mb-0">Itens (serviços e produtos)</h3>
            <button type="button" class="btn btn-sm btn-outline-light" id="lumis-add-os-line">+ Linha</button>
        </div>
        <div class="table-responsive">
            <table class="table lumis-table mb-0" id="os-lines">
                <thead>
                    <tr>
                        <th style="width: 120px;">Tipo</th>
                        <th>Item</th>
                        <th style="width: 80px;">Qtd</th>
                        <th style="width: 110px;">V. unit.</th>
                        <th style="width: 110px;">Desc.</th>
                        <th>Obs.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $idx => $ln): ?>
                        <?php
                        $lk = !empty($ln['product_id']) && (int) $ln['product_id'] > 0 ? 'product' : 'service';
                        ?>
                        <tr class="lumis-os-line">
                            <td>
                                <select name="items[<?= $idx ?>][line_type]" class="form-select form-select-sm app-input lumis-os-line-type">
                                    <option value="service" <?= $lk === 'service' ? 'selected' : '' ?>>Serviço</option>
                                    <option value="product" <?= $lk === 'product' ? 'selected' : '' ?>>Produto</option>
                                </select>
                            </td>
                            <td>
                                <div class="lumis-os-cell-svc" style="<?= $lk === 'product' ? 'display:none' : '' ?>">
                                    <select name="items[<?= $idx ?>][service_id]" class="form-select form-select-sm app-input">
                                        <option value="">—</option>
                                        <?php foreach ($services as $s): ?>
                                            <?php $sid = (int) ($s['id'] ?? 0); ?>
                                            <option value="<?= $sid ?>" <?= (int) ($ln['service_id'] ?? 0) === $sid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="lumis-os-cell-prd" style="<?= $lk === 'service' ? 'display:none' : '' ?>">
                                    <select name="items[<?= $idx ?>][product_id]" class="form-select form-select-sm app-input">
                                        <option value="">—</option>
                                        <?php foreach ($products as $p): ?>
                                            <?php $pid = (int) ($p['id'] ?? 0); ?>
                                            <option value="<?= $pid ?>" <?= (int) ($ln['product_id'] ?? 0) === $pid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
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
        <a class="btn btn-lumis-secondary" href="/ordens-servico">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>

<template id="tpl-os-line">
    <tr class="lumis-os-line">
        <td>
            <select name="items[__I__][line_type]" class="form-select form-select-sm app-input lumis-os-line-type">
                <option value="service" selected>Serviço</option>
                <option value="product">Produto</option>
            </select>
        </td>
        <td>
            <div class="lumis-os-cell-svc">
                <select name="items[__I__][service_id]" class="form-select form-select-sm app-input">
                    <option value="">—</option>
                    <?php foreach ($services as $s): ?>
                        <?php $sid = (int) ($s['id'] ?? 0); ?>
                        <option value="<?= $sid ?>"><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="lumis-os-cell-prd" style="display:none">
                <select name="items[__I__][product_id]" class="form-select form-select-sm app-input">
                    <option value="">—</option>
                    <?php foreach ($products as $p): ?>
                        <?php $pid = (int) ($p['id'] ?? 0); ?>
                        <option value="<?= $pid ?>"><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </td>
        <td><input type="text" name="items[__I__][qty]" class="form-control form-control-sm app-input" value="1"></td>
        <td><input type="text" name="items[__I__][unit_price]" class="form-control form-control-sm app-input" value="0"></td>
        <td><input type="text" name="items[__I__][line_discount]" class="form-control form-control-sm app-input" value="0"></td>
        <td><input type="text" name="items[__I__][description]" class="form-control form-control-sm app-input" value=""></td>
    </tr>
</template>
<script>
(function(){
  const btn = document.getElementById('lumis-add-os-line');
  const tb = document.querySelector('#os-lines tbody');
  const tpl = document.getElementById('tpl-os-line');
  if (!btn || !tb || !tpl) return;
  let idx = tb.querySelectorAll('tr').length;
  function bindRow(tr){
    const sel = tr.querySelector('.lumis-os-line-type');
    if (!sel) return;
    const toggle = function(){
      const v = sel.value;
      const s = tr.querySelector('.lumis-os-cell-svc');
      const p = tr.querySelector('.lumis-os-cell-prd');
      if (s && p) {
        if (v === 'product') { s.style.display='none'; p.style.display=''; }
        else { s.style.display=''; p.style.display='none'; }
      }
    };
    sel.addEventListener('change', toggle);
    toggle();
  }
  tb.querySelectorAll('tr.lumis-os-line').forEach(bindRow);
  btn.addEventListener('click', function(){
    const html = tpl.innerHTML.replace(/__I__/g, String(idx++));
    const wrap = document.createElement('tbody');
    wrap.innerHTML = html.trim();
    const tr = wrap.querySelector('tr');
    if (!tr) return;
    tb.appendChild(tr);
    bindRow(tr);
  });
})();
</script>

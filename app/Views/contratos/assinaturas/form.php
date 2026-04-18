<?php
declare(strict_types=1);
$mode = (string) ($mode ?? 'create');
$row = is_array($row ?? null) ? $row : null;
$isEdit = $mode === 'edit' && $row !== null;
$id = $isEdit ? (int) ($row['id'] ?? 0) : 0;
$clients = is_array($clients ?? null) ? $clients : [];
$stores = is_array($stores ?? null) ? $stores : [];
$periodicities = is_array($periodicities ?? null) ? $periodicities : [];
$action = $isEdit ? '/contratos/assinaturas/' . $id : '/contratos/assinaturas';
$stOpts = ['active' => 'Ativa', 'suspended' => 'Suspensa', 'closed' => 'Encerrada', 'cancelled' => 'Cancelada'];
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Contratos</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar assinatura' : 'Nova assinatura' ?></h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/contratos/assinaturas">Voltar</a>
</div>
<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section row g-3">
        <div class="col-md-4">
            <label class="form-label lumis-label">Número</label>
            <input type="text" name="subscription_number" class="form-control app-input" value="<?= htmlspecialchars((string) ($row['subscription_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label lumis-label">Cliente *</label>
            <select name="client_id" class="form-select app-input" required>
                <option value="">—</option>
                <?php foreach ($clients as $c): ?>
                    <?php $cid = (int) ($c['id'] ?? 0); ?>
                    <option value="<?= $cid ?>" <?= (string) ($row['client_id'] ?? '') === (string) $cid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label lumis-label">Loja</label>
            <select name="store_id" class="form-select app-input">
                <option value="">—</option>
                <?php foreach ($stores as $s): ?>
                    <?php $sid = (int) ($s['id'] ?? 0); ?>
                    <option value="<?= $sid ?>" <?= (string) ($row['store_id'] ?? '') === (string) $sid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label lumis-label">Plano / descrição</label>
            <input type="text" name="plan_description" class="form-control app-input" value="<?= htmlspecialchars((string) ($row['plan_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label lumis-label">Valor recorrente (R$)</label>
            <input type="text" name="recurring_amount" class="form-control app-input" value="<?= htmlspecialchars((string) ($row['recurring_amount'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label lumis-label">Periodicidade</label>
            <select name="periodicity_entry_id" class="form-select app-input">
                <option value="">—</option>
                <?php foreach ($periodicities as $p): ?>
                    <?php if ((int) ($p['status'] ?? 1) !== 1) {
                        continue;
                    } ?>
                    <?php $pid = (int) ($p['id'] ?? 0); ?>
                    <option value="<?= $pid ?>" <?= (string) ($row['periodicity_entry_id'] ?? '') === (string) $pid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label lumis-label">Início</label>
            <input type="date" name="start_date" class="form-control app-input" value="<?= htmlspecialchars(substr((string) ($row['start_date'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label lumis-label">Próxima cobrança</label>
            <input type="date" name="next_billing_date" class="form-control app-input" value="<?= htmlspecialchars(substr((string) ($row['next_billing_date'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label lumis-label">Status</label>
            <select name="status" class="form-select app-input">
                <?php foreach ($stOpts as $k => $lab): ?>
                    <option value="<?= $k ?>" <?= (string) ($row['status'] ?? 'active') === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label lumis-label">Observações</label>
            <textarea name="notes" class="form-control app-input" rows="2"><?= htmlspecialchars((string) ($row['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label lumis-label">Anexo (opcional)</label>
            <input type="file" name="attachment" class="form-control app-input">
        </div>
    </div>
    <div class="mt-3 d-flex gap-2 justify-content-end">
        <a class="btn btn-lumis-secondary" href="/contratos/assinaturas">Cancelar</a>
        <?php if ($isEdit ? can('contratos.assinaturas.edit') : can('contratos.assinaturas.create')): ?>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar' : 'Registrar' ?></button>
        <?php endif; ?>
    </div>
</form>

<?php
declare(strict_types=1);

/** @var array<string, mixed> $subscription */
$s = is_array($subscription ?? null) ? $subscription : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white">Meu plano</h2>
        <div class="text-secondary small">Plano contratado, limites e renovação.</div>
    </div>
</div>

<form class="lumis-form" method="post" action="/configuracoes/meu-plano">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label lumis-label" for="plan_name">Plano</label>
                <input type="text" class="form-control app-input" id="plan_name" name="plan_name" value="<?= htmlspecialchars((string) ($s['plan_name'] ?? 'Standard'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="status">Status</label>
                <select class="form-select app-input" id="status" name="status">
                    <?php $st = (string) ($s['status'] ?? 'active'); ?>
                    <option value="active" <?= $st === 'active' ? 'selected' : '' ?>>Ativo</option>
                    <option value="suspended" <?= $st === 'suspended' ? 'selected' : '' ?>>Suspenso</option>
                    <option value="cancelled" <?= $st === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label" for="max_users">Máx. usuários</label>
                <input type="number" min="1" class="form-control app-input" id="max_users" name="max_users" value="<?= (int) ($s['max_users'] ?? 50) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="renews_at">Renova em</label>
                <input type="date" class="form-control app-input" id="renews_at" name="renews_at" value="<?= htmlspecialchars((string) ($s['renews_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label" for="notes">Observações internas</label>
                <textarea class="form-control app-input" id="notes" name="notes" rows="3"><?= htmlspecialchars((string) ($s['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </div>
    <?php if (can('configuracoes.meu_plano.edit')): ?>
        <button type="submit" class="btn btn-primary rounded-3">Salvar</button>
    <?php endif; ?>
</form>

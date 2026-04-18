<?php
declare(strict_types=1);
$row = is_array($row ?? null) ? $row : [];
$id = (int) ($row['id'] ?? 0);
$st = (string) ($row['status'] ?? '');
$stLab = ['active' => 'Ativa', 'suspended' => 'Suspensa', 'closed' => 'Encerrada', 'cancelled' => 'Cancelada'];
$att = (string) ($row['attachment_path'] ?? '');
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Contratos</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($row['subscription_number'] ?? 'Assinatura'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">
            <span class="badge <?= $st === 'active' ? 'badge-lumis-success' : ($st === 'cancelled' ? 'text-bg-secondary' : 'badge-lumis-warning') ?>"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
            · <?= htmlspecialchars((string) ($row['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/contratos/assinaturas">Voltar</a>
        <?php if ($att !== ''): ?>
            <a class="btn btn-outline-light btn-sm rounded-3" href="/contratos/assinaturas/<?= $id ?>/anexo" target="_blank" rel="noopener">Anexo</a>
        <?php endif; ?>
        <?php if (can('contratos.assinaturas.edit') && !in_array($st, ['cancelled', 'closed'], true)): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="/contratos/assinaturas/<?= $id ?>/editar">Editar</a>
        <?php endif; ?>
    </div>
</div>
<div class="lumis-form-section mb-3 row g-2 text-secondary small">
    <div class="col-12"><span class="text-white">Plano:</span> <?= htmlspecialchars((string) ($row['plan_description'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
    <div class="col-md-4"><span class="text-white">Valor:</span> <?= htmlspecialchars(lumis_money_br((float) ($row['recurring_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
    <div class="col-md-4"><span class="text-white">Próx. cobrança:</span> <?= htmlspecialchars(substr((string) ($row['next_billing_date'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?></div>
    <?php if (!empty($row['notes'])): ?>
        <div class="col-12"><span class="text-white">Obs.:</span> <?= nl2br(htmlspecialchars((string) $row['notes'], ENT_QUOTES, 'UTF-8')) ?></div>
    <?php endif; ?>
</div>
<?php if (can('contratos.assinaturas.edit') && !in_array($st, ['cancelled', 'closed'], true)): ?>
    <div class="d-flex flex-wrap gap-2">
        <?php if ($st === 'active'): ?>
            <form method="post" action="/contratos/assinaturas/<?= $id ?>/suspender" onsubmit="return confirm('Suspender?');"><?= \App\Helpers\Csrf::field() ?><button type="submit" class="btn btn-outline-warning btn-sm">Suspender</button></form>
        <?php elseif ($st === 'suspended'): ?>
            <form method="post" action="/contratos/assinaturas/<?= $id ?>/reativar"><?= \App\Helpers\Csrf::field() ?><button type="submit" class="btn btn-outline-success btn-sm">Reativar</button></form>
        <?php endif; ?>
        <form method="post" action="/contratos/assinaturas/<?= $id ?>/cancelar" onsubmit="return confirm('Cancelar assinatura?');"><?= \App\Helpers\Csrf::field() ?><button type="submit" class="btn btn-outline-danger btn-sm">Cancelar</button></form>
    </div>
<?php endif; ?>

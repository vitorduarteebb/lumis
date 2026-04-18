<?php
declare(strict_types=1);

/** @var array<string, string> $eventLabels */
/** @var array<string, int> $enabledMap */
/** @var array<string, int|null> $templateMap */
$eventLabels = is_array($eventLabels ?? null) ? $eventLabels : [];
$enabledMap = is_array($enabledMap ?? null) ? $enabledMap : [];
$templateMap = is_array($templateMap ?? null) ? $templateMap : [];
$emailTemplates = is_array($emailTemplates ?? null) ? $emailTemplates : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white">Avisos por e-mail</h2>
        <div class="text-secondary small">Ative ou desative alertas automáticos (envio real depende do servidor de e-mail configurado).</div>
    </div>
</div>

<form class="lumis-form" method="post" action="/configuracoes/avisos-email">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <?php foreach ($eventLabels as $key => $label): ?>
                <?php $en = (int) ($enabledMap[$key] ?? 1); ?>
                <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom border-secondary-subtle py-2">
                    <div class="flex-grow-1">
                        <div class="text-white"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-secondary small font-monospace"><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select name="template_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" class="form-select form-select-sm app-input" style="min-width: 220px;" title="Modelo de e-mail">
                            <option value="0">— modelo —</option>
                            <?php
                            $selTpl = (int) ($templateMap[$key] ?? 0);
                            ?>
                            <?php foreach ($emailTemplates as $t): ?>
                                <?php $tid = (int) ($t['id'] ?? 0); ?>
                                <option value="<?= $tid ?>" <?= $selTpl === $tid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($t['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="ev_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" name="event_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" value="1" <?= $en === 1 ? 'checked' : '' ?>>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if (can('configuracoes.avisos_email.edit')): ?>
        <button type="submit" class="btn btn-primary rounded-3 mt-3">Salvar preferências</button>
    <?php endif; ?>
</form>

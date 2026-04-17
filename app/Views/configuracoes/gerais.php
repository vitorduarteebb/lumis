<?php
declare(strict_types=1);

/** @var array<string, mixed> $company */
/** @var array<string, mixed> $profile */
$company = is_array($company ?? null) ? $company : [];
$profile = is_array($profile ?? null) ? $profile : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white">Gerais</h2>
        <div class="text-secondary small">Nome exibido, empresa matriz e preferências regionais.</div>
    </div>
</div>

<form class="lumis-form" method="post" action="/configuracoes/gerais">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Identidade</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label lumis-label" for="app_title">Nome exibido no sistema</label>
                <input type="text" class="form-control app-input" id="app_title" name="app_title" value="<?= htmlspecialchars((string) ($profile['app_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex.: Lumis ERP">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="company_name">Razão social / nome da empresa (matriz)</label>
                <input type="text" class="form-control app-input" id="company_name" name="company_name" value="<?= htmlspecialchars((string) ($company['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
        </div>
    </div>
    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Regional</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label lumis-label" for="timezone">Fuso horário</label>
                <input type="text" class="form-control app-input" id="timezone" name="timezone" value="<?= htmlspecialchars((string) ($profile['timezone'] ?? 'America/Sao_Paulo'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="locale">Idioma / locale</label>
                <input type="text" class="form-control app-input" id="locale" name="locale" value="<?= htmlspecialchars((string) ($profile['locale'] ?? 'pt_BR'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>
    </div>
    <?php if (can('configuracoes.gerais.edit')): ?>
        <button type="submit" class="btn btn-primary rounded-3">Salvar</button>
    <?php endif; ?>
</form>

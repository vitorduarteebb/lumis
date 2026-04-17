<?php
declare(strict_types=1);

/** @var array<string, mixed> $profile */
$p = is_array($profile ?? null) ? $profile : [];
$logo = (string) ($p['logo_path'] ?? '');
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white">Marca da empresa</h2>
        <div class="text-secondary small">Logotipo e cores para relatórios e telas.</div>
    </div>
</div>

<?php if ($logo !== ''): ?>
    <div class="mb-3 p-3 rounded-3 border border-secondary-subtle d-inline-block">
        <div class="text-secondary small mb-1">Logo atual</div>
        <img src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') ?>" alt="Logo" style="max-height: 64px;">
    </div>
<?php endif; ?>

<form class="lumis-form" method="post" action="/configuracoes/marca-empresa" enctype="multipart/form-data">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label lumis-label" for="logo">Enviar logo (PNG, JPEG ou WebP)</label>
                <input type="file" class="form-control app-input" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="primary_color">Cor primária</label>
                <input type="text" class="form-control app-input" id="primary_color" name="primary_color" value="<?= htmlspecialchars((string) ($p['primary_color'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="#0d6efd">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="accent_color">Cor de destaque</label>
                <input type="text" class="form-control app-input" id="accent_color" name="accent_color" value="<?= htmlspecialchars((string) ($p['accent_color'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="#6f42c1">
            </div>
        </div>
    </div>
    <?php if (can('configuracoes.marca_empresa.edit')): ?>
        <button type="submit" class="btn btn-primary rounded-3">Salvar</button>
    <?php endif; ?>
</form>

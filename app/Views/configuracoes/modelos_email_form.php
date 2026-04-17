<?php
declare(strict_types=1);

/** @var string $mode */
/** @var array<string, mixed>|null $template */
/** @var array<string, string> $errors */
/** @var array<string, mixed> $old */

$mode = $mode ?? 'create';
$template = is_array($template ?? null) ? $template : null;
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$isEdit = $mode === 'edit';

$val = static function (string $key, string $default = '') use ($old, $template, $isEdit): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($isEdit && $template !== null && array_key_exists($key, $template) && $template[$key] !== null) {
        return (string) $template[$key];
    }

    return $default;
};

$action = $isEdit ? '/configuracoes/modelos-email/' . (int) ($template['id'] ?? 0) : '/configuracoes/modelos-email';
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar modelo' : 'Novo modelo' ?></h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/configuracoes/modelos-email">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label lumis-label" for="name">Nome <span class="text-danger">*</span></label>
                <input type="text" class="form-control app-input <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" required value="<?= htmlspecialchars($val('name'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="slug">Slug <span class="text-danger">*</span></label>
                <input type="text" class="form-control app-input <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" id="slug" name="slug" required value="<?= htmlspecialchars($val('slug'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['slug'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['slug'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="status">Status</label>
                <select class="form-select app-input" id="status" name="status">
                    <?php $st = (int) $val('status', '1'); ?>
                    <option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label lumis-label" for="subject">Assunto <span class="text-danger">*</span></label>
                <input type="text" class="form-control app-input <?= isset($errors['subject']) ? 'is-invalid' : '' ?>" id="subject" name="subject" required value="<?= htmlspecialchars($val('subject'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['subject'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['subject'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label lumis-label" for="body_html">Corpo HTML <span class="text-danger">*</span></label>
                <textarea class="form-control app-input font-monospace <?= isset($errors['body_html']) ? 'is-invalid' : '' ?>" id="body_html" name="body_html" rows="14" required><?= htmlspecialchars($val('body_html'), ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php if (isset($errors['body_html'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['body_html'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
        </div>
    </div>
    <?php if (can('configuracoes.modelos_email.edit')): ?>
        <button type="submit" class="btn btn-primary rounded-3"><?= $isEdit ? 'Salvar alterações' : 'Criar modelo' ?></button>
    <?php endif; ?>
</form>

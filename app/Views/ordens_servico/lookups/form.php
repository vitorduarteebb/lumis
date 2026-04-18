<?php

declare(strict_types=1);

$mode = $mode ?? 'create';
$row = is_array($row ?? null) ? $row : null;
$entryType = (string) ($entryType ?? 'os_status');
$typeLabel = (string) ($typeLabel ?? '');
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$isEdit = $mode === 'edit';
$action = $isEdit ? '/ordens-servico/opcoes-auxiliares/' . (int) ($row['id'] ?? 0) : '/ordens-servico/opcoes-auxiliares';
$val = static function (string $key, string $default = '') use ($old, $row, $isEdit): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($isEdit && $row !== null && isset($row[$key]) && $row[$key] !== null) {
        return (string) $row[$key];
    }

    return $default;
};
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Ordens de serviço</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar item' : 'Novo item' ?></h2>
        <div class="text-secondary small"><?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/ordens-servico/opcoes-auxiliares?type=<?= htmlspecialchars(rawurlencode($entryType), ENT_QUOTES, 'UTF-8') ?>">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>
    <?php if (!$isEdit): ?>
        <input type="hidden" name="entry_type" value="<?= htmlspecialchars($entryType, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label lumis-label" for="name">Nome <span class="text-danger">*</span></label>
                <input type="text" class="form-control app-input <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" required value="<?= htmlspecialchars($val('name'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="slug">Slug (identificador)</label>
                <input type="text" class="form-control app-input <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" id="slug" name="slug" value="<?= htmlspecialchars($val('slug'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['slug'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['slug'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="sort_order">Ordem</label>
                <input type="number" class="form-control app-input" id="sort_order" name="sort_order" value="<?= htmlspecialchars($val('sort_order', '0'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="status">Status</label>
                <?php $st = (int) $val('status', '1'); ?>
                <select class="form-select app-input" id="status" name="status">
                    <option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2 justify-content-end pt-2">
        <a class="btn btn-lumis-secondary" href="/ordens-servico/opcoes-auxiliares?type=<?= htmlspecialchars(rawurlencode($entryType), ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar' : 'Cadastrar' ?></button>
    </div>
</form>

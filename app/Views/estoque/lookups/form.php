<?php
declare(strict_types=1);
$mode = $mode ?? 'create';
$row = is_array($row ?? null) ? $row : null;
$entryType = (string) ($entryType ?? 'estoque_adjust_reason');
$typeLabel = (string) ($typeLabel ?? '');
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$isEdit = $mode === 'edit';
$base = '/estoque/opcoes-auxiliares';
$action = $isEdit ? $base . '/' . (int) ($row['id'] ?? 0) : $base;
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
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar item' : 'Novo item' ?></h2>
        <div class="text-secondary small"><?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="<?= $base ?>?type=<?= htmlspecialchars(rawurlencode($entryType), ENT_QUOTES, 'UTF-8') ?>">Voltar</a>
</div>
<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>
    <?php if (!$isEdit): ?>
        <input type="hidden" name="entry_type" value="<?= htmlspecialchars($entryType, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <div class="lumis-form-section row g-3">
        <div class="col-md-8">
            <label class="form-label lumis-label">Nome *</label>
            <input type="text" class="form-control app-input <?= isset($errors['name']) ? 'is-invalid' : '' ?>" name="name" required value="<?= htmlspecialchars($val('name'), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label lumis-label">Slug</label>
            <input type="text" class="form-control app-input" name="slug" value="<?= htmlspecialchars($val('slug'), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label lumis-label">Ordem</label>
            <input type="number" class="form-control app-input" name="sort_order" value="<?= htmlspecialchars($val('sort_order', '0'), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label lumis-label">Status</label>
            <?php $st = (int) $val('status', '1'); ?>
            <select class="form-select app-input" name="status"><option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option><option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option></select>
        </div>
    </div>
    <div class="d-flex gap-2 justify-content-end pt-3">
        <a class="btn btn-lumis-secondary" href="<?= $base ?>?type=<?= htmlspecialchars(rawurlencode($entryType), ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>

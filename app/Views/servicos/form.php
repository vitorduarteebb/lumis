<?php

declare(strict_types=1);

/** @var string $mode */
/** @var array<string, mixed>|null $service */
/** @var array<string, string> $errors */
/** @var array<string, mixed> $old */

$mode = $mode ?? 'create';
$service = is_array($service ?? null) ? $service : null;
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$isEdit = $mode === 'edit';
$action = $isEdit ? '/servicos/' . (int) ($service['id'] ?? 0) : '/servicos';

$val = static function (string $key, string $default = '') use ($old, $service, $isEdit): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($isEdit && $service !== null && array_key_exists($key, $service) && $service[$key] !== null) {
        $v = $service[$key];
        if (is_float($v) || is_int($v)) {
            return (string) $v;
        }

        return (string) $v;
    }

    return $default;
};
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Serviços</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar serviço' : 'Novo serviço' ?></h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/servicos">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Dados do serviço</div>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label lumis-label" for="name">Nome <span class="text-danger">*</span></label>
                <input type="text" class="form-control app-input <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" required value="<?= htmlspecialchars($val('name'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="category">Categoria</label>
                <input type="text" class="form-control app-input" id="category" name="category" value="<?= htmlspecialchars($val('category'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="price">Preço <span class="text-danger">*</span></label>
                <input type="text" inputmode="decimal" class="form-control app-input <?= isset($errors['price']) ? 'is-invalid' : '' ?>" id="price" name="price" value="<?= htmlspecialchars($val('price', '0'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['price'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="duration_minutes">Duração estimada (minutos)</label>
                <input type="text" inputmode="numeric" class="form-control app-input <?= isset($errors['duration_minutes']) ? 'is-invalid' : '' ?>" id="duration_minutes" name="duration_minutes" value="<?= htmlspecialchars($val('duration_minutes'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['duration_minutes'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['duration_minutes'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="status">Status</label>
                <?php $st = (int) $val('status', '1'); ?>
                <select class="form-select app-input" id="status" name="status">
                    <option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label lumis-label" for="description">Descrição</label>
                <textarea class="form-control app-input" id="description" name="description" rows="4"><?= htmlspecialchars($val('description'), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-end pt-2">
        <a class="btn btn-lumis-secondary" href="/servicos">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar' : 'Cadastrar' ?></button>
    </div>
</form>
